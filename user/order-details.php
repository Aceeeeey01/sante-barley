<?php
require __DIR__ . '/../includes/functions.php';

$u = require_user();
$o = q(
    'SELECT * FROM orders WHERE id=? AND user_id=?',
    [(int) ($_GET['id'] ?? 0), $u['id']]
)->fetch();

if (!$o) {
    http_response_code(404);
    exit('Order not found.');
}

$pageTitle = 'Order ' . $o['order_number'];
require __DIR__ . '/../includes/user-start.php';

$decoded = json_decode($o['shipping_address'], true);
$address = is_array($decoded) ? $decoded : [];
?>
<div class="breadcrumb">
    <a href="/user/orders.php">My orders</a><span>/</span><?= e($o['order_number']) ?>
</div>

<div class="page-heading">
    <h1>Your order, with care.</h1>
    <p><?= e($o['order_number']) ?> · <?= date('F d, Y', strtotime($o['created_at'])) ?></p>
    <span class="status <?= e($o['order_status']) ?>"><?= e($o['order_status']) ?></span>
</div>

<div class="table-wrap">
    <table>
        <thead>
        <tr><th>Product</th><th>Quantity</th><th>Price</th><th>Subtotal</th></tr>
        </thead>
        <tbody>
        <?php foreach (q('SELECT * FROM order_items WHERE order_id=?', [$o['id']]) as $i): ?>
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
        <h2>Delivering to</h2>
        <p>
            <?= e($address['full_name'] ?? '') ?><br>
            <?= e($address['address'] ?? '') ?><br>
            <?= e(trim(($address['barangay'] ?? '') . ', ' . ($address['city'] ?? ''), ', ')) ?><br>
            <?= e(trim(($address['province'] ?? '') . ' ' . ($address['postal_code'] ?? ''))) ?><br>
            <?= e($address['mobile'] ?? '') ?>
        </p>
        <?php if ($o['notes']): ?>
            <p>Notes: <?= e($o['notes']) ?></p>
        <?php endif; ?>
    </div>
    <div class="panel">
        <h2>Payment &amp; totals</h2>
        <div class="summary-line"><span>Subtotal</span><span><?= money($o['subtotal']) ?></span></div>
        <div class="summary-line"><span>Shipping</span><span><?= money($o['shipping_fee']) ?></span></div>
        <div class="summary-line summary-total"><span>Total</span><span><?= money($o['total_amount']) ?></span></div>
        <p>
    <strong><?= e($o['payment_method']) ?></strong> · <?= e($o['payment_status']) ?>
</p>

<?php
$methods = payment_methods();
$methodInfo = $methods[$o['payment_method']] ?? null;
$isManual = $methodInfo && $methodInfo['type'] === 'manual';
$isGateway = $methodInfo && $methodInfo['type'] === 'gateway';
?>

<?php if ($o['payment_status'] === 'Unpaid' && $isManual): ?>
    <p class="notice">
        Your order is awaiting payment. <a href="/contact.php" style="text-decoration:underline">Contact the store</a>
        with your order number for the store’s verified <?= e($o['payment_method']) ?> details.
        <strong>Never send money to unverified payment details.</strong>
    </p>
<?php elseif ($o['payment_status'] === 'Unpaid' && $isGateway): ?>
    <p class="notice">
        Card payments require a live gateway integration that is not configured on this demonstration site. Your order will remain unpaid until a payment method is confirmed.
    </p>
<?php endif; ?>
    </div>
</div>
<?php require __DIR__ . '/../includes/user-end.php'; ?>