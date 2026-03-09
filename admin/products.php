<?php
$adminTitle = 'Products';
require_once 'includes/admin_header.php';

// Handle flash messages
$msg = '';
if (isset($_GET['deleted'])){ $msg = 'Product deleted successfully.'; $msgType = 'success'; }
if (isset($_GET['updated'])){ $msg = 'Product updated successfully.'; $msgType = 'success'; }

// Filter
$low = isset($_GET['low']) ? 1 : 0;
$catFilter = isset($_GET['cat']) ? (int)$_GET['cat'] : 0;

$where = ['1=1'];
if ($low) $where[] = 'p.quantity <= 5';
if ($catFilter) $where[] = "p.category_id = $catFilter";
$whereStr = implode(' AND ', $where);

$products = $conn->query("SELECT p.*, c.name as cat_name FROM products p LEFT JOIN categories c ON p.category_id = c.id WHERE $whereStr ORDER BY p.created_at DESC");
$categories = $conn->query("SELECT * FROM categories ORDER BY name")->fetch_all(MYSQLI_ASSOC);
?>

<?php if ($msg): ?>
<div class="alert alert-<?= $msgType ?? 'success' ?>"><?= sanitize($msg) ?></div>
<?php endif; ?>

<div class="admin-table-card">
    <div class="admin-table-header">
        <h3>All Products (<?= $products->num_rows ?>)</h3>
        <div class="table-actions">
            <input type="text" id="tableSearch" class="search-input" placeholder="Search products...">
            <a href="add-product.php" class="btn btn-primary btn-sm">➕ Add Product</a>
        </div>
    </div>

    <!-- Filter bar -->
    <div style="padding:12px 24px; background:var(--light-gray); display:flex; gap:10px; flex-wrap:wrap; border-bottom:1px solid var(--border);">
        <a href="products.php" class="btn btn-sm <?= !$low && !$catFilter ? 'btn-primary' : 'btn-outline' ?>">All</a>
        <a href="products.php?low=1" class="btn btn-sm <?= $low ? 'btn-danger' : 'btn-outline' ?>">⚠️ Low Stock</a>
        <?php foreach ($categories as $c): ?>
        <a href="products.php?cat=<?= $c['id'] ?>" class="btn btn-sm <?= $catFilter==$c['id'] ? 'btn-primary' : 'btn-outline' ?>"><?= sanitize($c['name']) ?></a>
        <?php endforeach; ?>
    </div>

    <div style="overflow-x:auto;">
        <table class="table">
            <thead>
                <tr>
                    <th style="width:60px;">Image</th>
                    <th>Name</th>
                    <th>Category</th>
                    <th>Price</th>
                    <th>Sale Price</th>
                    <th>Stock</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php if ($products->num_rows === 0): ?>
            <tr><td colspan="8" style="text-align:center; padding:40px; color:var(--gray);">No products found.</td></tr>
            <?php else: while ($prod = $products->fetch_assoc()):
                $imgSrc = (!empty($prod['image']) && $prod['image'] !== 'default.jpg' && file_exists(__DIR__ . '/../uploads/products/' . $prod['image'])) ? ($base . 'uploads/products/' . $prod['image']) : null;
                $stockClass = $prod['quantity'] <= 0 ? 'stock-out' : ($prod['quantity'] <= 5 ? 'stock-low' : 'stock-ok');
            ?>
            <tr>
                <td>
                    <?php if ($imgSrc): ?>
                    <img src="<?= $imgSrc ?>" class="product-thumb" alt="">
                    <?php else: ?>
                    <div class="product-thumb-placeholder">🛒</div>
                    <?php endif; ?>
                </td>
                <td>
                    <strong><?= sanitize($prod['name']) ?></strong>
                    <?php if ($prod['is_featured']): ?><span class="badge badge-hot" style="display:inline-block;margin-left:4px;">Featured</span><?php endif; ?>
                </td>
                <td><?= sanitize($prod['cat_name'] ?? '—') ?></td>
                <td>Rs. <?= number_format($prod['price'], 2) ?></td>
                <td>
                    <?php if ($prod['is_on_sale'] && $prod['sale_price']): ?>
                    <span class="badge badge-sale">Rs. <?= number_format($prod['sale_price'], 2) ?></span>
                    <?php else: ?>
                    <span style="color:var(--gray);">—</span>
                    <?php endif; ?>
                </td>
                <td><span class="<?= $stockClass ?>"><?= $prod['quantity'] ?></span></td>
                <td>
                    <?php if ($prod['quantity'] > 0): ?>
                    <span class="status-badge status-delivered">Active</span>
                    <?php else: ?>
                    <span class="status-badge status-cancelled">Out of Stock</span>
                    <?php endif; ?>
                </td>
                <td>
                    <div style="display:flex; gap:6px;">
                        <a href="edit-product.php?id=<?= $prod['id'] ?>" class="btn btn-warning btn-sm">✏️ Edit</a>
                        <a href="delete-product.php?id=<?= $prod['id'] ?>" class="btn btn-danger btn-sm confirm-delete">🗑️ Delete</a>
                    </div>
                </td>
            </tr>
            <?php endwhile; endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once 'includes/admin_footer.php'; ?>
