<?php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', 'root');      // MAMP default password
define('DB_NAME', 'mrm_grocery');

$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if ($conn->connect_error) {
    die('<div style="padding:40px;text-align:center;font-family:Arial;color:#c00;">
        <h2>Database Connection Failed</h2>
        <p>Please make sure XAMPP MySQL is running and the database <strong>mrm_grocery</strong> is imported.</p>
        <p>Error: ' . $conn->connect_error . '</p>
    </div>');
}

$conn->set_charset('utf8mb4');

// Compute site base URL dynamically (works in any subdirectory)
if (!defined('SITE_BASE')) {
    $scriptName = $_SERVER['SCRIPT_NAME'] ?? '/index.php';
    $scriptDir  = dirname($scriptName);
    // If running from admin/, go up one level
    if (basename($scriptDir) === 'admin') {
        $scriptDir = dirname($scriptDir);
    }
    define('SITE_BASE', rtrim($scriptDir, '/'));
}
