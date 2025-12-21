<?php
require 'config/db.php';

// Create a test admin user
$username = 'admin';
$password = '123456'; // Simple password for testing
$hashed_password = password_hash($password, PASSWORD_DEFAULT);

$sql = "INSERT IGNORE INTO users (username, password, role, full_name) VALUES (?, ?, 'admin', 'Super Admin')";
$stmt = $pdo->prepare($sql);
$stmt->execute([$username, $hashed_password]);

echo "Setup Complete! <br>";
echo "Admin Username: <strong>admin</strong><br>";
echo "Admin Password: <strong>123456</strong><br>";
echo "<a href='admin/login.php'>Go to Admin Login</a>";
?>