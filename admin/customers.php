<?php
require __DIR__ . '/../includes/functions.php';
require_admin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $id     = (int) ($_POST['id'] ?? 0);
    $name   = trim($_POST['full_name'] ?? '');
    $mobile = trim($_POST['mobile'] ?? '');
    $status = $_POST['status'] ?? '';

    if (
        strlen($name) < 2
        || strlen($name) > 120
        || !preg_match('/^(09[0-9]{9}|\+639[0-9]{9})$/', $mobile)
        || !in_array($status, ['Active', 'Inactive'], true)
    ) {
        flash('Enter valid customer details.', 'error');
    } else {
        q(
            'UPDATE users
             SET full_name=?,mobile=?,status=?,updated_at=NOW()
             WHERE id=? AND id NOT IN (SELECT user_id FROM admins)',
            [$name, $mobile, $status, $id]
        );
        flash('Customer account updated.');
    }
    redirect('/admin/customers.php');
}

$pageTitle = 'Customers';
require __DIR__ . '/../includes/admin-start.php';

$search = trim($_GET['search'] ?? '');
?>
<div class="page-heading">
    <h1>Your growing community.</h1>
    <p>View and manage customer accounts.</p>
</div>

<form class="shop-toolbar">
    <label class="field">Search customers
        <input name="search" value="<?= e($search) ?>" placeholder="Name or email">
    </label>
    <button class="button">Search</button>
</form>

<div class="table-wrap">
    <table>
        <thead>
        <tr>
            <th>ID / Customer</th>
            <th>Email</th>
            <th>Registered</th>
            <th>Orders</th>
            <th>Edit name / mobile / status</th>
        </tr>
        </thead>
        <tbody>
        <?php
        $customers = q(
            'SELECT u.*, (SELECT COUNT(*) FROM orders WHERE user_id=u.id) total_orders
             FROM users u
             WHERE u.id NOT IN (SELECT user_id FROM admins)
               AND (u.full_name ILIKE ? OR u.email ILIKE ?)
             ORDER BY u.created_at DESC',
            ['%' . $search . '%', '%' . $search . '%']
        )->fetchAll();

        foreach ($customers as $c): ?>
            <tr>
                <td>
                    #<?= $c['id'] ?> · <?= e($c['full_name']) ?><br>
                    <details>
                        <summary>View address</summary>
                        <p><?= e($c['address'] . ', ' . $c['barangay'] . ', ' . $c['city'] . ', ' . $c['province'] . ' ' . $c['postal_code']) ?></p>
                    </details>
                </td>
                <td><?= e($c['email']) ?></td>
                <td><?= date('M d, Y', strtotime($c['created_at'])) ?></td>
                <td><?= $c['total_orders'] ?></td>
                <td>
                    <form method="post" class="inline-form">
                        <?= csrf() ?>
                        <input type="hidden" name="id" value="<?= $c['id'] ?>">
                        <input name="full_name" value="<?= e($c['full_name']) ?>"
                               aria-label="Full name" required maxlength="120">
                        <input name="mobile" value="<?= e($c['mobile']) ?>"
                               aria-label="Mobile number" required>
                        <select name="status" aria-label="Account status">
                            <option <?= $c['status'] === 'Active' ? 'selected' : '' ?>>Active</option>
                            <option <?= $c['status'] === 'Inactive' ? 'selected' : '' ?>>Inactive</option>
                        </select>
                        <button class="small-button">Save</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php require __DIR__ . '/../includes/admin-end.php'; ?>