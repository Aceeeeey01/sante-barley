<?php
$active = 'home';
require __DIR__ . '/includes/header.php';
?>
<main id="main">
    <section class="hero">
        <div class="hero-content">
            <div class="hero-eyebrow"><span></span> ROOTED IN NATURE. MADE FOR YOU.</div>
            <h1>Natural wellness<br>starts with<br><em>better choices.</em></h1>
            <p>Discover Santé Barley and explore a convenient<br class="desktop-br"> addition to your everyday wellness routine.</p>
            <div class="hero-buttons">
                <a href="/shop.php" class="button">Explore products <i class="fa-solid fa-arrow-right"></i></a>
                <a href="/about.php" class="button outline-button">Discover Santé <i class="fa-solid fa-arrow-up-right-from-square"></i></a>
            </div>
            <div class="hero-note">
                <span class="round-icon"><i class="fa-solid fa-seedling"></i></span>
                <div>
                    <strong>A little green. A little good. Every day.</strong>
                    <span>Thoughtfully chosen for your wellness journey.</span>
                </div>
            </div>
        </div>
        <div class="hero-art">
            <div class="landscape"></div>
            <div class="sun-halo"></div>
            <div class="nature-tag">
                <i class="fa-solid fa-leaf"></i>
                <span>Inspired by nature.<br><strong>Made for everyday life.</strong></span>
            </div>
            <div class="hero-orbit"></div>
            <div class="product-pedestal"></div>
            <img class="hero-box" src="/assets/images/products/sachets.svg" alt="Illustrated Santé Barley sachet box">
            <img class="hero-can" src="/assets/images/products/powder.svg" alt="Illustrated Santé Barley powder canister">
            <div class="green-drink">
                <div class="drink-rim"></div>
                <div class="drink-shine"></div>
            </div>
            <div class="leaf leaf-one"><i class="fa-solid fa-leaf"></i></div>
            <div class="leaf leaf-two"><i class="fa-solid fa-leaf"></i></div>
            <div class="hero-art-caption"><span class="caption-line"></span> YOUR DAILY DOSE OF GREEN</div>
            <div class="origin-badge">
                <i class="fa-solid fa-seedling"></i>
                <span>NATURE<br><strong>at heart</strong></span>
            </div>
            <span class="illustration-note">Product illustrations</span>
        </div>
        <div class="hero-bottom">
            <span>WELLNESS, THE NATURAL WAY</span>
            <a href="#favorites">A better everyday begins here <i class="fa-solid fa-arrow-down"></i></a>
        </div>
    </section>
    <section class="benefit-strip">
        <div><i class="fa-solid fa-seedling"></i><span><strong>Plant-based goodness</strong><small>Thoughtfully inspired by nature</small></span></div>
        <div><i class="fa-solid fa-sun"></i><span><strong>Fits your daily routine</strong><small>Simple choices, every day</small></span></div>
        <div><i class="fa-solid fa-truck-fast"></i><span><strong>Delivered nationwide</strong><small>From our store to your doorstep</small></span></div>
        <div><i class="fa-solid fa-shield-heart"></i><span><strong>Shop with confidence</strong><small>Care at every step of your journey</small></span></div>
    </section>
    <section class="section favorites" id="favorites">
        <div class="section-heading">
            <div>
                <span class="eyebrow">GOODNESS YOU’LL COME BACK TO</span>
                <h2>Find your everyday favorites<span>.</span></h2>
                <p>A little nourishment. A little balance. Something for every routine.</p>
            </div>
            <a href="/shop.php" class="text-link">Shop all products <i class="fa-solid fa-arrow-right"></i></a>
        </div>
        <div class="category-tabs" role="group" aria-label="Filter featured products">
            <button class="selected" data-category="all">All favorites</button>
            <button data-category="Santé Barley"><i class="fa-solid fa-seedling"></i> Santé Barley</button>
            <button data-category="Capsules"><i class="fa-solid fa-capsules"></i> Capsules</button>
            <button data-category="Specialty Drinks"><i class="fa-solid fa-mug-hot"></i> Specialty drinks</button>
            <button data-category="Wellness"><i class="fa-solid fa-heart"></i> Everyday wellness</button>
        </div>
        <div class="product-grid featured-grid">
            <?php
            $featured = q(
                "SELECT p.*, c.name category_name
                 FROM products p
                 JOIN categories c ON c.id = p.category_id
                 WHERE p.status = 'Active'
                 ORDER BY p.id
                 LIMIT 8"
            )->fetchAll();
            foreach ($featured as $index => $p):
                ?>
                <div class="featured-item <?= $index > 3 ? 'extra-product' : '' ?>"
                     data-category="<?= e($p['category_name']) ?>">
                    <?php product_card($p); ?>
                </div>
            <?php endforeach; ?>
        </div>
        <p class="catalog-note">Thoughtful choices start with good information. Always read the product label.</p>
    </section>
    <section class="about-band section" id="about">
        <div class="about-image">
            <img src="/assets/images/banners/countryside.jpg"
                 alt="Green fields and rolling countryside in warm morning light"
                 loading="lazy">
            <div class="image-caption"><i class="fa-solid fa-leaf"></i> FROM NATURE, WITH CARE.</div>
        </div>
        <div class="about-content">
            <span class="eyebrow">A MORE NATURAL KIND OF EVERYDAY</span>
            <h2>Rooted in nature.<br>Growing with you.</h2>
            <p>Wellness doesn’t have to be complicated. Sometimes, it begins with the small choices we make every day.</p>
            <p>Discover Santé Barley and a collection of products designed to complement a balanced lifestyle. From your morning ritual to your on-the-go moments, find a little goodness that fits you.</p>
            <a href="/about.php" class="button">Our story <i class="fa-solid fa-arrow-right"></i></a>
        </div>
    </section>
    <section class="section why-section" id="why-barley">
        <div class="center-heading">
            <span class="eyebrow">SMALL PLANT. A WORLD TO EXPLORE.</span>
            <h2>Why make room for barley?</h2>
            <p>Get to know the plant behind your everyday green routine.</p>
        </div>
        <div class="why-grid">
            <?php
            $whyCards = [
                ['leaf',         'Vitamins',              'Barley grass contains naturally occurring vitamins. Check individual labels for amounts.'],
                ['droplet',      'Minerals',              'Explore the nutritional composition listed on your product packaging.'],
                ['shield-heart', 'Antioxidants',          'Plants contain antioxidant compounds; amounts vary by product and preparation.'],
                ['seedling',     'Plant-based nutrients', 'A plant-inspired addition to a varied, balanced diet.'],
                ['sun',          'Everyday convenience',  'Find the format that works with your schedule and preferences.'],
                ['wheat-awn',    'Natural inspiration',   'Learn about ingredients and choose what suits your routine.'],
            ];
            foreach ($whyCards as $c):
                ?>
                <article class="why-card">
                    <i class="fa-solid fa-<?= $c[0] ?>"></i>
                    <h3><?= $c[1] ?></h3>
                    <p><?= $c[2] ?></p>
                </article>
            <?php endforeach; ?>
        </div>
        <p class="fine-print">Information provided on this website is for general educational and wellness purposes only and is not intended to diagnose, treat, cure, or prevent any disease.</p>
    </section>
    <section class="routine-section section" id="how-to-use">
        <div class="center-heading">
            <span class="eyebrow">YOUR ROUTINE, SIMPLIFIED</span>
            <h2>A little ritual. A better everyday.</h2>
            <p>Always follow the product label and official instructions.</p>
        </div>
        <div class="steps">
            <article>
                <span>01</span>
                <i class="fa-solid fa-box-open"></i>
                <h3>Prepare</h3>
                <p>Choose your product and read the label, including ingredients and directions.</p>
            </article>
            <article>
                <span>02</span>
                <i class="fa-solid fa-mug-saucer"></i>
                <h3>Mix / Consume</h3>
                <p>Follow the instructions on your specific product. Preparation varies by format.</p>
            </article>
            <article>
                <span>03</span>
                <i class="fa-solid fa-sun"></i>
                <h3>Make it your moment</h3>
                <p>Enjoy as part of a balanced diet and a mindful wellness routine.</p>
            </article>
        </div>
    </section>
    <section class="wellness-banner section" id="wellness">
        <div>
            <span class="eyebrow">WELLNESS IS A WAY OF LIVING</span>
            <h2>Small steps.<br>Meaningful everyday moments.</h2>
            <p>Eat a variety of foods, move in ways you enjoy, and make time for rest.<br>Your wellness journey is uniquely yours.</p>
            <a href="/wellness.php" class="text-link">Explore mindful living <i class="fa-solid fa-arrow-right"></i></a>
        </div>
        <div class="wellness-art">
            <i class="fa-solid fa-seedling"></i>
            <span>grow<br><em>at your pace.</em></span>
        </div>
    </section>
</main>
<?php include __DIR__ . '/includes/footer.php'; ?>