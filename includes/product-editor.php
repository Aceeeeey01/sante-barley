<?php
require_once __DIR__ . '/functions.php';
require_admin();

$id = (int) ($_GET['id'] ?? 0);
$p = $id ? q('SELECT * FROM products WHERE id=?', [$id])->fetch() : [];

if ($id && !$p) {
    http_response_code(404);
    exit('Product not found.');
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    try {
        $name      = trim($_POST['product_name'] ?? '');
        $slug      = trim($_POST['slug'] ?? '');
        $sku       = trim($_POST['sku'] ?? '');
        $price     = filter_var($_POST['price'] ?? '', FILTER_VALIDATE_FLOAT);
        $discount  = filter_var($_POST['discount'] ?? '', FILTER_VALIDATE_FLOAT);
        $stock     = filter_var($_POST['stock'] ?? '', FILTER_VALIDATE_INT);
        $threshold = filter_var($_POST['low_stock_threshold'] ?? '', FILTER_VALIDATE_INT);
        $category  = (int) ($_POST['category_id'] ?? 0);
        $status    = $_POST['status'] ?? '';
        $image     = $_POST['image'] ?? '';

        if (
            strlen($name) < 2
            || strlen($name) > 180
            || !preg_match('/^[a-z0-9-]{2,180}$/', $slug)
            || !preg_match('/^[a-zA-Z0-9_-]{2,50}$/', $sku)
        ) {
            throw new RuntimeException('Enter a product name, URL slug (lowercase letters, numbers, hyphens), and SKU.');
        }

        if (
            $price === false || $price < 0 || $price > 999999
            || $discount === false || $discount < 0 || $discount > 90
            || $stock === false || $stock < 0 || $stock > 1000000
            || $threshold === false || $threshold < 0 || $threshold > 1000000
        ) {
            throw new RuntimeException('Enter valid price, discount (0–90%), stock, and low-stock threshold values.');
        }

        if (
            !q('SELECT id FROM categories WHERE id=?', [$category])->fetchColumn()
            || !in_array($status, ['Active', 'Inactive', 'Out of Stock'], true)
        ) {
            throw new RuntimeException('Choose a valid category and status.');
        }

        if (
            !preg_match('#^/assets/images/products/[a-z0-9-]+\.svg$#', $image)
            || !is_file(__DIR__ . '/..' . $image)
        ) {
            throw new RuntimeException('Choose an available product illustration.');
        }

        foreach (['description', 'ingredients', 'instructions', 'size'] as $f) {
            if (strlen($_POST[$f] ?? '') > 5000) {
                throw new RuntimeException('Product text is too long.');
            }
        }

        $values = [
            $category,
            $name,
            $slug,
            trim($_POST['description'] ?? ''),
            $price,
            $discount,
            $stock,
            $threshold,
            $sku,
            $image,
            $status,
            trim($_POST['size'] ?? ''),
            trim($_POST['ingredients'] ?? ''),
            trim($_POST['instructions'] ?? ''),
        ];

        if ($id) {
            $values[] = $id;
            q(
                'UPDATE products
                 SET category_id=?,product_name=?,slug=?,description=?,price=?,discount=?,stock=?,
                     low_stock_threshold=?,sku=?,image=?,status=?,size=?,ingredients=?,instructions=?,updated_at=NOW()
                 WHERE id=?',
                $values
            );
        } else {
            q(
                'INSERT INTO products(category_id,product_name,slug,description,price,discount,stock,
                                      low_stock_threshold,sku,image,status,size,ingredients,instructions)
                 VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?)',
                $values
            );
        }

        flash('Product saved.');
        redirect('/admin/products.php');
    } catch (PDOException $e) {
        $error = $e->getCode() === '23505'
            ? 'That SKU or slug is already in use.'
            : 'Unable to save this product.';
    } catch (RuntimeException $e) {
        $error = $e->getMessage();
    }

    $p = array_merge($p, $_POST);
}

$pageTitle = $id ? 'Edit product' : 'Add product';
require __DIR__ . '/admin-start.php';
?>
<div class="page-heading">
    <h1><?= $pageTitle ?>.</h1>
    <p>Use verified product details and responsible wellness language.</p>
</div>

<?php if ($error): ?>
    <div class="error-box"><?= e($error) ?></div>
<?php endif; ?>

<form class="panel form-grid" method="post">
    <?= csrf() ?>
    <?php
    $basic = [
        'product_name' => 'Product name',
        'slug'         => 'URL slug',
        'sku'          => 'SKU',
        'size'         => 'Pack size',
    ];
    foreach ($basic as $key => $label) {
        field($label, $key, $p[$key] ?? '');
    }
    ?>

    <label class="field">Category
        <select name="category_id" required>
            <?php foreach (q('SELECT * FROM categories ORDER BY name') as $c): ?>
                <option value="<?= $c['id'] ?>" <?= ($p['category_id'] ?? 0) == $c['id'] ? 'selected' : '' ?>>
                    <?= e($c['name']) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </label>

    <label class="field">Status
        <select name="status">
            <?php foreach (['Active', 'Inactive', 'Out of Stock'] as $s): ?>
                <option <?= ($p['status'] ?? '') === $s ? 'selected' : '' ?>><?= $s ?></option>
            <?php endforeach; ?>
        </select>
    </label>

    <?php
    $nums = [
        'price'               => 'Price (PHP)',
        'discount'            => 'Discount (%)',
        'stock'               => 'Current stock',
        'low_stock_threshold' => 'Low-stock threshold',
    ];
    foreach ($nums as $key => $label): ?>
        <label class="field"><?= $label ?>
            <input type="number" min="0"
                   step="<?= ($key === 'price' || $key === 'discount') ? '0.01' : '1' ?>"
                   name="<?= $key ?>"
                   value="<?= e($p[$key] ?? ($key === 'low_stock_threshold' ? 10 : 0)) ?>"
                   required>
        </label>
    <?php endforeach; ?>

    <label class="field full">Product illustration
        <select name="image">
            <?php foreach (glob(__DIR__ . '/../assets/images/products/*.svg') as $file):
                $v = '/assets/images/products/' . basename($file); ?>
                <option value="<?= $v ?>" <?= ($p['image'] ?? '') === $v ? 'selected' : '' ?>>
                    <?= e(basename($file, '.svg')) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </label>

    <?php
    $texts = [
        'description'  => 'Description',
        'ingredients'  => 'Verified ingredients / label information',
        'instructions' => 'Official usage instructions',
    ];
    foreach ($texts as $key => $label): ?>
        <label class="field full"><?= $label ?>
            <textarea name="<?= $key ?>" maxlength="5000"><?= e($p[$key] ?? '') ?></textarea>
        </label>
    <?php endforeach; ?>

    <div class="full">
        <button class="button">Save product</button>
        <a href="/admin/products.php" class="text-link" style="margin-left:20px">Cancel</a>
    </div>
</form>
<?php require __DIR__ . '/admin-end.php'; ?>