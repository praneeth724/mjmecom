<?php
$pageTitle = 'My Orders';
require_once 'includes/db.php';
require_once 'includes/auth.php';

requireLogin();
$userId = (int)$_SESSION['user_id'];

$orders = $conn->prepare("SELECT o.*, COUNT(oi.id) as item_count FROM orders o LEFT JOIN order_items oi ON o.id = oi.order_id WHERE o.user_id = ? GROUP BY o.id ORDER BY o.created_at DESC");
$orders->bind_param('i', $userId);
$orders->execute();
$orderList = $orders->get_result()->fetch_all(MYSQLI_ASSOC);

// If viewing a single order
$viewOrder = null;
$viewItems = [];
if (isset($_GET['id'])) {
    $oid = (int)$_GET['id'];
    $s = $conn->prepare("SELECT * FROM orders WHERE id = ? AND user_id = ?");
    $s->bind_param('ii', $oid, $userId);
    $s->execute();
    $viewOrder = $s->get_result()->fetch_assoc();
    if ($viewOrder) {
        $si = $conn->prepare("SELECT * FROM order_items WHERE order_id = ?");
        $si->bind_param('i', $oid);
        $si->execute();
        $viewItems = $si->get_result()->fetch_all(MYSQLI_ASSOC);
    }
}

require_once 'includes/header.php';
?>

<div class="page-header">
    <div class="container">
        <h1>📦 My Orders</h1>
        <div class="breadcrumb"><a href="index.php">Home</a><span>/</span><span>My Orders</span></div>
    </div>
</div>

<div class="section">
    <div class="container">
        <?php if ($viewOrder): ?>
        <!-- ORDER DETAIL VIEW -->
        <div style="margin-bottom:20px;">
            <a href="my-orders.php" class="btn btn-outline btn-sm">← Back to All Orders</a>
        </div>
        <div style="display:grid; grid-template-columns:1fr 300px; gap:24px; align-items:start;" class="order-detail-layout">
            <div class="admin-form-card">
                <div class="admin-form-header">
                    <h3>Order #<?= str_pad($viewOrder['id'], 6, '0', STR_PAD_LEFT) ?></h3>
                </div>
                <div class="admin-form-body">
                    <div style="display:flex; gap:20px; flex-wrap:wrap; margin-bottom:20px;">
                        <div><strong>Date:</strong> <?= date('d M Y, h:i A', strtotime($viewOrder['created_at'])) ?></div>
                        <div><strong>Status:</strong> <span class="status-badge status-<?= $viewOrder['status'] ?>"><?= ucfirst($viewOrder['status']) ?></span></div>
                        <div><strong>Payment:</strong> <?= ucwords(str_replace('_',' ', $viewOrder['payment_method'])) ?></div>
                    </div>

                    <h4 style="margin-bottom:12px;">Items Ordered</h4>
                    <div class="orders-table-wrap">
                        <table class="table">
                            <thead><tr><th>Product</th><th>Qty</th><th>Price</th><th>Subtotal</th></tr></thead>
                            <tbody>
                            <?php foreach ($viewItems as $item): ?>
                            <tr>
                                <td><?= sanitize($item['product_name']) ?></td>
                                <td><?= $item['quantity'] ?></td>
                                <td>Rs. <?= number_format($item['price'], 2) ?></td>
                                <td>Rs. <?= number_format($item['price'] * $item['quantity'], 2) ?></td>
                            </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <?php if ($viewOrder['notes']): ?>
                    <div style="margin-top:16px; padding:12px; background:var(--light-gray); border-radius:8px; font-size:14px;">
                        <strong>Notes:</strong> <?= sanitize($viewOrder['notes']) ?>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            <div>
                <div class="cart-summary">
                    <h3>Order Total</h3>
                    <div class="summary-row total">
                        <span>Total</span>
                        <span>Rs. <?= number_format($viewOrder['total_amount'], 2) ?></span>
                    </div>
                </div>
                <div class="admin-form-card" style="margin-top:16px;">
                    <div class="admin-form-header"><h3>Delivery Info</h3></div>
                    <div class="admin-form-body" style="font-size:14px; line-height:1.8;">
                        <strong><?= sanitize($viewOrder['shipping_name']) ?></strong><br>
                        <?= sanitize($viewOrder['shipping_phone']) ?><br>
                        <?= sanitize($viewOrder['shipping_address']) ?><br>
                        <?= sanitize($viewOrder['shipping_city']) ?>
                    </div>
                </div>
            </div>
        </div>

        <?php elseif (empty($orderList)): ?>
        <div class="empty-cart">
            <div class="empty-icon">📦</div>
            <h3>No orders yet</h3>
            <p style="color:var(--gray); margin-bottom:24px;">Start shopping to see your orders here.</p>
            <a href="products.php" class="btn btn-primary btn-lg">🛍️ Start Shopping</a>
        </div>

        <?php else: ?>
        <div class="orders-table-wrap">
            <table class="table">
                <thead>
                    <tr>
                        <th>Order ID</th>
                        <th>Date</th>
                        <th>Items</th>
                        <th>Total</th>
                        <th>Payment</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($orderList as $order): ?>
                <tr>
                    <td><strong>#<?= str_pad($order['id'], 6, '0', STR_PAD_LEFT) ?></strong></td>
                    <td><?= date('d M Y', strtotime($order['created_at'])) ?></td>
                    <td><?= $order['item_count'] ?> item(s)</td>
                    <td><strong>Rs. <?= number_format($order['total_amount'], 2) ?></strong></td>
                    <td style="font-size:13px;"><?= ucwords(str_replace('_',' ', $order['payment_method'])) ?></td>
                    <td><span class="status-badge status-<?= $order['status'] ?>"><?= ucfirst($order['status']) ?></span></td>
                    <td><a href="my-orders.php?id=<?= $order['id'] ?>" class="btn btn-sm btn-outline">View</a></td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>
</div>

<style>
@media(max-width:600px){
    .order-detail-layout { grid-template-columns:1fr !important; }
}
</style>

<?php require_once 'includes/footer.php'; ?>
