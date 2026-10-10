<?php
$pageTitle = 'Frequently asked questions';
require __DIR__ . '/includes/header.php';
?>
<main class="page-wrap content-narrow" id="main">
    <div class="page-heading">
        <span class="eyebrow">A LITTLE CLARITY GOES A LONG WAY</span>
        <h1>We’re here to help.</h1>
        <p>Answers to your everyday questions.</p>
    </div>

    <div class="faq-list">
        <h2>Products &amp; wellness</h2>
        <details open>
            <summary>What is Santé Barley?</summary>
            <p>Santé Barley is marketed as a wellness and nutritional product. Explore the product label and official manufacturer information for ingredients and directions. It is not a medicine or a replacement for prescribed treatment.</p>
        </details>
        <details>
            <summary>How do I use the products?</summary>
            <p>Preparation and serving instructions differ between products. Follow your product’s current label and official instructions. Do not assume one product’s directions apply to another format.</p>
        </details>
        <details>
            <summary>Can I use these products if I have a health concern?</summary>
            <p>Consult a qualified healthcare professional regarding medical conditions, medications, pregnancy, breastfeeding, allergies, or other health concerns before using a product.</p>
        </details>
        <details>
            <summary>Are the images and prices official?</summary>
            <p>This is an independent demonstration storefront. Product illustrations, sample prices, and sample pack sizes are illustrative. Confirm actual packaging, prices, availability, ingredients, and directions with an authorized seller before purchase.</p>
        </details>

        <h2 id="shipping">Shipping &amp; delivery</h2>
        <details>
            <summary>Where do you deliver?</summary>
            <p>The store is designed for Philippine delivery addresses. Actual service availability and delivery timing must be confirmed by the store before dispatch. Contact support with your location and order number.</p>
        </details>
        <details>
            <summary>How much is shipping?</summary>
            <p>The current flat shipping fee is <?= money((float) setting('shipping_fee', '120')) ?>. Orders of <?= money((float) setting('free_shipping_threshold', '2500')) ?> or more qualify for free shipping. Your final total appears at checkout.</p>
        </details>
        <details>
            <summary>How do I track an order?</summary>
            <p>Sign in and visit <a class="text-link" href="/user/orders.php">My orders</a>. The order status changes as the store confirms, processes, ships, and delivers your order. Updates are also posted in your notifications.</p>
        </details>

        <h2>Payment &amp; accounts</h2>
        <details>
    <summary>Which payment methods are supported?</summary>
    <p>Choose <strong>Cash on Delivery</strong>, <strong>GCash</strong>, <strong>Maya</strong>, <strong>GrabPay</strong>, <strong>ShopeePay</strong>, <strong>Credit / Debit Card</strong>, or <strong>Bank Transfer</strong>.</p>
    <p>Online payment is not collected on this demonstration site. GCash, Maya, GrabPay, ShopeePay, and bank transfer orders stay unpaid until the store provides verified payment instructions and confirms receipt. Card payments require a live gateway integration and are not processed here.</p>
    <p><strong>Never send money to unverified payment details.</strong></p>
</details>
        <details>
            <summary>How do I update my account?</summary>
            <p>Visit <a class="text-link" href="/user/profile.php">My profile</a> to change your name, email, mobile number, picture, or password. Your default delivery details can be updated under Addresses.</p>
        </details>

        <h2 id="returns">Returns &amp; order changes</h2>
        <details>
            <summary>Can I cancel or change my order?</summary>
            <p>Contact support promptly with your order number. The store can cancel an eligible order before it is shipped. A paid order requires a verified refund before cancellation is recorded.</p>
        </details>
        <details>
            <summary>What if my product arrives damaged or incorrect?</summary>
            <p>Keep the packaging and order details, and contact the store with a description of the issue. The store will assess the concern and explain applicable return or replacement options. This does not limit rights under applicable Philippine consumer law.</p>
        </details>
    </div>

    <div class="panel" style="margin-top:35px;text-align:center">
        <h2>Still have a question?</h2>
        <p>We’d be happy to point you in the right direction.</p>
        <a class="button" href="/contact.php">Get in touch <i class="fa-solid fa-arrow-right"></i></a>
    </div>
</main>
<?php require __DIR__ . '/includes/footer.php'; ?>