<?php
$adminTitle = 'Orders';
require_once 'includes/admin_header.php';

$statusFilter = isset($_GET['status']) ? $_GET['status'] : '';
$validStatuses = ['pending','processing','shipped','delivered','cancelled'];

// Handle single order view
if (isset($_GET['id'])) {
    $oid = (int)$_GET['id'];
    $s = $conn->prepare("SELECT o.*, u.name as customer_name, u.email as customer_email FROM orders o JOIN users u ON o.user_id = u.id WHERE o.id = ?");
    $s->bind_param('i', $oid);
    $s->execute();
    $order = $s->get_result()->fetch_assoc();

    if ($order) {
        $items = $conn->prepare("SELECT oi.*, p.image FROM order_items oi LEFT JOIN products p ON oi.product_id = p.id WHERE oi.order_id = ?");
        $items->bind_param('i', $oid);
        $items->execute();
        $orderItems = $items->get_result()->fetch_all(MYSQLI_ASSOC);

        $adminTitle = 'Order #' . str_pad($oid, 6, '0', STR_PAD_LEFT);
        // Handle status update
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['new_status'])) {
            $newStatus = $_POST['new_status'];
            if (in_array($newStatus, $validStatuses)) {
                $upd = $conn->prepare("UPDATE orders SET status=? WHERE id=?");
                $upd->bind_param('si', $newStatus, $oid);
                $upd->execute();
                $order['status'] = $newStatus;
                echo '<div class="alert alert-success">✅ Order status updated to <strong>' . ucfirst($newStatus) . '</strong></div>';
            }
        }
        ?>
        <div style="margin-bottom:20px;">
            <a href="orders.php" class="btn btn-outline btn-sm">← All Orders</a>
        </div>

        <div style="display:grid; grid-template-columns:1fr 300px; gap:24px; align-items:start;">
            <div>
                <div class="admin-form-card" style="margin-bottom:20px;">
                    <div class="admin-form-header">
                        <h3>Order #<?= str_pad($order['id'], 6, '0', STR_PAD_LEFT) ?></h3>
                    </div>
                    <div class="admin-form-body">
                        <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px; margin-bottom:20px; font-size:14px;">
                            <div><strong>Customer:</strong> <?= sanitize($order['customer_name']) ?></div>
                            <div><strong>Email:</strong> <?= sanitize($order['customer_email']) ?></div>
                            <div><strong>Date:</strong> <?= date('d M Y, h:i A', strtotime($order['created_at'])) ?></div>
                            <div><strong>Payment:</strong> <?= ucwords(str_replace('_',' ',$order['payment_method'])) ?></div>
                            <div><strong>Status:</strong> <span class="status-badge status-<?= $order['status'] ?>"><?= ucfirst($order['status']) ?></span></div>
                        </div>

                        <h4 style="margin-bottom:12px;">Order Items</h4>
                        <table class="table">
                            <thead><tr><th>Image</th><th>Product</th><th>Qty</th><th>Unit Price</th><th>Subtotal</th></tr></thead>
                            <tbody>
                            <?php foreach ($orderItems as $item):
                                $imgSrc = (!empty($item['image']) && $item['image'] !== 'default.jpg' && file_exists(__DIR__ . '/../uploads/products/' . $item['image'])) ? ($base . 'uploads/products/' . $item['image']) : null;
                            ?>
                            <tr>
                                <td>
                                    <?php if ($imgSrc): ?><img src="<?= $imgSrc ?>" class="product-thumb" alt=""><?php else: ?><div class="product-thumb-placeholder">🛒</div><?php endif; ?>
                                </td>
                                <td><?= sanitize($item['product_name']) ?></td>
                                <td><?= $item['quantity'] ?></td>
                                <td>Rs. <?= number_format($item['price'], 2) ?></td>
                                <td><strong>Rs. <?= number_format($item['price'] * $item['quantity'], 2) ?></strong></td>
                            </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>

                        <?php if ($order['notes']): ?>
                        <div style="margin-top:16px; padding:12px; background:var(--light-gray); border-radius:8px; font-size:14px;">
                            <strong>Customer Notes:</strong> <?= sanitize($order['notes']) ?>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div>
                <div class="admin-form-card" style="margin-bottom:16px;">
                    <div class="admin-form-header"><h3>Order Total</h3></div>
                    <div class="admin-form-body">
                        <div class="summary-row total" style="margin:0; padding:0; border:none; font-size:20px;">
                            <span>Total</span>
                            <span>Rs. <?= number_format($order['total_amount'], 2) ?></span>
                        </div>
                    </div>
                </div>

                <div class="admin-form-card" style="margin-bottom:16px;">
                    <div class="admin-form-header"><h3>Update Status</h3></div>
                    <div class="admin-form-body">
                        <form method="POST">
                            <div class="form-group">
                                <select name="new_status" class="form-control">
                                    <?php foreach ($validStatuses as $s): ?>
                                    <option value="<?= $s ?>" <?= $order['status']===$s?'selected':'' ?>><?= ucfirst($s) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <button type="submit" class="btn btn-primary btn-full">💾 Update Status</button>
                        </form>
                    </div>
                </div>

                <div class="admin-form-card">
                    <div class="admin-form-header"><h3>Delivery Info</h3></div>
                    <div class="admin-form-body" style="font-size:14px; line-height:1.8;">
                        <strong><?= sanitize($order['shipping_name']) ?></strong><br>
                        📞 <?= sanitize($order['shipping_phone']) ?><br>
                        📍 <?= sanitize($order['shipping_address']) ?><br>
                        🏙️ <?= sanitize($order['shipping_city']) ?>
                    </div>
                </div>
            </div>
        </div>
        <?php
        require_once 'includes/admin_footer.php';
        exit;
    }
}

// LIST ALL ORDERS
$where = '1=1';
if ($statusFilter && in_array($statusFilter, $validStatuses)) {
    $where = "o.status = '" . $conn->real_escape_string($statusFilter) . "'";
}
$orders = $conn->query("SELECT o.*, u.name as customer_name, COUNT(oi.id) as item_count FROM orders o JOIN users u ON o.user_id = u.id LEFT JOIN order_items oi ON o.id = oi.order_id WHERE $where GROUP BY o.id ORDER BY o.created_at DESC");
?>

<div class="admin-table-card">
    <div class="admin-table-header">
        <h3>All Orders (<?= $orders->num_rows ?>)</h3>
        <div class="table-actions">
            <input type="text" id="tableSearch" class="search-input" placeholder="Search orders...">
        </div>
    </div>

    <!-- Status Filter Tabs -->
    <div style="padding:12px 24px; background:var(--light-gray); display:flex; gap:8px; flex-wrap:wrap; border-bottom:1px solid var(--border);">
        <a href="orders.php" class="btn btn-sm <?= !$statusFilter ? 'btn-primary' : 'btn-outline' ?>">All</a>
        <?php
        $statusColors = ['pending'=>'btn-warning','processing'=>'btn-outline','shipped'=>'btn-outline','delivered'=>'btn-outline','cancelled'=>'btn-danger'];
        foreach ($validStatuses as $s): ?>
        <a href="orders.php?status=<?= $s ?>" class="btn btn-sm <?= $statusFilter===$s ? ($statusColors[$s]??'btn-primary') : 'btn-outline' ?>">
            <?= ucfirst($s) ?>
        </a>
        <?php endforeach; ?>
    </div>

    <div style="overflow-x:auto;">
        <table class="table">
            <thead>
                <tr>
                    <th>Order ID</th>
                    <th>Customer</th>
                    <th>Date</th>
                    <th>Items</th>
                    <th>Total</th>
                    <th>Payment</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php if ($orders->num_rows === 0): ?>
            <tr><td colspan="8" style="text-align:center; padding:40px; color:var(--gray);">No orders found.</td></tr>
            <?php else: while ($o = $orders->fetch_assoc()): ?>
            <tr>
                <td><strong>#<?= str_pad($o['id'], 6, '0', STR_PAD_LEFT) ?></strong></td>
                <td><?= sanitize($o['customer_name']) ?></td>
                <td style="font-size:13px;"><?= date('d M Y', strtotime($o['created_at'])) ?><br><span style="color:var(--gray);"><?= date('h:i A', strtotime($o['created_at'])) ?></span></td>
                <td><?= $o['item_count'] ?> items</td>
                <td><strong>Rs. <?= number_format($o['total_amount'], 2) ?></strong></td>
                <td style="font-size:12px;"><?= ucwords(str_replace('_',' ',$o['payment_method'])) ?></td>
                <td><span class="status-badge status-<?= $o['status'] ?>"><?= ucfirst($o['status']) ?></span></td>
                <td>
                    <a href="orders.php?id=<?= $o['id'] ?>" class="btn btn-sm btn-primary">👁️ View</a>
                </td>
            </tr>
            <?php endwhile; endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once 'includes/admin_footer.php'; ?>
