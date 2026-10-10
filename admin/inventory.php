<?php
require __DIR__ . '/../includes/functions.php';
require_admin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $stock     = filter_var($_POST['stock'] ?? '', FILTER_VALIDATE_INT);
    $threshold = filter_var($_POST['threshold'] ?? '', FILTER_VALIDATE_INT);

    if (
        $stock === false || $stock < 0 || $stock > 1000000
        || $threshold === false || $threshold < 0 || $threshold > 1000000
    ) {
        flash('Enter valid stock and threshold values.', 'error');
    } else {
        q(
            'UPDATE products SET stock=?,low_stock_threshold=?,updated_at=NOW() WHERE id=?',
            [$stock, $threshold, (int) $_POST['id']]
        );
        flash('Inventory updated.');
    }
    redirect('/admin/inventory.php');
}

$pageTitle = 'Inventory';
require __DIR__ . '/../includes/admin-start.php';
?>
<div class="page-heading">
    <h1>Keep the goodness flowing.</h1>
    <p>Track stock levels and set low-stock alerts.</p>
</div>

<div class="table-wrap">
    <table>
        <thead>
        <tr>
            <th>Product</th>
            <th>SKU</th>
            <th>Status</th>
            <th>Stock / low-stock threshold</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach (q('SELECT * FROM products ORDER BY stock,id') as $p): ?>
            <tr>
                <td><?= e($p['product_name']) ?></td>
                <td><?= e($p['sku']) ?></td>
                <td>
                    <span class="status <?= $p['stock'] <= $p['low_stock_threshold'] ? 'Pending' : '' ?>">
                        <?= $p['stock'] < 1
                            ? 'Out of stock'
                            : ($p['stock'] <= $p['low_stock_threshold'] ? 'Low stock' : 'In stock') ?>
                    </span>
                </td>
                <td>
                    <form method="post" class="inline-form">
                        <?= csrf() ?>
                        <input type="hidden" name="id" value="<?= $p['id'] ?>">
                        <input type="number" min="0" max="1000000"
                               name="stock" value="<?= $p['stock'] ?>"
                               aria-label="Stock for <?= e($p['product_name']) ?>" required>
                        <input type="number" min="0" max="1000000"
                               name="threshold" value="<?= $p['low_stock_threshold'] ?>"
                               aria-label="Low stock threshold" required>
                        <button class="small-button">Save</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php require __DIR__ . '/../includes/admin-end.php'; ?>