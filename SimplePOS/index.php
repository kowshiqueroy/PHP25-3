<?php

include 'config.php';

if (isset($_SESSION['role'])) {
    header("Location: {$_SESSION['role']}/");
    exit;
}

if (isset($_POST['username']) && isset($_POST['password'])) {
   setcookie('username', $_POST['username'], time() + (86400 * 30), '/');
    $username = $_POST['username'];
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT id, username, role, password FROM users WHERE username = ? AND blocked = 0");
    if ($stmt) {
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result && $result->num_rows === 1) {
            $user = $result->fetch_assoc();
              
              
            if (password_verify($_POST['password'], $user['password'])) {

                // Start session and set session variables
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['role'] = $user['role'];

                // Redirect to dashboard or main page
                header("Location: " . ($user['role'] ? '1/' : '0/'));
                exit();
            } else {
                $msg = "Invalid credentials or user is blocked.";
                echo "<script>window.location.href='index.php?msg=".urlencode($msg)."';</script>";
            }
        } else {
            $msg = "No user found.";
            echo "<script>window.location.href='index.php?msg=".urlencode($msg)."';</script>";
        }

        $stmt->close();
    } else {
        $msg = "Database error. Please try again later.";
        echo "<script>window.location.href='index.php?msg=".urlencode($msg)."';</script>";
    }
}


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
    <div style="color: red;" id="msg"><?php echo isset($_GET['msg']) ? htmlspecialchars($_GET['msg']) : ''; ?></div>
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

    <?php
    if (isset($_COOKIE['username'])) {
      echo '<script>document.getElementById("username").value = "'.htmlspecialchars($_COOKIE['username']).'";</script>';
    }
    ?>

  
</body>
</html>