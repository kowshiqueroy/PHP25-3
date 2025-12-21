<?php
if (session_status() === PHP_SESSION_NONE) session_start();

// Security Check: Kick out if not admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

// Helper to highlight active menu
function isActive($page) {
    return basename($_SERVER['PHP_SELF']) == $page ? 'active' : '';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Portal</title>
    <link rel="stylesheet" href="css/admin-style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>

    <div class="overlay" onclick="toggleSidebar()"></div>

    <nav class="sidebar" id="sidebar">
        <div class="brand">
            <i class="fas fa-school"></i> School Admin
        </div>
        <ul class="nav-links">
            <li><a href="index.php" class="<?php echo isActive('index.php'); ?>"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
            <li><a href="admissions.php" class="<?php echo isActive('admissions.php'); ?>"><i class="fas fa-user-plus"></i> Admissions</a></li>
            <li><a href="notices.php" class="<?php echo isActive('notices.php'); ?>"><i class="fas fa-bullhorn"></i> Notices</a></li>
            <li><a href="students.php" class="<?php echo isActive('students.php'); ?>"><i class="fas fa-users"></i> Students</a></li>
            <li><a href="teachers.php" class="<?php echo isActive('teachers.php'); ?>"><i class="fas fa-chalkboard-teacher"></i> Teachers</a></li>
            <li><a href="users.php" class="<?php echo isActive('users.php'); ?>"><i class="fas fa-users"></i> Users</a></li>
            <li><a href="settings.php" class="<?php echo isActive('settings.php'); ?>"><i class="fas fa-cogs"></i> Settings</a></li>
            <li style="margin-top: auto; border-top: 1px solid #334155;">
                <a href="logout.php" style="color: #ef4444;"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </li>
        </ul>
    </nav>

    <div class="main-content">
        
        <header class="top-header">
            <div class="toggle-btn" onclick="toggleSidebar()">
                <i class="fas fa-bars"></i>
            </div>
            <div class="user-info">
                <span><?php echo htmlspecialchars($_SESSION['full_name']); ?></span>
                <div class="avatar"><?php echo strtoupper(substr($_SESSION['username'], 0, 1)); ?></div>
            </div>
        </header>

        <div class="page-wrapper">