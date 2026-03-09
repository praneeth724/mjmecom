<?php
$adminTitle = 'Dashboard';
require_once 'includes/admin_header.php';

// Stats
$totalProducts  = $conn->query("SELECT COUNT(*) FROM products")->fetch_row()[0];
$totalOrders    = $conn->query("SELECT COUNT(*) FROM orders")->fetch_row()[0];
$totalUsers     = $conn->query("SELECT COUNT(*) FROM users WHERE role='user'")->fetch_row()[0];
$totalRevenue   = $conn->query("SELECT COALESCE(SUM(total_amount),0) FROM orders WHERE status != 'cancelled'")->fetch_row()[0];
$pendingOrders  = $conn->query("SELECT COUNT(*) FROM orders WHERE status='pending'")->fetch_row()[0];
$lowStockCount  = $conn->query("SELECT COUNT(*) FROM products WHERE quantity <= 5")->fetch_row()[0];

// Recent orders
$recentOrders = $conn->query("SELECT o.*, u.name as customer_name FROM orders o JOIN users u ON o.user_id = u.id ORDER BY o.created_at DESC LIMIT 8");

// Recent products
$recentProducts = $conn->query("SELECT * FROM products ORDER BY created_at DESC LIMIT 5");
?>

<!-- STATS CARDS -->
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon green">📦</div>
        <div class="stat-info">
            <div class="stat-value"><?= $totalOrders ?></div>
            <div class="stat-label">Total Orders</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon orange">⏳</div>
        <div class="stat-info">
            <div class="stat-value"><?= $pendingOrders ?></div>
            <div class="stat-label">Pending Orders</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon blue">🛍️</div>
        <div class="stat-info">
            <div class="stat-value"><?= $totalProducts ?></div>
            <div class="stat-label">Products</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon green">👥</div>
        <div class="stat-info">
            <div class="stat-value"><?= $totalUsers ?></div>
            <div class="stat-label">Customers</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon green">💰</div>
        <div class="stat-info">
            <div class="stat-value" style="font-size:18px;">Rs.<?= number_format($totalRevenue, 0) ?></div>
            <div class="stat-label">Total Revenue</div>
        </div>
    </div>
    <div class="stat-card" style="cursor:pointer;" onclick="location.href='products.php?low=1'">
        <div class="stat-icon red">⚠️</div>
        <div class="stat-info">
            <div class="stat-value" style="color:<?= $lowStockCount > 0 ? 'var(--sale)' : 'inherit' ?>"><?= $lowStockCount ?></div>
            <div class="stat-label">Low Stock Items</div>
        </div>
    </div>
</div>

<div style="display:grid; grid-template-columns:1fr 320px; gap:24px; align-items:start;">
    <!-- RECENT ORDERS -->
    <div class="admin-table-card">
        <div class="admin-table-header">
            <h3>Recent Orders</h3>
            <a href="orders.php" class="btn btn-outline btn-sm">View All</a>
        </div>
        <div style="overflow-x:auto;">
            <table class="table">
                <thead>
                    <tr>
                        <th>Order ID</th>
                        <th>Customer</th>
                        <th>Date</th>
                        <th>Total</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                <?php if ($recentOrders->num_rows === 0): ?>
                <tr><td colspan="6" style="text-align:center; color:var(--gray); padding:30px;">No orders yet</td></tr>
                <?php else: while ($o = $recentOrders->fetch_assoc()): ?>
                <tr>
                    <td><strong>#<?= str_pad($o['id'], 6, '0', STR_PAD_LEFT) ?></strong></td>
                    <td><?= sanitize($o['customer_name']) ?></td>
                    <td style="font-size:13px;"><?= date('d M Y', strtotime($o['created_at'])) ?></td>
                    <td><strong>Rs. <?= number_format($o['total_amount'], 2) ?></strong></td>
                    <td><span class="status-badge status-<?= $o['status'] ?>"><?= ucfirst($o['status']) ?></span></td>
                    <td><a href="orders.php?id=<?= $o['id'] ?>" class="btn btn-sm btn-outline">View</a></td>
                </tr>
                <?php endwhile; endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- RECENT PRODUCTS + QUICK ACTIONS -->
    <div>
        <div class="admin-table-card" style="margin-bottom:20px;">
            <div class="admin-table-header">
                <h3>Quick Actions</h3>
            </div>
            <div style="padding:16px; display:flex; flex-direction:column; gap:10px;">
                <a href="add-product.php" class="btn btn-primary btn-full">➕ Add New Product</a>
                <a href="orders.php?status=pending" class="btn btn-warning btn-full">⏳ View Pending Orders (<?= $pendingOrders ?>)</a>
                <a href="products.php?low=1" class="btn btn-danger btn-full">⚠️ Low Stock Items (<?= $lowStockCount ?>)</a>
                <a href="categories.php" class="btn btn-outline btn-full">📂 Manage Categories</a>
            </div>
        </div>

        <div class="admin-table-card">
            <div class="admin-table-header">
                <h3>Recent Products</h3>
                <a href="products.php" class="btn btn-outline btn-sm">View All</a>
            </div>
            <div style="padding:4px 0;">
                <?php while ($prod = $recentProducts->fetch_assoc()):
                    $imgSrc = (!empty($prod['image']) && $prod['image'] !== 'default.jpg' && file_exists(__DIR__ . '/../uploads/products/' . $prod['image'])) ? ($base . 'uploads/products/' . $prod['image']) : null;
                ?>
                <div style="display:flex; align-items:center; gap:12px; padding:12px 16px; border-bottom:1px solid var(--border);">
                    <?php if ($imgSrc): ?>
                    <img src="<?= $imgSrc ?>" class="product-thumb" alt="">
                    <?php else: ?>
                    <div class="product-thumb-placeholder">🛒</div>
                    <?php endif; ?>
                    <div style="flex:1; min-width:0;">
                        <div style="font-size:14px; font-weight:600; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;"><?= sanitize($prod['name']) ?></div>
                        <div style="font-size:13px; color:var(--gray);">Rs. <?= number_format($prod['price'], 2) ?> | Stock: <?= $prod['quantity'] ?></div>
                    </div>
                    <a href="edit-product.php?id=<?= $prod['id'] ?>" class="btn btn-sm btn-outline">Edit</a>
                </div>
                <?php endwhile; ?>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/admin_footer.php'; ?>
