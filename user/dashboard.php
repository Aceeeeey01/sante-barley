<?php
$pageTitle = 'Your wellness space';
require __DIR__ . '/../includes/user-start.php';

$stats = q(
    "SELECT COUNT(*) total,
            COUNT(*) FILTER (WHERE order_status='Pending') pending,
            COUNT(*) FILTER (WHERE order_status='Delivered') completed
     FROM orders
     WHERE user_id=?",
    [$u['id']]
)->fetch();

$w = q('SELECT COUNT(*) FROM wishlist WHERE user_id=?', [$u['id']])->fetchColumn();
?>
<div class="page-heading">
    <span class="eyebrow">YOUR WELLNESS SPACE</span>
    <h1>Welcome, <?= e(explode(' ', $u['full_name'])[0]) ?>.</h1>
    <p>A little goodness for your everyday. Here’s your journey at a glance.</p>
</div>

<div class="stat-grid">
    <?php
    $cards = [
        ['box',          'Total orders',     $stats['total']],
        ['clock',        'Pending orders',   $stats['pending']],
        ['circle-check', 'Completed orders', $stats['completed']],
        ['heart',        'Wishlist items',   $w],
    ];
    foreach ($cards as $s): ?>
        <div class="stat-card">
            <i class="fa-solid fa-<?= $s[0] ?>"></i>
            <span><?= $s[1] ?></span>
            <strong><?= $s[2] ?></strong>
        </div>
    <?php endforeach; ?>
</div>

<div class="top-row" style="margin:35px 0 20px">
    <h2 style="font-size:22px;margin:0">Your recent orders</h2>
    <a href="/user/orders.php" class="text-link">View all <i class="fa-solid fa-arrow-right"></i></a>
</div>

<?php
$orders = q(
    'SELECT * FROM orders WHERE user_id=? ORDER BY created_at DESC LIMIT 5',
    [$u['id']]
)->fetchAll();
include __DIR__ . '/../includes/order-table.php';
?>

<div class="panel" style="margin-top:30px;background:#eef3e4">
    <span class="eyebrow">MAKE ROOM FOR A LITTLE GREEN</span>
    <h2>Your next everyday favorite is waiting.</h2>
    <p>Find thoughtful additions to your daily wellness routine.</p>
    <a class="button" href="/shop.php">Explore products <i class="fa-solid fa-arrow-right"></i></a>
</div>
<?php require __DIR__ . '/../includes/user-end.php'; ?>