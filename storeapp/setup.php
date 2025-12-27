<?php
// setup.php
$setup_password = "admin"; // CHANGE THIS

if (!isset($_POST['pass']) || $_POST['pass'] !== $setup_password) {
    die('<form method="POST">Password: <input type="password" name="pass"><button>Install</button></form>');
}

$host = 'localhost';
$user = 'root';
$pass = '';

try {
    $pdo = new PDO("mysql:host=$host", $user, $pass);
    $pdo->exec("CREATE DATABASE IF NOT EXISTS pos_db");
    $pdo->exec("USE pos_db");
    
    // 1. Users & Shops
    $pdo->exec("CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(50) UNIQUE,
        password VARCHAR(255),
        role ENUM('superadmin','user') DEFAULT 'user',
        is_active TINYINT DEFAULT 1
    )");

    $pdo->exec("CREATE TABLE IF NOT EXISTS shops (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100)
    )");

    // 2. Permissions (Granular per shop)
    $pdo->exec("CREATE TABLE IF NOT EXISTS user_shop_access (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT,
        shop_id INT,
        permissions JSON, 
        FOREIGN KEY (user_id) REFERENCES users(id),
        FOREIGN KEY (shop_id) REFERENCES shops(id)
    )");

    // 3. Transactions (The core table)
    $pdo->exec("CREATE TABLE IF NOT EXISTS transactions (
        id INT AUTO_INCREMENT PRIMARY KEY,
        shop_id INT,
        entry_date DATETIME DEFAULT CURRENT_TIMESTAMP,
        actual_date DATE,
        prod_type VARCHAR(100),
        prod_name VARCHAR(100),
        unit VARCHAR(50),
        party_name VARCHAR(100),
        qty_in DECIMAL(10,2) DEFAULT 0,
        qty_out DECIMAL(10,2) DEFAULT 0,
        reason VARCHAR(50), 
        handled_by VARCHAR(50),
        slip_no VARCHAR(50),
        location VARCHAR(100),
        rate DECIMAL(10,2),
        total_price DECIMAL(10,2),
        extra_charge DECIMAL(10,2) DEFAULT 0,
        condition_text TEXT,
        remarks TEXT,
        sync_id VARCHAR(100) UNIQUE COMMENT 'For offline sync dedup'
    )");

    // Demo Data
    $passHash = password_hash('123456', PASSWORD_DEFAULT);
    $pdo->exec("INSERT IGNORE INTO users (username, password, role) VALUES ('admin', '$passHash', 'superadmin')");
    $pdo->exec("INSERT IGNORE INTO shops (name) VALUES ('Main Warehouse'), ('Retail Outlet 1')");
    
    echo "Database initialized successfully! Default User: admin / 123456";

} catch (PDOException $e) {
    die("DB Error: " . $e->getMessage());
}
?>