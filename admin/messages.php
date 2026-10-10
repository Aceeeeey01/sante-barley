<?php
require __DIR__ . '/../includes/functions.php';
require_admin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $status = $_POST['status'] ?? '';
    if (in_array($status, ['Unread', 'Read', 'Resolved'], true)) {
        q('UPDATE messages SET status=? WHERE id=?', [$status, (int) $_POST['id']]);
        flash('Message status updated.');
    }
    redirect('/admin/messages.php');
}

$pageTitle = 'Messages';
require __DIR__ . '/../includes/admin-start.php';
?>
<div class="page-heading">
    <h1>A little help goes a long way.</h1>
    <p>Customer questions and support requests.</p>
</div>

<?php
$messages = q('SELECT * FROM messages ORDER BY created_at DESC')->fetchAll();

if (!$messages) {
    echo '<div class="empty-state"><h2>Your inbox is clear.</h2><p>New customer messages will appear here.</p></div>';
}

foreach ($messages as $m): ?>
    <article class="panel" style="margin-bottom:20px">
        <h3><?= e($m['subject']) ?></h3>
        <p><?= nl2br(e($m['content'])) ?></p>
        <p style="font-size:11px">
            <?= e($m['name']) ?> · <?= e($m['email']) ?> · <?= date('M d, Y', strtotime($m['created_at'])) ?>
        </p>
        <form method="post" class="inline-form">
            <?= csrf() ?>
            <input type="hidden" name="id" value="<?= $m['id'] ?>">
            <select name="status" aria-label="Message status">
                <?php foreach (['Unread', 'Read', 'Resolved'] as $s): ?>
                    <option <?= $m['status'] === $s ? 'selected' : '' ?>><?= $s ?></option>
                <?php endforeach; ?>
            </select>
            <button class="small-button">Update</button>
            <a href="mailto:<?= e($m['email']) ?>">Reply by email</a>
        </form>
    </article>
<?php endforeach; ?>
<?php require __DIR__ . '/../includes/admin-end.php'; ?>