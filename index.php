<?php
$pageTitle = 'Home';
require_once 'includes/db.php';
require_once 'includes/auth.php';

// Fetch featured products
$featured = $conn->query("SELECT p.*, c.name as cat_name FROM products p LEFT JOIN categories c ON p.category_id = c.id WHERE p.is_featured = 1 AND p.quantity > 0 ORDER BY p.created_at DESC LIMIT 8");

// Fetch sale products
$sale_products = $conn->query("SELECT p.*, c.name as cat_name FROM products p LEFT JOIN categories c ON p.category_id = c.id WHERE p.is_on_sale = 1 AND p.quantity > 0 ORDER BY p.created_at DESC LIMIT 4");

// Fetch categories
$categories = $conn->query("SELECT c.*, COUNT(p.id) as product_count FROM categories c LEFT JOIN products p ON c.id = p.category_id GROUP BY c.id ORDER BY c.name");

$catIcons = ['Vegetables'=>'🥦','Fruits'=>'🍎','Dairy & Eggs'=>'🥚','Grains & Rice'=>'🌾','Beverages'=>'🥤','Snacks'=>'🍟','Meat & Fish'=>'🐟','Household'=>'🏠'];

require_once 'includes/header.php';
?>

<!-- HERO SECTION -->
<section class="hero">
    <div class="container">
        <div class="hero-content">
            <div class="hero-text">
                <div class="hero-badge">🌿 Fresh & Natural</div>
                <h2>Fresh Groceries<br><span>Delivered Fast!</span></h2>
                <p>Shop the freshest vegetables, fruits, dairy, and more. Quality products at wholesale prices for your family.</p>
                <div class="hero-btns">
                    <a href="products.php" class="btn btn-accent btn-lg">🛍️ Shop Now</a>
                    <a href="products.php?sale=1" class="btn btn-lg" style="background:rgba(255,255,255,0.2); color:white; border:2px solid rgba(255,255,255,0.5);">🔥 View Sales</a>
                </div>
                <div style="display:flex; gap:28px; margin-top:32px; flex-wrap:wrap;">
                    <div style="color:rgba(255,255,255,0.9);">
                        <div style="font-size:22px; font-weight:800;">500+</div>
                        <div style="font-size:12px; opacity:0.8;">Products</div>
                    </div>
                    <div style="color:rgba(255,255,255,0.9);">
                        <div style="font-size:22px; font-weight:800;">1000+</div>
                        <div style="font-size:12px; opacity:0.8;">Customers</div>
                    </div>
                    <div style="color:rgba(255,255,255,0.9);">
                        <div style="font-size:22px; font-weight:800;">100%</div>
                        <div style="font-size:12px; opacity:0.8;">Fresh</div>
                    </div>
                </div>
            </div>
            <div class="hero-image">
                <div class="hero-img-circle">🛒</div>
            </div>
        </div>
    </div>
</section>

<!-- CATEGORIES SECTION -->
<section class="section section-alt">
    <div class="container">
        <div class="section-header">
            <h2 class="section-title">Shop by Category</h2>
            <a href="products.php" class="view-all">View All →</a>
        </div>
        <div class="categories-grid">
            <?php $categories->data_seek(0); while ($cat = $categories->fetch_assoc()): ?>
            <a href="products.php?category=<?= $cat['id'] ?>" class="category-card">
                <span class="category-icon"><?= $catIcons[$cat['name']] ?? '📦' ?></span>
                <div class="category-name"><?= sanitize($cat['name']) ?></div>
                <div style="font-size:11px; color:var(--gray); margin-top:4px;"><?= $cat['product_count'] ?> items</div>
            </a>
            <?php endwhile; ?>
        </div>
    </div>
</section>

<!-- FEATURED PRODUCTS -->
<section class="section">
    <div class="container">
        <div class="section-header">
            <h2 class="section-title">Featured Products</h2>
            <a href="products.php" class="view-all">View All →</a>
        </div>
        <div class="products-grid">
            <?php $featured->data_seek(0); while ($p = $featured->fetch_assoc()): ?>
            <?php include 'includes/product_card.php'; ?>
            <?php endwhile; ?>
        </div>
    </div>
</section>

<!-- MEGA SALE BANNER -->
<?php $sale_products->data_seek(0); $saleArr = $sale_products->fetch_all(MYSQLI_ASSOC); ?>
<?php if (!empty($saleArr)): ?>
<section class="mega-sale-banner">
    <div class="container">
        <div class="sale-content">
            <div class="sale-badge-big">
                <div class="percent">UP TO<br>30%</div>
                <div class="off-text">OFF</div>
            </div>
            <div class="sale-info">
                <h2>🔥 Mega Sale is ON!</h2>
                <p>Don't miss our amazing deals on fresh groceries and wholesale products. Limited time offer!</p>
                <div class="sale-items-preview">
                    <?php foreach ($saleArr as $sp): ?>
                    <span class="sale-item-chip">
                        <?= sanitize($sp['name']) ?>
                        — <strong>Rs. <?= number_format($sp['sale_price'], 0) ?></strong>
                    </span>
                    <?php endforeach; ?>
                </div>
                <div style="margin-top:20px;">
                    <a href="products.php?sale=1" class="btn btn-lg" style="background:white; color:var(--sale); font-weight:800;">
                        🛍️ Shop Sale Items
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- SALE PRODUCTS GRID -->
<?php if (!empty($saleArr)): ?>
<section class="section section-alt">
    <div class="container">
        <div class="section-header">
            <h2 class="section-title" style="color:var(--sale);">🔥 On Sale Now</h2>
            <a href="products.php?sale=1" class="view-all" style="color:var(--sale);">View All Sales →</a>
        </div>
        <div class="products-grid">
            <?php foreach ($saleArr as $p): ?>
            <?php include 'includes/product_card.php'; ?>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- WHY CHOOSE US -->
<section class="section">
    <div class="container">
        <div class="section-header">
            <h2 class="section-title">Why Choose MRM?</h2>
        </div>
        <div class="features-grid">
            <div class="feature-card">
                <span class="feature-icon">🌿</span>
                <h3>100% Fresh</h3>
                <p>All our products are sourced daily from trusted local farmers and suppliers.</p>
            </div>
            <div class="feature-card">
                <span class="feature-icon">💰</span>
                <h3>Best Prices</h3>
                <p>Wholesale prices available for bulk orders. Save more when you buy more.</p>
            </div>
            <div class="feature-card">
                <span class="feature-icon">🚚</span>
                <h3>Fast Delivery</h3>
                <p>Same day delivery available for orders placed before 12 PM within Colombo.</p>
            </div>
            <div class="feature-card">
                <span class="feature-icon">🔒</span>
                <h3>Secure Shopping</h3>
                <p>Your personal information and payments are always safe with us.</p>
            </div>
        </div>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>
