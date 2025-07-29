<?php

// Database configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'simple_ai');

// Check if allow_url_fopen is enabled for web search functionality
if (!ini_get('allow_url_fopen')) {
    error_log("Warning: allow_url_fopen is disabled. Web search functionality may not work. Please enable it in your php.ini file.");
    // You might want to display a user-friendly message in the UI as well
}

?>