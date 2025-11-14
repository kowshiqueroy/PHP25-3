<?php
require_once '../config.php';

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
        $default_user1 = 'user1';
        $default_pass1 = password_hash('user1234', PASSWORD_DEFAULT);
        $role1 = 0; // User role
        $stmt1 = $conn->prepare("INSERT INTO user (username, password, role) VALUES (?, ?, ?)");
        if ($stmt1) {
            $stmt1->bind_param("ssi", $default_user1, $default_pass1, $role1);
            $stmt1->execute();
            $stmt1->close();
        }

        $default_user2 = 'user2';
        $default_pass2 = password_hash('user1234', PASSWORD_DEFAULT);
        $role2 = 1; // Admin role
        $stmt2 = $conn->prepare("INSERT INTO user (username, password, role) VALUES (?, ?, ?)");
        if ($stmt2) {
            $stmt2->bind_param("ssi", $default_user2, $default_pass2, $role2);
            $stmt2->execute();
            $stmt2->close();
        }
    }
}


// Create student table if not exists
$conn->query("CREATE TABLE IF NOT EXISTS student (
    id INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL,
    father_name VARCHAR(50) NOT NULL,
    mother_name VARCHAR(50) NOT NULL,
    dob DATE NOT NULL,
    blood VARCHAR(5) NOT NULL,
    phone VARCHAR(15) NOT NULL,
    reg_id VARCHAR(20) NOT NULL,
    address VARCHAR(255) NOT NULL,
    photo VARCHAR(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");
?>