<?php
require __DIR__ . '/../includes/functions.php';

$u = require_user();
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();

    $values = [];
    foreach (['address', 'province', 'city', 'barangay', 'postal_code'] as $f) {
        $v = trim($_POST[$f] ?? '');
        if (!$v || strlen($v) > 100) {
            $error = 'Complete all address fields (maximum 100 characters each).';
        }
        $values[] = $v;
    }

    if (!preg_match('/^[0-9]{4}$/', end($values))) {
        $error = 'Use a four-digit Philippine postal code.';
    }

    if (!$error) {
        $values[] = $u['id'];
        q(
            'UPDATE users SET address=?,province=?,city=?,barangay=?,postal_code=?,updated_at=NOW() WHERE id=?',
            $values
        );
        flash('Your delivery address has been saved.');
        redirect('/user/addresses.php');
    }
}

$pageTitle = 'My address';
require __DIR__ . '/../includes/user-start.php';
?>
<div class="page-heading">
    <h1>Your everyday destination.</h1>
    <p>Save your default delivery address for a simpler checkout.</p>
</div>

<?php if ($error): ?>
    <div class="error-box"><?= e($error) ?></div>
<?php endif; ?>

<form class="panel form-grid" method="post">
    <?= csrf() ?>
    <?php
    $fields = [
        'address'     => 'House number / street',
        'province'    => 'Province',
        'city'        => 'City / municipality',
        'barangay'    => 'Barangay',
        'postal_code' => 'Postal code',
    ];
    foreach ($fields as $key => $label) {
        field($label, $key, $_POST[$key] ?? $u[$key]);
    }
    ?>
    <div class="full"><button class="button">Save address</button></div>
</form>
<?php require __DIR__ . '/../includes/user-end.php'; ?>