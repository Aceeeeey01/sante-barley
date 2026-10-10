<?php
require_once __DIR__ . '/functions.php';
require_admin();
$pageTitle = $pageTitle ?? 'Store administration';
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title><?= e($pageTitle) ?> | Santé Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Manrope:wght@400;500;600;700&display=swap"
          rel="stylesheet">
    <link rel="stylesheet"
          href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
    <link rel="stylesheet" href="/assets/css/style.css">
    <link rel="stylesheet" href="/assets/css/responsive.css">
    <script src="/assets/js/main.js" defer></script>
</head>
<body>
<header class="admin-header">
    <div style="display:flex;align-items:center">
        <a class="brand" href="/admin/">
            <span class="brand-name">santé</span>
            <span class="brand-sub">STORE ADMINISTRATION</span>
        </a>
        <span class="admin-label">A LITTLE CARE BEHIND EVERY ORDER</span>
    </div>
    <a href="/">View storefront <i class="fa-solid fa-arrow-up-right-from-square"></i></a>
</header>
<?php if (isset($_SESSION['flash'])):
    $f = $_SESSION['flash'];
    unset($_SESSION['flash']);
    ?>
    <div class="toast <?= e($f[1]) ?>" role="status">
        <?= e($f[0]) ?>
        <button onclick="this.parentElement.remove()" aria-label="Dismiss">×</button>
    </div>
<?php endif; ?>
<div class="dashboard-layout">
    <aside class="dashboard-sidebar">
        <span class="eyebrow">MANAGE YOUR STORE</span>
        <?php
        $navItems = [
            'index'       => ['chart-line',  'Dashboard'],
            'products'    => ['box',         'Products'],
            'categories'  => ['layer-group', 'Categories'],
            'orders'      => ['bag-shopping','Orders'],
            'customers'   => ['users',       'Customers'],
            'inventory'   => ['warehouse',   'Inventory'],
            'payments'    => ['peso-sign',   'Payments'],
            'promotions'  => ['tag',         'Promotions'],
            'reviews'     => ['star',        'Reviews'],
            'messages'    => ['envelope',    'Messages'],
            'reports'     => ['chart-column','Reports'],
            'settings'    => ['gear',        'Settings'],
        ];
        $currentFile = basename($_SERVER['SCRIPT_NAME'], '.php');
        foreach ($navItems as $key => $nav):
            ?>
            <a class="<?= $currentFile === $key ? 'selected' : '' ?>"
               href="/admin/<?= $key ?>.php">
                <i class="fa-solid fa-<?= $nav[0] ?>"></i><?= $nav[1] ?>
            </a>
        <?php endforeach; ?>
        <button type="button" data-logout-open>
    <i class="fa-solid fa-arrow-right-from-bracket"></i> Logout
</button>
    </aside>
    <main id="main" class="dashboard-main">