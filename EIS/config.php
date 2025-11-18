<?php
    $conn = new mysqli("localhost", "root", "", "EIS");
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    session_start();
    $website_name = "EIS";
    date_default_timezone_set('Asia/Dhaka');
?>