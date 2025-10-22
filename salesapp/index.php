<?php
require_once 'connection.php';
if (isset($_SESSION['user_id'])) {
    header("Location: ".$_SESSION['role']);
    exit();
}
$msg='';
if (isset($_POST['login'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT * FROM user WHERE username = ? AND status = 1");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 1) {
        $user = $result->fetch_assoc();
        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];
            header("Location: ".$user['role']);
            exit();
        }
        else {
            $msg = "Invalid password.";
        }
    } else {
        $msg = "Invalid username.";
    }
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Glassy Login</title>
  <style>
    * {
      box-sizing: border-box;
    }

    body {
      margin: 0;
      padding: 0;
      font-family: 'Segoe UI', sans-serif;
      background: linear-gradient(135deg, #74ebd5, #ACB6E5);
      height: 100vh;
      display: flex;
      justify-content: center;
      align-items: center;
    }

    .login-container {
      background: rgba(255, 255, 255, 0.15);
      backdrop-filter: blur(10px);
      -webkit-backdrop-filter: blur(10px);
      border-radius: 16px;
      padding: 2rem;
      width: 90%;
      max-width: 360px;
      box-shadow: 0 8px 32px rgba(0, 0, 0, 0.2);
      color: #fff;
    }

    .login-container h2 {
      text-align: center;
      margin-bottom: 1.5rem;
      font-weight: 600;
    }

    .form-group {
      margin-bottom: 1rem;
    }

    .form-group label {
      display: block;
      margin-bottom: 0.5rem;
      font-size: 0.9rem;
    }

    .form-group input {
      width: 100%;
      padding: 0.6rem;
      border: none;
      border-radius: 8px;
      background: rgba(255, 255, 255, 0.2);
      color: #fff;
      font-size: 1rem;
    }

    .form-group input::placeholder {
      color: #eee;
    }

    .login-btn {
      width: 100%;
      padding: 0.7rem;
      border: none;
      border-radius: 8px;
      background-color: rgba(255, 255, 255, 0.3);
      color: #fff;
      font-weight: bold;
      font-size: 1rem;
      cursor: pointer;
      transition: background-color 0.3s ease;
    }

    .login-btn:hover {
      background-color: rgba(255, 255, 255, 0.5);
    }

    .message-box {
      margin-top: 1rem;
      text-align: center;
      font-size: 0.9rem;
      min-height: 1.2rem;
      color: #ff000dff;
    }
  </style>
</head>
<body>
  <div class="login-container">
    <h2>Welcome Back</h2>
    <form action="" method="post">
    <div class="form-group">
      <label for="username">Username</label>
      <input type="text" name="username" id="username" placeholder="Enter username" />
    </div>
    <div class="form-group">
      <label for="password">Password</label>
      <input type="password" name="password" id="password" placeholder="Enter password" />
    </div>
    <button class="login-btn" name="login" type="submit">Login</button>
    </form>

    <div class="message-box" id="messageBox"><?php echo $msg; ?></div>
  </div>


</body>
</html>