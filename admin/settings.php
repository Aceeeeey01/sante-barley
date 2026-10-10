<?php
require __DIR__ . '/../includes/functions.php';
require_admin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $fee       = filter_var($_POST['shipping_fee'] ?? '', FILTER_VALIDATE_FLOAT);
    $threshold = filter_var($_POST['free_shipping_threshold'] ?? '', FILTER_VALIDATE_FLOAT);

    if (
        $fee === false || $fee < 0 || $fee > 10000
        || $threshold === false || $threshold < 0 || $threshold > 1000000
    ) {
        flash('Enter valid shipping amounts.', 'error');
    } else {
        $db->beginTransaction();
        q('UPDATE settings SET value=? WHERE key=?', [(string) $fee, 'shipping_fee']);
        q('UPDATE settings SET value=? WHERE key=?', [(string) $threshold, 'free_shipping_threshold']);
        $db->commit();
        flash('Store settings saved.');
    }
    redirect('/admin/settings.php');
}

$pageTitle = 'Settings';
require __DIR__ . '/../includes/admin-start.php';
?>
<div class="page-heading">
    <h1>The details that make it yours.</h1>
    <p>Manage your store’s delivery settings.</p>
</div>

<form class="panel form-grid" method="post">
    <?= csrf() ?>
    <?php
    $fields = [
        'shipping_fee'            => 'Flat shipping fee (PHP)',
        'free_shipping_threshold' => 'Free shipping minimum (PHP)',
    ];
    foreach ($fields as $key => $label): ?>
        <label class="field"><?= $label ?>
            <input type="number" step="0.01" min="0"
                   name="<?= $key ?>"
                   value="<?= e(setting($key)) ?>" required>
        </label>
    <?php endforeach; ?>
    <div class="full"><button class="button">Save settings</button></div>
</form>
<?php require __DIR__ . '/../includes/admin-end.php'; ?>