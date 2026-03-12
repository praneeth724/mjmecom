<?php
$adminTitle = 'Edit Product';
require_once 'includes/admin_header.php';

$id = (int)($_GET['id'] ?? 0);
if (!$id) { header('Location: products.php'); exit; }

$prodStmt = $conn->prepare("SELECT * FROM products WHERE id = ?");
$prodStmt->bind_param('i', $id);
$prodStmt->execute();
$prod = $prodStmt->get_result()->fetch_assoc();
if (!$prod) { header('Location: products.php'); exit; }

$categories = $conn->query("SELECT * FROM categories ORDER BY name")->fetch_all(MYSQLI_ASSOC);
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name        = trim($_POST['name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $price       = (float)($_POST['price'] ?? 0);
    $salePrice   = !empty($_POST['sale_price']) ? (float)$_POST['sale_price'] : null;
    $quantity    = (int)($_POST['quantity'] ?? 0);
    $categoryId  = (int)($_POST['category_id'] ?? 0) ?: null;
    $isFeatured  = isset($_POST['is_featured']) ? 1 : 0;
    $isOnSale    = isset($_POST['is_on_sale']) ? 1 : 0;

    if (!$name || $price <= 0) {
        $error = 'Product name and price are required.';
    } else {
        $imageName = $prod['image']; // Keep existing image by default

        if (!empty($_FILES['product_image']['name'])) {
            $file = $_FILES['product_image'];
            $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
            $maxSize = 5 * 1024 * 1024;

            if (!in_array($file['type'], $allowedTypes)) {
                $error = 'Invalid image type.';
            } elseif ($file['size'] > $maxSize) {
                $error = 'Image too large. Max 5MB.';
            } else {
                $ext          = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
                $newImageName = uniqid('prod_') . '.' . $ext;
                $uploadDir    = __DIR__ . '/../uploads/products/';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0755, true);
                }
                if (move_uploaded_file($file['tmp_name'], $uploadDir . $newImageName)) {
                    // Delete old image if it's not default
                    if ($imageName !== 'default.jpg' && file_exists($uploadDir . $imageName)) {
                        @unlink($uploadDir . $imageName);
                    }
                    $imageName = $newImageName;
                } else {
                    $error = 'Failed to upload image.';
                }
            }
        }

        if (!$error) {
            $stmt = $conn->prepare("UPDATE products SET name=?, description=?, price=?, sale_price=?, quantity=?, category_id=?, image=?, is_featured=?, is_on_sale=? WHERE id=?");
            $stmt->bind_param('ssddiisiii', $name, $description, $price, $salePrice, $quantity, $categoryId, $imageName, $isFeatured, $isOnSale, $id);
            if ($stmt->execute()) {
                header('Location: products.php?updated=1');
                exit;
            } else {
                $error = 'Failed to update product.';
            }
        }
    }
    // Reload prod data for display
    if ($error) {
        $prod['name'] = $_POST['name'] ?? $prod['name'];
        $prod['description'] = $_POST['description'] ?? $prod['description'];
        $prod['price'] = $_POST['price'] ?? $prod['price'];
        $prod['sale_price'] = $_POST['sale_price'] ?? $prod['sale_price'];
        $prod['quantity'] = $_POST['quantity'] ?? $prod['quantity'];
        $prod['category_id'] = $_POST['category_id'] ?? $prod['category_id'];
        $prod['is_featured'] = isset($_POST['is_featured']) ? 1 : 0;
        $prod['is_on_sale'] = isset($_POST['is_on_sale']) ? 1 : 0;
    }
}

$existingImg = (!empty($prod['image']) && $prod['image'] !== 'default.jpg' && file_exists(__DIR__ . '/../uploads/products/' . $prod['image'])) ? ($base . 'uploads/products/' . $prod['image']) : null;
?>

<div style="margin-bottom:20px; display:flex; align-items:center; justify-content:space-between;">
    <a href="products.php" class="btn btn-outline btn-sm">← Back to Products</a>
    <a href="delete-product.php?id=<?= $id ?>" class="btn btn-danger btn-sm confirm-delete">🗑️ Delete Product</a>
</div>

<?php if ($error): ?>
<div class="alert alert-error">❌ <?= sanitize($error) ?></div>
<?php endif; ?>

<form method="POST" action="edit-product.php?id=<?= $id ?>" enctype="multipart/form-data">
    <div style="display:grid; grid-template-columns:1fr 320px; gap:24px; align-items:start;">
        <div>
            <div class="admin-form-card" style="margin-bottom:20px;">
                <div class="admin-form-header"><h3>Edit: <?= sanitize($prod['name']) ?></h3></div>
                <div class="admin-form-body">
                    <div class="form-group">
                        <label>Product Name *</label>
                        <input type="text" name="name" class="form-control" required value="<?= sanitize($prod['name']) ?>">
                    </div>
                    <div class="form-group">
                        <label>Description</label>
                        <textarea name="description" class="form-control" rows="4"><?= sanitize($prod['description'] ?? '') ?></textarea>
                    </div>
                    <div class="form-row three">
                        <div class="form-group">
                            <label>Price (Rs.) *</label>
                            <input type="number" name="price" class="form-control" step="0.01" min="0" required value="<?= $prod['price'] ?>">
                        </div>
                        <div class="form-group" id="salePriceWrap">
                            <label>Sale Price (Rs.)</label>
                            <input type="number" name="sale_price" class="form-control" step="0.01" min="0" value="<?= $prod['sale_price'] ?? '' ?>">
                        </div>
                        <div class="form-group">
                            <label>Quantity (Stock)</label>
                            <input type="number" name="quantity" class="form-control" min="0" required value="<?= $prod['quantity'] ?>">
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Category</label>
                        <select name="category_id" class="form-control">
                            <option value="">-- Select Category --</option>
                            <?php foreach ($categories as $c): ?>
                            <option value="<?= $c['id'] ?>" <?= $prod['category_id'] == $c['id'] ? 'selected' : '' ?>>
                                <?= sanitize($c['name']) ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <div>
            <div class="admin-form-card" style="margin-bottom:20px;">
                <div class="admin-form-header"><h3>Product Image</h3></div>
                <div class="admin-form-body">
                    <div class="img-preview" id="imgPreview">
                        <?php if ($existingImg): ?>
                        <img src="<?= $existingImg ?>" alt="Current product image">
                        <?php else: ?>
                        <span class="placeholder">🖼️</span>
                        <?php endif; ?>
                    </div>
                    <div class="form-group" style="margin-top:10px;">
                        <label>Change Image</label>
                        <input type="file" name="product_image" id="product_image" class="form-control"
                               accept="image/jpeg,image/png,image/gif,image/webp">
                        <small style="color:var(--gray); display:block; margin-top:4px;">Leave empty to keep current image</small>
                    </div>
                </div>
            </div>

            <div class="admin-form-card" style="margin-bottom:20px;">
                <div class="admin-form-header"><h3>Product Options</h3></div>
                <div class="admin-form-body">
                    <div class="form-group">
                        <label class="toggle-check">
                            <input type="checkbox" name="is_on_sale" id="is_on_sale" value="1" <?= $prod['is_on_sale'] ? 'checked' : '' ?>>
                            <span>🔥 On Sale (Enable Sale Price)</span>
                        </label>
                    </div>
                    <div class="form-group">
                        <label class="toggle-check">
                            <input type="checkbox" name="is_featured" value="1" <?= $prod['is_featured'] ? 'checked' : '' ?>>
                            <span>⭐ Featured on Homepage</span>
                        </label>
                    </div>
                </div>
            </div>

            <button type="submit" class="btn btn-primary btn-full btn-lg">💾 Update Product</button>
        </div>
    </div>
</form>

<?php require_once 'includes/admin_footer.php'; ?>
