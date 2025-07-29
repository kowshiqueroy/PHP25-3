<?php

if (!isset($_SESSION['rolename']) || $_SESSION['rolename'] !== 'sradmin') {
    header("Location: ../index.php");
    exit();
}

?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title><?php echo $sitename ?></title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
    <style>

</style>
    <style>
        body { font-family: 'Arial', sans-serif; background: #f8f9fa; }
        @media print {
           
            .content {
                margin-left: -200px;
                padding: 0;
            }
            .no-print {
                display: none;
            }
        }
        .navbar { background:rgb(23, 126, 9); color: white;


    position: -webkit-sticky;
    position: sticky;
    top: 0;
    z-index: 1000;

        
        
        
        
        }
        .menu-box {
            width: 250px; position: fixed; left: -260px; top: 0; height: 100vh; 
            background: #212529; color: white; transition: 0.4s ease-in-out; z-index: 999;
            box-shadow: 3px 0px 10px rgba(0,0,0,0.2);
            overflow-y: auto;
            overflow-x: hidden;
            z-index: 1003;
            
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
    <nav class="navbar navbar-dark d-flex align-items-center justify-content-between px-3 no-print">
        <button class="btn btn-warning" id="menuToggle">☰</button>
        <span class="navbar-brand">Dashboard</span>
        <div class="user-info">
        <span class="text-white small desktop-items"><?php echo $_SESSION['rolename'];?></span>
  
        <button class="btn btn-danger" onclick="window.location.href='../logout.php'">Logout</button>
        </div>
    </nav>

    <div class="menu-box p-3 no-print" id="menuBox">
        <button class="btn btn-secondary w-100" id="closeMenu">Close</button>
        <ul>
        <span class="text-white small"><?php echo $_SESSION['username']." (".$_SESSION['id'].") ".$_SESSION['rolename'];?></span>
        
            <li><a href="index.php"><i class="fas fa-home"></i> Home</a></li>
            <li><a href="users.php"><i class="fas fa-users"></i> Users</a></li>
            <!-- <li><a href="settings.php"><i class="fas fa-cog"></i> Settings</a></li> -->
            <li><a href="orders.php"><i class="fas fa-shopping-cart"></i> Orders</a></li>
            <li><a href="chat.php"><i class="fas fa-comments"></i> Chat</a></li>
            <li><a href="note.php"><i class="fas fa-sticky-note"></i> Note</a></li>
             <li><a href="notice.php"><i class="fas fa-bullhorn"></i> Notice</a></li>
             <li><a href="map.php"><i class="fas fa-map-marker-alt"></i> Map</a></li>
           
        </ul>
    </div>

<script>
        $(document).ready(function () {
            $("#menuToggle").click(function () {
                $("#menuBox").toggleClass("show");
            });
            $("#closeMenu").click(function () {
                $("#menuBox").removeClass("show");
            });
           
            $(".select2").select2({
                theme: "classic"
            });

            $(".select2edit").select2({
              theme: "classic",
                tags: true,
                
            });


        });

           
    </script>

    <div class="container mt-3">



        <style>
            .preloader {
                position: fixed;
                top: 0;
                left: 0;
                width: 100vw;
                height: 100vh;
                background: blur(white);
                display: flex;
                justify-content: center;
                align-items: center;
                z-index: 9999;
            }
            .preloader .spinner {
                width: 150px;
                height: 150px;
                border: 10px solid #f3f3f3;
                border-top: 10px solid #3498db;
                border-radius: 50%;
                animation: spin 0.5s linear infinite;
            }
            @keyframes spin {
                0% { transform: rotate(0deg); }
                100% { transform: rotate(360deg); }
            }
            .spinner {
                transition: transform 0.5s ease-in-out;
            }
        </style>
        <div class="preloader no-print">
            <div class="spinner"></div>
        </div>
        <script>
            var preloader = document.querySelector(".preloader");
            var loadTime = new Date().getTime();
            window.addEventListener("load", function () {
                var currentTime = new Date().getTime();
                var timeTaken = currentTime - loadTime;
                if (timeTaken < 500) {
                    setTimeout(function () {
                        preloader.style.display = "none";
                    }, 500 - timeTaken);
                } else {
                    preloader.style.display = "none";
                }
            });
        </script>





        <div class="content">
           
            <?php
            // $_SESSION['querylist'] = array(
            //     'draft' => '',
            //     'approval' => '',
            //     'delivery' => '',
            //     'back' => '',
            //     'close' => '',
            //     'created_by' => '',
            //     'route_id' => '',
            //     'person_id' => '',
            //     'order_date' => '',
            //     'order_to_date' => '',
            //     'delivery_from_date' => '',
            //     'delivery_to_date' => '',
            // );
            ?>
