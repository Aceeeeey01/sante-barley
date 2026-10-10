<header class="site-header">
    <div class="nav-wrap">
        <a href="/" class="brand" aria-label="Santé Barley Philippines home">
            <span class="brand-name">
                santé<span class="brand-leaf"><i class="fa-solid fa-leaf"></i></span>
            </span>
            <span class="brand-sub">BARLEY PHILIPPINES</span>
        </a>

        <nav class="main-nav" aria-label="Main navigation">
            <a class="<?= $active === 'home' ? 'active' : '' ?>" href="/">Home</a>
            <a href="/about.php">About Santé</a>
            <a href="/#why-barley">Why Barley</a>
            <a class="<?= $active === 'shop' ? 'active' : '' ?>" href="/shop.php">
                Products <i class="fa-solid fa-angle-down"></i>
            </a>
            <a href="/#wellness">Wellness</a>
            <a href="/#how-to-use">How to Use</a>
            <a href="/faq.php">FAQs</a>
            <a href="/contact.php">Contact</a>
        </nav>

        <div class="nav-actions">
            <a href="/shop.php" class="icon-button" aria-label="Search products">
                <i class="fa-solid fa-magnifying-glass"></i>
            </a>
            <a href="<?= user() ? '/user/dashboard.php' : '/login.php' ?>"
               class="icon-button" aria-label="My account">
                <i class="fa-regular fa-user"></i>
            </a>
            <a href="/cart.php" class="icon-button cart-icon" aria-label="Shopping cart">
                <i class="fa-solid fa-bag-shopping"></i>
                <span class="cart-count"><?= cart_count() ?></span>
            </a>
            <a href="<?= user() ? '/user/dashboard.php' : '/register.php' ?>"
               class="button nav-join">
                <?= user() ? 'My Account' : 'Join the wellness' ?>
                <i class="fa-solid fa-arrow-right"></i>
            </a>
            <button class="icon-button mobile-toggle"
                    aria-label="Open navigation"
                    aria-expanded="false">
                <i class="fa-solid fa-bars"></i>
            </button>
        </div>
    </div>
</header>