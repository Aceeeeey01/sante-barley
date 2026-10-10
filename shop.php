<?php
require __DIR__ . '/includes/functions.php';

$active = 'shop';
$pageTitle = 'Shop your everyday wellness';

$search = trim($_GET['search'] ?? '');
$cat    = (int) ($_GET['category'] ?? 0);
$min    = max(0, (float) ($_GET['min'] ?? 0));
$max    = max(0, (float) ($_GET['max'] ?? 0));
$sort   = $_GET['sort'] ?? 'featured';
$page   = max(1, (int) ($_GET['page'] ?? 1));

$where = "p.status='Active'";
$args  = [];

if ($search !== '') {
    $where .= ' AND p.product_name ILIKE ?';
    $args[] = '%' . $search . '%';
}
if ($cat) {
    $where .= ' AND p.category_id=?';
    $args[] = $cat;
}
if ($min) {
    $where .= ' AND p.price*(1-p.discount/100)>=?';
    $args[] = $min;
}
if ($max) {
    $where .= ' AND p.price*(1-p.discount/100)<=?';
    $args[] = $max;
}

$orderMap = [
    'featured'   => 'p.id',
    'price_low'  => 'p.price*(1-p.discount/100) ASC',
    'price_high' => 'p.price*(1-p.discount/100) DESC',
    'newest'     => 'p.created_at DESC',
    'name'       => 'p.product_name ASC',
];
$order = $orderMap[$sort] ?? 'p.id';

$count = (int) q('SELECT COUNT(*) FROM products p WHERE ' . $where, $args)->fetchColumn();
$pages = max(1, (int) ceil($count / 8));
$page  = min($page, $pages);

$products = q(
    'SELECT p.*, c.name category_name
     FROM products p
     JOIN categories c ON c.id = p.category_id
     WHERE ' . $where . '
     ORDER BY ' . $order . '
     LIMIT 8 OFFSET ' . (($page - 1) * 8),
    $args
)->fetchAll();

require __DIR__ . '/includes/header.php';
?>
<main class="page-wrap" id="main">
    <div class="breadcrumb">
        <a href="/">Home</a><span>/</span>Shop
    </div>
    <div class="page-heading">
        <span class="eyebrow">THOUGHTFUL CHOICES, EVERY DAY</span>
        <h1>Find your kind of goodness.</h1>
        <p>Explore products for your everyday wellness routine.</p>
    </div>

    <?php foreach (q('SELECT * FROM promotions WHERE active=TRUE ORDER BY id DESC LIMIT 3') as $promotion): ?>
        <div class="notice" style="margin-bottom:15px">
            <strong><?= e($promotion['title']) ?></strong> — <?= e($promotion['description']) ?>
        </div>
    <?php endforeach; ?>

    <form class="shop-toolbar" method="get">
        <label class="field">Search products
            <input type="search" name="search"
                   placeholder="What are you looking for?"
                   value="<?= e($search) ?>">
        </label>
        <label class="field">Category
            <select name="category">
                <option value="">All categories</option>
                <?php foreach (q('SELECT * FROM categories ORDER BY id') as $c): ?>
                    <option value="<?= $c['id'] ?>" <?= $cat == $c['id'] ? 'selected' : '' ?>>
                        <?= e($c['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </label>
        <label class="field">Min price
            <input type="number" name="min" min="0" placeholder="₱ 0" value="<?= $min ?: '' ?>">
        </label>
        <label class="field">Max price
            <input type="number" name="max" min="0" placeholder="No limit" value="<?= $max ?: '' ?>">
        </label>
        <label class="field">Sort by
            <select name="sort">
                <?php
                $sortOptions = [
                    'featured'   => 'Featured',
                    'price_low'  => 'Price: low to high',
                    'price_high' => 'Price: high to low',
                    'newest'     => 'Newest',
                    'name'       => 'Name',
                ];
                foreach ($sortOptions as $v => $label): ?>
                    <option value="<?= $v ?>" <?= $v === $sort ? 'selected' : '' ?>><?= $label ?></option>
                <?php endforeach; ?>
            </select>
        </label>
        <button class="button">Apply <i class="fa-solid fa-sliders"></i></button>
    </form>

    <p class="fine-print" style="text-align:left;margin:0 0 20px">
        <?= $count ?> products found
        <a href="/shop.php" style="float:right;text-decoration:underline">Reset filters</a>
    </p>

    <div class="product-grid">
        <?php foreach ($products as $p) product_card($p); ?>
        <?php if (!$products): ?>
            <div class="empty-state">
                <i class="fa-solid fa-magnifying-glass"></i>
                <h2>No products found</h2>
                <p>Try another search or explore all categories.</p>
                <a href="/shop.php" class="button">View all products</a>
            </div>
        <?php endif; ?>
    </div>

    <?php if ($pages > 1): ?>
        <nav class="pagination" aria-label="Pagination">
            <?php for ($i = 1; $i <= $pages; $i++): ?>
                <a class="<?= $i === $page ? 'active' : '' ?>"
                   href="?<?= e(http_build_query(array_merge($_GET, ['page' => $i]))) ?>">
                    <?= $i ?>
                </a>
            <?php endfor; ?>
        </nav>
    <?php endif; ?>
</main>
<?php include __DIR__ . '/includes/footer.php'; ?>