<?php
$pageTitle = 'Privacy & terms';
require __DIR__ . '/includes/header.php';
?>
<main class="page-wrap content-narrow" id="main">
    <div class="page-heading">
        <span class="eyebrow">CLEAR, THOUGHTFUL, TRANSPARENT</span>
        <h1>Privacy &amp; terms.</h1>
        <p>Last updated <?= date('F Y') ?> · Demonstration storefront</p>
    </div>

    <h2>Your information</h2>
    <p>Account details, delivery addresses, order records, reviews, and messages are stored to support the shopping experience. Passwords are hashed; payment card information is never requested or stored. Essential session cookies keep you signed in and remember your shopping bag.</p>

    <h2>Your choices</h2>
    <p>Update your account details and password in My Profile. Contact support to request account assistance, access, correction, or deletion of personal information, subject to applicable record retention obligations. Do not enter real personal or financial information into this demonstration environment.</p>

    <h2>Shopping terms</h2>
    <p>This is an independent demonstration, not an official Santé sales channel. Product artwork, sample prices, pack sizes, inventory, and orders are for demonstration. No fulfillment or payment processing is provided. Before commercial launch, the operator must verify product information, establish authorized supply and fulfillment, publish business contact details and applicable policies, and configure payment and data protection services.</p>

    <h2>Responsible wellness information</h2>
    <p>Content is for general educational and wellness purposes only. Products are not presented as cures, disease prevention, or replacements for prescription medicine. Seek professional guidance regarding medical conditions, medications, pregnancy, allergies, or other health concerns.</p>

    <h2>Third-party resources</h2>
    <p>This website loads fonts from Google Fonts and icons from cdnjs. These providers receive standard connection information when those resources load. External links take you to third-party websites with their own terms and privacy policies.</p>

    <h2>Questions?</h2>
    <p><a href="/contact.php" class="text-link">Contact support <i class="fa-solid fa-arrow-right"></i></a></p>
</main>
<?php require __DIR__ . '/includes/footer.php'; ?>