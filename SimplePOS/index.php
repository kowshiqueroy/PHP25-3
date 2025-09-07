<?php

include 'config.php';

?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>SimplePOS Login</title>
  <link rel="icon" href="favicon.ico" type="image/x-icon" />
  <style>
    * {
      box-sizing: border-box;
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }

    body {
      margin: 0;
      padding: 0;
      background: linear-gradient(135deg, #2c3e50, #3498db);
      display: flex;
      justify-content: center;
      align-items: center;
      height: 100vh;
    }

    .login-container {
      background-color: #fff;
      padding: 2rem;
      border-radius: 12px;
      box-shadow: 0 8px 20px rgba(0, 0, 0, 0.2);
      width: 100%;
      max-width: 400px;
      text-align: center;
    }

    .login-container h1 {
      margin-bottom: 1.5rem;
      color: #2c3e50;
    }

    .input-group {
      margin-bottom: 1rem;
      text-align: left;
    }

    .input-group label {
      display: block;
      margin-bottom: 0.5rem;
      font-weight: 600;
    }

    .input-group input {
      width: 100%;
      padding: 0.75rem;
      border: 1px solid #ccc;
      border-radius: 8px;
      transition: border-color 0.3s;
    }

    .input-group input:focus {
      border-color: #3498db;
      outline: none;
    }

    .login-btn {
      background-color: #3498db;
      color: white;
      border: none;
      padding: 0.75rem 2rem;
      border-radius: 8px;
      cursor: pointer;
      font-size: 1rem;
      transition: background-color 0.3s;
    }

    .login-btn:hover {
      background-color: #2980b9;
    }

    .message {
      margin-top: 1rem;
      font-size: 0.9rem;
      color: red;
      display: none;
    }
  </style>
</head>
<body>
  <div class="login-container">
    <h1>SimplePOS</h1>
    <form id="loginForm" method="POST" >
      <div class="input-group">
        <label for="username">Username</label>
        <input type="text" id="username" name="username" required />
      </div>
      <div class="input-group">
        <label for="password">Password</label>
        <input type="password" id="password" name="password" required />
      </div>
      <button type="submit" class="login-btn">Login</button>
  
    </form>
  </div>

 
</body>
</html>