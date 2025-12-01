<?php
require_once '..\config.php';
if(!isset($_SESSION['user_id'])) {
    header("Location: ..\index.php");
    exit;
}
if(isset($_SESSION['role']) && $_SESSION['role'] != 3) {
    header("Location: ".$_SESSION['role']);
    exit;
 
}
// --- MENU DATA ---
$menuItems = [
    ['icon' => 'fa-house', 'label' => 'Home', 'link' => 'index.php'],
     ['icon' => 'fa-clipboard', 'label' => 'Orders', 'link' => '#'],
     ['icon' => 'fa-money-bill', 'label' => 'Cash', 'link' => '#'],
    ['icon' => 'fa-map', 'label' => 'Routes', 'link' => '#'],
      ['icon' => 'fa-store', 'label' => 'Shops', 'link' => '#'],
    ['icon' => 'fa-box', 'label' => 'Items', 'link' => '#'],
      ['icon' => 'fa-question', 'label' => 'Survey', 'link' => '#'],
  //damage
    ['icon' => 'fa-triangle-exclamation', 'label' => 'Damage', 'link' => '#'],
    ['icon' => 'fa-chart-pie', 'label' => 'Reports', 'link' => '#'],
   //gift
    ['icon' => 'fa-gift', 'label' => 'Gift', 'link' => '#'],
    //promotions
    ['icon' => 'fa-tag', 'label' => 'Promotions', 'link' => '#'],
    //branding
    ['icon' => 'fa-palette', 'label' => 'Branding', 'link' => '#'],

    ['icon' => 'fa-right-from-bracket', 'label' => 'Logout', 'link' => '..\logout.php'],
];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?=APP_NAME; ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="..\style.css">
    
    
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