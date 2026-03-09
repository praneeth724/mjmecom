<?php
$adminTitle = 'Customers';
require_once 'includes/admin_header.php';

$users = $conn->query("SELECT u.*, COUNT(o.id) as order_count, COALESCE(SUM(o.total_amount),0) as total_spent FROM users u LEFT JOIN orders o ON u.id = o.user_id WHERE u.role = 'user' GROUP BY u.id ORDER BY u.created_at DESC");
?>

<div class="admin-table-card">
    <div class="admin-table-header">
        <h3>Registered Customers (<?= $users->num_rows ?>)</h3>
        <input type="text" id="tableSearch" class="search-input" placeholder="Search customers...">
    </div>
    <div style="overflow-x:auto;">
        <table class="table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Orders</th>
                    <th>Total Spent</th>
                    <th>Joined</th>
                </tr>
            </thead>
            <tbody>
            <?php if ($users->num_rows === 0): ?>
            <tr><td colspan="7" style="text-align:center; padding:40px; color:var(--gray);">No customers yet.</td></tr>
            <?php else: while ($u = $users->fetch_assoc()): ?>
            <tr>
                <td><?= $u['id'] ?></td>
                <td><strong><?= sanitize($u['name']) ?></strong></td>
                <td><?= sanitize($u['email']) ?></td>
                <td><?= sanitize($u['phone'] ?? '—') ?></td>
                <td><?= $u['order_count'] ?></td>
                <td><strong>Rs. <?= number_format($u['total_spent'], 2) ?></strong></td>
                <td style="font-size:13px;"><?= date('d M Y', strtotime($u['created_at'])) ?></td>
            </tr>
            <?php endwhile; endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once 'includes/admin_footer.php'; ?>
