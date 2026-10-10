<?php
require __DIR__ . '/../includes/functions.php';
require_admin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $status = $_POST['status'] ?? '';
    if (in_array($status, ['Approved', 'Rejected', 'Pending'], true)) {
        q('UPDATE reviews SET status=? WHERE id=?', [$status, (int) $_POST['id']]);
        flash('Review moderation saved.');
    }
    redirect('/admin/reviews.php');
}

$pageTitle = 'Reviews';
require __DIR__ . '/../includes/admin-start.php';
?>
<div class="page-heading">
    <h1>Listen to your community.</h1>
    <p>Moderate customer reviews. Do not publish unsupported medical claims.</p>
</div>

<?php
$reviews = q(
    'SELECT r.*, u.full_name, p.product_name
     FROM reviews r
     JOIN users u ON u.id=r.user_id
     JOIN products p ON p.id=r.product_id
     ORDER BY r.created_at DESC'
)->fetchAll();

if (!$reviews) {
    echo '<div class="empty-state"><h2>No reviews yet.</h2><p>Verified customer reviews will appear here.</p></div>';
}

foreach ($reviews as $r): ?>
    <article class="panel" style="margin-bottom:20px">
        <h3><?= e($r['product_name']) ?> · <?= str_repeat('★', (int) $r['rating']) ?></h3>
        <p><?= e($r['content']) ?></p>
        <p><?= e($r['full_name']) ?></p>
        <form method="post" class="inline-form">
            <?= csrf() ?>
            <input type="hidden" name="id" value="<?= $r['id'] ?>">
            <select name="status" aria-label="Moderation status">
                <?php foreach (['Pending', 'Approved', 'Rejected'] as $s): ?>
                    <option <?= $r['status'] === $s ? 'selected' : '' ?>><?= $s ?></option>
                <?php endforeach; ?>
            </select>
            <button class="small-button">Save moderation</button>
        </form>
    </article>
<?php endforeach; ?>
<?php require __DIR__ . '/../includes/admin-end.php'; ?>