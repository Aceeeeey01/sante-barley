<?php
require __DIR__ . '/../includes/functions.php';

$u = require_user();
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    try {
        if (($_POST['action'] ?? '') === 'password') {
            if (!password_verify($_POST['current_password'] ?? '', $u['password'])) {
                throw new RuntimeException('Your current password is incorrect.');
            }
            $new = $_POST['new_password'] ?? '';
            if (strlen($new) < 12 || strlen($new) > 72 || $new !== ($_POST['confirm_password'] ?? '')) {
                throw new RuntimeException('Use 12–72 bytes and match your confirmation password.');
            }
            q('UPDATE users SET password=?,updated_at=NOW() WHERE id=?', [
                password_hash($new, PASSWORD_DEFAULT),
                $u['id'],
            ]);
            session_regenerate_id(true);
            flash('Your password has been changed.');
            redirect('/user/profile.php');
        }

        $name     = trim($_POST['full_name'] ?? '');
        $email    = strtolower(trim($_POST['email'] ?? ''));
        $username = trim($_POST['username'] ?? '');
        $mobile   = trim($_POST['mobile'] ?? '');

        if (
            strlen($name) < 2
            || strlen($name) > 120
            || !filter_var($email, FILTER_VALIDATE_EMAIL)
            || strlen($email) > 254
            || !preg_match('/^[a-zA-Z0-9_]{3,60}$/', $username)
            || !preg_match('/^(09[0-9]{9}|\+639[0-9]{9})$/', $mobile)
        ) {
            throw new RuntimeException('Please enter a valid name, username, email, and Philippine mobile number.');
        }

        $image = $u['profile_image'];

        if (!empty($_FILES['profile_image']['name'])) {
            $file = $_FILES['profile_image'];
            if ($file['error'] !== UPLOAD_ERR_OK || $file['size'] > 2000000) {
                throw new RuntimeException('Choose a JPG, PNG, or WebP image under 2 MB.');
            }
            $mime = (new finfo(FILEINFO_MIME_TYPE))->file($file['tmp_name']);
            if (
                !in_array($mime, ['image/jpeg', 'image/png', 'image/webp'], true)
                || !getimagesize($file['tmp_name'])
            ) {
                throw new RuntimeException('Choose a valid JPG, PNG, or WebP image.');
            }
            $dir = __DIR__ . '/../var/avatars';
            if (!is_dir($dir)) {
                mkdir($dir, 0700, true);
            }
            $filename = bin2hex(random_bytes(16));
            if (!move_uploaded_file($file['tmp_name'], $dir . '/' . $filename)) {
                throw new RuntimeException('Unable to save your photo.');
            }
            $image = $filename;
        }

        q(
            'UPDATE users SET full_name=?,username=?,email=?,mobile=?,profile_image=?,updated_at=NOW() WHERE id=?',
            [$name, $username, $email, $mobile, $image, $u['id']]
        );
        flash('Your profile has been updated.');
        redirect('/user/profile.php');
    } catch (PDOException $e) {
        $error = $e->getCode() === '23505'
            ? 'That email or username is already in use.'
            : 'Unable to update your profile.';
    } catch (RuntimeException $e) {
        $error = $e->getMessage();
    }
}

$pageTitle = 'My profile';
require __DIR__ . '/../includes/user-start.php';
?>
<div class="page-heading">
    <h1>My profile.</h1>
    <p>A space that’s yours. Keep your details up to date.</p>
</div>

<?php if ($error): ?>
    <div class="error-box"><?= e($error) ?></div>
<?php endif; ?>

<div class="panel">
    <div class="avatar">
        <?php if ($u['profile_image']): ?>
            <img src="/user/avatar.php" alt="Your profile picture">
        <?php else: ?>
            <?= e(mb_substr($u['full_name'], 0, 1)) ?>
        <?php endif; ?>
    </div>
    <form method="post" enctype="multipart/form-data" class="form-grid">
        <?= csrf() ?>
        <?php
        $fields = [
            'full_name' => 'Full name',
            'username'  => 'Username',
            'email'     => 'Email address',
            'mobile'    => 'Mobile number',
        ];
        foreach ($fields as $key => $label) {
            field($label, $key, $_POST[$key] ?? $u[$key], $key === 'email' ? 'email' : 'text');
        }
        ?>
        <label class="field full">Profile picture (JPG, PNG, WebP · max 2 MB)
            <input type="file" name="profile_image" accept="image/jpeg,image/png,image/webp">
        </label>
        <div class="full">
            <button class="button">Update profile</button>
            <a href="/user/addresses.php" class="text-link" style="margin-left:20px">Manage my address</a>
        </div>
    </form>
</div>

<div class="panel" style="margin-top:25px">
    <h2>Change password</h2>
    <form method="post" class="form-grid">
        <?= csrf() ?>
        <input type="hidden" name="action" value="password">
        <?php
        field('Current password', 'current_password', '', 'password');
        field('New password (12+ characters)', 'new_password', '', 'password');
        field('Confirm new password', 'confirm_password', '', 'password');
        ?>
        <div class="full"><button class="button outline-button">Change password</button></div>
    </form>
</div>
<?php require __DIR__ . '/../includes/user-end.php'; ?>