<?php
require __DIR__ . '/../includes/functions.php';
require_admin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();

    if (isset($_POST['toggle'])) {
        q('UPDATE promotions SET active=NOT active WHERE id=?', [(int) $_POST['toggle']]);
        flash('Promotion visibility updated.');
    } else {
        $title       = trim($_POST['title'] ?? '');
        $description = trim($_POST['description'] ?? '');

        if (
            strlen($title) < 3 || strlen($title) > 150
            || strlen($description) < 5 || strlen($description) > 1000
        ) {
            flash('Enter a title (3–150 characters) and description (5–1,000 characters).', 'error');
        } else {
            q('INSERT INTO promotions(title,description) VALUES (?,?)', [$title, $description]);
            flash('Promotion created.');
        }
    }
    redirect('/admin/promotions.php');
}

$pageTitle = 'Promotions';
require __DIR__ . '/../includes/admin-start.php';
?>
<div class="page-heading">
    <h1>Share a little extra goodness.</h1>
    <p>Create announcements for the shop. Set product discounts in the product editor.</p>
</div>

<form method="post" class="panel form-grid">
    <?= csrf() ?>
    <?php field('Promotion title', 'title'); ?>
    <label class="field full">Description
        <textarea name="description" maxlength="1000" required></textarea>
    </label>
    <div class="full"><button class="button">Create promotion</button></div>
</form>

<?php foreach (q('SELECT * FROM promotions ORDER BY id DESC') as $p): ?>
    <div class="panel" style="margin-top:20px">
        <h3><?= e($p['title']) ?></h3>
        <p><?= e($p['description']) ?></p>
        <form method="post">
            <?= csrf() ?>
            <button class="small-button" name="toggle" value="<?= $p['id'] ?>">
                <?= $p['active'] ? 'Hide promotion' : 'Publish promotion' ?>
            </button>
        </form>
    </div>
<?php endforeach; ?>
<?php require __DIR__ . '/../includes/admin-end.php'; ?>