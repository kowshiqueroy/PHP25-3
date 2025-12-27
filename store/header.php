<?php require_once 'config.php'; checkAuth(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Inventory Manager</title>
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="style.css">
    <link rel="manifest" href="manifest.json">
</head>
<body>
<div class="top-bar">
    <div class="logo">Company Inventory</div>
    
    <button id="syncBtn" onclick="syncOfflineData()" style="display:none; background: green; color:white; border:none; padding:8px 15px; border-radius:4px; margin-left:auto; margin-right:10px; cursor:pointer; font-weight:bold;">
      🔄  Offline (<span id="syncCount"></span>)
    </button>

    <div class="user-info">
        
        <button onclick="window.location.href='login.php?logout=1'" 
        style="display:block; background: red; color:white; border:none; padding:8px 15px; border-radius:4px; margin-left:auto; margin-right:10px;
         cursor:pointer; font-weight:bold;"><?= ucfirst($_SESSION['username'] ?? 'User') ?> Logout</button>
    </div>
</div>
<div class="container"></div>