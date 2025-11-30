<?php
// config/config.php

// --- Database Configuration ---
// Replace with your actual database credentials
define('DB_HOST', 'localhost');
define('DB_NAME', 'sales_management');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

// --- DSN (Data Source Name) ---
$dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;

// --- PDO Options ---
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

/**
 * Establishes a secure database connection using PDO.
 *
 * @return PDO A PDO connection object.
 */
function get_db_connection() {
    global $dsn, $options;
    try {
        return new PDO($dsn, DB_USER, DB_PASS, $options);
    } catch (\PDOException $e) {
        // In a real-world scenario, you would log this error and
        // show a generic error message to the user.
        throw new \PDOException($e->getMessage(), (int)$e->getCode());
    }
}
