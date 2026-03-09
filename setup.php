<?php
// MRM Grocery - Quick Setup Checker
// Run this once to verify your installation
// Delete this file after setup is complete

$checks = [];

// Check PHP version
$checks['PHP Version (>=7.4)'] = version_compare(PHP_VERSION, '7.4.0', '>=');

// Check extensions
$checks['MySQLi Extension'] = extension_loaded('mysqli');
$checks['GD Extension (images)'] = extension_loaded('gd');
$checks['File uploads enabled'] = ini_get('file_uploads');

// Check uploads directory
$uploadDir = __DIR__ . '/uploads/products/';
$checks['Uploads directory exists'] = is_dir($uploadDir);
$checks['Uploads directory writable'] = is_writable($uploadDir);

// Check database
$dbOk = false;
$dbMsg = '';
try {
    $conn = new mysqli('localhost', 'root', 'root', 'mrm_grocery'); // MAMP default
    if ($conn->connect_error) {
        $dbMsg = $conn->connect_error;
    } else {
        $dbOk = true;
        // Check tables
        $tables = ['users','products','categories','cart','orders','order_items'];
        $missing = [];
        foreach ($tables as $t) {
            $r = $conn->query("SHOW TABLES LIKE '$t'");
            if ($r->num_rows === 0) $missing[] = $t;
        }
        if (!empty($missing)) {
            $dbMsg = 'Missing tables: ' . implode(', ', $missing) . '. Please import database/mrm_grocery.sql';
            $dbOk = false;
        } else {
            $adminCount = $conn->query("SELECT COUNT(*) FROM users WHERE role='admin'")->fetch_row()[0];
            $dbMsg = "Connected! Admin accounts: $adminCount, " .
                     "Products: " . $conn->query("SELECT COUNT(*) FROM products")->fetch_row()[0];
        }
        $conn->close();
    }
} catch (Exception $e) {
    $dbMsg = $e->getMessage();
}
// For MAMP the default password is 'root'
$checks['Database Connection'] = $dbOk;

$allOk = !in_array(false, $checks);
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>MRM Grocery - Setup Check</title>
    <style>
        body { font-family: Segoe UI, sans-serif; background:#f4f6f4; margin:0; padding:40px 20px; }
        .card { background:white; max-width:600px; margin:0 auto; border-radius:12px; box-shadow:0 4px 20px rgba(0,0,0,0.1); overflow:hidden; }
        .header { background:#1b6b3a; color:white; padding:28px 32px; }
        .header h1 { margin:0; font-size:22px; }
        .header p { margin:6px 0 0; opacity:0.85; font-size:14px; }
        .body { padding:28px 32px; }
        .check { display:flex; justify-content:space-between; align-items:center; padding:10px 0; border-bottom:1px solid #eee; font-size:14px; }
        .ok { color:#28a745; font-weight:700; }
        .fail { color:#dc3545; font-weight:700; }
        .db-msg { font-size:13px; color:#666; padding:8px 12px; background:#f8f9fa; border-radius:6px; margin:12px 0; }
        .success-box { background:#d4edda; border:1px solid #c3e6cb; color:#155724; padding:16px; border-radius:8px; margin-top:20px; }
        .error-box { background:#f8d7da; border:1px solid #f5c6cb; color:#721c24; padding:16px; border-radius:8px; margin-top:20px; }
        .step { background:#e8f5e9; border-left:4px solid #1b6b3a; padding:14px 16px; border-radius:0 8px 8px 0; margin:10px 0; font-size:14px; }
        h3 { color:#1b6b3a; margin:24px 0 12px; }
        .btn { display:inline-block; background:#1b6b3a; color:white; padding:11px 24px; border-radius:8px; text-decoration:none; font-weight:600; margin-top:16px; }
    </style>
</head>
<body>
<div class="card">
    <div class="header">
        <h1>🛒 MRM Grocery — Setup Check</h1>
        <p>Verifying your installation...</p>
    </div>
    <div class="body">
        <h3>System Checks</h3>
        <?php foreach ($checks as $label => $ok): ?>
        <div class="check">
            <span><?= $label ?></span>
            <span class="<?= $ok ? 'ok' : 'fail' ?>"><?= $ok ? '✅ OK' : '❌ FAIL' ?></span>
        </div>
        <?php endforeach; ?>

        <div class="db-msg">Database: <?= htmlspecialchars($dbMsg) ?></div>

        <?php if ($allOk): ?>
        <div class="success-box">
            <strong>✅ All checks passed!</strong><br>
            Your MRM Grocery site is ready to use.<br><br>
            <strong>Admin Login:</strong> admin@mrm.com / admin123<br>
            <strong>User Login:</strong> kamal@example.com / user123<br><br>
            ⚠️ <strong>Delete this file (setup.php) after setup!</strong>
        </div>
        <a href="index.php" class="btn">🏠 Go to Homepage</a>
        <a href="admin/index.php" class="btn" style="margin-left:8px; background:#e63946;">⚙️ Admin Panel</a>
        <?php else: ?>
        <div class="error-box">
            <strong>❌ Some checks failed.</strong> Follow the steps below to fix them.
        </div>
        <?php endif; ?>

        <h3>Setup Instructions (MAMP)</h3>
        <div class="step">1. Open <strong>MAMP</strong> → click <strong>Start Servers</strong> (Apache + MySQL green).</div>
        <div class="step">2. Open phpMyAdmin: <code>http://localhost/phpMyAdmin/</code> (or via MAMP → Open WebStart page → phpMyAdmin).</div>
        <div class="step">3. In phpMyAdmin → create database: <code>mrm_grocery</code> → Import <code>database/mrm_grocery.sql</code>.</div>
        <div class="step">4. Place this project folder in: <code>C:\MAMP\htdocs\ecomgura\</code></div>
        <div class="step">5. Visit: <code>http://localhost/ecomgura/</code><br>
            If MAMP uses port 8888: <code>http://localhost:8888/ecomgura/</code></div>
        <div class="step">6. Make sure <code>uploads/products/</code> is writable (right-click → Properties → Security → allow write).</div>
        <div class="step">7. <strong>Delete setup.php</strong> after everything is working!</div>
    </div>
</div>
</body>
</html>
