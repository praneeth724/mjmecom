<?php
$pageTitle = 'Products';
require_once 'includes/db.php';
require_once 'includes/auth.php';

// Filters
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$category = isset($_GET['category']) ? (int)$_GET['category'] : 0;
$sale = isset($_GET['sale']) ? 1 : 0;
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'newest';
$page = max(1, (int)($_GET['page'] ?? 1));
$perPage = 12;
$offset = ($page - 1) * $perPage;

// Build query
$where = ['p.quantity >= 0'];
$params = [];
$types = '';

if ($search !== '') {
    $where[] = '(p.name LIKE ? OR p.description LIKE ?)';
    $params[] = "%$search%";
    $params[] = "%$search%";
    $types .= 'ss';
}
if ($category > 0) {
    $where[] = 'p.category_id = ?';
    $params[] = $category;
    $types .= 'i';
}
if ($sale) {
    $where[] = 'p.is_on_sale = 1';
}

$whereStr = implode(' AND ', $where);

$orderMap = [
    'newest' => 'p.created_at DESC',
    'price_asc' => 'COALESCE(NULLIF(p.sale_price,0)*p.is_on_sale, p.price) ASC',
    'price_desc' => 'COALESCE(NULLIF(p.sale_price,0)*p.is_on_sale, p.price) DESC',
    'name_asc' => 'p.name ASC',
];
$orderBy = $orderMap[$sort] ?? 'p.created_at DESC';

// Count total
$countSql = "SELECT COUNT(*) FROM products p LEFT JOIN categories c ON p.category_id = c.id WHERE $whereStr";
$stmt = $conn->prepare($countSql);
if (!empty($params)) $stmt->bind_param($types, ...$params);
$stmt->execute();
$totalProducts = $stmt->get_result()->fetch_row()[0];
$totalPages = max(1, ceil($totalProducts / $perPage));

// Fetch products
$sql = "SELECT p.*, c.name as cat_name FROM products p LEFT JOIN categories c ON p.category_id = c.id WHERE $whereStr ORDER BY $orderBy LIMIT ? OFFSET ?";
$stmt2 = $conn->prepare($sql);
$allTypes = $types . 'ii';
$allParams = array_merge($params, [$perPage, $offset]);
$stmt2->bind_param($allTypes, ...$allParams);
$stmt2->execute();
$products = $stmt2->get_result();

// Categories for filter
$cats = $conn->query("SELECT c.*, COUNT(p.id) as cnt FROM categories c LEFT JOIN products p ON c.id = p.category_id GROUP BY c.id ORDER BY c.name");
$catList = $cats->fetch_all(MYSQLI_ASSOC);

require_once 'includes/header.php';
?>

<!-- PAGE HEADER -->
<div class="page-header">
    <div class="container">
        <h1><?= $sale ? '🔥 Mega Sale' : ($search ? 'Search: ' . sanitize($search) : 'All Products') ?></h1>
        <div class="breadcrumb">
            <a href="index.php">Home</a>
            <span>/</span>
            <span><?= $sale ? 'Mega Sale' : 'Products' ?></span>
        </div>
    </div>
</div>

<div class="products-page">
    <div class="container">
        <?php if ($sale): ?>
        <div class="mega-sale-banner" style="border-radius:12px; margin-bottom:30px; padding:30px 0;">
            <div class="container">
                <div style="text-align:center; color:white;">
                    <h2 style="font-size:32px; font-weight:800;">🔥 MEGA SALE — Up to 30% OFF!</h2>
                    <p style="opacity:0.9; margin-top:8px;">Limited time offers on selected items. Shop now before stock runs out!</p>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <div class="products-layout">
            <!-- SIDEBAR FILTER -->
            <aside class="filter-sidebar">
                <h3 style="font-size:16px; font-weight:700; margin-bottom:18px; padding-bottom:10px; border-bottom:2px solid var(--border);">🔍 Filter</h3>
                <form method="GET" action="products.php">
                    <?php if ($search): ?><input type="hidden" name="search" value="<?= sanitize($search) ?>"><?php endif; ?>
                    <?php if ($sale): ?><input type="hidden" name="sale" value="1"><?php endif; ?>
                    <div class="filter-group">
                        <h4>Category</h4>
                        <label>
                            <input type="radio" name="category" value="0" <?= $category === 0 ? 'checked' : '' ?> onchange="this.form.submit()"> All Categories
                        </label>
                        <?php foreach ($catList as $c): ?>
                        <label>
                            <input type="radio" name="category" value="<?= $c['id'] ?>" <?= $category === (int)$c['id'] ? 'checked' : '' ?> onchange="this.form.submit()">
                            <?= sanitize($c['name']) ?>
                            <span style="margin-left:auto; color:var(--gray); font-size:12px;">(<?= $c['cnt'] ?>)</span>
                        </label>
                        <?php endforeach; ?>
                    </div>
                    <div class="filter-group">
                        <h4>Availability</h4>
                        <label>
                            <input type="checkbox" name="sale" value="1" <?= $sale ? 'checked' : '' ?> onchange="this.form.submit()">
                            On Sale Only 🔥
                        </label>
                    </div>
                    <div class="filter-group">
                        <h4>Sort By</h4>
                        <select name="sort" onchange="this.form.submit()">
                            <option value="newest" <?= $sort==='newest'?'selected':'' ?>>Newest First</option>
                            <option value="price_asc" <?= $sort==='price_asc'?'selected':'' ?>>Price: Low to High</option>
                            <option value="price_desc" <?= $sort==='price_desc'?'selected':'' ?>>Price: High to Low</option>
                            <option value="name_asc" <?= $sort==='name_asc'?'selected':'' ?>>Name A-Z</option>
                        </select>
                    </div>
                    <?php if ($search || $category || $sale): ?>
                    <a href="products.php" class="btn btn-outline btn-sm btn-full" style="margin-top:8px;">✕ Clear Filters</a>
                    <?php endif; ?>
                </form>
            </aside>

            <!-- PRODUCTS MAIN -->
            <div class="products-main">
                <div class="products-toolbar">
                    <div class="results-count">
                        Showing <?= min($totalProducts, $offset + 1) ?>–<?= min($totalProducts, $offset + $perPage) ?> of <strong><?= $totalProducts ?></strong> products
                    </div>
                    <form method="GET" style="display:flex; gap:8px; align-items:center;">
                        <?php if ($search): ?><input type="hidden" name="search" value="<?= sanitize($search) ?>"><?php endif; ?>
                        <?php if ($category): ?><input type="hidden" name="category" value="<?= $category ?>"><?php endif; ?>
                        <?php if ($sale): ?><input type="hidden" name="sale" value="1"><?php endif; ?>
                        <select name="sort" class="sort-select" onchange="this.form.submit()">
                            <option value="newest" <?= $sort==='newest'?'selected':'' ?>>Newest</option>
                            <option value="price_asc" <?= $sort==='price_asc'?'selected':'' ?>>Price ↑</option>
                            <option value="price_desc" <?= $sort==='price_desc'?'selected':'' ?>>Price ↓</option>
                            <option value="name_asc" <?= $sort==='name_asc'?'selected':'' ?>>A-Z</option>
                        </select>
                    </form>
                </div>

                <?php if ($products->num_rows === 0): ?>
                <div style="text-align:center; padding:60px 20px; background:white; border-radius:12px; box-shadow:var(--shadow);">
                    <div style="font-size:64px; margin-bottom:16px;">🔍</div>
                    <h3 style="color:var(--gray); margin-bottom:8px;">No Products Found</h3>
                    <p style="color:var(--gray); font-size:14px;">Try adjusting your filters or search term.</p>
                    <a href="products.php" class="btn btn-primary" style="margin-top:16px;">View All Products</a>
                </div>
                <?php else: ?>
                <div class="products-grid">
                    <?php while ($p = $products->fetch_assoc()): ?>
                    <?php include 'includes/product_card.php'; ?>
                    <?php endwhile; ?>
                </div>

                <!-- PAGINATION -->
                <?php if ($totalPages > 1): ?>
                <div class="pagination">
                    <?php
                    $qParams = array_filter(['search'=>$search,'category'=>$category?:null,'sale'=>$sale?:null,'sort'=>$sort]);
                    $qStr = http_build_query($qParams);
                    $qStr = $qStr ? '&' . $qStr : '';
                    ?>
                    <?php if ($page > 1): ?>
                    <a href="?page=<?= $page-1 ?><?= $qStr ?>" class="page-link">‹</a>
                    <?php endif; ?>
                    <?php for ($i = max(1,$page-2); $i <= min($totalPages,$page+2); $i++): ?>
                    <a href="?page=<?= $i ?><?= $qStr ?>" class="page-link <?= $i===$page?'active':'' ?>"><?= $i ?></a>
                    <?php endfor; ?>
                    <?php if ($page < $totalPages): ?>
                    <a href="?page=<?= $page+1 ?><?= $qStr ?>" class="page-link">›</a>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
