<?php
require __DIR__ . '/../includes/functions.php';

$u = require_user();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    q('UPDATE notifications SET is_read=TRUE WHERE user_id=?', [$u['id']]);
    flash('All notifications marked as read.');
    redirect('/user/notifications.php');
}

$pageTitle = 'Notifications';
require __DIR__ . '/../includes/user-start.php';
?>
<div class="top-row">
    <div class="page-heading">
        <h1>A little update for you.</h1>
        <p>Your orders and community updates, in one place.</p>
    </div>
    <form method="post">
        <?= csrf() ?>
        <button class="small-button">Mark all as read</button>
    </form>
</div>

<div class="panel">
    <?php
    $notes = q(
        'SELECT * FROM notifications WHERE user_id=? ORDER BY created_at DESC',
        [$u['id']]
    )->fetchAll();

    if (!$notes) {
        echo '<p>You’re all caught up.</p>';
    }

    foreach ($notes as $n): ?>
        <div class="notification">
            <p>
                <?php if (!$n['is_read']): ?>
                    <i class="fa-solid fa-circle" style="font-size:7px;color:#6d994d"></i>
                <?php endif; ?>
                <?= e($n['content']) ?>
            </p>
            <small><?= date('M d, Y · g:i a', strtotime($n['created_at'])) ?></small>
        </div>
    <?php endforeach; ?>
</div>
<?php require __DIR__ . '/../includes/user-end.php'; ?>