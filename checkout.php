<?php
require __DIR__ . '/includes/functions.php';

$u = require_user();
$items = cart_items();
if (!$items) {
    flash('Your cart is empty.', 'info');
    redirect('/cart.php');
}

$error = '';
$_SESSION['checkout_token'] ??= bin2hex(random_bytes(24));

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    try {
        if (!hash_equals($_SESSION['checkout_token'], $_POST['checkout_token'] ?? '')) {
            throw new RuntimeException('This checkout has already been submitted.');
        }

        $required = ['full_name', 'email', 'mobile', 'address', 'province', 'city', 'barangay', 'postal_code'];
        $address = [];
        foreach ($required as $f) {
            $value = trim($_POST[$f] ?? '');
            if ($value === '' || mb_strlen($value) > 254) {
                throw new RuntimeException('Complete all shipping fields with valid information.');
            }
            $address[$f] = $value;
        }

        if (
            !filter_var($address['email'], FILTER_VALIDATE_EMAIL)
            || !preg_match('/^(09[0-9]{9}|\+639[0-9]{9})$/', $address['mobile'])
            || !preg_match('/^[0-9]{4}$/', $address['postal_code'])
        ) {
            throw new RuntimeException('Enter a valid email, Philippine mobile number, and 4-digit postal code.');
        }

        $method = $_POST['payment_method'] ?? '';
        if (!in_array($method, payment_method_names(), true)) {
            throw new RuntimeException('Choose a payment method.');
}

        $notes = trim($_POST['notes'] ?? '');
        if (strlen($notes) > 2000) {
            throw new RuntimeException('Order notes must be under 2,000 characters.');
        }

        $db->beginTransaction();
        $locked = [];
        $subtotal = 0;
        $basket = $_SESSION['cart'];
        ksort($basket, SORT_NUMERIC);

        foreach ($basket as $id => $quantity) {
            $p = q('SELECT * FROM products WHERE id=? FOR UPDATE', [$id])->fetch();
            if (!$p || $p['status'] !== 'Active' || $p['stock'] < $quantity || $quantity < 1) {
                throw new RuntimeException('A product in your cart is no longer available in the requested quantity. Please update your cart.');
            }
            $p['quantity'] = $quantity;
            $locked[] = $p;
            $subtotal += price($p) * $quantity;
        }

        $fee = shipping($subtotal);
        $number = 'SB-' . date('ymd') . '-' . strtoupper(bin2hex(random_bytes(4)));

        $oid = q(
            'INSERT INTO orders(user_id,order_number,subtotal,shipping_fee,total_amount,payment_method,shipping_address,notes)
             VALUES (?,?,?,?,?,?,?,?)
             RETURNING id',
            [
                $u['id'],
                $number,
                $subtotal,
                $fee,
                $subtotal + $fee,
                $method,
                json_encode($address),
                $notes,
            ]
        )->fetchColumn();

        foreach ($locked as $p) {
            q(
                'INSERT INTO order_items(order_id,product_id,product_name,quantity,price,subtotal)
                 VALUES (?,?,?,?,?,?)',
                [$oid, $p['id'], $p['product_name'], $p['quantity'], price($p), price($p) * $p['quantity']]
            );
            q('UPDATE products SET stock=stock-?,updated_at=NOW() WHERE id=?', [$p['quantity'], $p['id']]);
        }

        q('INSERT INTO payments(order_id,method,amount) VALUES (?,?,?)', [$oid, $method, $subtotal + $fee]);
        q('INSERT INTO notifications(user_id,content) VALUES (?,?)', [
            $u['id'],
            'Your order ' . $number . ' has been placed. We will keep you updated here.',
        ]);

        $db->commit();
        $_SESSION['cart'] = [];
        unset($_SESSION['checkout_token']);
        flash('Thank you! Your order has been placed.');
        redirect('/user/order-details.php?id=' . $oid);
    } catch (Throwable $e) {
        if ($db->inTransaction()) {
            $db->rollBack();
        }
        error_log((string) $e);
        $error = $e instanceof RuntimeException && !($e instanceof PDOException)
            ? $e->getMessage()
            : 'We could not complete your order. Please try again.';
    }
}

$subtotal = array_sum(array_map(fn($p) => price($p) * $p['quantity'], $items));
$pageTitle = 'Checkout';
require __DIR__ . '/includes/user-start.php';
?>
<main class="page-wrap" id="main">
    <div class="breadcrumb">
        <a href="/cart.php">Shopping bag</a><span>/</span>Checkout
    </div>
    <div class="page-heading">
        <span class="eyebrow">ONE STEP CLOSER TO YOUR DOORSTEP</span>
        <h1>Let’s make it yours.</h1>
    </div>

    <?php if ($error): ?>
        <div class="error-box" role="alert"><?= e($error) ?></div>
    <?php endif; ?>

    <form method="post" class="checkout-layout">
        <?= csrf() ?>
        <input type="hidden" name="checkout_token" value="<?= e($_SESSION['checkout_token']) ?>">

        <div class="panel">
            <h2>Delivery details</h2>
            <div class="form-grid">
                <?php
                $fields = [
                    'full_name'   => 'Full name',
                    'email'       => 'Email',
                    'mobile'      => 'Mobile number',
                    'address'     => 'Street address / house number',
                    'province'    => 'Province',
                    'city'        => 'City / Municipality',
                    'barangay'    => 'Barangay',
                    'postal_code' => 'Postal code',
                ];
                foreach ($fields as $key => $label) {
                    field($label, $key, $_POST[$key] ?? $u[$key] ?? '', $key === 'email' ? 'email' : 'text');
                }
                ?>
                <label class="field full">Order notes (optional)
                    <textarea name="notes" maxlength="2000"
                              placeholder="Anything we should know about your delivery?"><?= e($_POST['notes'] ?? '') ?></textarea>
                </label>
            </div>

            <<h2 style="margin-top:32px">Payment method</h2>
<?php
$selected = $_POST['payment_method'] ?? 'Cash on Delivery';
foreach (payment_methods() as $value => $m): ?>
    <label class="payment-option">
        <input type="radio" name="payment_method" value="<?= e($value) ?>"
            <?= $selected === $value ? 'checked' : '' ?>>
        <i class="fa-solid fa-<?= e($m['icon']) ?>"></i>
        <span>
            <strong><?= e($m['label']) ?></strong><br>
            <small><?= e($m['note']) ?></small>
        </span>
    </label>
<?php endforeach; ?>

<p class="notice">
    Cash on Delivery is paid on arrival. GCash, Maya, GrabPay, ShopeePay, and bank transfer orders stay unpaid until the store confirms payment instructions and receipt. Credit / Debit Card requires a live gateway integration — no card information is collected on this site.
</p>
        </div>

        <aside class="panel order-summary">
            <h2>Your order</h2>
            <?php foreach ($items as $p): ?>
                <div class="summary-line">
                    <span><?= e($p['product_name']) ?> × <?= $p['quantity'] ?></span>
                    <strong><?= money(price($p) * $p['quantity']) ?></strong>
                </div>
            <?php endforeach; ?>
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
            <button class="button">Place order <i class="fa-solid fa-arrow-right"></i></button>
            <small>By ordering, you agree to our <a href="/privacy.php">terms and privacy policy</a>.</small>
            <small>Preview storefront · Sample products and prices</small>
        </aside>
    </form>
</main>
<?php require __DIR__ . '/includes/user-end.php'; ?>