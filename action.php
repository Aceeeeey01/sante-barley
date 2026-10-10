<?php
require __DIR__ . '/includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit;
}

verify_csrf();

$action = $_POST['action'] ?? '';
$id = (int) ($_POST['id'] ?? 0);

try {
    if ($action === 'add_cart') {
        $p = q("SELECT * FROM products WHERE id=? AND status='Active'", [$id])->fetch();
        $qty = filter_var($_POST['quantity'] ?? 1, FILTER_VALIDATE_INT);

        if (!$p || !$qty || $qty < 1 || $qty > 99) {
            throw new RuntimeException('Please choose a valid product and quantity.');
        }

        $total = ($_SESSION['cart'][$id] ?? 0) + $qty;
        if ($total > $p['stock'] || $total > 99) {
            throw new RuntimeException('The requested quantity is not available.');
        }

        $_SESSION['cart'][$id] = $total;

        if (str_contains($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json')) {
            header('Content-Type: application/json');
            echo json_encode([
                'message' => $p['product_name'] . ' added to your cart.',
                'count'   => cart_count(),
            ]);
            exit;
        }

        flash('Added to your shopping cart.');
        redirect(isset($_POST['buy_now']) ? '/checkout.php' : '/cart.php');
    }

    if ($action === 'update_cart') {
        if (isset($_POST['remove'])) {
            unset($_SESSION['cart'][(int) $_POST['remove']]);
            flash('Product removed.');
            redirect('/cart.php');
        }

        $next = [];
        foreach ($_POST['quantity'] ?? [] as $pid => $n) {
            $n = filter_var($n, FILTER_VALIDATE_INT);
            $p = q("SELECT stock FROM products WHERE id=? AND status='Active'", [(int) $pid])->fetch();

            if ($n === false || $n < 0 || $n > 99 || !$p || $n > $p['stock']) {
                throw new RuntimeException('One or more quantities exceed available stock.');
            }

            if ($n) {
                $next[(int) $pid] = $n;
            }
        }

        $_SESSION['cart'] = $next;
        flash('Cart updated.');
        redirect('/cart.php');
    }

    if ($action === 'wishlist') {
        $u = require_user();
        $exists = q(
            'SELECT 1 FROM wishlist WHERE user_id=? AND product_id=?',
            [$u['id'], $id]
        )->fetchColumn();

        if ($exists) {
            q('DELETE FROM wishlist WHERE user_id=? AND product_id=?', [$u['id'], $id]);
            flash('Removed from your wishlist.');
        } else {
            if (!q("SELECT id FROM products WHERE id=? AND status='Active'", [$id])->fetchColumn()) {
                throw new RuntimeException('This product is unavailable.');
            }
            q(
                'INSERT INTO wishlist(user_id,product_id) VALUES (?,?) ON CONFLICT DO NOTHING',
                [$u['id'], $id]
            );
            flash('Saved to your wishlist.');
        }

        redirect('/user/wishlist.php');
    }

    if ($action === 'logout') {
        $_SESSION = [];
        session_regenerate_id(true);
        flash('You have been signed out.');
        redirect('/login.php');
    }

    if ($action === 'review') {
        $u = require_user();
        $rating = (int) ($_POST['rating'] ?? 0);
        $content = trim($_POST['content'] ?? '');

        if ($rating < 1 || $rating > 5 || strlen($content) < 5 || strlen($content) > 2000) {
            throw new RuntimeException('Enter a rating and a review between 5 and 2,000 characters.');
        }

        $delivered = q(
            "SELECT 1
             FROM order_items i
             JOIN orders o ON o.id = i.order_id
             WHERE o.user_id=? AND i.product_id=? AND o.order_status='Delivered'",
            [$u['id'], $id]
        )->fetchColumn();

        if (!$delivered) {
            throw new RuntimeException('Reviews are available after your purchase is delivered.');
        }

        q(
            "INSERT INTO reviews(user_id,product_id,rating,content)
             VALUES (?,?,?,?)
             ON CONFLICT(user_id,product_id)
             DO UPDATE SET rating=EXCLUDED.rating, content=EXCLUDED.content, status='Pending'",
            [$u['id'], $id, $rating, $content]
        );

        flash('Your review has been submitted for moderation.');
        redirect('/product.php?id=' . $id);
    }

    throw new RuntimeException('This action is unavailable.');
} catch (RuntimeException $e) {
    if (str_contains($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json')) {
        http_response_code(422);
        header('Content-Type: application/json');
        echo json_encode(['message' => $e->getMessage()]);
        exit;
    }

    flash($e->getMessage(), 'error');
    redirect('/cart.php');
}