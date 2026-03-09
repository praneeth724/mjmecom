<?php
require_once 'includes/db.php';
require_once 'includes/auth.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if (!$id) { header('Location: products.php'); exit; }

$stmt = $conn->prepare("SELECT p.*, c.name as cat_name FROM products p LEFT JOIN categories c ON p.category_id = c.id WHERE p.id = ?");
$stmt->bind_param('i', $id);
$stmt->execute();
$p = $stmt->get_result()->fetch_assoc();
if (!$p) { header('Location: products.php'); exit; }

$pageTitle = $p['name'];
$displayPrice = (!empty($p['sale_price']) && $p['is_on_sale']) ? $p['sale_price'] : $p['price'];
$isOnSale = !empty($p['sale_price']) && $p['is_on_sale'];
$savings = $isOnSale ? ($p['price'] - $p['sale_price']) : 0;
$savePct = $isOnSale ? round($savings / $p['price'] * 100) : 0;

// Related products
$related = $conn->prepare("SELECT p.*, c.name as cat_name FROM products p LEFT JOIN categories c ON p.category_id = c.id WHERE p.category_id = ? AND p.id != ? AND p.quantity > 0 LIMIT 4");
$related->bind_param('ii', $p['category_id'], $id);
$related->execute();
$relatedProducts = $related->get_result();

$imgSrc = (!empty($p['image']) && $p['image'] !== 'default.jpg' && file_exists(__DIR__ . '/uploads/products/' . $p['image']))
    ? (SITE_BASE . '/uploads/products/' . $p['image']) : null;

require_once 'includes/header.php';
?>

<!-- PAGE HEADER -->
<div class="page-header">
    <div class="container">
        <h1><?= sanitize($p['name']) ?></h1>
        <div class="breadcrumb">
            <a href="index.php">Home</a><span>/</span>
            <a href="products.php">Products</a><span>/</span>
            <?php if ($p['cat_name']): ?>
            <a href="products.php?category=<?= $p['category_id'] ?>"><?= sanitize($p['cat_name']) ?></a><span>/</span>
            <?php endif; ?>
            <span><?= sanitize($p['name']) ?></span>
        </div>
    </div>
</div>

<div class="product-detail">
    <div class="container">
        <div class="product-detail-grid">
            <!-- Image -->
            <div class="product-detail-image">
                <?php if ($imgSrc): ?>
                    <img src="<?= $imgSrc ?>" alt="<?= sanitize($p['name']) ?>">
                <?php else: ?>
                    <div class="no-img-big">🛒</div>
                <?php endif; ?>
            </div>
            <!-- Info -->
            <div class="product-detail-info">
                <?php if ($p['cat_name']): ?>
                <div style="font-size:13px; color:var(--gray); margin-bottom:8px; text-transform:uppercase; letter-spacing:0.5px;">
                    <a href="products.php?category=<?= $p['category_id'] ?>" style="color:var(--primary); font-weight:600;"><?= sanitize($p['cat_name']) ?></a>
                </div>
                <?php endif; ?>

                <h1><?= sanitize($p['name']) ?></h1>

                <?php if ($isOnSale): ?>
                <div style="display:inline-flex; align-items:center; gap:8px; background:#fff3cd; padding:6px 14px; border-radius:20px; margin:10px 0;">
                    <span style="font-size:12px; font-weight:700; color:#856404;">🔥 MEGA SALE</span>
                    <span style="font-size:13px; font-weight:700; color:var(--sale);">Save <?= $savePct ?>%</span>
                </div>
                <?php endif; ?>

                <div class="product-detail-price">
                    <span class="detail-price-main" style="<?= $isOnSale ? 'color:var(--sale)' : '' ?>">
                        Rs. <?= number_format($displayPrice, 2) ?>
                    </span>
                    <?php if ($isOnSale): ?>
                    <span class="detail-price-original">Rs. <?= number_format($p['price'], 2) ?></span>
                    <span class="detail-save-badge">Save Rs. <?= number_format($savings, 2) ?></span>
                    <?php endif; ?>
                </div>

                <div class="product-meta">
                    <div class="meta-row">
                        <span class="meta-label">Availability:</span>
                        <?php if ($p['quantity'] > 10): ?>
                        <span class="meta-value stock-ok">✅ In Stock (<?= $p['quantity'] ?> available)</span>
                        <?php elseif ($p['quantity'] > 0): ?>
                        <span class="meta-value stock-low">⚠️ Low Stock (<?= $p['quantity'] ?> left)</span>
                        <?php else: ?>
                        <span class="meta-value stock-out">❌ Out of Stock</span>
                        <?php endif; ?>
                    </div>
                    <?php if ($p['cat_name']): ?>
                    <div class="meta-row">
                        <span class="meta-label">Category:</span>
                        <span class="meta-value"><a href="products.php?category=<?= $p['category_id'] ?>" style="color:var(--primary);"><?= sanitize($p['cat_name']) ?></a></span>
                    </div>
                    <?php endif; ?>
                </div>

                <?php if ($p['description']): ?>
                <div class="product-description"><?= nl2br(sanitize($p['description'])) ?></div>
                <?php endif; ?>

                <?php if ($p['quantity'] > 0): ?>
                <div>
                    <label style="font-size:13px; font-weight:600; display:block; margin-bottom:8px;">Quantity:</label>
                    <div class="qty-selector">
                        <button class="qty-btn" data-action="dec" type="button">−</button>
                        <input type="number" class="qty-input" id="detail-qty" value="1" min="1" max="<?= $p['quantity'] ?>">
                        <button class="qty-btn" data-action="inc" type="button">+</button>
                    </div>
                </div>
                <div style="display:flex; gap:12px; margin-top:20px; flex-wrap:wrap;">
                    <button class="btn btn-primary btn-lg btn-cart" data-product-id="<?= $p['id'] ?>">
                        🛒 Add to Cart
                    </button>
                    <a href="cart.php" class="btn btn-outline btn-lg">View Cart</a>
                </div>
                <?php else: ?>
                <div class="alert alert-error" style="margin-top:16px;">
                    ❌ This product is currently out of stock. Please check back later.
                </div>
                <?php endif; ?>

                <div style="margin-top:20px; padding:14px; background:var(--light-gray); border-radius:8px; display:flex; gap:20px; flex-wrap:wrap; font-size:13px; color:var(--gray);">
                    <span>🚚 Free delivery on orders above Rs. 2000</span>
                    <span>🔒 Secure checkout</span>
                    <span>🔄 Easy returns</span>
                </div>
            </div>
        </div>

        <!-- RELATED PRODUCTS -->
        <?php if ($relatedProducts->num_rows > 0): ?>
        <div style="margin-top:50px;">
            <div class="section-header">
                <h2 class="section-title">Related Products</h2>
            </div>
            <div class="products-grid">
                <?php while ($p = $relatedProducts->fetch_assoc()): ?>
                <?php include 'includes/product_card.php'; ?>
                <?php endwhile; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
