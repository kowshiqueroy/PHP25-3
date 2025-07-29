<?php
// Database configuration
$db_host = 'localhost';
$db_name = 'qc';
$db_user = 'root'; // Change to your database username
$db_pass = '';

// Database connection using MySQLi
$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);

// Check connection
if ($conn->connect_error) {
    echo "Connection failed: " . $conn->connect_error;
    exit;
}
// Set character set to utf8
$conn->set_charset("utf8");
// Start session
session_start();
$company_name = 'Quality Control'; // Set your company name here
?>