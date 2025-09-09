<?php
include_once 'config.php';

// Create users table if not exists
$conn->query("CREATE TABLE IF NOT EXISTS users (
    id INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(10) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role BOOLEAN DEFAULT 0,
    blocked BOOLEAN DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    created_by INT(11) UNSIGNED DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");


// Insert default admin user if table is empty
$res = $conn->query("SELECT COUNT(*) as count FROM users");
if ($res) {
    $row = $res->fetch_assoc();
    if ($row && isset($row['count']) && $row['count'] == 0) {
        $default_user = 'admin';
        $default_pass = password_hash('admin', PASSWORD_DEFAULT);
        $stmt = $conn->prepare("INSERT INTO users (username, password, role) VALUES (?, ?, 0)");
        if ($stmt) {
            $stmt->bind_param("ss", $default_user, $default_pass);
            $stmt->execute();
            $stmt->close();
        }
    }
}


// Create settings table if not exists
$conn->query("CREATE TABLE IF NOT EXISTS settings (
    id INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    companyname VARCHAR(255) NOT NULL,
    address VARCHAR(255) NOT NULL,
    photopath VARCHAR(255) NOT NULL,
    bannerpath VARCHAR(255) NOT NULL,
    phone VARCHAR(20) NOT NULL,
    theme BOOLEAN DEFAULT 0,
    language BOOLEAN DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");


// Insert demo data if settings table is empty
$res = $conn->query("SELECT COUNT(*) as count FROM settings");
if ($res) {
    $row = $res->fetch_assoc();
    if ($row && isset($row['count']) && $row['count'] == 0) {
        $stmt = $conn->prepare("INSERT INTO settings (companyname, address, photopath, bannerpath, phone, theme, language) VALUES ('My Company', '123 Main St, Anytown, USA', 'assets/images/logo.png', 'assets/images/banner.jpg', '123-456-7890', 'light', 'en')");
        if ($stmt) {
            $stmt->execute();
            $stmt->close();
        }
    }
}
