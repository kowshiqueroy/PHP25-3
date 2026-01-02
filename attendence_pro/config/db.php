<?php
$host = 'localhost';
$db   = 'attendance_pro';
$user = 'root';
$pass = ''; // Change if you have a password

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
session_start();
//timezone
date_default_timezone_set(timezoneId: 'Asia/Dhaka');
?>