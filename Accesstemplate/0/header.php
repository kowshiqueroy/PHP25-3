<?php
include '../config.php';


if (!isset($_SESSION['role'])) {
    header("Location: ../");
    exit();
}
if ( $_SESSION['role'] !== 0 ) {
    header("Location: ../".($_SESSION['role'] ? '1/' : '0/'));
exit();
}

?>









<?php


if (!isset($_SESSION['theme'])) {
    $result = $conn->query("SELECT * FROM settings");
    if ($result && $result->num_rows === 1) {
        $row = $result->fetch_assoc();
        $_SESSION['companyname'] = $row['companyname'];
        $_SESSION['theme'] = $row['theme'];
        $_SESSION['language'] = $row['language'];
    }
}

$theme = $_SESSION['theme'] ?? 0 ; // 0 for light, 1 for dark
$language = $_SESSION['language'] ?? 0; // 0 for English, 1 for Bangla
$username = $_SESSION['username'] ?? 'User';
$role = $_SESSION['role'] ? 'Manager' : 'Admin';
$company = $_SESSION['companyname'] ?? 'Company Name';

$lang = [
    // English
    0 => [
        'admin_panel' => $company,
        'dashboard' => 'Dashboard',
        'users' => 'Users',
        'products' => 'Products',
        'reports' => 'Reports',
        'settings' => 'Settings',
        'logout' => 'Logout',
        'Success' => 'Success',
        'Error' => 'Error',
        'sales' => 'Sales',
        'stocks' => 'Stocks',
    ],
    // Bangla
    1 => [
        'admin_panel' => $company,
        'dashboard' => 'ড্যাশবোর্ড',
        'users' => 'ব্যবহারকারী',
        'products' => 'পণ্য',
        'reports' => 'রিপোর্ট',
        'settings' => 'সেটিংস',
        'logout' => 'লগআউট',
        'Success' => 'সফল',
        'Error' => 'ত্রুটি',
        'sales' => 'বিক্রয়',
        'stocks' => 'স্টকস',
        
    ]
];

?>
<!DOCTYPE html>
<html lang="en" class="<?php echo $theme == 1 ? 'dark' : ''; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title><?php echo $lang[$language]['admin_panel']; ?></title>
    <link rel="stylesheet" href="style.css">
    <!-- <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css" integrity="sha384-MCw98/SFnGE8fJT3GXwEOngsV7Zt27NXFoaoApmYm81iuXoPkFOJwJ8ERdknLPMO" crossorigin="anonymous"> -->
</head>
<body>

    <nav class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <a href="#" class="logo">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M21 3H3c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h18c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm0 16H3V5h18v14zM5 9h14v2H5V9zm0-4h14v2H5V5zm0-4h14v2H5V1z"></path>
                </svg>
                <span onClick="window.location.href='index.php'"><?php echo $lang[$language]['admin_panel']; ?></span>
            </a>
        </div>
        <ul class="sidebar-nav">
            <li class="nav-item active">
                <a href="index.php">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25h-2.25a2.25 2.25 0 01-2.25-2.25v-2.25z" />
                    </svg>
                    <span><?php echo $lang[$language]['dashboard']; ?></span>
                </a>
            </li>
           
            <li class="nav-item">
                <a href="products.php">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z" />
                    </svg>
                    <span><?php echo $lang[$language]['products']; ?></span>
                </a>
            </li>
             <li class="nav-item">
                <a href="sales.php">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                    </svg>
                    <span><?php echo $lang[$language]['sales']; ?></span>
                </a>
            </li>

            <li class="nav-item">
                <a href="stock.php">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h13a2 2 0 012 2v14a2 2 0 01-2 2h-5m-1 0a2 2 0 00-2 2v1a2 2 0 002 2h1a2 2 0 002-2v-1a2 2 0 00-2-2h-1z" />

                    </svg>
                    <span><?php echo $lang[$language]['stocks']; ?></span>
                </a>
            </li>
            <li class="nav-item">
                <a href="reports.php">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 6a7.5 7.5 0 107.5 7.5h-7.5V6z" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 10.5H21A7.5 7.5 0 0013.5 3v7.5z" />
                    </svg>
                    <span><?php echo $lang[$language]['reports']; ?></span>
                </a>
            </li>

             <li class="nav-item">
                <a href="users.php">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" />
                    </svg>
                    <span><?php echo $lang[$language]['users']; ?></span>
                </a>
            </li>
        <li class="nav-item">
    <a href="settings.php" class="nav-link">
        <svg xmlns="http://www.w3.org/2000/svg" class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
            <path stroke-linecap="round" stroke-linejoin="round" d="M11.25 2.25c.414 0 .75.336.75.75v1.5a.75.75 0 01-.75.75h-1.5a.75.75 0 01-.75-.75V3a.75.75 0 01.75-.75h1.5zM4.5 11.25c0-.414.336-.75.75-.75h1.5a.75.75 0 01.75.75v1.5a.75.75 0 01-.75.75H5.25a.75.75 0 01-.75-.75v-1.5zM11.25 20.25c-.414 0-.75-.336-.75-.75v-1.5a.75.75 0 01.75-.75h1.5a.75.75 0 01.75.75v1.5a.75.75 0 01-.75.75h-1.5zM20.25 11.25c0 .414-.336.75-.75.75h-1.5a.75.75 0 01-.75-.75v-1.5a.75.75 0 01.75-.75h1.5c.414 0 .75.336.75.75v1.5zM12 15a3 3 0 100-6 3 3 0 000 6z" />
        </svg>
        <span><?php echo $lang[$language]['settings']; ?></span>
    </a>
</li>

<li class="nav-item">
   <hr>
        
        <div class="user-info">
            <span class="user-name">👨‍💻 <?php echo $username; ?> </span>
            <span class="role">💼 <?php echo $role; ?></span>
            <span class="company">🏢 <?php echo $company; ?></span>
        </div>

</li>

        </ul>
    </nav>

    <div class="main-container" id="main-container">
        <header class="fixed-header">
            <div class="header-left">
                <button class="menu-toggle" id="menu-toggle">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" /></svg>
                </button>
            </div>
            <div class="header-center" onClick="window.location.href='index.php'">
                <?php echo $lang[$language]['admin_panel']; ?>
            </div>
            <div class="header-right">
                <button onclick="window.location.href='../logout.php'" class="logout-btn"><?php echo $lang[$language]['logout']; ?></button>
            </div>
        </header>

        <main class="main-content">