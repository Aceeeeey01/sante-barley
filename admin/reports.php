<?php
require __DIR__ . '/../includes/functions.php';
require_admin();

$rows = q(
    "SELECT TO_CHAR(date_trunc('month',created_at),'YYYY-MM') AS month_label,
            COUNT(*) orders,
            COUNT(*) FILTER (WHERE order_status='Delivered') delivered,
            COALESCE(SUM(total_amount) FILTER (WHERE payment_status='Paid' AND order_status!='Cancelled'),0) sales
     FROM orders
     GROUP BY date_trunc('month',created_at)
     ORDER BY month_label DESC"
)->fetchAll();

if (isset($_GET['export'])) {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="sante-sales-report.csv"');
    $out = fopen('php://output', 'w');
    fputcsv($out, ['Month', 'Orders', 'Delivered', 'Paid sales (PHP)'], ',', '"', '');
    foreach ($rows as $r) {
        fputcsv($out, array_values($r), ',', '"', '');
    }
    fclose($out);
    exit;
}

$pageTitle = 'Reports';
require __DIR__ . '/../includes/admin-start.php';
?>
<div class="top-row">
    <div class="page-heading">
        <h1>A clear view of your growth.</h1>
        <p>Order volume, completed deliveries, and verified paid sales.</p>
    </div>
    <a href="?export=csv" class="button">
        <i class="fa-solid fa-download"></i> Export CSV
    </a>
</div>

<div class="table-wrap">
    <table>
        <thead>
        <tr><th>Month</th><th>Orders</th><th>Delivered</th><th>Paid sales</th></tr>
        </thead>
        <tbody>
        <?php foreach ($rows as $r): ?>
            <tr>
                <td><?= $r['month_label'] ?></td>
                <td><?= $r['orders'] ?></td>
                <td><?= $r['delivered'] ?></td>
                <td><?= money($r['sales']) ?></td>
            </tr>
        <?php endforeach; ?>

        <?php if (!$rows): ?>
            <tr><td colspan="4">Your first order will start your sales story.</td></tr>
        <?php endif; ?>
        </tbody>
    </table>
</div>

<div class="form-grid" style="margin-top:25px">
    <section class="panel">
        <h2>Product sales</h2>
        <?php
        $popular = q(
            "SELECT i.product_name, SUM(i.quantity) units, SUM(i.subtotal) total
             FROM order_items i
             JOIN orders o ON o.id=i.order_id
             WHERE o.order_status!='Cancelled'
             GROUP BY i.product_name
             ORDER BY units DESC
             LIMIT 10"
        )->fetchAll();

        if (!$popular) {
            echo '<p>No product sales yet.</p>';
        }

        foreach ($popular as $p): ?>
            <div class="summary-line">
                <span><?= e($p['product_name']) ?></span>
                <strong><?= $p['units'] ?> units</strong>
            </div>
        <?php endforeach; ?>
    </section>

    <section class="panel">
        <h2>Customer growth</h2>
        <?php
        $growth = q(
            "SELECT TO_CHAR(created_at,'YYYY-MM') AS month_label, COUNT(*) count
             FROM users
             WHERE id NOT IN (SELECT user_id FROM admins)
             GROUP BY TO_CHAR(created_at,'YYYY-MM')
             ORDER BY month_label DESC
             LIMIT 6"
        )->fetchAll();

        foreach ($growth as $c): ?>
            <div class="summary-line">
                <span><?= $c['month_label'] ?></span>
                <strong><?= $c['count'] ?> customers</strong>
            </div>
        <?php endforeach; ?>
    </section>
</div>
<?php require __DIR__ . '/../includes/admin-end.php'; ?>