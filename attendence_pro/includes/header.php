<?php
session_start();
// config/db.php content inline for simplicity
require_once 'config/db.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
    <title>Face Attendance Pro</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/face-api.js@0.22.2/dist/face-api.min.js"></script>
    <style>
        :root { --glass: rgba(255, 255, 255, 0.15); --border: rgba(255, 255, 255, 0.2); }
        body {
            background: linear-gradient(135deg, #1f1c2c, #928dab);
            min-height: 100vh; color: #fff; font-family: 'Poppins', sans-serif;
            padding-bottom: 60px; /* Space for mobile nav */
        }
        /* Glassmorphism Card */
        .glass-card {
            background: var(--glass); backdrop-filter: blur(12px);
            border: 1px solid var(--border); border-radius: 16px;
            padding: 20px; box-shadow: 0 4px 30px rgba(0, 0, 0, 0.1);
        }
        .form-control, .form-select {
            background: rgba(0,0,0,0.2); border: 1px solid var(--border); color: #fff;
        }
        .form-control:focus { background: rgba(0,0,0,0.4); color: #fff; border-color: #00d2ff; box-shadow: none; }
        
        /* Mobile Nav */
        .bottom-nav {
            position: fixed; bottom: 0; left: 0; width: 100%;
            background: rgba(20, 20, 30, 0.95); backdrop-filter: blur(10px);
            display: flex; justify-content: space-around; padding: 12px;
            border-top: 1px solid var(--border); z-index: 1000;
        }
        .nav-item { color: rgba(255,255,255,0.5); text-align: center; font-size: 0.8rem; text-decoration: none; }
        .nav-item.active { color: #00d2ff; }
        .nav-item i { font-size: 1.2rem; display: block; margin-bottom: 2px; }

        /* Print Styles */
        @media print {
            .bottom-nav, .no-print { display: none !important; }
            body { background: white !important; color: black !important; }
            .glass-card { background: none; border: none; box-shadow: none; padding: 0; }
        }
    </style>
</head>
<body>
    <?php if(isset($_SESSION['admin_id'])): 
        $page = basename($_SERVER['PHP_SELF']); 
    ?>
    <nav class="bottom-nav">
        <a href="dashboard.php" class="nav-item <?= $page=='dashboard.php'?'active':'' ?>">
            <i class="fa fa-home"></i> Home
        </a>
        <a href="attendance.php" class="nav-item <?= $page=='attendance.php'?'active':'' ?>">
            <i class="fa fa-camera"></i> AI
        </a>
        <a href="reports.php" class="nav-item <?= $page=='reports.php'?'active':'' ?>">
            <i class="fa fa-file-alt"></i> Reports
        </a>
        <a href="unknowns.php" class="nav-item <?= $page=='unknowns.php'?'active':'' ?>">
            <i class="fa fa-user-secret"></i> Unknowns
        </a>
           <a href="settings.php" class="nav-item <?= $page=='settings.php'?'active':'' ?>">
            <i class="fa fa-cog"></i> Settings
        </a>
    </nav>
    <?php endif; ?>
    
    <div class="container pt-4"></div>