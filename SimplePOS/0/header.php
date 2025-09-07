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
        'analytics' => 'Analytics',
        'settings' => 'Settings',
        'logout' => 'Logout',
        'Success' => 'Success',
        'Error' => 'Error',
        
    ],
    // Bangla
    1 => [
        'admin_panel' => $company,
        'dashboard' => 'ড্যাশবোর্ড',
        'users' => 'ব্যবহারকারী',
        'products' => 'পণ্য',
        'analytics' => 'বিশ্লেষণ',
        'settings' => 'সেটিংস',
        'logout' => 'লগআউট',
        'Success' => 'সফল',
        'Error' => 'ত্রুটি',
        
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
                <span><?php echo $lang[$language]['admin_panel']; ?></span>
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
                <a href="users.php">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" />
                    </svg>
                    <span><?php echo $lang[$language]['users']; ?></span>
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
                <a href="analytics.php">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 6a7.5 7.5 0 107.5 7.5h-7.5V6z" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 10.5H21A7.5 7.5 0 0013.5 3v7.5z" />
                    </svg>
                    <span><?php echo $lang[$language]['analytics']; ?></span>
                </a>
            </li>
            <li class="nav-item">
                <a href="settings.php">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.149.894c.07.424.384.764.78.93.384.164.652.295.75.371.204.156.385.31.545.454.152.149.24.29.29.375l.089.152c.22.424.42.862.6 1.324.18.462.33.92.434 1.39.127.462.209.92.259 1.394.02.182.03.365.03.546 0 .18-.01.362-.03.543-.05.474-.132.932-.259 1.395-.104.47-.254.928-.434 1.39-.18.462-.38.9-.6 1.325-.089.153-.177.3-.29.376-.1.076-.368.207-.75.37-.396.166-.71.506-.78.93l-.15.894c-.09.542-.56.94-1.11.94h-2.594c-.55 0-1.02-.398-1.11-.94l-.148-.894c-.07-.424-.384-.764-.78-.93-.384-.164-.652-.295-.75-.371-.203-.156-.385-.31-.545-.454-.153-.149-.24-.29-.29-.375l-.089-.152c-.22-.424-.42-.862-.6-1.324-.18-.462-.33-.92-.435-1.39-.127-.462-.209-.92-.26-1.394-.02-.182-.03-.365-.03-.546 0-.18.01-.362.03-.543.05-.474.132-.932.26-1.395.103-.47.253-.928.434-1.39.18-.462.38-.9.6-1.325.089-.153.177-.3.29-.376.1-.076.368-.207.75-.37.396-.166.71-.506.78-.93l.149-.894z" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
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
            <div class="header-center">
                <?php echo $lang[$language]['admin_panel']; ?>
            </div>
            <div class="header-right">
                <button onclick="window.location.href='../logout.php'" class="logout-btn"><?php echo $lang[$language]['logout']; ?></button>
            </div>
        </header>

        <main class="main-content">