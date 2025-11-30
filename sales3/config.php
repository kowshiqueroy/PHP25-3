<?php
// config.php - Centralized database configuration and settings

// Database credentials
define('DB_HOST', 'localhost');
define('DB_PORT', '3306');
define('DB_NAME', 'sales_app_db_simple'); // New database name for the simple app
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

// Establish PDO connection
function get_db_connection() {
    $dsn = "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];
    try {
        return new PDO($dsn, DB_USER, DB_PASS, $options);
    } catch (PDOException $e) {
        // In a real application, log this error securely
        error_log("Database connection failed: " . $e->getMessage());
        die("Database connection failed. Please check your configuration.");
    }
}

// Application settings
define('APP_NAME', 'Simple Sales App');
define('DEFAULT_ADMIN_USERNAME', 'admin');
define('DEFAULT_ADMIN_PASSWORD', 'password123'); // Hashed on first run
define('DEFAULT_COMPANY_NAME', 'Simple Corp');

// Roles (for easier management in procedural code)
define('ROLE_ADMIN', 'Admin');
define('ROLE_SALES_REP', 'Sales Representative');
define('ROLE_MANAGER', 'Manager');
define('ROLE_VIEWER', 'Viewer');

// Invoice Statuses
define('STATUS_DRAFTED', 'Drafted');
define('STATUS_CONFIRMED', 'Confirmed');
define('STATUS_APPROVED', 'Approved');
define('STATUS_REJECTED', 'Rejected');
define('STATUS_ON_PROCESS', 'On Process');
define('STATUS_ON_DELIVERY', 'On Delivery');
define('STATUS_DELIVERED', 'Delivered');
define('STATUS_RETURNED', 'Returned');
define('STATUS_DAMAGED', 'Damaged');

// Cash Collection Statuses
define('CC_STATUS_PENDING', 'Pending');
define('CC_STATUS_APPROVED', 'Approved');
define('CC_STATUS_REJECTED', 'Rejected');

// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
