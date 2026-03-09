<?php $base = SITE_BASE . '/'; ?>
<!-- FOOTER -->
<footer>
    <div class="container">
        <div class="footer-grid">
            <div class="footer-brand">
                <a href="<?= $base ?>index.php" class="logo" style="margin-bottom:14px; display:flex; align-items:center; gap:12px;">
                    <div class="logo-icon">M</div>
                    <div class="logo-text">
                        <h1 style="color:white;font-size:18px;">MRM Grocery</h1>
                        <span>&amp; Wholesale</span>
                    </div>
                </a>
                <p>Your trusted neighborhood grocery and wholesale store. Fresh products, best prices, delivered to your door.</p>
                <div style="display:flex;gap:10px;margin-top:14px;">
                    <a href="#" style="width:36px;height:36px;background:rgba(255,255,255,0.1);border-radius:8px;display:flex;align-items:center;justify-content:center;color:white;font-size:14px;font-weight:700;">f</a>
                    <a href="#" style="width:36px;height:36px;background:rgba(255,255,255,0.1);border-radius:8px;display:flex;align-items:center;justify-content:center;font-size:14px;">📷</a>
                    <a href="#" style="width:36px;height:36px;background:rgba(255,255,255,0.1);border-radius:8px;display:flex;align-items:center;justify-content:center;font-size:14px;">🐦</a>
                </div>
            </div>
            <div class="footer-col">
                <h4>Quick Links</h4>
                <ul>
                    <li><a href="<?= $base ?>index.php">🏠 Home</a></li>
                    <li><a href="<?= $base ?>products.php">🛍️ All Products</a></li>
                    <li><a href="<?= $base ?>products.php?sale=1">🔥 Mega Sale</a></li>
                    <li><a href="<?= $base ?>cart.php">🛒 My Cart</a></li>
                    <li><a href="<?= $base ?>my-orders.php">📦 My Orders</a></li>
                </ul>
            </div>
            <div class="footer-col">
                <h4>Categories</h4>
                <ul>
                    <li><a href="<?= $base ?>products.php?category=1">🥦 Vegetables</a></li>
                    <li><a href="<?= $base ?>products.php?category=2">🍎 Fruits</a></li>
                    <li><a href="<?= $base ?>products.php?category=3">🥚 Dairy &amp; Eggs</a></li>
                    <li><a href="<?= $base ?>products.php?category=4">🌾 Grains &amp; Rice</a></li>
                    <li><a href="<?= $base ?>products.php?category=5">🥤 Beverages</a></li>
                </ul>
            </div>
            <div class="footer-col">
                <h4>Contact Us</h4>
                <div class="contact-item"><i>📍</i><span>123 Main Street, Colombo 07, Sri Lanka</span></div>
                <div class="contact-item"><i>📞</i><span>+94 11 234 5678 / +94 77 123 4567</span></div>
                <div class="contact-item"><i>✉️</i><span>info@mrmgrocery.lk</span></div>
                <div class="contact-item"><i>🕐</i><span>Mon - Sat: 8:00 AM - 9:00 PM</span></div>
            </div>
        </div>
        <div class="footer-bottom">
            <span>&copy; <?= date('Y') ?> MRM Grocery &amp; Wholesale. All rights reserved.</span>
            <span>Made with ❤️ for fresh groceries</span>
        </div>
    </div>
</footer>
</body>
</html>
