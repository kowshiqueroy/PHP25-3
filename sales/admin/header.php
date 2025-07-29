<?php
if (!isset($_SESSION['rolename']) || $_SESSION['rolename'] !== 'admin') {
    header("Location: ../index.php");
    exit();
}

?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $sitename ?></title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
    <style>
        body { font-family: 'Arial', sans-serif; background: #f8f9fa; }
        .navbar { background: #343a40; color: white;
        position: -webkit-sticky;
    position: sticky;
    top: 0;
    z-index: 1000; }
        .menu-box {
            width: 250px; position: fixed; left: -260px; top: 0; height: 100vh; 
            background: #212529; color: white; transition: 0.4s ease-in-out; z-index: 999;
            box-shadow: 3px 0px 10px rgba(0,0,0,0.2);
            overflow-y: auto;
            overflow-x: hidden;
            
        }
        .menu-box.show { left: 0; }
        .menu-box span { display: block; text-align: center; }
        .menu-box ul { padding: 10px; }
        .menu-box ul li { padding: 8px 0; list-style: none; }
        .menu-box ul li a { text-decoration: none; color: white; padding: 5px 10px; 
            border-radius: 5px; transition: 0.3s; display: block;
        }
        .menu-box ul li a:hover { background: #495057; }
        .content { margin-left: 20px; padding: 20px; }
        .hidden-buttons { display: none; }
        .card { box-shadow: 0px 0px 10px rgba(0,0,0,0.1); }
        .user-info { display: flex; align-items: center; gap: 15px; }
        @media (max-width: 768px) { 
            .two-columns { flex-direction: column; }
            .desktop-items { display: none; } /* Hide elements on mobile */
            .user-info, #menuToggle { display: block; }
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-dark d-flex align-items-center justify-content-between px-3">
        <button class="btn btn-primary" id="menuToggle">☰</button>
        <span class="navbar-brand">Dashboard</span>
        <div class="user-info">
        <span class="text-white small desktop-items"><?php echo $_SESSION['rolename'];?></span>
  
        <button class="btn btn-danger" onclick="window.location.href='../logout.php'">Logout</button>
        </div>
    </nav>

    <div class="menu-box p-3" id="menuBox">
        <button class="btn btn-secondary w-100" id="closeMenu">Close</button>
        <ul>
        <span class="text-white small"><?php echo $_SESSION['username']." (".$_SESSION['id'].") ".$_SESSION['rolename'];?></span>
        
            <li><a href="index.php"><i class="fas fa-home"></i> Home</a></li>
            <li><a href="users.php"><i class="fas fa-users"></i> Users</a></li>
            <li><a href="settings.php"><i class="fas fa-cog"></i> Settings</a></li>
            <li><a href="notice.php"><i class="fas fa-bell"></i> Notice</a></li>
           
        </ul>
    </div>

    <div class="container mt-3">
        <div class="content">
           
            
       