<?php if (!$orders): ?>
    <div class="empty-state">
        <i class="fa-solid fa-box-open"></i>
        <h2>No orders yet.</h2>
        <p>New customer orders will appear here.</p>
    </div>
<?php else: ?>
    <div class="table-wrap">
        <table>
            <thead>
            <tr>
                <th>Order</th>
                <th>Customer</th>
                <th>Date</th>
                <th>Total</th>
                <th>Payment</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($orders as $o): ?>
                <tr>
                    <td><?= e($o['order_number']) ?></td>
                    <td><?= e($o['full_name']) ?></td>
                    <td><?= date('M d, Y', strtotime($o['created_at'])) ?></td>
                    <td><?= money($o['total_amount']) ?></td>
                    <td><?= e($o['payment_method']) ?><br><small><?= e($o['payment_status']) ?></small></td>
                    <td><span class="status <?= e($o['order_status']) ?>"><?= e($o['order_status']) ?></span></td>
                    <td><a href="/admin/order-details.php?id=<?= $o['id'] ?>">View / update</a></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>