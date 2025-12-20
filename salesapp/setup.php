<?php
require_once 'connection.php';

// Create user table if not exists
$conn->query("CREATE TABLE IF NOT EXISTS user (
    id INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(80) NOT NULL,
    role INT(1) NOT NULL DEFAULT 0,
    created_by INT(11) NOT NULL DEFAULT 1,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    status BOOLEAN NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

// Insert default admin user if table is empty
$res = $conn->query("SELECT COUNT(*) as count FROM user");
if ($res) {
    $row = $res->fetch_assoc();
    if ($row && isset($row['count']) && $row['count'] == 0) {
        $default_user = 'admin';
        $default_pass = password_hash('admin123', PASSWORD_DEFAULT);
        echo "Inserting default admin user.../n";
        echo $default_pass . "\n";
        $role = 1; // Admin role
        $stmt = $conn->prepare("INSERT INTO user (username, password, role) VALUES (?, ?, ?)");
        if ($stmt) {
            $stmt->bind_param("ssi", $default_user, $default_pass, $role);
            $stmt->execute();
            $stmt->close();
        }

        echo "Default admin user inserted successfully.\n";
        echo "Inserting default user with role 0.../n";
        $default_user = 'user';
        $default_pass = password_hash('user123', PASSWORD_DEFAULT);
        $role = 0; // User role
        $stmt = $conn->prepare("INSERT INTO user (username, password, role) VALUES (?, ?, ?)");
        if ($stmt) {
            $stmt->bind_param("ssi", $default_user, $default_pass, $role);
            $stmt->execute();
            $stmt->close();
        }

        echo "Default user with role 0 inserted successfully.\n";
    } else {
        echo "Default admin user already exists.\n";
    }
}

// Create route table if not exists
$conn->query("CREATE TABLE IF NOT EXISTS route (
    id INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL UNIQUE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

$conn->query("CREATE TABLE IF NOT EXISTS shop (
    id INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL UNIQUE,
    route_id INT(11)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

// Create orders table if not exists
$conn->query("CREATE TABLE IF NOT EXISTS orders (
    id INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    route_id INT(11) NOT NULL,
    shop_id INT(11) NOT NULL,
    total DECIMAL(15,2) NOT NULL DEFAULT 0,
    order_date DATE NOT NULL,
    delivery_date DATE NOT NULL,
    order_status INT(1) NOT NULL DEFAULT 0,
    latitude DECIMAL(10,8) NOT NULL,
    longitude DECIMAL(11,8) NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    created_by INT(11) NOT NULL,
    approved_by INT(11) NOT NULL,
    remarks VARCHAR(255) NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");
$conn->query("CREATE TABLE IF NOT EXISTS item (
    id INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL UNIQUE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

?>