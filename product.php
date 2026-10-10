<?php
require __DIR__ . '/includes/functions.php';

$id = (int) ($_GET['id'] ?? 0);
$p = q(
    "SELECT p.*, c.name category_name
     FROM products p
     JOIN categories c ON c.id = p.category_id
     WHERE p.id=? AND p.status!='Inactive'",
    [$id]
)->fetch();

if (!$p) {
    http_response_code(404);
    $pageTitle = 'Product not found';
    require __DIR__ . '/includes/header.php';
    echo '<main id="main" class="page-wrap empty-state"><h1>Product not found</h1><a class="button" href="/shop.php">Explore products</a></main>';
    require __DIR__ . '/includes/footer.php';
    exit;
}

$pageTitle = $p['product_name'];
require __DIR__ . '/includes/header.php';

$available = $p['stock'] > 0 && $p['status'] === 'Active';

$canReview = false;
if (user()) {
    $canReview = (bool) q(
        "SELECT 1
         FROM order_items i
         JOIN orders o ON o.id = i.order_id
         WHERE o.user_id=? AND i.product_id=? AND o.order_status='Delivered'",
        [user()['id'], $p['id']]
    )->fetchColumn();
}
?>
<main id="main" class="page-wrap">
    <div class="breadcrumb">
        <a href="/">Home</a><span>/</span>
        <a href="/shop.php">Products</a><span>/</span>
        <?= e($p['product_name']) ?>
    </div>

    <div class="product-detail">
        <div class="detail-image">
            <img src="<?= e($p['image']) ?>"
                 alt="Illustration of <?= e($p['product_name']) ?>">
            <small>Illustrative packaging · Refer to the actual product label</small>
        </div>

        <div class="detail-info">
            <span class="eyebrow"><?= e($p['category_name']) ?></span>
            <h1><?= e($p['product_name']) ?></h1>
            <p><?= e($p['size']) ?></p>

            <div class="detail-price">
                <?= money(price($p)) ?>
                <?php if ($p['discount'] > 0): ?>
                    <del style="font-size:17px;color:#999"><?= money($p['price']) ?></del>
                <?php endif; ?>
            </div>

            <span class="stock-pill">
                <?= $available
                    ? 'In stock · ' . $p['stock'] . ' available'
                    : 'Currently out of stock' ?>
            </span>

            <p style="margin-top:20px"><?= e($p['description']) ?></p>

            <form method="post" action="/action.php">
                <?= csrf() ?>
                <input type="hidden" name="action" value="add_cart">
                <input type="hidden" name="id" value="<?= $p['id'] ?>">
                <div class="quantity-row">
                    <input class="quantity-input" type="number" name="quantity"
                           aria-label="Quantity" value="1" min="1"
                           max="<?= max(1, min(99, $p['stock'])) ?>">
                    <button class="button" <?= !$available ? 'disabled' : '' ?>>
                        <i class="fa-solid fa-bag-shopping"></i> Add to cart
                    </button>
                    <button class="button outline-button"
                            name="buy_now" value="1"
                            <?= !$available ? 'disabled' : '' ?>>
                        Buy now
                    </button>
                </div>
            </form>

            <?php if (user()): ?>
                <form method="post" action="/action.php">
                    <?= csrf() ?>
                    <input type="hidden" name="action" value="wishlist">
                    <input type="hidden" name="id" value="<?= $p['id'] ?>">
                    <button class="small-button">
                        <i class="fa-regular fa-heart"></i> Save to wishlist
                    </button>
                </form>
            <?php else: ?>
                <a class="small-button" href="/login.php">
                    <i class="fa-regular fa-heart"></i> Sign in to save
                </a>
            <?php endif; ?>

            <div class="product-accordions">
                <details open>
                    <summary>Product information &amp; ingredients</summary>
                    <p><?= e($p['ingredients']) ?></p>
                </details>
                <details>
                    <summary>How to use</summary>
                    <p><?= e($p['instructions']) ?></p>
                </details>
                <details>
                    <summary>Shipping &amp; returns</summary>
                    <p>Shipping is calculated at checkout. <a href="/faq.php#shipping">Read our shipping and returns information.</a></p>
                </details>
            </div>
        </div>
    </div>

    <div class="notice" style="margin-top:30px">
        May be included as part of a balanced lifestyle. Not intended to diagnose, treat, cure, or prevent any disease. Consult your healthcare professional regarding medical conditions, medications, pregnancy, or allergies.
    </div>

    <section style="margin-top:45px">
        <h2>Customer reviews</h2>
        <?php
        $reviews = q(
            "SELECT r.*, u.full_name
             FROM reviews r
             JOIN users u ON u.id = r.user_id
             WHERE r.product_id=? AND r.status='Approved'
             ORDER BY r.created_at DESC",
            [$p['id']]
        )->fetchAll();

        if (!$reviews) {
            echo '<p>No reviews yet. Share your experience after your purchase is delivered.</p>';
        }

        foreach ($reviews as $r): ?>
            <article class="review">
                <h4><?= e($r['full_name']) ?> · <?= str_repeat('★', (int) $r['rating']) ?></h4>
                <p><?= e($r['content']) ?></p>
                <small>Verified purchase</small>
            </article>
        <?php endforeach; ?>

        <?php if ($canReview): ?>
            <details class="panel">
                <summary>Write a review</summary>
                <form class="form-stack" style="margin-top:20px" method="post" action="/action.php">
                    <?= csrf() ?>
                    <input type="hidden" name="action" value="review">
                    <input type="hidden" name="id" value="<?= $p['id'] ?>">
                    <label class="field">Rating
                        <select name="rating">
                            <?php for ($i = 5; $i >= 1; $i--) echo '<option value="' . $i . '">' . $i . ' stars</option>'; ?>
                        </select>
                    </label>
                    <label class="field">Your experience
                        <textarea name="content" minlength="5" maxlength="2000" required></textarea>
                    </label>
                    <button class="button">Submit review</button>
                </form>
            </details>
        <?php elseif (user()): ?>
            <p class="fine-print" style="text-align:left">
                Reviews are available after your purchase is delivered.
            </p>
        <?php endif; ?>
    </section>
</main>
<?php require __DIR__ . '/includes/footer.php'; ?>