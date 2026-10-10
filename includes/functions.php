<?php
declare(strict_types=1);

ini_set('session.use_strict_mode', '1');

session_set_cookie_params([
    'httponly' => true,
    'secure'   => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
    'samesite' => 'Lax',
    'path'     => '/',
]);

session_start();

header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('Referrer-Policy: strict-origin-when-cross-origin');

require_once __DIR__ . '/../config/database.php';

set_exception_handler(function (Throwable $error): void {
    error_log((string) $error);
    http_response_code(500);
    echo '<p>Something went wrong. Please refresh the page or try again shortly.</p>';
});

function e($s): string
{
    return htmlspecialchars((string) $s, ENT_QUOTES, 'UTF-8');
}

function q(string $sql, array $args = []): PDOStatement
{
    global $db;
    $s = $db->prepare($sql);
    $s->execute($args);
    return $s;
}

function money($n): string
{
    return '₱' . number_format((float) $n, 2);
}

function price(array $p): float
{
    return round((float) $p['price'] * (1 - (float) $p['discount'] / 100), 2);
}

function redirect(string $to): never
{
    header('Location: ' . $to);
    exit;
}

function flash(string $message, string $type = 'success'): void
{
    $_SESSION['flash'] = [$message, $type];
}

function logout_modal(): void
{
    ?>
    <div id="logout-modal" class="modal-backdrop" hidden>
        <div class="modal" role="dialog" aria-modal="true" aria-labelledby="logout-modal-title">
            <h3 id="logout-modal-title">Ready to sign out?</h3>
            <p>You’ll need to sign in again to access your cart, orders, and wishlist.</p>
            <form method="post" action="/action.php">
                <?= csrf() ?>
                <input type="hidden" name="action" value="logout">
                <div class="modal-actions">
                    <button type="button" class="button outline-button" data-logout-cancel>Stay signed in</button>
                    <button class="button">Sign out</button>
                </div>
            </form>
        </div>
    </div>
    <?php
}

function csrf(): string
{
    $_SESSION['csrf'] ??= bin2hex(random_bytes(32));
    return '<input type="hidden" name="csrf" value="' . e($_SESSION['csrf']) . '">';
}

function verify_csrf(): void
{
    if (
        !hash_equals($_SESSION['csrf'] ?? '', (string) ($_POST['csrf'] ?? ''))
        || empty($_SESSION['csrf'])
    ) {
        http_response_code(419);
        exit('Your session has expired. Return to the previous page and refresh.');
    }
}

function user(): ?array
{
    static $u = false;
    if ($u === false) {
        $u = empty($_SESSION['uid'])
            ? null
            : (q(
                "SELECT * FROM users WHERE id=? AND status='Active'",
                [$_SESSION['uid']]
            )->fetch() ?: null);
    }
    return $u;
}

function require_user(): array
{
    $u = user();
    if (!$u) {
        flash('Please sign in to continue.', 'info');
        redirect('/login.php');
    }
    return $u;
}

function is_admin(): bool
{
    return user() && (bool) q(
        'SELECT id FROM admins WHERE user_id=?',
        [user()['id']]
    )->fetchColumn();
}

function require_admin(): void
{
    require_user();
    if (!is_admin()) {
        http_response_code(403);
        exit('Access restricted to store administrators.');
    }
}

function setting(string $key, string $default = ''): string
{
    static $cache = [];
    if (!array_key_exists($key, $cache)) {
        $cache[$key] = (string) (
            q('SELECT value FROM settings WHERE key=?', [$key])->fetchColumn()
            ?: $default
        );
    }
    return $cache[$key];
}

function cart_items(): array
{
    $items = [];
    foreach ($_SESSION['cart'] ?? [] as $id => $qty) {
        $p = q(
            'SELECT * FROM products WHERE id=? AND status!=?',
            [(int) $id, 'Inactive']
        )->fetch();
        if ($p) {
            $p['quantity'] = (int) $qty;
            $items[] = $p;
        }
    }
    return $items;
}

function cart_count(): int
{
    return array_sum($_SESSION['cart'] ?? []);
}

function shipping(float $subtotal): float
{
    return $subtotal >= (float) setting('free_shipping_threshold', '2500')
        ? 0
        : (float) setting('shipping_fee', '120');
}

function payment_methods(): array
{
    return [
        'Cash on Delivery'    => [
            'label' => 'Cash on Delivery',
            'icon'  => 'money-bill-wave',
            'note'  => 'Pay in cash when your order arrives.',
            'type'  => 'offline',
        ],
        'GCash'               => [
            'label' => 'GCash',
            'icon'  => 'mobile-screen',
            'note'  => 'Send payment to the store’s verified GCash number. Your order stays unpaid until the store confirms receipt.',
            'type'  => 'manual',
        ],
        'Maya'                => [
            'label' => 'Maya',
            'icon'  => 'wallet',
            'note'  => 'Send payment to the store’s verified Maya account. Your order stays unpaid until the store confirms receipt.',
            'type'  => 'manual',
        ],
        'GrabPay'             => [
            'label' => 'GrabPay',
            'icon'  => 'car-side',
            'note'  => 'Send payment through your GrabPay wallet to the store’s verified account. Your order stays unpaid until the store confirms receipt.',
            'type'  => 'manual',
        ],
        'ShopeePay'           => [
            'label' => 'ShopeePay',
            'icon'  => 'bag-shopping',
            'note'  => 'Send payment through ShopeePay to the store’s verified account. Your order stays unpaid until the store confirms receipt.',
            'type'  => 'manual',
        ],
        'Credit / Debit Card' => [
            'label' => 'Credit / Debit Card',
            'icon'  => 'credit-card',
            'note'  => 'Card payments require a live gateway integration. No card details are collected on this site.',
            'type'  => 'gateway',
        ],
        'Bank Transfer'       => [
            'label' => 'Bank Transfer',
            'icon'  => 'building-columns',
            'note'  => 'Transfer to the store’s verified bank account. Your order stays unpaid until the store confirms receipt.',
            'type'  => 'manual',
        ],
    ];
}

function payment_method_names(): array
{
    return array_keys(payment_methods());
}

function product_card(array $p): void
{
    $rating = q(
        "SELECT AVG(rating) AS rating, COUNT(*) AS count
         FROM reviews
         WHERE product_id=? AND status='Approved'",
        [$p['id']]
    )->fetch();

    $wishable = (bool) user();
    ?>
    <article class="product-card">
        <div class="product-visual">
            <span class="product-label"><?= $p['id'] == 1
                ? 'EVERYDAY FAVORITE'
                : ($p['category_name'] == 'Specialty Drinks'
                    ? 'A LITTLE INDULGENCE'
                    : 'DAILY WELLNESS') ?></span>
            <?php if ($wishable): ?>
                <form action="/action.php" method="post" class="wish-form">
                    <?= csrf() ?>
                    <input type="hidden" name="action" value="wishlist">
                    <input type="hidden" name="id" value="<?= $p['id'] ?>">
                    <button class="icon-button wishlist"
                            title="Save <?= e($p['product_name']) ?> to wishlist">
                        <i class="fa-regular fa-heart"></i>
                    </button>
                </form>
            <?php else: ?>
                <a href="/login.php"
                   class="wish-form icon-button wishlist"
                   title="Sign in to save <?= e($p['product_name']) ?>">
                    <i class="fa-regular fa-heart"></i>
                </a>
            <?php endif; ?>
            <a href="/product.php?id=<?= $p['id'] ?>">
                <img src="<?= e($p['image']) ?>"
                     alt="Illustration of <?= e($p['product_name']) ?>"
                     loading="lazy">
            </a>
        </div>
        <div class="product-info">
            <div class="eyebrow muted"><?= e($p['category_name']) ?></div>
            <a href="/product.php?id=<?= $p['id'] ?>">
                <h3><?= e($p['product_name']) ?></h3>
            </a>
            <p class="product-size">
                <?= e($p['size']) ?> <span>•</span>
                <?= $p['stock'] > 0 ? 'In stock' : 'Out of stock' ?>
            </p>
            <p class="product-description"><?= e($p['description']) ?></p>
            <div class="rating">
                <i class="fa-solid fa-star"></i>
                <?= $rating['count']
                    ? number_format((float) $rating['rating'], 1)
                      . ' <span>(' . $rating['count'] . ' reviews)</span>'
                    : '<span>New · Be the first to review</span>' ?>
            </div>
            <div class="product-bottom">
                <strong><?= money(price($p)) ?></strong>
                <form action="/action.php" method="post" class="add-cart">
                    <?= csrf() ?>
                    <input type="hidden" name="action" value="add_cart">
                    <input type="hidden" name="id" value="<?= $p['id'] ?>">
                    <button class="small-button" <?= $p['stock'] < 1 ? 'disabled' : '' ?>>
                        <i class="fa-solid fa-plus"></i> Add to cart
                    </button>
                </form>
            </div>
        </div>
    </article>
    <?php
}

function field(
    string $label,
    string $name,
    string $value = '',
    string $type = 'text',
    bool $required = true
): void {
    $max = $type === 'password'
        ? 72
        : ($type === 'number' ? null : 254);
    ?>
    <label class="field">
        <?= e($label) ?>
        <input type="<?= e($type) ?>"
               name="<?= e($name) ?>"
               value="<?= e($value) ?>"
               <?= $required ? 'required' : '' ?>
               <?= $max !== null ? ' maxlength="' . $max . '"' : '' ?>>
    </label>
    <?php
}