<?php
require __DIR__ . '/../includes/functions.php';
require_admin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $id     = (int) ($_POST['id'] ?? 0);
    $status = $_POST['status'] ?? '';

    try {
        $db->beginTransaction();

        $o = q('SELECT * FROM orders WHERE id=? FOR UPDATE', [$id])->fetch();

        $allowed = [
            'Unpaid'   => ['Paid'],
            'Paid'     => ['Refunded'],
            'Refunded' => [],
        ];

        if (
            !$o
            || !isset($allowed[$o['payment_status']])
            || !in_array($status, $allowed[$o['payment_status']], true)
            || ($o['order_status'] === 'Cancelled' && $status === 'Paid')
        ) {
            throw new RuntimeException('This payment status transition is not allowed.');
        }

        q('UPDATE orders SET payment_status=?,updated_at=NOW() WHERE id=?', [$status, $id]);
        q('UPDATE payments SET status=? WHERE order_id=?', [$status, $id]);
        q('INSERT INTO notifications(user_id,content) VALUES (?,?)', [
            $o['user_id'],
            'Payment for order ' . $o['order_number'] . ' is now ' . strtolower($status) . '.',
        ]);

        $db->commit();
        flash('Verified payment status recorded.');
    } catch (Throwable $e) {
        if ($db->inTransaction()) {
            $db->rollBack();
        }
        flash(
            $e instanceof PDOException ? 'Unable to update payment.' : $e->getMessage(),
            'error'
        );
    }
    redirect('/admin/payments.php');
}

$pageTitle = 'Payments';
require __DIR__ . '/../includes/admin-start.php';

$methods = payment_methods();

$filter = $_GET['method'] ?? '';
$validFilter = in_array($filter, payment_method_names(), true);

$payments = q(
    'SELECT p.*, o.order_number, o.payment_status AS order_payment_status, o.user_id
     FROM payments p
     JOIN orders o ON o.id = p.order_id'
    . ($validFilter ? ' WHERE p.method = ?' : '')
    . ' ORDER BY p.created_at DESC',
    $validFilter ? [$filter] : []
)->fetchAll();

/* Optional summary counts by method */
$summary = q(
    "SELECT method,
            COUNT(*) AS total,
            COUNT(*) FILTER (WHERE status = 'Paid')     AS paid,
            COUNT(*) FILTER (WHERE status = 'Unpaid')   AS unpaid,
            COUNT(*) FILTER (WHERE status = 'Refunded') AS refunded,
            COALESCE(SUM(amount) FILTER (WHERE status = 'Paid'), 0) AS paid_amount
     FROM payments
     GROUP BY method
     ORDER BY method"
)->fetchAll();
?>
<div class="page-heading">
    <h1>Payment records.</h1>
    <p>Mark payments only after verifying receipt or completing a refund outside the store.</p>
</div>

<p class="notice">
    These controls record verified payments. They do not collect money or issue refunds. GCash, Maya, GrabPay, ShopeePay, and bank transfers require the store to publish verified payment instructions and confirm receipt. Credit / Debit Card requires a live gateway integration and is not processed here.
</p>

<?php if ($summary): ?>
    <div class="stat-grid" style="margin:25px 0">
        <?php foreach ($summary as $s):
            $info = $methods[$s['method']] ?? null;
            ?>
            <div class="stat-card">
                <i class="fa-solid fa-<?= e($info['icon'] ?? 'receipt') ?>"></i>
                <span><?= e($s['method']) ?></span>
                <strong><?= money($s['paid_amount']) ?></strong>
                <small style="display:block;font-size:10px;color:#859277;margin-top:6px">
                    <?= (int) $s['paid'] ?> paid ·
                    <?= (int) $s['unpaid'] ?> unpaid ·
                    <?= (int) $s['refunded'] ?> refunded
                </small>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<form class="shop-toolbar">
    <label class="field">Payment method
        <select name="method">
            <option value="">All methods</option>
            <?php foreach ($methods as $value => $m): ?>
                <option value="<?= e($value) ?>" <?= $filter === $value ? 'selected' : '' ?>>
                    <?= e($m['label']) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </label>
    <button class="button">Filter</button>
    <?php if ($validFilter): ?>
        <a class="text-link" href="/admin/payments.php" style="margin-left:10px">Reset</a>
    <?php endif; ?>
</form>

<div class="table-wrap">
    <table>
        <thead>
        <tr>
            <th>Order</th>
            <th>Method</th>
            <th>Amount</th>
            <th>Status</th>
            <th>Record verified status</th>
        </tr>
        </thead>
        <tbody>
        <?php if (!$payments): ?>
            <tr>
                <td colspan="5" style="text-align:center;padding:25px;color:#859277">
                    No payments yet.
                </td>
            </tr>
        <?php endif; ?>

        <?php foreach ($payments as $p):
            $info = $methods[$p['method']] ?? null;
            $canRecord = in_array($p['status'], ['Unpaid', 'Paid'], true);
            ?>
            <tr>
                <td>
                    <a href="/admin/order-details.php?id=<?= $p['order_id'] ?>">
                        <?= e($p['order_number']) ?>
                    </a>
                </td>
                <td>
                    <?php if ($info): ?>
                        <i class="fa-solid fa-<?= e($info['icon']) ?>" style="color:#476b3d;margin-right:6px"></i>
                        <?= e($info['label']) ?>
                    <?php else: ?>
                        <?= e($p['method']) ?>
                    <?php endif; ?>
                </td>
                <td><?= money($p['amount']) ?></td>
                <td>
                    <span class="status <?= e($p['status']) ?>"><?= e($p['status']) ?></span>
                </td>
                <td>
                    <?php if ($canRecord): ?>
                        <form method="post" class="inline-form"
                              data-confirm="Have you independently verified this payment or completed the refund?">
                            <?= csrf() ?>
                            <input type="hidden" name="id" value="<?= $p['order_id'] ?>">
                            <select name="status" aria-label="Payment status">
                                <?php if ($p['status'] === 'Unpaid'): ?>
                                    <option value="Paid">Mark as Paid</option>
                                <?php elseif ($p['status'] === 'Paid'): ?>
                                    <option value="Refunded">Mark as Refunded</option>
                                <?php endif; ?>
                            </select>
                            <button class="small-button">Record</button>
                        </form>
                    <?php else: ?>
                        <span class="fine-print" style="text-align:left;margin:0">
                            No further action — already refunded.
                        </span>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php require __DIR__ . '/../includes/admin-end.php'; ?>