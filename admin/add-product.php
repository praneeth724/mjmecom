<?php
$adminTitle = 'Add Product';
require_once 'includes/admin_header.php';

$categories = $conn->query("SELECT * FROM categories ORDER BY name")->fetch_all(MYSQLI_ASSOC);
$error = '';
$success = '';

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
        $imageName = 'default.jpg';

        // Handle image upload
        if (!empty($_FILES['product_image']['name'])) {
            $file = $_FILES['product_image'];
            $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
            $maxSize = 5 * 1024 * 1024; // 5MB

            if (!in_array($file['type'], $allowedTypes)) {
                $error = 'Invalid image type. Allowed: JPG, PNG, GIF, WEBP.';
            } elseif ($file['size'] > $maxSize) {
                $error = 'Image too large. Max 5MB.';
            } else {
                $ext       = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
                $imageName = uniqid('prod_') . '.' . $ext;
                $uploadDir = __DIR__ . '/../uploads/products/';
                // Auto-create directory if missing
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0755, true);
                }
                if (!move_uploaded_file($file['tmp_name'], $uploadDir . $imageName)) {
                    $error = 'Upload failed. Make sure the uploads/products/ folder has write permission in MAMP.';
                    $imageName = 'default.jpg';
                }
            }
        }

        if (!$error) {
            $stmt = $conn->prepare("INSERT INTO products (name, description, price, sale_price, quantity, category_id, image, is_featured, is_on_sale) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param('ssddiisii', $name, $description, $price, $salePrice, $quantity, $categoryId, $imageName, $isFeatured, $isOnSale);
            if ($stmt->execute()) {
                header('Location: products.php?updated=1');
                exit;
            } else {
                $error = 'Failed to save product.';
            }
        }
    }
}
?>

<div style="margin-bottom:20px; display:flex; align-items:center; justify-content:space-between;">
    <a href="products.php" class="btn btn-outline btn-sm">← Back to Products</a>
</div>

<?php if ($error): ?>
<div class="alert alert-error">❌ <?= sanitize($error) ?></div>
<?php endif; ?>

<form method="POST" action="add-product.php" enctype="multipart/form-data">
    <div style="display:grid; grid-template-columns:1fr 320px; gap:24px; align-items:start;">
        <div>
            <div class="admin-form-card" style="margin-bottom:20px;">
                <div class="admin-form-header"><h3>Product Information</h3></div>
                <div class="admin-form-body">
                    <div class="form-group">
                        <label>Product Name *</label>
                        <input type="text" name="name" class="form-control" required
                               value="<?= sanitize($_POST['name'] ?? '') ?>"
                               placeholder="e.g. Fresh Tomatoes (1kg)">
                    </div>
                    <div class="form-group">
                        <label>Description</label>
                        <textarea name="description" class="form-control" rows="4"
                                  placeholder="Describe the product..."><?= sanitize($_POST['description'] ?? '') ?></textarea>
                    </div>
                    <div class="form-row three">
                        <div class="form-group">
                            <label>Price (Rs.) *</label>
                            <input type="number" name="price" class="form-control" step="0.01" min="0" required
                                   value="<?= sanitize($_POST['price'] ?? '') ?>" placeholder="0.00">
                        </div>
                        <div class="form-group" id="salePriceWrap">
                            <label>Sale Price (Rs.)</label>
                            <input type="number" name="sale_price" class="form-control" step="0.01" min="0"
                                   value="<?= sanitize($_POST['sale_price'] ?? '') ?>" placeholder="Leave empty if no sale">
                        </div>
                        <div class="form-group">
                            <label>Quantity (Stock) *</label>
                            <input type="number" name="quantity" class="form-control" min="0" required
                                   value="<?= sanitize($_POST['quantity'] ?? '0') ?>">
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Category</label>
                        <select name="category_id" class="form-control">
                            <option value="">-- Select Category --</option>
                            <?php foreach ($categories as $c): ?>
                            <option value="<?= $c['id'] ?>" <?= ($_POST['category_id'] ?? '') == $c['id'] ? 'selected' : '' ?>>
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
                    <div class="form-group">
                        <label>Upload Image</label>
                        <input type="file" name="product_image" id="product_image" class="form-control"
                               accept="image/jpeg,image/png,image/gif,image/webp">
                        <small style="color:var(--gray); display:block; margin-top:4px;">Max 5MB. JPG, PNG, GIF, WEBP</small>
                    </div>
                    <div class="img-preview" id="imgPreview">
                        <span class="placeholder">🖼️</span>
                    </div>
                </div>
            </div>

            <div class="admin-form-card" style="margin-bottom:20px;">
                <div class="admin-form-header"><h3>Product Options</h3></div>
                <div class="admin-form-body">
                    <div class="form-group">
                        <label class="toggle-check">
                            <input type="checkbox" name="is_on_sale" id="is_on_sale" value="1" <?= isset($_POST['is_on_sale']) ? 'checked' : '' ?>>
                            <span>🔥 On Sale (Enable Sale Price)</span>
                        </label>
                    </div>
                    <div class="form-group">
                        <label class="toggle-check">
                            <input type="checkbox" name="is_featured" value="1" <?= isset($_POST['is_featured']) ? 'checked' : '' ?>>
                            <span>⭐ Featured on Homepage</span>
                        </label>
                    </div>
                </div>
            </div>

            <button type="submit" class="btn btn-primary btn-full btn-lg">
                ✅ Save Product
            </button>
        </div>
    </div>
</form>

<?php require_once 'includes/admin_footer.php'; ?>
