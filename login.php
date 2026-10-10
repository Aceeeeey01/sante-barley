<?php
require __DIR__ . '/includes/functions.php';

if (user()) {
    redirect(is_admin() ? '/admin/' : '/user/dashboard.php');
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();

    $identity = strtolower(trim($_POST['email'] ?? ''));
    $password = (string) ($_POST['password'] ?? '');

    $key   = hash('sha256', $identity . '|' . ($_SERVER['REMOTE_ADDR'] ?? ''));
    $ipKey = 'ip:' . hash('sha256', $_SERVER['REMOTE_ADDR'] ?? '');

    /* -------- Rate-limit check (read-only) -------- */
    $limited = false;

    foreach ([$key => 10, $ipKey => 30] as $k => $max) {
        $row = q(
            'SELECT attempts, window_start
             FROM login_attempts
             WHERE identity=?',
            [$k]
        )->fetch();

        if (!$row) {
            continue;
        }

        $expired = strtotime((string) $row['window_start']) < time() - 900;

        if ($expired) {
            q('DELETE FROM login_attempts WHERE identity=?', [$k]);
            continue;
        }

        if ((int) $row['attempts'] >= $max) {
            $limited = true;
            break;
        }
    }

    if ($limited) {
        http_response_code(429);
        $error = 'Too many sign-in attempts. Please try again in 15 minutes.';
    } else {
        /* -------- Verify credentials -------- */
        $u = q('SELECT * FROM users WHERE email=?', [$identity])->fetch();

        $dummyHash = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2uheWG/igi.';
        $valid = password_verify($password, $u['password'] ?? $dummyHash);

        if ($u && $valid && $u['status'] === 'Active') {
            /* Successful login — clear counters for this identity only */
            q('DELETE FROM login_attempts WHERE identity=?', [$key]);

            /* Opportunistic cleanup of stale rows */
            q("DELETE FROM login_attempts WHERE window_start < NOW() - INTERVAL '1 day'");

            session_regenerate_id(true);
            $_SESSION['uid'] = $u['id'];

            redirect(
                q('SELECT id FROM admins WHERE user_id=?', [$u['id']])->fetchColumn()
                    ? '/admin/'
                    : '/user/dashboard.php'
            );
        }

        /* -------- Failed login — record the attempt -------- */
        foreach ([$key, $ipKey] as $k) {
            q(
                "INSERT INTO login_attempts(identity, attempts, window_start)
                 VALUES (?, 1, NOW())
                 ON CONFLICT(identity) DO UPDATE
                 SET attempts = CASE
                     WHEN login_attempts.window_start < NOW() - INTERVAL '15 minutes' THEN 1
                     ELSE login_attempts.attempts + 1
                 END,
                 window_start = CASE
                     WHEN login_attempts.window_start < NOW() - INTERVAL '15 minutes' THEN NOW()
                     ELSE login_attempts.window_start
                 END",
                [$k]
            );
        }

        /* Generic message prevents user enumeration */
        $error = 'The email or password is incorrect, or the account is inactive.';
    }
}

$pageTitle = 'Welcome back';
require __DIR__ . '/includes/header.php';
?>
<main id="main" class="auth-layout">
    <section class="auth-story">
        <i class="fa-solid fa-leaf"></i>
        <span class="eyebrow">YOUR EVERYDAY, A LITTLE GREENER</span>
        <h2>Good to have<br>you here.</h2>
        <p>Pick up where you left off. Your next little wellness moment is waiting.</p>
    </section>
    <section class="auth-form">
        <h1>Welcome back.</h1>
        <p>Sign in to your wellness space.</p>

        <?php if ($error): ?>
            <div class="error-box" role="alert"><?= e($error) ?></div>
        <?php endif; ?>

        <form method="post" class="form-stack">
            <?= csrf() ?>
            <?php
            field('Email address', 'email', $_POST['email'] ?? '', 'email');
            field('Password', 'password', '', 'password');
            ?>
            <button class="button">Sign in <i class="fa-solid fa-arrow-right"></i></button>
        </form>

        <p class="auth-bottom">New here? <a href="/register.php">Create an account</a></p>
        <p class="auth-bottom"><a href="/contact.php">Need help accessing your account?</a></p>
    </section>
</main>
<?php require __DIR__ . '/includes/footer.php'; ?>