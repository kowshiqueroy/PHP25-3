<?php
session_start();
if (!isset($_SESSION['user_id'])) {
  header("Location: index.php");
  exit;
}
?>
<!DOCTYPE html>
<html lang="="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>CivicThinkers</title>
  <link rel="icon" href="logo.png" type="image/png" />
  <style>
    :root {
      --blue: #4285F4;
      --red: #EA4335;
      --yellow: #FBBC05;
      --green: #34A853;
      --bg: #f9f9f9;
      --text: #333;
    }

    body {
      margin: 0;
      font-family: 'Segoe UI', sans-serif;
      background: var(--bg);
      display: flex;
      flex-direction: column;
      height: 100vh;
      color: var(--text);
    }

    header {
      background: var(--blue);
      color: white;
      padding: 15px 20px;
      text-align: center;
      box-shadow: 0 4px 8px rgba(0,0,0,0.1);
    }

    header img {
      height: 40px;
      border-radius: 50%;
      vertical-align: middle;
    }

    header h1 {
      display: inline-block;
      margin: 0 10px;
      font-size: 24px;
      font-weight: 600;
      vertical-align: middle;
    }

    main {
      flex: 1;
      overflow-y: auto;
      padding: 20px;
    }

    section {
      margin-bottom: 25px;
      background: white;
      border-radius: 12px;
      padding: 15px;
      box-shadow: 0 4px 12px rgba(0,0,0,0.05);
    }

    h2 {
      margin-top: 0;
      font-size: 20px;
      color: var(--blue);
    }

    input[type="text"],
    input[type="email"],
    input[type="number"],
    input[type="password"]
     {
      width: 90%;
      padding: 12px;
      margin: 10px 0;
      border: 2px solid var(--blue);
      border-radius: 8px;
      font-size: 16px;
    }
    select {
      width: 100%;
      padding: 12px;
      margin: 10px 0;
      border: 2px solid var(--blue);
      border-radius: 8px;
      font-size: 16px;
    }

    .form-group {
      margin: 10px 0;
    }

    label {
      display: block;
      margin-bottom: 5px;
      font-weight: 500;
    }

    .radio-group, .checkbox-group {
      display: flex;
      gap: 15px;
      margin: 10px 0;
    }

    button {
      width: 100%;
      padding: 12px;
      margin-top: 10px;
      background: var(--green);
      color: white;
      font-weight: bold;
      border: none;
      border-radius: 8px;
      font-size: 16px;
      cursor: pointer;
      transition: transform 0.2s;
    }

    button:hover {
      transform: scale(1.03);
    }

    table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 10px;
    }

    th, td {
      padding: 10px;
      border-bottom: 1px solid #eee;
      text-align: left;
    }

    th {
      background: var(--yellow);
      color: black;
    }

    .grid {
      display: grid;
      grid-template-columns: repeat(2, 1fr);
      gap: 15px;
      margin-top: 10px;
    }

    .grid-item {
      background: linear-gradient(135deg, #4285F4, #EA4335, #FBBC05, #34A853);
      color: white;
      text-align: center;
      padding: 20px;
      border-radius: 12px;
      font-weight: bold;
      cursor: pointer;
      transition: transform 0.2s, box-shadow 0.2s;
    }

    .grid-item:hover {
      transform: scale(1.05);
      box-shadow: 0 6px 12px rgba(0,0,0,0.1);
    }

    .grid-item i {
      font-size: 28px;
      display: block;
      margin-bottom: 8px;
    }

    nav {
      background: var(--blue);
      color: white;
      display: flex;
      justify-content: space-around;
      padding: 12px 0;
      position: sticky;
      bottom: 0;
      border-top: 2px solid var(--green);
    }

    nav div {
      text-align: center;
      font-size: 14px;
      cursor: pointer;
      position: relative;
    }

    nav div:hover {
      color: var(--yellow);
    }

    .dropdown {
      position: absolute;
      bottom: 40px;
      left: 50%;
      transform: translateX(-50%);
      background: white;
      color: var(--text);
      border-radius: 8px;
      box-shadow: 0 4px 12px rgba(0,0,0,0.1);
      display: none;
      flex-direction: column;
      min-width: 120px;
      z-index: 10;
    }

    .dropdown a {
      padding: 10px;
      text-align: center;
      text-decoration: none;
      color: var(--text);
      border-bottom: 1px solid #eee;
    }

    .dropdown a:last-child {
      border-bottom: none;
    }

    .dropdown a:hover {
      background: var(--bg);
    }
  </style>
</head>
<body>
  <header>
    <img src="logo.png" />
    <h1>CivicThinkers</h1>
  </header>


  <div class="loading">
    <img src="logo.png" style="width: 400px; height: 400px;" />
  </div>

  <style>
    .loading {
      position: fixed;
      opacity: 0.5;
      top: 50%;
      left: 50%;
      transform: translate(-50%, -50%);
      display: flex;
      align-items: center;
      justify-content: center;
      z-index: 10;
    }
  </style>

  <script>
    let timeout = null;
    window.addEventListener('load', function() {
      timeout = setTimeout(function() {
        document.querySelector('.loading').style.display = 'none';
        document.querySelector('main').style.display = 'block';
      }, 300);
    });

  
  </script>
  <main>