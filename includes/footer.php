<section class="newsletter">
    <div>
        <span class="eyebrow">A LITTLE WELLNESS IN YOUR INBOX</span>
        <h2>Good things grow together.</h2>
        <p>Create an account for product updates and a little everyday inspiration.</p>
    </div>
    <a class="button cream-button" href="/register.php">
        Join our community <i class="fa-solid fa-arrow-right"></i>
    </a>
</section>

<footer>
    <div class="footer-grid">
        <div class="footer-about">
            <a href="/" class="brand">
                <span class="brand-name">santé<i class="fa-solid fa-leaf"></i></span>
                <span class="brand-sub">BARLEY PHILIPPINES</span>
            </a>
            <p>Rooted in nature.<br>Made for your everyday wellness.</p>
            <span class="footer-location">
                <i class="fa-solid fa-location-dot"></i> Philippines
            </span>
            <div class="socials">
                <a href="https://www.facebook.com/santeofficial"
                   aria-label="Santé on Facebook"
                   target="_blank" rel="noopener">
                    <i class="fa-brands fa-facebook-f"></i>
                </a>
                <a href="https://www.instagram.com/santeofficial/"
                   aria-label="Santé on Instagram"
                   target="_blank" rel="noopener">
                    <i class="fa-brands fa-instagram"></i>
                </a>
                <a href="https://www.tiktok.com/search?q=sante%20barley%20official"
                   aria-label="Find Santé on TikTok"
                   target="_blank" rel="noopener">
                    <i class="fa-brands fa-tiktok"></i>
                </a>
            </div>
        </div>

        <div>
            <h4>Explore</h4>
            <a href="/about.php">About Santé</a>
            <a href="/shop.php">All products</a>
            <a href="/#why-barley">Why barley?</a>
            <a href="/#wellness">Wellness journal</a>
            <a href="/contact.php">Contact us</a>
        </div>

        <div>
            <h4>Here to help</h4>
            <a href="/user/dashboard.php">My account</a>
            <a href="/user/orders.php">Track your order</a>
            <a href="/faq.php#shipping">Shipping &amp; delivery</a>
            <a href="/faq.php#returns">Returns &amp; exchanges</a>
            <a href="/faq.php">FAQs &amp; help center</a>
        </div>

        <div>
            <h4>Mindfully chosen.</h4>
            <p>Make room for small, positive choices in your daily routine.</p>
            <div class="payment-marks">
    <span><i class="fa-solid fa-money-bill-wave"></i> COD</span>
    <span>GCash</span>
    <span>Maya</span>
    <span>GrabPay</span>
    <span>ShopeePay</span>
    <span><i class="fa-solid fa-credit-card"></i> Card</span>
    <span><i class="fa-solid fa-building-columns"></i> Bank</span>
</div>
            <a class="text-link" href="/admin/">
                Store administration <i class="fa-solid fa-arrow-up-right-from-square"></i>
            </a>
        </div>
    </div>

    <div class="disclaimer">
        <i class="fa-solid fa-circle-info"></i>
        <p>This website provides general product and wellness information and is not a substitute for professional medical advice. Consult a qualified healthcare professional regarding medical conditions, medications, pregnancy, allergies, or other health concerns. Products are not intended to diagnose, treat, cure, or prevent any disease.</p>
    </div>

    <div class="footer-bottom">
        <span>© <?= date('Y') ?> Santé Barley Philippines. All Rights Reserved.</span>
        <span>Independent demonstration storefront · Product illustrations &amp; sample prices</span>
        <a href="/privacy.php">Privacy &amp; terms</a>
    </div>
</footer>

<button class="back-top icon-button" aria-label="Back to top">
    <i class="fa-solid fa-arrow-up"></i>
</button>
 <?php if (user()): logout_modal(); endif; ?>
</body>
</html>