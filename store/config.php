<?php
// config.php
session_start();
$host = 'localhost';
if (strpos($_SERVER['HTTP_HOST'], 'localhost') !== false || strpos($_SERVER['HTTP_HOST'], 'free.app') !== false) {
    $db   = 'inventory_db';
    $user = 'root';
    $pass = '';
} else {
    $db   = 'u312077073_inventory_db';
    $user = 'u312077073_inventory_db';
    $pass = 'KR5877kush';
}
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$opt = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO("mysql:host=$host;charset=$charset", $user, $pass, $opt);
    // Select DB specifically if created manually, otherwise setup.php handles it
    $pdo->exec("USE $db"); 
} catch (\PDOException $e) {
    // If DB doesn't exist, we might be in setup mode
}

// Helper: Check Auth
function checkAuth($allowed_roles = []) {
    if (!isset($_SESSION['user_id'])) {
        header("Location: login.php");
        exit;
    }
    if (!empty($allowed_roles) && !in_array($_SESSION['role'], $allowed_roles)) {
        die("Access Denied");
    }
}
?>