<?php
require __DIR__ . '/includes/functions.php';

$pageTitle = 'Your shopping cart';
$items = cart_items();
$subtotal = array_sum(array_map(fn($p) => price($p) * $p['quantity'], $items));

if (user()) {
    require __DIR__ . '/includes/user-start.php';
} else {
    require __DIR__ . '/includes/header.php';
}
?>
<?php if (!user()): ?>
    <div class="breadcrumb">
        <a href="/">Home</a><span>/</span>Shopping cart
    </div>
<?php endif; ?>

<div class="page-heading">
    <span class="eyebrow">A LITTLE GOODNESS, JUST FOR YOU</span>
    <h1>Your shopping bag.</h1>
    <p><?= cart_count() ?> items for your everyday routine.</p>
</div>

<?php if (!$items): ?>
    <div class="empty-state">
        <i class="fa-solid fa-bag-shopping"></i>
        <h2>Good things are waiting.</h2>
        <p>Your bag is empty. Find something to make your everyday a little greener.</p>
        <a class="button" href="/shop.php">Explore products <i class="fa-solid fa-arrow-right"></i></a>
    </div>
<?php else: ?>
    <div class="cart-layout">
        <form method="post" action="/action.php">
            <?= csrf() ?>
            <input type="hidden" name="action" value="update_cart">
            <?php foreach ($items as $p): ?>
                <div class="cart-row">
                    <a href="/product.php?id=<?= $p['id'] ?>">
                        <img src="<?= e($p['image']) ?>" alt="<?= e($p['product_name']) ?>">
                    </a>
                    <div>
                        <h3><a href="/product.php?id=<?= $p['id'] ?>"><?= e($p['product_name']) ?></a></h3>
                        <p><?= money(price($p)) ?> · <?= e($p['size']) ?></p>
                    </div>
                    <input type="number"
                           name="quantity[<?= $p['id'] ?>]"
                           min="0" max="<?= min(99, $p['stock']) ?>"
                           value="<?= $p['quantity'] ?>"
                           aria-label="Quantity for <?= e($p['product_name']) ?>">
                    <strong><?= money(price($p) * $p['quantity']) ?></strong>
                    <button name="remove" value="<?= $p['id'] ?>"
                            class="icon-button danger"
                            aria-label="Remove <?= e($p['product_name']) ?>"
                            formnovalidate>
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>
            <?php endforeach; ?>
            <div class="top-row" style="margin-top:25px">
                <a href="/shop.php" class="text-link">
                    <i class="fa-solid fa-arrow-left"></i> Continue shopping
                </a>
                <button class="button outline-button">Update quantities</button>
            </div>
        </form>

        <aside class="panel order-summary">
            <h2>Order summary</h2>
            <div class="summary-line">
                <span>Subtotal</span><span><?= money($subtotal) ?></span>
            </div>
            <div class="summary-line">
                <span>Shipping</span>
                <span><?= shipping($subtotal) ? money(shipping($subtotal)) : 'Free' ?></span>
            </div>
            <div class="summary-line summary-total">
                <span>Total</span>
                <span><?= money($subtotal + shipping($subtotal)) ?></span>
            </div>
            <a href="/checkout.php" class="button">
                Continue to checkout <i class="fa-solid fa-arrow-right"></i>
            </a>
            <small><i class="fa-solid fa-lock"></i> Secure checkout · Prices in Philippine pesos</small>
            <?php if (shipping($subtotal) > 0): ?>
                <p class="notice" style="margin-top:20px">
                    You’re <?= money((float) setting('free_shipping_threshold', '2500') - $subtotal) ?> away from free shipping.
                </p>
            <?php endif; ?>
        </aside>
    </div>
<?php endif; ?>
<?php
if (user()) {
    require __DIR__ . '/includes/user-end.php';
} else {
    require __DIR__ . '/includes/footer.php';
}
?>