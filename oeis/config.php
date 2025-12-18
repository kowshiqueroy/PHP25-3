<?php
// PHP part (your app config)
define('DB_HOST', 'localhost');
define('DB_NAME', 'oeis');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

define('APP_NAME', 'Ovijat EIS');
define('DEVELOPER_NAME', 'Kowshique Roy');
define('VERSION_NAME', '2.3.1');

session_start();
date_default_timezone_set('Asia/Dhaka');

?>