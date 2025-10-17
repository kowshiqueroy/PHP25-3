<?php
include_once 'config.php';

// Create users table if not exists
$conn->query("CREATE TABLE IF NOT EXISTS users (
    id INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    status INT(1) NOT NULL DEFAULT 1,
    cash_in INT NOT NULL DEFAULT 0,
    cash_out INT NOT NULL DEFAULT 0,
    balance INT NOT NULL DEFAULT 0,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    created_by INT(11) UNSIGNED NOT NULL DEFAULT 1,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

// Create default user if table is empty
$res = $conn->query("SELECT COUNT(*) as count FROM users");
if ($res) {
   $row = $res->fetch_assoc();
if ($row && isset($row['count']) && $row['count'] == 0) {
    $default_user = 'admin';
    $default_pass = password_hash('admin123', PASSWORD_DEFAULT);
    $stmt = $conn->prepare("INSERT INTO users (username, password, status, cash_in, cash_out, balance, created_by) VALUES (?, ?, ?, ?, ?, ?, ?)");
    if ($stmt) {
        $status = 1; // ← Fix: assign to a variable
        $cash_in = 0;
        $cash_out = 0;
        $balance = 0;
        $created_by = 1;
        $stmt->bind_param("ssiiiii", $default_user, $default_pass, $status, $cash_in, $cash_out, $balance, $created_by);
        $stmt->execute();
        $stmt->close();
    }
}
}
?>