<?php
session_start();
require_once 'includes/db.php';
require_once 'includes/auth.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    echo json_encode([
        'success'  => false,
        'message'  => 'Please login to use the cart.',
        'redirect' => SITE_BASE . '/login.php'
    ]);
    exit;
}

$action = $_POST['action'] ?? '';
$userId = (int)$_SESSION['user_id'];

function cartCount($conn, $userId) {
    $r = $conn->query("SELECT COALESCE(SUM(quantity),0) FROM cart WHERE user_id = $userId");
    return (int)$r->fetch_row()[0];
}

function cartTotal($conn, $userId) {
    $r = $conn->query("
        SELECT COALESCE(SUM(
            CASE WHEN p.is_on_sale=1 AND p.sale_price IS NOT NULL
                 THEN p.sale_price ELSE p.price END * c.quantity
        ), 0)
        FROM cart c JOIN products p ON c.product_id = p.id
        WHERE c.user_id = $userId
    ");
    return 'Rs. ' . number_format((float)$r->fetch_row()[0], 2);
}

if ($action === 'add') {
    $productId = (int)($_POST['product_id'] ?? 0);
    $qty       = max(1, (int)($_POST['qty'] ?? 1));
    if (!$productId) { echo json_encode(['success'=>false,'message'=>'Invalid product']); exit; }

    $stmt = $conn->prepare("SELECT id, name, quantity FROM products WHERE id = ?");
    $stmt->bind_param('i', $productId);
    $stmt->execute();
    $product = $stmt->get_result()->fetch_assoc();

    if (!$product || $product['quantity'] < $qty) {
        echo json_encode(['success'=>false,'message'=>'Insufficient stock']);
        exit;
    }

    // Upsert cart row
    $chk = $conn->prepare("SELECT id, quantity FROM cart WHERE user_id=? AND product_id=?");
    $chk->bind_param('ii', $userId, $productId);
    $chk->execute();
    $existing = $chk->get_result()->fetch_assoc();

    if ($existing) {
        $newQty = min($existing['quantity'] + $qty, $product['quantity']);
        $upd = $conn->prepare("UPDATE cart SET quantity=? WHERE id=?");
        $upd->bind_param('ii', $newQty, $existing['id']);
        $upd->execute();
    } else {
        $ins = $conn->prepare("INSERT INTO cart (user_id, product_id, quantity) VALUES (?,?,?)");
        $ins->bind_param('iii', $userId, $productId, $qty);
        $ins->execute();
    }

    echo json_encode([
        'success'    => true,
        'message'    => '"' . $product['name'] . '" added to cart!',
        'cart_count' => cartCount($conn, $userId)
    ]);

} elseif ($action === 'update') {
    $cartId = (int)($_POST['cart_id'] ?? 0);
    $qty    = max(1, (int)($_POST['qty'] ?? 1));

    $stmt = $conn->prepare("SELECT c.id, p.price, p.sale_price, p.is_on_sale, p.quantity FROM cart c JOIN products p ON c.product_id=p.id WHERE c.id=? AND c.user_id=?");
    $stmt->bind_param('ii', $cartId, $userId);
    $stmt->execute();
    $item = $stmt->get_result()->fetch_assoc();
    if (!$item) { echo json_encode(['success'=>false]); exit; }

    $qty = min($qty, $item['quantity']);
    $upd = $conn->prepare("UPDATE cart SET quantity=? WHERE id=?");
    $upd->bind_param('ii', $qty, $cartId);
    $upd->execute();

    $unitPrice = ($item['is_on_sale'] && $item['sale_price']) ? $item['sale_price'] : $item['price'];
    echo json_encode([
        'success'    => true,
        'subtotal'   => 'Rs. ' . number_format($unitPrice * $qty, 2),
        'cart_total' => cartTotal($conn, $userId),
        'cart_count' => cartCount($conn, $userId)
    ]);

} elseif ($action === 'remove') {
    $cartId = (int)($_POST['cart_id'] ?? 0);
    $stmt = $conn->prepare("DELETE FROM cart WHERE id=? AND user_id=?");
    $stmt->bind_param('ii', $cartId, $userId);
    $stmt->execute();

    echo json_encode([
        'success'    => true,
        'cart_total' => cartTotal($conn, $userId),
        'cart_count' => cartCount($conn, $userId)
    ]);

} else {
    echo json_encode(['success'=>false,'message'=>'Invalid action']);
}
