<?php
require __DIR__ . '/includes/functions.php';

if (user()) {
    redirect('/user/dashboard.php');
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();

    $name     = trim($_POST['full_name'] ?? '');
    $username = trim($_POST['username'] ?? '');
    $email    = strtolower(trim($_POST['email'] ?? ''));
    $mobile   = trim($_POST['mobile'] ?? '');
    $password = $_POST['password'] ?? '';

    if (
        strlen($name) < 2
        || strlen($name) > 120
        || !preg_match('/^[a-zA-Z0-9_]{3,60}$/', $username)
        || !filter_var($email, FILTER_VALIDATE_EMAIL)
        || strlen($email) > 254
        || !preg_match('/^(09[0-9]{9}|\+639[0-9]{9})$/', $mobile)
    ) {
        $error = 'Enter your name, a valid username, email, and Philippine mobile number (09xxxxxxxxx).';
    } elseif (strlen($password) < 12 || strlen($password) > 72) {
        $error = 'Use a password with 12–72 bytes.';
    } elseif ($password !== ($_POST['confirm_password'] ?? '')) {
        $error = 'Your passwords do not match.';
    } else {
        try {
            $id = q(
                'INSERT INTO users(full_name,username,email,mobile,password)
                 VALUES (?,?,?,?,?)
                 RETURNING id',
                [$name, $username, $email, $mobile, password_hash($password, PASSWORD_DEFAULT)]
            )->fetchColumn();

            session_regenerate_id(true);
            $_SESSION['uid'] = $id;

            q('INSERT INTO notifications(user_id,content) VALUES (?,?)', [
                $id,
                'Welcome to your wellness community! Explore products and build your everyday routine.',
            ]);

            flash('Welcome! Your account is ready.');
            redirect('/user/dashboard.php');
        } catch (PDOException $e) {
            if ($e->getCode() === '23505') {
                $error = 'That email or username is already registered.';
            } else {
                throw $e;
            }
        }
    }
}

$pageTitle = 'Create an account';
require __DIR__ . '/includes/header.php';
?>
<main id="main" class="auth-layout">
    <section class="auth-story">
        <i class="fa-solid fa-seedling"></i>
        <span class="eyebrow">GROW WITH US</span>
        <h2>A little green.<br>A little good.<br>Every day.</h2>
        <p>Your wellness journey begins with one small choice. Join our community today.</p>
    </section>
    <section class="auth-form">
        <h1>Let’s grow together.</h1>
        <p>Create your Santé Barley account.</p>

        <?php if ($error): ?>
            <div class="error-box" role="alert"><?= e($error) ?></div>
        <?php endif; ?>

        <form method="post" class="form-grid">
            <?= csrf() ?>
            <?php
            field('Full name', 'full_name', $_POST['full_name'] ?? '');
            field('Username', 'username', $_POST['username'] ?? '');
            field('Email address', 'email', $_POST['email'] ?? '', 'email');
            field('Mobile number', 'mobile', $_POST['mobile'] ?? '', 'tel');
            field('Password (12+ characters)', 'password', '', 'password');
            field('Confirm password', 'confirm_password', '', 'password');
            ?>
            <p class="fine-print full" style="text-align:left;margin:0">
                By creating an account, you agree to our
                <a href="/privacy.php" style="text-decoration:underline">privacy policy and terms</a>.
            </p>
            <button class="button full">Create account <i class="fa-solid fa-arrow-right"></i></button>
        </form>

        <p class="auth-bottom">Already part of the community? <a href="/login.php">Sign in</a></p>
    </section>
</main>
<?php include __DIR__ . '/includes/footer.php'; ?>