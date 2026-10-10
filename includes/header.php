<?php
require_once __DIR__ . '/functions.php';
$pageTitle = $pageTitle ?? 'Natural wellness, every day';
$active = $active ?? '';
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description"
          content="Explore Santé Barley products and thoughtful essentials for your everyday wellness routine in the Philippines.">
    <title><?= e($pageTitle) ?> | Santé Barley Philippines</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Manrope:wght@400;500;600;700;800&display=swap"
          rel="stylesheet">
    <link rel="stylesheet"
          href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
    <link rel="stylesheet" href="/assets/css/style.css">
    <link rel="stylesheet" href="/assets/css/responsive.css">
    <script src="/assets/js/main.js" defer></script>
</head>
<body>
<a class="skip-link" href="#main">Skip to content</a>
<div class="announcement">
    <span><i class="fa-solid fa-leaf"></i> Small daily choices. A healthier, happier you.</span>
    <span>Free shipping on orders ₱<?= number_format((float) setting('free_shipping_threshold', '2500')) ?>+ <i class="fa-solid fa-arrow-right"></i></span>
</div>
<?php include __DIR__ . '/navbar.php'; ?>
<?php if (isset($_SESSION['flash'])):
    $f = $_SESSION['flash'];
    unset($_SESSION['flash']);
    ?>
    <div class="toast <?= e($f[1]) ?>" role="status">
        <?= e($f[0]) ?>
        <button aria-label="Dismiss notification"
                onclick="this.parentElement.remove()">×</button>
    </div>
<?php endif; ?>
<div id="live-toast" class="toast" role="status" aria-live="polite" hidden></div>