<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/auth.php';
$cartCount   = getCartCount($conn);
$currentPage = basename($_SERVER['PHP_SELF']);
$base        = SITE_BASE . '/';   // e.g. "/ecomgura/"  or  "/"
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($pageTitle) ? sanitize($pageTitle) . ' - ' : '' ?>MRM Grocery &amp; Wholesale</title>
    <link rel="stylesheet" href="<?= $base ?>css/style.css">
    <link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='90'>🛒</text></svg>">
</head>
<body>

<!-- TOP BAR -->
<div class="top-bar">
    <div class="container">
        <div>
            <span>📞 +94 11 234 5678</span>
            &nbsp;&nbsp;
            <span>✉️ info@mrmgrocery.lk</span>
        </div>
        <div class="top-bar-links">
            <span>🕐 Mon-Sat: 8:00 AM - 9:00 PM</span>
            <?php if (isLoggedIn()): ?>
                <a href="<?= $base ?>my-orders.php">My Orders</a>
                <a href="<?= $base ?>logout.php">Logout</a>
                <?php if (isAdmin()): ?>
                    <a href="<?= $base ?>admin/index.php">Admin Panel</a>
                <?php endif; ?>
            <?php else: ?>
                <a href="<?= $base ?>login.php">Login</a>
                <a href="<?= $base ?>register.php">Register</a>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- HEADER -->
<header>
    <div class="container">
        <div class="header-main">
            <a href="<?= $base ?>index.php" class="logo">
                <div class="logo-icon">M</div>
                <div class="logo-text">
                    <h1>MRM Grocery</h1>
                    <span>&amp; Wholesale</span>
                </div>
            </a>
            <div class="header-search">
                <form method="GET" action="<?= $base ?>products.php">
                    <input type="text" name="search" placeholder="Search for products..."
                           value="<?= isset($_GET['search']) ? sanitize($_GET['search']) : '' ?>">
                    <button type="submit">🔍</button>
                </form>
            </div>
            <div class="header-actions">
                <?php if (isLoggedIn()): ?>
                <a href="<?= $base ?>my-orders.php" class="header-action-btn">
                    <span style="font-size:22px;">📦</span>
                    <span>Orders</span>
                </a>
                <?php endif; ?>
                <a href="<?= $base ?>cart.php" class="header-action-btn" style="position:relative;">
                    <span style="font-size:22px;">🛒</span>
                    <?php if ($cartCount > 0): ?>
                    <span class="cart-badge"><?= $cartCount ?></span>
                    <?php endif; ?>
                    <span>Cart</span>
                </a>
                <?php if (isLoggedIn()): ?>
                <a href="<?= $base ?>logout.php" class="header-action-btn">
                    <span style="font-size:22px;">👤</span>
                    <span><?= sanitize(explode(' ', $_SESSION['user_name'])[0]) ?></span>
                </a>
                <?php else: ?>
                <a href="<?= $base ?>login.php" class="header-action-btn">
                    <span style="font-size:22px;">👤</span>
                    <span>Login</span>
                </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</header>

<!-- NAVBAR -->
<nav>
    <div class="container">
        <div class="nav-container">
            <div class="nav-links" id="navLinks">
                <?php
                $navItems = [
                    'index.php'              => 'Home',
                    'products.php'           => 'Products',
                    'products.php?sale=1'    => 'Mega Sale 🔥',
                    'products.php?category=2'=> 'Fruits',
                    'products.php?category=1'=> 'Vegetables',
                ];
                foreach ($navItems as $href => $label):
                    $activeFile = explode('?', $href)[0];
                    $isActive   = $currentPage === $activeFile;
                    $isSale     = strpos($href, 'sale=1') !== false;
                ?>
                <a href="<?= $base . $href ?>"
                   class="<?= $isActive ? 'active' : '' ?><?= $isSale ? ' sale-link' : '' ?>">
                    <?= $label ?>
                </a>
                <?php endforeach; ?>
            </div>
            <button class="hamburger" id="hamburger" aria-label="Menu">
                <span></span><span></span><span></span>
            </button>
        </div>
    </div>
    <!-- MOBILE NAV -->
    <div class="mobile-nav" id="mobileNav">
        <a href="<?= $base ?>index.php">🏠 Home</a>
        <a href="<?= $base ?>products.php">🛍️ Products</a>
        <a href="<?= $base ?>products.php?sale=1">🔥 Mega Sale</a>
        <a href="<?= $base ?>products.php?category=2">🍎 Fruits</a>
        <a href="<?= $base ?>products.php?category=1">🥦 Vegetables</a>
        <a href="<?= $base ?>cart.php">🛒 Cart (<?= $cartCount ?>)</a>
        <?php if (isLoggedIn()): ?>
        <a href="<?= $base ?>my-orders.php">📦 My Orders</a>
        <?php if (isAdmin()): ?>
        <a href="<?= $base ?>admin/index.php">⚙️ Admin Panel</a>
        <?php endif; ?>
        <a href="<?= $base ?>logout.php">🚪 Logout</a>
        <?php else: ?>
        <a href="<?= $base ?>login.php">👤 Login</a>
        <a href="<?= $base ?>register.php">📝 Register</a>
        <?php endif; ?>
    </div>
</nav>

<div class="toast-container"></div>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="<?= $base ?>js/main.js" defer></script>
