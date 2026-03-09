<?php
$pageTitle = 'Shopping Cart';
require_once 'includes/db.php';
require_once 'includes/auth.php';

if (!isLoggedIn()) {
    header('Location: login.php?redirect=cart.php');
    exit;
}

$userId = (int)$_SESSION['user_id'];
$stmt = $conn->prepare("SELECT c.id as cart_id, c.quantity, p.id as product_id, p.name, p.price, p.sale_price, p.is_on_sale, p.quantity as stock, p.image, cat.name as cat_name FROM cart c JOIN products p ON c.product_id = p.id LEFT JOIN categories cat ON p.category_id = cat.id WHERE c.user_id = ? ORDER BY c.created_at DESC");
$stmt->bind_param('i', $userId);
$stmt->execute();
$cartItems = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

$subtotal = 0;
foreach ($cartItems as $item) {
    $price = ($item['is_on_sale'] && $item['sale_price']) ? $item['sale_price'] : $item['price'];
    $subtotal += $price * $item['quantity'];
}
$shipping = $subtotal >= 2000 ? 0 : 150;
$total = $subtotal + $shipping;

require_once 'includes/header.php';
?>

<!-- PAGE HEADER -->
<div class="page-header">
    <div class="container">
        <h1>🛒 Shopping Cart</h1>
        <div class="breadcrumb">
            <a href="index.php">Home</a><span>/</span><span>Cart</span>
        </div>
    </div>
</div>

<div class="cart-page">
    <div class="container">
        <?php if (empty($cartItems)): ?>
        <div class="empty-cart">
            <div class="empty-icon">🛒</div>
            <h3>Your cart is empty</h3>
            <p style="color:var(--gray); margin-bottom:24px;">Add some fresh products to your cart!</p>
            <a href="products.php" class="btn btn-primary btn-lg">🛍️ Start Shopping</a>
        </div>
        <?php else: ?>
        <div class="cart-layout">
            <!-- CART ITEMS -->
            <div class="cart-items">
                <div class="cart-header-row">
                    <span>Product</span>
                    <span>Price</span>
                    <span>Quantity</span>
                    <span>Subtotal</span>
                    <span></span>
                </div>
                <?php foreach ($cartItems as $item):
                    $unitPrice = ($item['is_on_sale'] && $item['sale_price']) ? $item['sale_price'] : $item['price'];
                    $lineTotal = $unitPrice * $item['quantity'];
                    $imgSrc = (!empty($item['image']) && $item['image'] !== 'default.jpg' && file_exists(__DIR__ . '/uploads/products/' . $item['image'])) ? ($base . 'uploads/products/' . $item['image']) : null;
                ?>
                <div class="cart-item">
                    <div class="cart-item-info">
                        <div class="cart-item-img">
                            <?php if ($imgSrc): ?>
                                <img src="<?= $imgSrc ?>" alt="<?= sanitize($item['name']) ?>">
                            <?php else: ?>
                                <div class="no-img-sm">🛒</div>
                            <?php endif; ?>
                        </div>
                        <div>
                            <div class="cart-item-name">
                                <a href="product.php?id=<?= $item['product_id'] ?>"><?= sanitize($item['name']) ?></a>
                            </div>
                            <div class="cart-item-cat"><?= sanitize($item['cat_name'] ?? '') ?></div>
                            <?php if ($item['is_on_sale']): ?>
                            <span class="badge badge-sale" style="margin-top:4px;">On Sale</span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div>
                        <span style="font-weight:600; color:var(--primary);">Rs. <?= number_format($unitPrice, 2) ?></span>
                        <?php if ($item['is_on_sale'] && $item['sale_price']): ?>
                        <br><span style="font-size:12px; color:var(--gray); text-decoration:line-through;">Rs. <?= number_format($item['price'], 2) ?></span>
                        <?php endif; ?>
                    </div>
                    <div>
                        <div class="cart-qty-control">
                            <button class="cart-qty-btn" data-action="dec">−</button>
                            <input type="number" class="cart-qty-input" value="<?= $item['quantity'] ?>" min="1" max="<?= $item['stock'] ?>" data-cart-id="<?= $item['cart_id'] ?>">
                            <button class="cart-qty-btn" data-action="inc">+</button>
                        </div>
                    </div>
                    <div>
                        <span class="item-subtotal" data-cart-id="<?= $item['cart_id'] ?>" style="font-weight:700; color:var(--primary); font-size:16px;">
                            Rs. <?= number_format($lineTotal, 2) ?>
                        </span>
                    </div>
                    <div>
                        <button class="btn-remove" data-cart-id="<?= $item['cart_id'] ?>" title="Remove">✕</button>
                    </div>
                </div>
                <?php endforeach; ?>

                <!-- CART FOOTER -->
                <div style="padding:16px 20px; display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:12px; background:var(--light-gray);">
                    <a href="products.php" class="btn btn-outline btn-sm">← Continue Shopping</a>
                    <div style="font-size:14px; color:var(--gray);">
                        🚚 Free shipping on orders above <strong>Rs. 2,000</strong>
                    </div>
                </div>
            </div>

            <!-- CART SUMMARY -->
            <div class="cart-summary">
                <h3>Order Summary</h3>
                <div class="summary-row">
                    <span>Subtotal (<?= count($cartItems) ?> items)</span>
                    <span>Rs. <?= number_format($subtotal, 2) ?></span>
                </div>
                <div class="summary-row">
                    <span>Shipping</span>
                    <span><?= $shipping === 0 ? '<span style="color:var(--primary); font-weight:600;">FREE</span>' : 'Rs. ' . number_format($shipping, 2) ?></span>
                </div>
                <?php if ($shipping > 0): ?>
                <div style="font-size:12px; color:var(--gray); margin-bottom:12px;">Add Rs. <?= number_format(2000 - $subtotal, 2) ?> more for free shipping</div>
                <?php endif; ?>
                <div class="summary-row total">
                    <span>Total</span>
                    <span id="cart-total">Rs. <?= number_format($total, 2) ?></span>
                </div>
                <a href="checkout.php" class="btn btn-primary btn-full btn-lg" style="margin-top:16px;">
                    Proceed to Checkout →
                </a>
                <div style="text-align:center; margin-top:14px; font-size:12px; color:var(--gray);">
                    🔒 Secure & Safe Checkout
                </div>
                <div class="divider"></div>
                <div style="font-size:13px; color:var(--gray); line-height:1.6;">
                    <strong>We Accept:</strong><br>
                    💵 Cash on Delivery &nbsp; 📱 Online Transfer
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
