<?php

// Database configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'shishubot_db');
define('DB_USER', 'root');
define('DB_PASS', '');

// Establish database connection using PDO
try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

?>