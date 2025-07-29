<?php
// Database credentials & session settings
define('DB_HOST', 'localhost');
define('DB_NAME', 'chatbotapp'); // replace with your database name
define('DB_USER', 'root');
define('DB_PASS', '');

// Session cookie settings
session_start([
    'cookie_lifetime' => 86400,
    'cookie_httponly' => true
]);