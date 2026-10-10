<?php
$pageTitle = 'Orders';
require __DIR__ . '/../includes/admin-start.php';

$status = $_GET['status'] ?? '';
$statuses = ['Pending', 'Confirmed', 'Processing', 'Shipped', 'Delivered', 'Cancelled'];
?>
<div class="page-heading">
    <h1>Every order, with care.</h1>
    <p>Track and manage your customers’ wellness deliveries.</p>
</div>

<form class="shop-toolbar">
    <label class="field">Order status
        <select name="status">
            <option value="">All orders</option>
            <?php foreach ($statuses as $s): ?>
                <option <?= $status === $s ? 'selected' : '' ?>><?= $s ?></option>
            <?php endforeach; ?>
        </select>
    </label>
    <button class="button">Filter</button>
</form>

<?php
$valid = in_array($status, $statuses, true);

$orders = q(
    'SELECT o.*, u.full_name
     FROM orders o
     JOIN users u ON u.id=o.user_id'
    . ($valid ? ' WHERE o.order_status=?' : '')
    . ' ORDER BY o.created_at DESC',
    $valid ? [$status] : []
)->fetchAll();

include __DIR__ . '/../includes/admin-orders-table.php';
require __DIR__ . '/../includes/admin-end.php';
?>