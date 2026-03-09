<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function isAdmin() {
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
}

function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: /login.php?redirect=' . urlencode($_SERVER['REQUEST_URI']));
        exit;
    }
}

function requireAdmin() {
    if (!isLoggedIn() || !isAdmin()) {
        header('Location: /login.php');
        exit;
    }
}

function getCartCount($conn) {
    if (!isLoggedIn()) return 0;
    $uid = (int)$_SESSION['user_id'];
    $res = $conn->query("SELECT SUM(quantity) as total FROM cart WHERE user_id = $uid");
    $row = $res->fetch_assoc();
    return $row['total'] ?? 0;
}

function sanitize($str) {
    return htmlspecialchars(strip_tags(trim($str)), ENT_QUOTES, 'UTF-8');
}

function formatPrice($price) {
    return 'Rs. ' . number_format($price, 2);
}
