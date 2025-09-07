<?php
include_once 'config.php';

// Create users table if not exists
$conn->query("CREATE TABLE IF NOT EXISTS users (
    id INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(10) NOT NULL UNIQUE,
    password VARCHAR(32) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    created_by INT(11) UNSIGNED
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");
