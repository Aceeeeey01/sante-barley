<?php
require_once __DIR__ . '/functions.php';
$u = require_user();
require __DIR__ . '/header.php';
?>
<div class="dashboard-layout">
    <aside class="dashboard-sidebar" aria-label="Customer navigation">
        <span class="eyebrow">YOUR WELLNESS SPACE</span>
        <?php
        $navItems = [
            'dashboard'      => ['house',           'Dashboard'],
            'profile'        => ['user',            'My profile'],
            'orders'         => ['box',             'My orders'],
            'wishlist'       => ['heart',           'Wishlist'],
            'cart'           => ['bag-shopping',    'Shopping cart'],
            'addresses'      => ['location-dot',    'Addresses'],
            'notifications'  => ['bell',            'Notifications'],
            'support'        => ['circle-question', 'Help & support'],
        ];
        $currentFile = basename($_SERVER['SCRIPT_NAME'], '.php');
        foreach ($navItems as $key => $nav):
            if ($key === 'cart') {
                $href = '/cart.php';
            } elseif ($key === 'support') {
                $href = '/contact.php';
            } else {
                $href = '/user/' . $key . '.php';
            }
            ?>
            <a class="<?= $currentFile === $key ? 'selected' : '' ?>" href="<?= $href ?>">
                <i class="fa-solid fa-<?= $nav[0] ?>"></i><?= $nav[1] ?>
            </a>
        <?php endforeach; ?>
        <button type="button" data-logout-open>
    <i class="fa-solid fa-arrow-right-from-bracket"></i> Logout
</button>
    </aside>
    <main id="main" class="dashboard-main">