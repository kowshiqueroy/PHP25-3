<?php
// views/layout/header.php - Common header and navigation

$current_user = get_current_user();
$page = $_GET['page'] ?? 'dashboard';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= APP_NAME ?> - <?= ucfirst(str_replace('_', ' ', $page)) ?></title>
    <link rel="stylesheet" href="style.css">
    <!-- Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
    <header class="app-header">
        <div class="container">
            <h1 class="app-title"><?= APP_NAME ?></h1>
            <button class="menu-toggle" aria-label="Toggle navigation">
                <i class="fas fa-bars"></i>
            </button>
            <nav class="main-nav">
                <ul class="nav-list">
                    <?php if (is_logged_in()): ?>
                        <li><a href="index.php?page=dashboard" class="<?= $page == 'dashboard' ? 'active' : '' ?>">Dashboard</a></li>
                        <?php if (has_role(ROLE_ADMIN)): ?>
                            <li><a href="index.php?page=users" class="<?= $page == 'users' ? 'active' : '' ?>">Users</a></li>
                            <li><a href="index.php?page=company_profile" class="<?= $page == 'company_profile' ? 'active' : '' ?>">Company</a></li>
                            <li><a href="index.php?page=routes" class="<?= $page == 'routes' ? 'active' : '' ?>">Routes</a></li>
                            <li><a href="index.php?page=shops" class="<?= $page == 'shops' ? 'active' : '' ?>">Shops</a></li>
                            <li><a href="index.php?page=items" class="<?= $page == 'items' ? 'active' : '' ?>">Items</a></li>
                        <?php endif; ?>
                        <?php if (has_any_role([ROLE_SALES_REP, ROLE_ADMIN])): ?>
                            <li><a href="index.php?page=invoices" class="<?= $page == 'invoices' ? 'active' : '' ?>">Invoices</a></li>
                            <li><a href="index.php?page=cash_collections" class="<?= $page == 'cash_collections' ? 'active' : '' ?>">Cash Collections</a></li>
                        <?php endif; ?>
                        <?php if (has_any_role([ROLE_MANAGER, ROLE_ADMIN])): ?>
                            <li><a href="index.php?page=approvals" class="<?= $page == 'approvals' ? 'active' : '' ?>">Approvals</a></li>
                        <?php endif; ?>
                        <?php if (has_any_role([ROLE_VIEWER, ROLE_SALES_REP, ROLE_MANAGER, ROLE_ADMIN])): ?>
                            <li><a href="index.php?page=reports" class="<?= $page == 'reports' ? 'active' : '' ?>">Reports</a></li>
                        <?php endif; ?>
                        <?php if (has_role(ROLE_ADMIN)): ?>
                            <li><a href="index.php?page=logs" class="<?= $page == 'logs' ? 'active' : '' ?>">Logs</a></li>
                        <?php endif; ?>
                        <li><a href="index.php?action=logout" id="logout-btn">Logout</a></li>
                    <?php endif; ?>
                </ul>
            </nav>
        </div>
    </header>

    <main id="app-content" class="container">
        <?php if (isset($_GET['message'])): ?>
            <div class="alert success"><?= htmlspecialchars($_GET['message']) ?></div>
        <?php endif; ?>
        <?php if (isset($_GET['error'])): ?>
            <div class="alert error"><?= htmlspecialchars($_GET['error']) ?></div>
        <?php endif; ?>
