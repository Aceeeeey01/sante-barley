<?php
$pageTitle = 'Dashboard';
require __DIR__ . '/../includes/admin-start.php';

$s = q(
    "SELECT COALESCE(SUM(total_amount) FILTER (WHERE payment_status='Paid' AND order_status!='Cancelled'),0) sales,
            COUNT(*) orders,
            COUNT(*) FILTER (WHERE order_status='Pending') pending,
            COUNT(*) FILTER (WHERE order_status='Delivered') completed
     FROM orders"
)->fetch();
?>
<div class="page-heading">
    <span class="eyebrow">STORE OVERVIEW</span>
    <h1>A good day to grow.</h1>
    <p>Welcome back. Here’s what’s happening in your store.</p>
</div>

<div class="stat-grid">
    <?php
    $cards = [
        ['peso-sign',           'Total paid sales',  money($s['sales'])],
        ['bag-shopping',        'Total orders',      $s['orders']],
        ['clock',               'Pending orders',    $s['pending']],
        ['circle-check',        'Completed orders',  $s['completed']],
        ['users',               'Customers',         q('SELECT COUNT(*) FROM users WHERE id NOT IN (SELECT user_id FROM admins)')->fetchColumn()],
        ['box',                 'Products',          q("SELECT COUNT(*) FROM products WHERE status!='Inactive'")->fetchColumn()],
        ['triangle-exclamation','Low stock',         q("SELECT COUNT(*) FROM products WHERE stock<=low_stock_threshold AND status!='Inactive'")->fetchColumn()],
    ];
    foreach ($cards as $stat): ?>
        <div class="stat-card">
            <i class="fa-solid fa-<?= $stat[0] ?>"></i>
            <span><?= $stat[1] ?></span>
            <strong><?= $stat[2] ?></strong>
        </div>
    <?php endforeach; ?>
</div>

<div class="form-grid">
    <section class="panel">
        <h2>Sales overview</h2>
        <p>Paid, non-cancelled orders · last 6 months</p>
        <?php
        $monthly = q(
            "SELECT TO_CHAR(month,'Mon') label,
                    COALESCE(SUM(o.total_amount),0) value
             FROM generate_series(
                 date_trunc('month',NOW()) - INTERVAL '5 months',
                 date_trunc('month',NOW()),
                 INTERVAL '1 month'
             ) month
             LEFT JOIN orders o
                 ON date_trunc('month',o.created_at)=month
                AND o.payment_status='Paid'
                AND o.order_status!='Cancelled'
             GROUP BY month
             ORDER BY month"
        )->fetchAll();

        $values = array_column($monthly, 'value');
        $peak = $values ? max(1, ...array_map('floatval', $values)) : 1;
        ?>
        <div class="bar-chart">
            <?php foreach ($monthly as $m): ?>
                <div class="bar-column">
                    <span><?= money($m['value']) ?></span>
                    <div style="height:<?= max(2, 100 * $m['value'] / $peak) ?>px"></div>
                    <span><?= $m['label'] ?></span>
                </div>
            <?php endforeach; ?>
        </div>
    </section>

    <section class="panel">
        <h2>Inventory check-in</h2>
        <p>A little attention keeps your store running smoothly.</p>
        <?php
        $low = q(
            "SELECT * FROM products
             WHERE stock<=low_stock_threshold AND status!='Inactive'
             ORDER BY stock
             LIMIT 5"
        )->fetchAll();

        if (!$low): ?>
            <div class="empty-state" style="padding:25px">
                <i class="fa-solid fa-circle-check"></i>
                <h3>All stocked up.</h3>
                <p>Your active products are above their low-stock thresholds.</p>
            </div>
        <?php else:
            foreach ($low as $p): ?>
                <div class="summary-line">
                    <span><?= e($p['product_name']) ?></span>
                    <strong class="danger"><?= $p['stock'] ?> left</strong>
                </div>
            <?php endforeach;
        endif; ?>
        <a href="/admin/inventory.php" class="text-link">
            Manage inventory <i class="fa-solid fa-arrow-right"></i>
        </a>
    </section>
</div>

<div class="top-row" style="margin:30px 0 20px">
    <h2 style="font-size:22px;margin:0">Recent orders</h2>
    <a href="/admin/orders.php" class="text-link">Manage orders</a>
</div>

<?php
$orders = q(
    'SELECT o.*, u.full_name
     FROM orders o
     JOIN users u ON u.id=o.user_id
     ORDER BY o.created_at DESC
     LIMIT 5'
)->fetchAll();

include __DIR__ . '/../includes/admin-orders-table.php';
require __DIR__ . '/../includes/admin-end.php';
?>