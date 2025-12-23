<?php
$host = 'localhost';
$dbname = 'eduresult_pro';
$username = 'root'; // Change as needed
$password = '';     // Change as needed


if ($_SERVER['SERVER_NAME'] != "localhost" && strpos($_SERVER['SERVER_NAME'], "free.app") === false)
{    
    $dbname = 'u312077073_edu';
    $username = 'u312077073_edu';
    $password = 'KR5877kush';
}
try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // If DB doesn't exist, connect without dbname to create it
    if (strpos($e->getMessage(), "Unknown database") !== false) {
        $pdo = new PDO("mysql:host=$host;charset=utf8mb4", $username, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    } else {
        die("Connection failed: " . $e->getMessage());
    }
}
?>