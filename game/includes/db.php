<?php
$conn = new mysqli("localhost", "root", "", "gamehub");
if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

$site_name="Tiparu";
?>