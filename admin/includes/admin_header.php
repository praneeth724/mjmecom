<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/auth.php';
requireAdmin();

$pendingOrders = (int)$conn->query("SELECT COUNT(*) FROM orders WHERE status='pending'")->fetch_row()[0];
$currentPage   = basename($_SERVER['PHP_SELF']);
$base          = SITE_BASE . '/';        // e.g. "/ecomgura/"
$adminBase     = SITE_BASE . '/admin/';  // e.g. "/ecomgura/admin/"
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($adminTitle) ? sanitize($adminTitle) . ' - ' : '' ?>Admin | MRM Grocery</title>
    <link rel="stylesheet" href="<?= $base ?>css/style.css">
    <link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='90'>⚙️</text></svg>">
</head>
<body>
<div class="admin-wrapper">
    <!-- SIDEBAR -->
    <aside class="admin-sidebar" id="adminSidebar">
        <div class="admin-logo">
            <div class="logo-icon">M</div>
            <div>
                <h2>MRM Admin</h2>
                <span>Control Panel</span>
            </div>
        </div>
        <nav class="admin-nav">
            <div class="admin-nav-section">Dashboard</div>
            <a href="<?= $adminBase ?>index.php" class="<?= $currentPage==='index.php'?'active':'' ?>">
                📊 <span>Dashboard</span>
            </a>

            <div class="admin-nav-section">Catalog</div>
            <a href="<?= $adminBase ?>products.php"
               class="<?= in_array($currentPage,['products.php','edit-product.php'])?'active':'' ?>">
                🛍️ <span>Products</span>
            </a>
            <a href="<?= $adminBase ?>add-product.php"
               class="<?= $currentPage==='add-product.php'?'active':'' ?>">
                ➕ <span>Add Product</span>
            </a>
            <a href="<?= $adminBase ?>categories.php"
               class="<?= $currentPage==='categories.php'?'active':'' ?>">
                📂 <span>Categories</span>
            </a>

            <div class="admin-nav-section">Orders</div>
            <a href="<?= $adminBase ?>orders.php"
               class="<?= $currentPage==='orders.php'?'active':'' ?>">
                📦 <span>All Orders</span>
                <?php if ($pendingOrders > 0): ?>
                <span style="margin-left:auto;background:var(--sale);color:white;border-radius:10px;padding:2px 7px;font-size:11px;font-weight:700;"><?= $pendingOrders ?></span>
                <?php endif; ?>
            </a>

            <div class="admin-nav-section">Users</div>
            <a href="<?= $adminBase ?>users.php"
               class="<?= $currentPage==='users.php'?'active':'' ?>">
                👥 <span>Customers</span>
            </a>

            <div class="admin-nav-section">Site</div>
            <a href="<?= $base ?>index.php" target="_blank">🌐 <span>View Site</span></a>
            <a href="<?= $base ?>logout.php">🚪 <span>Logout</span></a>
        </nav>
    </aside>

    <!-- MAIN CONTENT -->
    <div class="admin-main">
        <div class="admin-topbar">
            <div style="display:flex;align-items:center;gap:14px;">
                <button id="adminSidebarToggle"
                        style="background:none;border:none;cursor:pointer;font-size:22px;display:none;"
                        class="hamburger-admin">☰</button>
                <h1><?= isset($adminTitle) ? sanitize($adminTitle) : 'Dashboard' ?></h1>
            </div>
            <div style="display:flex;align-items:center;gap:14px;font-size:14px;color:var(--gray);">
                <span>👤 <?= sanitize($_SESSION['user_name']) ?></span>
                <a href="<?= $base ?>logout.php" class="btn btn-danger btn-sm">Logout</a>
            </div>
        </div>
        <div class="admin-content">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="<?= $base ?>js/main.js"></script>
<style>
@media(max-width:900px){
    .hamburger-admin { display:flex !important; }
    .admin-sidebar { transform: translateX(-260px); }
    .admin-sidebar.open { transform: translateX(0) !important; }
    .admin-main { margin-left: 0 !important; }
}
</style>
