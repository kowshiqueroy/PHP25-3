<?php
// includes/header.php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../config/db.php'; // Ensure DB is connected

// Fetch Site Settings (Cached logic can be added here)
$settings_stmt = $pdo->query("SELECT * FROM site_settings");
$settings_raw = $settings_stmt->fetchAll();
$settings = [];
foreach ($settings_raw as $s) { $settings[$s['setting_key']] = $s['setting_value']; }

// Fetch Menus
$menu_stmt = $pdo->query("SELECT * FROM menus WHERE is_active=1 ORDER BY sort_order ASC");
$menus = $menu_stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $settings['institute_name'] ?? 'School Portal'; ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>

    <div class="top-bar">
        <div class="container flex-between">
            <span><i class="fas fa-phone"></i> <?php echo $settings['phone']; ?> | EIIN: <?php echo $settings['emis_code']; ?></span>
            <?php if(isset($_SESSION['user_id'])): ?>
                <a href="admin/index.php" style="color:white;"><i class="fas fa-user-cog"></i> ড্যাশবোর্ড</a>
            <?php else: ?>
                <a href="admin/login.php" style="color:white;"><i class="fas fa-sign-in-alt"></i> লগইন</a>
            <?php endif; ?>
        </div>
    </div>

    <header class="main-header">
        <div class="container flex-between">
            <a href="index.php" class="brand">
                <div class="logo-circle"><i class="fas fa-graduation-cap"></i></div>
                <div class="brand-text">
                    <h1><?php echo $settings['institute_name']; ?></h1>
                    <span>স্থাপিত: ১৯৬০ | শিক্ষা শান্তি প্রগতি</span>
                </div>
            </a>

            <div class="nav-toggle" onclick="document.getElementById('nav-menu').classList.toggle('active')">
                <i class="fas fa-bars"></i>
            </div>

            <nav class="nav-menu" id="nav-menu">
                <?php foreach($menus as $menu): ?>
                    <a href="<?php echo $menu['link']; ?>" class="nav-link">
                        <?php echo $menu['title_bn']; ?>
                    </a>
                <?php endforeach; ?>
            </nav>
        </div>
    </header>

    <main style="flex: 1;">