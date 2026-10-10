<?php
require __DIR__ . '/../includes/functions.php';
require_admin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $id = (int) ($_POST['id'] ?? 0);
    q("UPDATE products SET status='Inactive',updated_at=NOW() WHERE id=?", [$id]);
    flash('Product removed from the storefront. Order history is preserved.');
    redirect('/admin/products.php');
}

$pageTitle = 'Products';
require __DIR__ . '/../includes/admin-start.php';

$search = trim($_GET['search'] ?? '');
$cat    = (int) ($_GET['category'] ?? 0);

$args  = ['%' . $search . '%'];
$where = 'p.product_name ILIKE ?';

if ($cat) {
    $where .= ' AND p.category_id=?';
    $args[] = $cat;
}
?>
<div class="top-row">
    <div class="page-heading">
        <h1>Your product collection.</h1>
        <p>Manage the goodness you share with your customers.</p>
    </div>
    <a href="/admin/add-product.php" class="button">
        <i class="fa-solid fa-plus"></i> Add product
    </a>
</div>

<form class="shop-toolbar">
    <label class="field">Search
        <input name="search" value="<?= e($search) ?>" placeholder="Product name">
    </label>
    <label class="field">Category
        <select name="category">
            <option value="">All categories</option>
            <?php foreach (q('SELECT * FROM categories ORDER BY name') as $c): ?>
                <option value="<?= $c['id'] ?>" <?= $cat == $c['id'] ? 'selected' : '' ?>>
                    <?= e($c['name']) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </label>
    <button class="button">Filter</button>
</form>

<div class="table-wrap">
    <table>
        <thead>
        <tr>
            <th>Product</th>
            <th>Name / SKU</th>
            <th>Category</th>
            <th>Price</th>
            <th>Discount</th>
            <th>Stock</th>
            <th>Status</th>
            <th>Actions</th>
        </tr>
        </thead>
        <tbody>
        <?php
        $products = q(
            'SELECT p.*, c.name category_name
             FROM products p
             JOIN categories c ON c.id=p.category_id
             WHERE ' . $where . '
             ORDER BY p.id',
            $args
        )->fetchAll();

        foreach ($products as $p): ?>
            <tr>
                <td><img src="<?= e($p['image']) ?>" alt=""></td>
                <td><?= e($p['product_name']) ?><br><small><?= e($p['sku']) ?></small></td>
                <td><?= e($p['category_name']) ?></td>
                <td><?= money(price($p)) ?></td>
                <td><?= $p['discount'] ?>%</td>
                <td><?= $p['stock'] ?></td>
                <td><span class="status <?= e($p['status']) ?>"><?= e($p['status']) ?></span></td>
                <td>
                    <div class="inline-form">
                        <a href="/product.php?id=<?= $p['id'] ?>">View</a>
                        <a href="/admin/edit-product.php?id=<?= $p['id'] ?>">Edit</a>
                        <form method="post"
                              data-confirm="Remove this product from the storefront? Order history will be kept.">
                            <?= csrf() ?>
                            <input type="hidden" name="id" value="<?= $p['id'] ?>">
                            <button class="small-button danger">Delete</button>
                        </form>
                    </div>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php require __DIR__ . '/../includes/admin-end.php'; ?>