<?php
// qc/setup.php
include_once 'config.php';

// Create users table if not exists
$conn->query("CREATE TABLE IF NOT EXISTS users (
    id INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    status ENUM('active', 'inactive') DEFAULT 'active',
 
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

// Insert default admin user if table is empty
$res = $conn->query("SELECT COUNT(*) as count FROM users");
if ($res) {
    $row = $res->fetch_assoc();
    if ($row && isset($row['count']) && $row['count'] == 0) {
        $default_user = 'admin';
        $default_pass = password_hash('admin123', PASSWORD_DEFAULT);
        $stmt = $conn->prepare("INSERT INTO users (username, password) VALUES (?, ?)");
        if ($stmt) {
            $stmt->bind_param("ss", $default_user, $default_pass);
            $stmt->execute();
            $stmt->close();
        }
    }
}

// Create products table if not exists
$conn->query("CREATE TABLE IF NOT EXISTS products (
    id INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    tp_rate DECIMAL(10,2) NOT NULL,
    dp_rate DECIMAL(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

?>