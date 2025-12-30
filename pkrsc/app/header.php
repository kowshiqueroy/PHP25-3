<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 1. Security Redirect
// Allow public access pages (like public_result.php), otherwise block.


    if (!isset($_SESSION['user_id'])) {
        header("Location: login.php");
        exit;
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
    :root { 
        --sidebar-bg: #1e293b; 
        --sidebar-hover: rgba(255, 255, 255, 0.08);
        --sidebar-active: #3b82f6; /* Modern Blue */
        --sidebar-text: #94a3b8;
        --sidebar-text-hover: #f8fafc;
        --content-bg: #f1f5f9; 
        --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }

    body { 
        background-color: var(--content-bg); 
        min-height: 100vh; 
        overflow-x: hidden; 
        font-family: 'Inter', system-ui, -apple-system, sans-serif;
    }

    /* Sidebar Styling */
    #sidebar-wrapper {
        min-height: 100vh;
        margin-left: -15rem;
        transition: var(--transition);
        background-color: var(--sidebar-bg);
        color: white;
        position: fixed;
        width: 12rem;
        z-index: 1050;
        box-shadow: 4px 0 10px rgba(0, 0, 0, 0.1);
    }

    #sidebar-wrapper .sidebar-heading {
        padding: 0.5rem 0.5rem;
        font-size: 1rem;
        font-weight: 700;
        letter-spacing: -0.025em;
        border-bottom: 1px solid rgba(255, 255, 255, 0.05);
        color: #fff;
    }

    #sidebar-wrapper .list-group {
        padding: 0.5rem 0.2rem;
    }

    #sidebar-wrapper .list-group-item {
        background-color: transparent;
        color: var(--sidebar-text);
        border: none;
        padding: 0.8rem 1rem;
        border-radius: 0.5rem;
        margin-bottom: 0.2rem;
        display: flex;
        align-items: center;
        transition: var(--transition);
        font-size: 0.75rem;
        font-weight: 500;
    }

    #sidebar-wrapper .list-group-item i {
        width: 1rem;
        margin-right: 0.75rem;
        font-size: 1rem;
        text-align: center;
    }

    #sidebar-wrapper .list-group-item:hover {
        background-color: var(--sidebar-hover);
        color: var(--sidebar-text-hover);
    }

    #sidebar-wrapper .list-group-item.active {
        background-color: var(--sidebar-active);
        color: #fff;
        box-shadow: 0 4px 6px -1px rgba(59, 130, 246, 0.5);
    }

    /* Page Content Wrapper */
    #page-content-wrapper { 
        width: 100%; 
        transition: var(--transition); 
        min-height: 100vh;
        display: flex;
        flex-direction: column;
    }

    /* Toggled State for Mobile */
    body.sb-sidenav-toggled #sidebar-wrapper { margin-left: 0; }
    
    /* Desktop State */
    @media (min-width: 768px) {
        #sidebar-wrapper { margin-left: 0; }
        #page-content-wrapper { margin-left: 15rem; width: calc(100% - 15rem); }
        
        body.sb-sidenav-toggled #sidebar-wrapper { margin-left: -15rem; }
        body.sb-sidenav-toggled #page-content-wrapper { margin-left: 0; width: 100%; }
    }

    /* Navbar customization to match content */
    .navbar {
        background-color: white !important;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        padding: 0.75rem 1.5rem;
    }

    /* Print Media */
    @media print {
        #sidebar-wrapper, .navbar, .no-print, .btn { display: none !important; }
        #page-content-wrapper { margin-left: 0 !important; width: 100% !important; padding: 0 !important; }
        body { background-color: white; }
        .card { border: none !important; box-shadow: none !important; }
    }
</style>
</head>
<body>

<div class="d-flex" id="wrapper">
    <div id="sidebar-wrapper" >
        <div class="sidebar-heading text-center fw-bold border-bottom border-secondary">
            <i class="fa-solid fa-graduation-cap me-2"></i> EduResult
        </div>
        <div class="list-group list-group-flush mt-1 small text-secondary">
            <a href="index.php" class="list-group-item list-group-item-action"><i class="fa-solid fa-gauge me-2"></i> Dashboard</a>
            <a href="classes_students.php" class="list-group-item list-group-item-action"><i class="fa-solid fa-chalkboard-user me-2"></i> Classes & Rolls</a>
            <a href="subjects.php" class="list-group-item list-group-item-action"><i class="fa-solid fa-book me-2"></i> Subjects & Grading</a>
            <a href="marks_entry.php" class="list-group-item list-group-item-action"><i class="fa-solid fa-pen-to-square me-2"></i> Marks Entry</a>
            
    
            <a href="result_class.php" class="list-group-item list-group-item-action"><i class="fa-solid fa-table me-2"></i> Class Result</a>
             <a href="summary.php" class="list-group-item list-group-item-action"><i class="fa-solid fa-table me-2"></i> Result Summary</a>
            <a href="result.php" class="list-group-item list-group-item-action"><i class="fa-solid fa-id-card me-2"></i> Student Report</a>
            <a href="admit_card_generator.php" class="list-group-item list-group-item-action"><i class="fa-solid fa-address-card me-2"></i> Admit Card Generator</a>
            
        
            <a href="settings.php" class="list-group-item list-group-item-action"><i class="fa-solid fa-gear me-2"></i> Settings</a>
            <a href="users.php" class="list-group-item list-group-item-action"><i class="fa-solid fa-users me-2"></i> User Management</a>
            <a href="logout.php" class="list-group-item list-group-item-action text-danger"><i class="fa-solid fa-power-off me-2"></i> Logout</a>
        </div>
    </div>

    <div id="page-content-wrapper">
        <nav class="navbar navbar-expand-lg navbar-light bg-white border-bottom shadow-sm">
            <div class="container-fluid">
                <button class="btn btn-outline-secondary btn-sm" id="sidebarToggle"><i class="fa-solid fa-bars"></i></button>
                
                <div class="ms-auto d-flex align-items-center">
                    <span class="me-3 text-muted small d-none d-sm-block">EST: <strong><?php echo htmlspecialchars($established); ?></strong></span>
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