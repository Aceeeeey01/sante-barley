<?php
require __DIR__ . '/../includes/functions.php';
require_admin();

$id = (int) ($_GET['id'] ?? 0);
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    try {
        $db->beginTransaction();

        $o = q('SELECT * FROM orders WHERE id=? FOR UPDATE', [$id])->fetch();
        if (!$o) {
            throw new RuntimeException('Order not found.');
        }

        $next = $_POST['order_status'] ?? '';
        $transitions = [
            'Pending'    => ['Confirmed', 'Cancelled'],
            'Confirmed'  => ['Processing', 'Cancelled'],
            'Processing' => ['Shipped', 'Cancelled'],
            'Shipped'    => ['Delivered'],
            'Delivered'  => [],
            'Cancelled'  => [],
        ];

        if (
            $next !== $o['order_status']
            && !in_array($next, $transitions[$o['order_status']] ?? [], true)
        ) {
            throw new RuntimeException('That status transition is not allowed. Follow the order sequence.');
        }

        if ($next === 'Cancelled' && $o['order_status'] !== 'Cancelled') {
            if ($o['payment_status'] === 'Paid') {
                throw new RuntimeException('Record the verified refund before cancelling a paid order.');
            }
            foreach (q('SELECT * FROM order_items WHERE order_id=? ORDER BY product_id', [$id])->fetchAll() as $i) {
                q(
                    'UPDATE products SET stock=stock+?,updated_at=NOW() WHERE id=?',
                    [$i['quantity'], $i['product_id']]
                );
            }
        }

        q('UPDATE orders SET order_status=?,updated_at=NOW() WHERE id=?', [$next, $id]);

        if ($next !== $o['order_status']) {
            q('INSERT INTO notifications(user_id,content) VALUES (?,?)', [
                $o['user_id'],
                'Order ' . $o['order_number'] . ' is now ' . strtolower($next) . '.',
            ]);
        }

        $db->commit();
        flash('Order status updated.');
        redirect('/admin/order-details.php?id=' . $id);
    } catch (Throwable $e) {
        if ($db->inTransaction()) {
            $db->rollBack();
        }
        $error = $e instanceof RuntimeException && !($e instanceof PDOException)
            ? $e->getMessage()
            : 'Unable to update the order.';
    }
}

$o = q(
    'SELECT o.*, u.full_name, u.email
     FROM orders o
     JOIN users u ON u.id=o.user_id
     WHERE o.id=?',
    [$id]
)->fetch();

if (!$o) {
    http_response_code(404);
    exit('Order not found.');
}

$decoded = json_decode($o['shipping_address'], true);
$address = is_array($decoded) ? $decoded : [];

$pageTitle = $o['order_number'];
require __DIR__ . '/../includes/admin-start.php';
?>
<div class="page-heading">
    <h1><?= e($o['order_number']) ?></h1>
    <p><?= e($o['full_name']) ?> · <?= e($o['email']) ?> · <?= date('F d, Y', strtotime($o['created_at'])) ?></p>
</div>

<?php if ($error): ?>
    <div class="error-box"><?= e($error) ?></div>
<?php endif; ?>

<div class="panel">
    <form method="post" class="inline-form">
        <?= csrf() ?>
        <label for="order-status">Order status</label>
        <select id="order-status" name="order_status">
            <?php foreach (['Pending', 'Confirmed', 'Processing', 'Shipped', 'Delivered', 'Cancelled'] as $s): ?>
                <option <?= $o['order_status'] === $s ? 'selected' : '' ?>><?= $s ?></option>
            <?php endforeach; ?>
        </select>
        <button class="button">Update status</button>
    </form>
    <p class="fine-print" style="text-align:left">
        Pending → Confirmed → Processing → Shipped → Delivered. Orders can be cancelled before shipping; stock is restored once.
    </p>
</div>

<div class="table-wrap" style="margin-top:25px">
    <table>
        <thead>
        <tr><th>Product</th><th>Quantity</th><th>Price</th><th>Subtotal</th></tr>
        </thead>
        <tbody>
        <?php foreach (q('SELECT * FROM order_items WHERE order_id=?', [$id]) as $i): ?>
            <tr>
                <td><?= e($i['product_name']) ?></td>
                <td><?= $i['quantity'] ?></td>
                <td><?= money($i['price']) ?></td>
                <td><?= money($i['subtotal']) ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>

<div class="form-grid" style="margin-top:25px">
    <div class="panel">
        <h2>Delivery details</h2>
        <?php foreach ($address as $key => $value): ?>
            <p style="margin-bottom:6px;font-size:12px">
                <strong><?= e(ucwords(str_replace('_', ' ', $key))) ?>:</strong> <?= e($value) ?>
            </p>
        <?php endforeach; ?>
        <p>Notes: <?= e($o['notes']) ?></p>
    </div>
    <div class="panel">
        <h2>Order summary</h2>
        <div class="summary-line"><span>Subtotal</span><span><?= money($o['subtotal']) ?></span></div>
        <div class="summary-line"><span>Shipping</span><span><?= money($o['shipping_fee']) ?></span></div>
        <div class="summary-line summary-total"><span>Total</span><span><?= money($o['total_amount']) ?></span></div>
        <p><?= e($o['payment_method']) ?> · <?= e($o['payment_status']) ?></p>
        <a href="/admin/payments.php" class="text-link">Manage payments</a>
    </div>
</div>
<?php require __DIR__ . '/../includes/admin-end.php'; ?>