<?php
$host = 'localhost';
if (strpos($_SERVER['HTTP_HOST'], 'localhost') !== false || strpos($_SERVER['HTTP_HOST'], 'free.app') !== false) {
    $db   = 'attendance_pro';
    $user = 'root';
    $pass = '';
} else {
    $db   = 'u312077073_attendance_pro';
    $user = 'u312077073_attendance_pro';
    $pass = 'KR5877kush';
}

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
session_start();
//timezone
date_default_timezone_set(timezoneId: 'Asia/Dhaka');
?>