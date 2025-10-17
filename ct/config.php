<?php

// Database configuration
$db_host = 'localhost';
$db_name = 'ct';
$db_user = 'root'; // Change to your database username
$db_pass = '';

// Database connection using MySQLi
$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
date_default_timezone_set('Asia/Dhaka');
