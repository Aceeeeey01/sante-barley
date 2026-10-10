<?php
require __DIR__ . '/../includes/functions.php';
require_admin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $id   = (int) ($_POST['id'] ?? 0);
    $name = trim($_POST['name'] ?? '');

    try {
        if (isset($_POST['delete'])) {
            if (q('SELECT COUNT(*) FROM products WHERE category_id=?', [$id])->fetchColumn()) {
                throw new RuntimeException('Move the category’s products before deleting it.');
            }
            q('DELETE FROM categories WHERE id=?', [$id]);
        } else {
            if (strlen($name) < 2 || strlen($name) > 100) {
                throw new RuntimeException('Category names must contain 2–100 characters.');
            }
            if ($id) {
                q('UPDATE categories SET name=? WHERE id=?', [$name, $id]);
            } else {
                q('INSERT INTO categories(name) VALUES (?)', [$name]);
            }
        }
        flash('Categories updated.');
    } catch (Throwable $e) {
        flash(
            $e instanceof PDOException ? 'That category is already in use.' : $e->getMessage(),
            'error'
        );
    }
    redirect('/admin/categories.php');
}

$pageTitle = 'Categories';
require __DIR__ . '/../includes/admin-start.php';
?>
<div class="page-heading">
    <h1>A place for every product.</h1>
    <p>Organize your wellness collection.</p>
</div>

<form method="post" class="shop-toolbar">
    <?= csrf() ?>
    <label class="field">New category
        <input name="name" maxlength="100" required>
    </label>
    <button class="button">Add category</button>
</form>

<div class="table-wrap">
    <table>
        <thead>
        <tr><th>Category</th><th>Products</th><th>Edit</th></tr>
        </thead>
        <tbody>
        <?php
        $cats = q(
            'SELECT c.*, COUNT(p.id) count
             FROM categories c
             LEFT JOIN products p ON p.category_id=c.id
             GROUP BY c.id
             ORDER BY c.id'
        )->fetchAll();

        foreach ($cats as $c): ?>
            <tr>
                <td><?= e($c['name']) ?></td>
                <td><?= $c['count'] ?></td>
                <td>
                    <form class="inline-form" method="post">
                        <?= csrf() ?>
                        <input type="hidden" name="id" value="<?= $c['id'] ?>">
                        <input name="name" value="<?= e($c['name']) ?>"
                               aria-label="Category name" required maxlength="100">
                        <button class="small-button">Save</button>
                        <button class="small-button danger" name="delete" value="1">Delete</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php require __DIR__ . '/../includes/admin-end.php'; ?>