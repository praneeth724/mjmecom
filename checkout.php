<?php
$pageTitle = 'Checkout';
require_once 'includes/db.php';
require_once 'includes/auth.php';

requireLogin();
$userId = (int)$_SESSION['user_id'];

// Fetch cart
$stmt = $conn->prepare("SELECT c.id as cart_id, c.quantity, p.id as product_id, p.name, p.price, p.sale_price, p.is_on_sale, p.quantity as stock FROM cart c JOIN products p ON c.product_id = p.id WHERE c.user_id = ?");
$stmt->bind_param('i', $userId);
$stmt->execute();
$cartItems = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

if (empty($cartItems)) {
    header('Location: cart.php');
    exit;
}

$subtotal = 0;
foreach ($cartItems as $item) {
    $price = ($item['is_on_sale'] && $item['sale_price']) ? $item['sale_price'] : $item['price'];
    $subtotal += $price * $item['quantity'];
}
$shipping = $subtotal >= 2000 ? 0 : 150;
$total = $subtotal + $shipping;

// Fetch user info
$userStmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$userStmt->bind_param('i', $userId);
$userStmt->execute();
$user = $userStmt->get_result()->fetch_assoc();

$error = '';
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $shippingName    = trim($_POST['shipping_name'] ?? '');
    $shippingPhone   = trim($_POST['shipping_phone'] ?? '');
    $shippingAddress = trim($_POST['shipping_address'] ?? '');
    $shippingCity    = trim($_POST['shipping_city'] ?? '');
    $paymentMethod   = $_POST['payment_method'] ?? 'cash_on_delivery';
    $notes           = trim($_POST['notes'] ?? '');

    if (!$shippingName || !$shippingPhone || !$shippingAddress || !$shippingCity) {
        $error = 'Please fill all required fields.';
    } else {
        $conn->begin_transaction();
        try {
            // Create order
            $ins = $conn->prepare("INSERT INTO orders (user_id, total_amount, shipping_name, shipping_phone, shipping_address, shipping_city, payment_method, notes) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $ins->bind_param('idssssss', $userId, $total, $shippingName, $shippingPhone, $shippingAddress, $shippingCity, $paymentMethod, $notes);
            $ins->execute();
            $orderId = $conn->insert_id;

            // Insert order items & update stock
            foreach ($cartItems as $item) {
                $unitPrice = ($item['is_on_sale'] && $item['sale_price']) ? $item['sale_price'] : $item['price'];
                $itemIns = $conn->prepare("INSERT INTO order_items (order_id, product_id, product_name, quantity, price) VALUES (?, ?, ?, ?, ?)");
                $itemIns->bind_param('iisid', $orderId, $item['product_id'], $item['name'], $item['quantity'], $unitPrice);
                $itemIns->execute();

                // Reduce stock
                $upd = $conn->prepare("UPDATE products SET quantity = GREATEST(0, quantity - ?) WHERE id = ?");
                $upd->bind_param('ii', $item['quantity'], $item['product_id']);
                $upd->execute();
            }

            // Clear cart
            $del = $conn->prepare("DELETE FROM cart WHERE user_id = ?");
            $del->bind_param('i', $userId);
            $del->execute();

            $conn->commit();
            header("Location: order-success.php?order_id=$orderId");
            exit;
        } catch (Exception $e) {
            $conn->rollback();
            $error = 'Failed to place order. Please try again.';
        }
    }
}

require_once 'includes/header.php';
?>

<!-- PAGE HEADER -->
<div class="page-header">
    <div class="container">
        <h1>Checkout</h1>
        <div class="breadcrumb">
            <a href="index.php">Home</a><span>/</span>
            <a href="cart.php">Cart</a><span>/</span>
            <span>Checkout</span>
        </div>
    </div>
</div>

<div class="checkout-page">
    <div class="container">
        <?php if ($error): ?>
        <div class="alert alert-error">❌ <?= sanitize($error) ?></div>
        <?php endif; ?>

        <form id="checkoutForm" method="POST" action="checkout.php">
            <div class="checkout-layout">
                <div>
                    <!-- SHIPPING INFO -->
                    <div class="checkout-form-card" style="margin-bottom:24px;">
                        <h3><span class="step-num">1</span> Shipping Information</h3>
                        <div class="form-grid">
                            <div class="form-group">
                                <label>Full Name *</label>
                                <input type="text" name="shipping_name" class="form-control" required
                                       value="<?= sanitize($_POST['shipping_name'] ?? $user['name']) ?>" placeholder="Your full name">
                            </div>
                            <div class="form-group">
                                <label>Phone Number *</label>
                                <input type="tel" name="shipping_phone" class="form-control" required
                                       value="<?= sanitize($_POST['shipping_phone'] ?? $user['phone'] ?? '') ?>" placeholder="0771234567">
                            </div>
                            <div class="form-group full">
                                <label>Delivery Address *</label>
                                <textarea name="shipping_address" class="form-control" required rows="2"
                                          placeholder="House No, Street, Area"><?= sanitize($_POST['shipping_address'] ?? $user['address'] ?? '') ?></textarea>
                            </div>
                            <div class="form-group">
                                <label>City *</label>
                                <input type="text" name="shipping_city" class="form-control" required
                                       value="<?= sanitize($_POST['shipping_city'] ?? '') ?>" placeholder="Colombo">
                            </div>
                            <div class="form-group">
                                <label>Email</label>
                                <input type="email" class="form-control" value="<?= sanitize($user['email']) ?>" readonly style="background:var(--light-gray); cursor:not-allowed;">
                            </div>
                        </div>
                    </div>

                    <!-- PAYMENT METHOD -->
                    <div class="checkout-form-card" style="margin-bottom:24px;">
                        <h3><span class="step-num">2</span> Payment Method</h3>
                        <label class="payment-option selected">
                            <input type="radio" name="payment_method" value="cash_on_delivery" checked>
                            <div class="payment-option-info">
                                <strong>💵 Cash on Delivery</strong>
                                <p>Pay with cash when your order arrives.</p>
                            </div>
                        </label>
                        <label class="payment-option">
                            <input type="radio" name="payment_method" value="bank_transfer">
                            <div class="payment-option-info">
                                <strong>🏦 Bank Transfer</strong>
                                <p>Transfer to our bank account. We'll confirm after receipt.</p>
                            </div>
                        </label>
                        <label class="payment-option">
                            <input type="radio" name="payment_method" value="online_payment">
                            <div class="payment-option-info">
                                <strong>📱 Online Transfer (Frimi / iPay)</strong>
                                <p>Pay via digital wallet or mobile banking.</p>
                            </div>
                        </label>
                    </div>

                    <!-- NOTES -->
                    <div class="checkout-form-card">
                        <h3><span class="step-num">3</span> Order Notes <span style="font-weight:400; font-size:14px; color:var(--gray);">(Optional)</span></h3>
                        <div class="form-group">
                            <textarea name="notes" class="form-control" rows="3" placeholder="Any special instructions for your order..."><?= sanitize($_POST['notes'] ?? '') ?></textarea>
                        </div>
                    </div>
                </div>

                <!-- ORDER REVIEW -->
                <div class="order-review">
                    <h3>Order Summary</h3>
                    <?php foreach ($cartItems as $item):
                        $unitPrice = ($item['is_on_sale'] && $item['sale_price']) ? $item['sale_price'] : $item['price'];
                        $lineTotal = $unitPrice * $item['quantity'];
                    ?>
                    <div class="order-item-row">
                        <div>
                            <div class="order-item-name"><?= sanitize($item['name']) ?></div>
                            <div class="order-item-qty">x<?= $item['quantity'] ?></div>
                        </div>
                        <span class="order-item-price">Rs. <?= number_format($lineTotal, 2) ?></span>
                    </div>
                    <?php endforeach; ?>

                    <div class="divider"></div>
                    <div class="summary-row">
                        <span>Subtotal</span>
                        <span>Rs. <?= number_format($subtotal, 2) ?></span>
                    </div>
                    <div class="summary-row">
                        <span>Shipping</span>
                        <span><?= $shipping === 0 ? '<span style="color:var(--primary);font-weight:600;">FREE</span>' : 'Rs. ' . number_format($shipping, 2) ?></span>
                    </div>
                    <div class="summary-row total">
                        <span>Total</span>
                        <span>Rs. <?= number_format($total, 2) ?></span>
                    </div>

                    <button type="submit" class="btn btn-primary btn-full btn-lg" style="margin-top:20px; font-size:16px;">
                        ✅ Place Order
                    </button>
                    <div style="text-align:center; margin-top:12px; font-size:12px; color:var(--gray);">
                        🔒 Your information is secure and encrypted
                    </div>
                    <a href="cart.php" style="display:block; text-align:center; margin-top:10px; font-size:13px; color:var(--gray);">← Back to Cart</a>
                </div>
            </div>
        </form>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
