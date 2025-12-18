<?php
require_once '..\config.php';
if(!isset($_SESSION['user_id'])) {
    header("Location: ..\index.php");
    exit;
}
if(isset($_SESSION['role']) && ($_SESSION['role'] != 3 && $_SESSION['role'] != 1)) {
    header("Location: ".$_SESSION['role']);
    exit;
 
}
// --- MENU DATA ---
$menuItems = [
    ['icon' => 'fa-house', 'label' => 'Home', 'link' => 'index.php'],
    ['icon' => 'fa-clipboard', 'label' => 'Orders', 'link' => 'orders.php'],
    ['icon' => 'fa-truck', 'label' => 'Serial', 'link' => 'serials.php'],
    ['icon' => 'fa-store', 'label' => 'Shops', 'link' => 'shops.php'],
    ['icon' => 'fa-money-bill', 'label' => 'Cash', 'link' => 'cash.php'],
    ['icon' => 'fa-map', 'label' => 'Routes', 'link' => 'routes.php'],
    ['icon' => 'fa-box', 'label' => 'Items', 'link' => 'items.php'],
    ['icon' => 'fa-question', 'label' => 'Survey', 'link' => '#'],
    ['icon' => 'fa-triangle-exclamation', 'label' => 'Damage', 'link' => '#'],
    ['icon' => 'fa-chart-pie', 'label' => 'Reports', 'link' => '#'],
    ['icon' => 'fa-gift', 'label' => 'Brand Gift Promos', 'link' => '#'],
    ['icon' => 'fa-right-from-bracket', 'label' => 'Logout', 'link' => '..\logout.php'],
];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title><?=APP_NAME; ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="..\style.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
</head>
<body>

    <header>
        <div class="app-name"><i class="fa-solid fa-building" style="color:var(--warning)"></i> <?php echo APP_NAME; ?> <span style="color:var(--primary)"> App</span></div>
        <div class="user-info">
            <span class="user-company"><i class="fa-regular fa-building"></i> <?php echo APP_NAME; ?></span>
            <span class="user-role"> <i class="fa-solid fa-user-tag"></i> <?php echo $_SESSION['role']; ?></span>
            <span class="user-handle"><i class="fa-regular fa-user"></i><?php echo $_SESSION['user_id']; ?>@<?php echo $_SESSION['username']; ?></span>
        </div>
    </header>