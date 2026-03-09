<?php
$adminTitle = 'Categories';
require_once 'includes/admin_header.php';

$msg = '';
$msgType = 'success';

// Add category
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_cat'])) {
    $catName = trim($_POST['cat_name'] ?? '');
    if ($catName) {
        $check = $conn->prepare("SELECT id FROM categories WHERE name = ?");
        $check->bind_param('s', $catName);
        $check->execute();
        if ($check->get_result()->num_rows > 0) {
            $msg = 'Category already exists.'; $msgType = 'error';
        } else {
            $ins = $conn->prepare("INSERT INTO categories (name) VALUES (?)");
            $ins->bind_param('s', $catName);
            $ins->execute();
            $msg = "Category '$catName' added!";
        }
    } else {
        $msg = 'Category name is required.'; $msgType = 'error';
    }
}

// Delete category
if (isset($_GET['delete'])) {
    $cid = (int)$_GET['delete'];
    // Check if has products
    $check = $conn->query("SELECT COUNT(*) FROM products WHERE category_id = $cid")->fetch_row()[0];
    if ($check > 0) {
        $msg = "Cannot delete — $check products use this category."; $msgType = 'error';
    } else {
        $conn->query("DELETE FROM categories WHERE id = $cid");
        $msg = 'Category deleted.';
    }
}

$categories = $conn->query("SELECT c.*, COUNT(p.id) as product_count FROM categories c LEFT JOIN products p ON c.id = p.category_id GROUP BY c.id ORDER BY c.name");
?>

<?php if ($msg): ?>
<div class="alert alert-<?= $msgType ?>"><?= sanitize($msg) ?></div>
<?php endif; ?>

<div style="display:grid; grid-template-columns:1fr 320px; gap:24px; align-items:start;">
    <div class="admin-table-card">
        <div class="admin-table-header"><h3>All Categories</h3></div>
        <table class="table">
            <thead><tr><th>ID</th><th>Name</th><th>Products</th><th>Action</th></tr></thead>
            <tbody>
            <?php while ($c = $categories->fetch_assoc()): ?>
            <tr>
                <td><?= $c['id'] ?></td>
                <td><strong><?= sanitize($c['name']) ?></strong></td>
                <td><?= $c['product_count'] ?></td>
                <td>
                    <a href="categories.php?delete=<?= $c['id'] ?>" class="btn btn-danger btn-sm confirm-delete">🗑️ Delete</a>
                </td>
            </tr>
            <?php endwhile; ?>
            </tbody>
        </table>
    </div>

    <div class="admin-form-card">
        <div class="admin-form-header"><h3>Add New Category</h3></div>
        <div class="admin-form-body">
            <form method="POST" action="categories.php">
                <div class="form-group">
                    <label>Category Name *</label>
                    <input type="text" name="cat_name" class="form-control" required placeholder="e.g. Spices">
                </div>
                <button type="submit" name="add_cat" class="btn btn-primary btn-full">➕ Add Category</button>
            </form>
        </div>
    </div>
</div>

<?php require_once 'includes/admin_footer.php'; ?>
