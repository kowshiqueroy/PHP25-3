<?php
require_once 'config.php';
// Create user table if not exists
$conn->query("CREATE TABLE IF NOT EXISTS user (
    id INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role INT(1) NOT NULL DEFAULT 0,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    status TINYINT(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");
// Insert default users if table is empty
$res = $conn->query("SELECT COUNT(*) as count FROM user");
if ($res) {
    $row = $res->fetch_assoc();
    if ($row && isset($row['count']) && $row['count'] == 0) {
        $default_user1 = 'admin';
        $default_pass1 = password_hash('1234', PASSWORD_DEFAULT);
        $role1 = 0; // User role
        $stmt1 = $conn->prepare("INSERT INTO user (username, password, role) VALUES (?, ?, ?)");
        if ($stmt1) {
            $stmt1->bind_param("ssi", $default_user1, $default_pass1, $role1);
            $stmt1->execute();
            $stmt1->close();
            echo "Default admin user created: username='admin', password='1234'\n";
        }
    }
}

// Create route table if not exists
$conn->query("CREATE TABLE IF NOT EXISTS route (
    id INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
   role INT(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

// Create shop table if not exists
$conn->query("CREATE TABLE IF NOT EXISTS shop (
    id INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    route_id INT(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

$conn->query("CREATE TABLE IF NOT EXISTS item (
    id INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
   role INT(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

$conn->query("CREATE TABLE IF NOT EXISTS order_info (
    `id` INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `route_id` INT(11) NOT NULL,
    `shop_id` INT(11) NOT NULL,
    `order_date` TIMESTAMP NOT NULL,
    `user_id` INT(11) NOT NULL,
    `status` TINYINT(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");
//$conn->query("ALTER TABLE `order_info` ADD COLUMN `status` TINYINT(1) NOT NULL DEFAULT 0");


?>