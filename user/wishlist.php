<?php
$pageTitle = 'My wishlist';
require __DIR__ . '/../includes/user-start.php';
?>
<div class="page-heading">
    <span class="eyebrow">SAVE A LITTLE GOODNESS FOR LATER</span>
    <h1>Your favorites, all together.</h1>
    <p>Thoughtful finds for your next wellness moment. Tap a heart to remove an item.</p>
</div>

<div class="product-grid">
    <?php
    $products = q(
        "SELECT p.*, c.name category_name
         FROM wishlist w
         JOIN products p ON p.id = w.product_id
         JOIN categories c ON c.id = p.category_id
         WHERE w.user_id=? AND p.status!='Inactive'
         ORDER BY p.id",
        [$u['id']]
    )->fetchAll();

    foreach ($products as $p) {
        product_card($p);
    }

    if (!$products): ?>
        <div class="empty-state">
            <i class="fa-regular fa-heart"></i>
            <h2>Room for your favorites.</h2>
            <p>Tap the heart on a product to save it here.</p>
            <a class="button" href="/shop.php">Find your favorites</a>
        </div>
    <?php endif; ?>
</div>
<?php require __DIR__ . '/../includes/user-end.php'; ?>