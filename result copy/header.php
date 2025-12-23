<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 1. Security Redirect
// Allow public access pages (like public_result.php), otherwise block.
$current_page = basename($_SERVER['PHP_SELF']);
$public_pages = ['login.php', 'public_result.php'];

if (!in_array($current_page, $public_pages)) {
    if (!isset($_SESSION['user_id'])) {
        header("Location: login.php");
        exit;
    }
}

require_once 'db.php';

// 2. Fetch Global Settings
$stmt = $pdo->query("SELECT school_name, established FROM settings WHERE id=1");
$settings = $stmt->fetch();
$school_name = $settings['school_name'] ?? 'EduResult Pro';
$established = $settings['established'] ?? '----';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($school_name); ?> - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    
    <style>
        :root { --sidebar-bg: #1e293b; --content-bg: #f8fafc; }
        body { background-color: var(--content-bg); min-height: 100vh; overflow-x: hidden; }
        
        /* Sidebar Styling */
        #sidebar-wrapper {
            min-height: 100vh;
            margin-left: -15rem;
            transition: margin .25s ease-out;
            background-color: var(--sidebar-bg);
            color: white;
            position: fixed;
            width: 15rem;
            z-index: 1000;
        }
        #sidebar-wrapper .sidebar-heading { padding: 0.875rem 1.25rem; font-size: 1.2rem; }
        #sidebar-wrapper .list-group-item {
            background-color: transparent;
            color: #cbd5e1;
            border: none;
            padding: 12px 20px;
        }
        #sidebar-wrapper .list-group-item:hover, #sidebar-wrapper .list-group-item.active {
            background-color: rgba(255,255,255,0.1);
            color: #fff;
        }
        
        /* Page Content Wrapper */
        #page-content-wrapper { width: 100%; transition: margin .25s ease-out; }
        
        /* Toggled State for Desktop */
        body.sb-sidenav-toggled #sidebar-wrapper { margin-left: 0; }
        body.sb-sidenav-toggled #page-content-wrapper { margin-left: 15rem; }
        
        /* Mobile Default: Sidebar Hidden, Toggled shows it */
        @media (min-width: 768px) {
            #sidebar-wrapper { margin-left: 0; }
            #page-content-wrapper { margin-left: 15rem; }
            body.sb-sidenav-toggled #sidebar-wrapper { margin-left: -15rem; }
            body.sb-sidenav-toggled #page-content-wrapper { margin-left: 0; }
        }
        
        /* Print Media */
        @media print {
            #sidebar-wrapper, .navbar, .no-print { display: none !important; }
            #page-content-wrapper { margin-left: 0 !important; width: 100% !important; }
            body { background-color: white; }
        }
    </style>
</head>
<body>

<div class="d-flex" id="wrapper">
    <div id="sidebar-wrapper" >
        <div class="sidebar-heading text-center fw-bold border-bottom border-secondary">
            <i class="fa-solid fa-graduation-cap me-2"></i> EduResult
        </div>
        <div class="list-group list-group-flush mt-3">
            <a href="index.php" class="list-group-item list-group-item-action"><i class="fa-solid fa-gauge me-2"></i> Dashboard</a>
            <a href="classes.php" class="list-group-item list-group-item-action"><i class="fa-solid fa-chalkboard-user me-2"></i> Classes & Rolls</a>
            <a href="subjects.php" class="list-group-item list-group-item-action"><i class="fa-solid fa-book me-2"></i> Subjects & Grading</a>
            <a href="marks_entry.php" class="list-group-item list-group-item-action"><i class="fa-solid fa-pen-to-square me-2"></i> Marks Entry</a>
            
    
            <a href="result_class.php" class="list-group-item list-group-item-action"><i class="fa-solid fa-table me-2"></i> Class Result</a>
            <a href="result.php" class="list-group-item list-group-item-action"><i class="fa-solid fa-id-card me-2"></i> Student Report</a>
            <a href="admit_card_generator.php" class="list-group-item list-group-item-action"><i class="fa-solid fa-address-card me-2"></i> Admit Card Generator</a>
            
        
            <a href="settings.php" class="list-group-item list-group-item-action"><i class="fa-solid fa-gear me-2"></i> Settings</a>
            <a href="logout.php" class="list-group-item list-group-item-action text-danger"><i class="fa-solid fa-power-off me-2"></i> Logout</a>
        </div>
    </div>

    <div id="page-content-wrapper">
        <nav class="navbar navbar-expand-lg navbar-light bg-white border-bottom shadow-sm">
            <div class="container-fluid">
                <button class="btn btn-outline-secondary btn-sm" id="sidebarToggle"><i class="fa-solid fa-bars"></i></button>
                
                <div class="ms-auto d-flex align-items-center">
                    <span class="me-3 text-muted small d-none d-sm-block">Session: <strong><?php echo htmlspecialchars($established); ?></strong></span>
                    <div class="dropdown">
                        <a class="nav-link dropdown-toggle text-dark" href="#" role="button" data-bs-toggle="dropdown">
                            <i class="fa-solid fa-user-circle fa-lg"></i> Admin
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="settings.php">Settings</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item text-danger" href="logout.php">Logout</a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </nav>
        
        <div class="container-fluid px-4 py-4"></div>