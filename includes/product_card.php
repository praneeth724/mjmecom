<?php
// Reusable product card partial - expects $p (product row with cat_name)
$_base    = SITE_BASE . '/';
$_imgFile = $p['image'] ?? '';
$imgSrc   = ($_imgFile && $_imgFile !== 'default.jpg' && file_exists(__DIR__ . '/../uploads/products/' . $_imgFile))
            ? ($_base . 'uploads/products/' . $_imgFile) : null;
$displayPrice = (!empty($p['sale_price']) && $p['is_on_sale']) ? $p['sale_price'] : $p['price'];
$isOnSale     = !empty($p['sale_price']) && $p['is_on_sale'];
$isOutOfStock = (int)$p['quantity'] <= 0;
?>
<div class="product-card">
    <div class="product-badge">
        <?php if ($isOnSale): ?><span class="badge badge-sale">Sale</span><?php endif; ?>
        <?php if (!empty($p['is_featured'])): ?><span class="badge badge-hot">Featured</span><?php endif; ?>
        <?php if ($isOutOfStock): ?><span class="badge badge-out">Out of Stock</span><?php endif; ?>
    </div>
    <a href="<?= $_base ?>product.php?id=<?= (int)$p['id'] ?>">
        <div class="product-image">
            <?php if ($imgSrc): ?>
                <img src="<?= $imgSrc ?>" alt="<?= sanitize($p['name']) ?>" loading="lazy">
            <?php else: ?>
                <div class="no-img">🛒</div>
            <?php endif; ?>
        </div>
    </a>
    <div class="product-body">
        <?php if (!empty($p['cat_name'])): ?>
        <div class="product-category"><?= sanitize($p['cat_name']) ?></div>
        <?php endif; ?>
        <a href="<?= $_base ?>product.php?id=<?= (int)$p['id'] ?>">
            <div class="product-name"><?= sanitize($p['name']) ?></div>
        </a>
        <div class="product-price">
            <span class="price-current <?= $isOnSale ? 'price-sale' : '' ?>">
                Rs. <?= number_format($displayPrice, 2) ?>
            </span>
            <?php if ($isOnSale): ?>
            <span class="price-original">Rs. <?= number_format($p['price'], 2) ?></span>
            <?php endif; ?>
        </div>
        <div class="product-footer">
            <?php if ($isOutOfStock): ?>
                <button class="btn-cart" disabled>Out of Stock</button>
            <?php else: ?>
                <button class="btn-cart" data-product-id="<?= (int)$p['id'] ?>">
                    🛒 Add to Cart
                </button>
            <?php endif; ?>
        </div>
        <?php if (!$isOutOfStock): ?>
        <div class="qty-badge">In stock: <?= (int)$p['quantity'] ?></div>
        <?php endif; ?>
    </div>
</div>
