<?php
$pageTitle = 'Order Placed!';
require_once 'includes/db.php';
require_once 'includes/auth.php';

requireLogin();
$userId = (int)$_SESSION['user_id'];
$orderId = (int)($_GET['order_id'] ?? 0);

$stmt = $conn->prepare("SELECT o.*, COUNT(oi.id) as item_count FROM orders o LEFT JOIN order_items oi ON o.id = oi.order_id WHERE o.id = ? AND o.user_id = ? GROUP BY o.id");
$stmt->bind_param('ii', $orderId, $userId);
$stmt->execute();
$order = $stmt->get_result()->fetch_assoc();

if (!$order) { header('Location: index.php'); exit; }

// Order items
$items = $conn->prepare("SELECT * FROM order_items WHERE order_id = ?");
$items->bind_param('i', $orderId);
$items->execute();
$orderItems = $items->get_result()->fetch_all(MYSQLI_ASSOC);

require_once 'includes/header.php';
?>

<div class="success-page">
    <div class="container">
        <div class="success-card">
            <span class="success-icon">🎉</span>
            <h2>Order Placed Successfully!</h2>
            <p>Thank you, <strong><?= sanitize($_SESSION['user_name']) ?></strong>! Your order has been received.</p>
            <p style="font-size:13px;">We'll process your order and deliver it soon.</p>

            <div class="order-number">
                Order ID: <strong>#<?= str_pad($order['id'], 6, '0', STR_PAD_LEFT) ?></strong>
            </div>

            <div style="background:var(--light-gray); border-radius:10px; padding:20px; text-align:left; margin:20px 0;">
                <h4 style="margin-bottom:14px; font-size:15px; color:var(--dark);">Order Details</h4>
                <?php foreach ($orderItems as $item): ?>
                <div style="display:flex; justify-content:space-between; padding:8px 0; border-bottom:1px solid var(--border); font-size:14px;">
                    <span><?= sanitize($item['product_name']) ?> x<?= $item['quantity'] ?></span>
                    <span style="font-weight:600;">Rs. <?= number_format($item['price'] * $item['quantity'], 2) ?></span>
                </div>
                <?php endforeach; ?>
                <div style="display:flex; justify-content:space-between; margin-top:12px; font-size:16px; font-weight:700; color:var(--primary);">
                    <span>Total Paid</span>
                    <span>Rs. <?= number_format($order['total_amount'], 2) ?></span>
                </div>
            </div>

            <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px; text-align:left; font-size:13px; background:#f0fff4; padding:16px; border-radius:10px; margin-bottom:24px;">
                <div><strong>Payment:</strong><br><?= ucwords(str_replace('_',' ',$order['payment_method'])) ?></div>
                <div><strong>Status:</strong><br><span class="status-badge status-<?= $order['status'] ?>"><?= ucfirst($order['status']) ?></span></div>
                <div><strong>Deliver To:</strong><br><?= sanitize($order['shipping_city']) ?></div>
                <div><strong>Date:</strong><br><?= date('d M Y', strtotime($order['created_at'])) ?></div>
            </div>

            <div style="display:flex; gap:12px; justify-content:center; flex-wrap:wrap;">
                <a href="my-orders.php" class="btn btn-primary">📦 View My Orders</a>
                <a href="products.php" class="btn btn-outline">🛍️ Continue Shopping</a>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
