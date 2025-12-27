<?php
$host = 'localhost'; $user = 'root'; $pass = '';
try {
    $pdo = new PDO("mysql:host=$host", $user, $pass);
    $pdo->exec("CREATE DATABASE IF NOT EXISTS inventory_db");
    $pdo->exec("USE inventory_db");

    // Tables
    $sql = "
    CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(50) UNIQUE,
        password VARCHAR(255),
        role ENUM('admin', 'staff', 'viewer')
    );
    CREATE TABLE IF NOT EXISTS settings (
        setting_key VARCHAR(50) PRIMARY KEY,
        setting_value TEXT
    );
    CREATE TABLE IF NOT EXISTS products (
        id INT AUTO_INCREMENT PRIMARY KEY,
        p_type VARCHAR(100),
        p_name VARCHAR(100),
        p_unit VARCHAR(20),
        UNIQUE KEY(p_type, p_name)
    );
    CREATE TABLE IF NOT EXISTS transactions (
        id INT AUTO_INCREMENT PRIMARY KEY,
        entry_date DATE,
        txn_type ENUM('IN', 'OUT'),
        product_id INT,
        reason VARCHAR(100),
        from_to VARCHAR(100),
        section VARCHAR(100),
        slip_no VARCHAR(50),
        location VARCHAR(100),
        quantity DECIMAL(10,2),
        unit_value DECIMAL(10,2),
        total_value DECIMAL(12,2),
        mfg_date DATE,
        exp_date DATE,
        px_condition VARCHAR(50),
        remarks TEXT,
        handled_by VARCHAR(100),
        user_id INT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    );
    ";
    $pdo->exec($sql);
    
    // Default Admin
    $pass = password_hash('admin123', PASSWORD_DEFAULT);
    $pdo->exec("INSERT IGNORE INTO users (username, password, role) VALUES ('admin', '$pass', 'admin')");
    
    echo "Setup Complete. User: admin / Pass: admin123";
} catch (PDOException $e) { echo $e->getMessage(); }
?>