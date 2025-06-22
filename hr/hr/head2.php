<?php  

include_once "head1.php";

    $currentPage = basename($_SERVER['PHP_SELF']);
    if(isset($_SESSION['cp']) && $_SESSION['cp'] == true && $currentPage != "profile.php")
    {
        echo '<script>location.replace("profile.php");</script>';
    }
    

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EOvijat</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="apple-touch-icon" sizes="180x180" href="img/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="img/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="img/favicon-16x16.png">
    <link rel="manifest" href="img/site.webmanifest">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/jquery.dataTables.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.2.2/css/buttons.dataTables.min.css">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.2.2/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.2.2/js/buttons.flash.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.2.2/js/buttons.html5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.2.2/js/buttons.print.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body {
            margin: 0;
            font-family: Arial, sans-serif;
        }
        header {
            position: sticky;
            top: 0;
            display: flex;
            justify-content: space-between;
            align-items: center;
            background-color: #333;
            color: white;
            padding: 10px 20px;
            z-index: 500;
        }
        header h1 {
            margin: 0;
        }
        .menu-icon, .logout-icon {
            cursor: pointer;
            width: 30px;
        }
        .sidebar {
            position: fixed;
            top: 50px;
            left: -200px;
            width: 200px;
            height: 100%;
            background-color: #444;
            color: white;
            padding-top: 20px;
            transition: left 0.3s;
            z-index: 1000;
            font-size: 20px;
        }
        .sidebar.open {
            left: 0;
        }
        .sidebar nav ul {
            list-style: none;
            padding: 0;
        }
        .sidebar nav ul li {
            padding: 10px;
        }
        .sidebar nav ul li a {
            color: white;
            text-decoration: none;
        }
        main {
            margin:0;
            padding: 20px;
        }

        .Print{
        display: none;

    }

    @media print {

        header,
        aside,
        .noPrint {
            display: none;
        }
        .Print{
            display: block;
        }
    }
    </style>
</head>
<body>
    <header>
        <div class="menu-icon">☰</div>
        <h1>EOvijat</h1>
        <div class="logout-icon" onclick="window.location.href='logout.php'"><i class="fas fa-power-off"></i></div>
    </header>
    <aside class="sidebar">
        <nav>
            <ul>
            <li><a href="index.php"><i style="width: 30px;" class="fas fa-tv"></i> Dashboard</li>
            <li><a href="person.php"><i style="width: 30px;" class="fas fa-address-card"></i> Person</li>
            <li><a href="profile.php"><i style="width: 30px;" class="fas fa-user-secret"></i> Profile</a></li>
            </ul>
        </nav>
    </aside>
    <main>
       

