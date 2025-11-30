<?php
// templates/header.php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sales Management</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

<header>
    <div class="logo">SalesPro</div>
    <nav class="no-print">
        <ul>
            <?php if (isset($_SESSION['user_id'])): ?>
                <li><a href="index.php">Dashboard</a></li>
                <li><a href="logout.php">Logout</a></li>
            <?php else: ?>
                <li><a href="login.php">Login</a></li>
            <?php endif; ?>
        </ul>
    </nav>
</header>

<div class="container">
