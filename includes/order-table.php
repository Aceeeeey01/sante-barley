<?php if (!$orders): ?>
    <div class="empty-state">
        <i class="fa-solid fa-box-open"></i>
        <h2>Your journey starts here.</h2>
        <p>When you place an order, you’ll find all the details here.</p>
        <a href="/shop.php" class="button">Discover your favorites</a>
    </div>
<?php else: ?>
    <div class="table-wrap">
        <table>
            <thead>
            <tr>
                <th>Order</th>
                <th>Date</th>
                <th>Total</th>
                <th>Payment</th>
                <th>Status</th>
                <th></th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($orders as $o): ?>
                <tr>
                    <td><?= e($o['order_number']) ?></td>
                    <td><?= date('M d, Y', strtotime($o['created_at'])) ?></td>
                    <td><?= money($o['total_amount']) ?></td>
                    <td><?= e($o['payment_method']) ?></td>
                    <td><span class="status <?= e($o['order_status']) ?>"><?= e($o['order_status']) ?></span></td>
                    <td><a href="/user/order-details.php?id=<?= $o['id'] ?>">View details</a></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>