<?php
$pageTitle = 'My orders';
require __DIR__ . '/../includes/user-start.php';
?>
<div class="page-heading">
    <span class="eyebrow">FOLLOW YOUR GOODNESS</span>
    <h1>My orders.</h1>
    <p>From our store to your doorstep, keep track of every order.</p>
</div>

<?php
$orders = q(
    'SELECT * FROM orders WHERE user_id=? ORDER BY created_at DESC',
    [$u['id']]
)->fetchAll();
include __DIR__ . '/../includes/order-table.php';
require __DIR__ . '/../includes/user-end.php';
?>