<?php
require __DIR__ . '/includes/functions.php';
require_user();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $_SESSION = [];
    session_regenerate_id(true);
    redirect('/login.php');
}

$pageTitle = 'Sign out';
require __DIR__ . '/includes/header.php';
?>
<main id="main" class="page-wrap">
    <div class="empty-state">
        <h1>Ready to sign out?</h1>
        <form method="post">
            <?= csrf() ?>
            <button class="button">Sign out</button>
        </form>
    </div>
</main>
<?php require __DIR__ . '/includes/footer.php'; ?>