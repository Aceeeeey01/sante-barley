<?php
require __DIR__ . '/includes/functions.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();

    $name    = trim($_POST['name'] ?? '');
    $email   = trim($_POST['email'] ?? '');
    $subject = trim($_POST['subject'] ?? '');
    $content = trim($_POST['content'] ?? '');

    if (time() - ($_SESSION['last_message'] ?? 0) < 60) {
        $error = 'Please wait a minute before sending another message.';
    } elseif (
        strlen($name) < 2
        || strlen($name) > 120
        || !filter_var($email, FILTER_VALIDATE_EMAIL)
        || strlen($email) > 254
        || strlen($subject) < 3
        || strlen($subject) > 180
        || strlen($content) < 10
        || strlen($content) > 5000
    ) {
        $error = 'Enter your name, a valid email, a subject, and a message of 10–5,000 characters.';
    } else {
        q('INSERT INTO messages(name,email,subject,content) VALUES (?,?,?,?)', [
            $name,
            $email,
            $subject,
            $content,
        ]);
        $_SESSION['last_message'] = time();
        flash('Your message has been received. Thank you for reaching out.');
        redirect('/contact.php');
    }
}

$pageTitle = 'Contact us';

if (user()) {
    require __DIR__ . '/includes/user-start.php';
} else {
    require __DIR__ . '/includes/header.php';
}
?>
<?php if (!user()): ?>
    <div class="breadcrumb">
        <a href="/">Home</a><span>/</span>Contact
    </div>
<?php endif; ?>

<div class="page-heading">
    <span class="eyebrow">LET’S TALK</span>
    <h1>A little help, a little care.</h1>
    <p>Questions about products, your order, or getting started? Leave us a message.</p>
</div>

<div class="contact-grid">
    <aside class="contact-info">
        <i class="fa-solid fa-comments"></i>
        <h2 style="font-size:28px">Good conversations<br>start here.</h2>
        <p>Include your order number if you’re asking about a purchase. Please do not include passwords, payment credentials, or sensitive medical information.</p>
        <h3 style="margin-top:30px">
            <i class="fa-solid fa-location-dot" style="font-size:14px;margin-right:8px"></i> Philippines
        </h3>
        <p>For medical or health-related questions, please speak with a qualified healthcare professional.</p>
        <a href="/faq.php" class="text-link">
            Explore our FAQs <i class="fa-solid fa-arrow-right" style="font-size:12px;margin:0"></i>
        </a>
    </aside>

    <div class="panel">
        <?php if ($error): ?>
            <div class="error-box"><?= e($error) ?></div>
        <?php endif; ?>
        <form class="form-grid" method="post">
            <?= csrf() ?>
            <?php
            field('Your name', 'name', $_POST['name'] ?? user()['full_name'] ?? '');
            field('Email address', 'email', $_POST['email'] ?? user()['email'] ?? '', 'email');
            ?>
            <label class="field full">Subject
                <input name="subject" required maxlength="180"
                       value="<?= e($_POST['subject'] ?? '') ?>"
                       placeholder="How can we help?">
            </label>
            <label class="field full">Your message
                <textarea name="content" required minlength="10" maxlength="5000"
                          placeholder="Tell us a little more..."><?= e($_POST['content'] ?? '') ?></textarea>
            </label>
            <div class="full">
                <button class="button">Send message <i class="fa-solid fa-arrow-right"></i></button>
            </div>
        </form>
    </div>
</div>
<?php
if (user()) {
    require __DIR__ . '/includes/user-end.php';
} else {
    require __DIR__ . '/includes/footer.php';
}
?>