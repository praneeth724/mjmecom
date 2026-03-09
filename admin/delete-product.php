<?php
require_once 'includes/admin_header.php';

$id = (int)($_GET['id'] ?? 0);
if (!$id) { header('Location: products.php'); exit; }

$stmt = $conn->prepare("SELECT image FROM products WHERE id = ?");
$stmt->bind_param('i', $id);
$stmt->execute();
$prod = $stmt->get_result()->fetch_assoc();

if ($prod) {
    // Delete image file
    if (!empty($prod['image']) && $prod['image'] !== 'default.jpg') {
        $imgPath = __DIR__ . '/../uploads/products/' . $prod['image'];
        if (file_exists($imgPath)) @unlink($imgPath);
    }
    $del = $conn->prepare("DELETE FROM products WHERE id = ?");
    $del->bind_param('i', $id);
    $del->execute();
}

header('Location: products.php?deleted=1');
exit;
