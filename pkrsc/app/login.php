<?php
session_start();
require 'db.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['role'] = $user['role'];
        $stmt = $pdo->prepare("UPDATE users SET last_login = NOW() WHERE id = ?");
        $stmt->execute([$user['id']]);
        header("Location: index.php");
        exit;
    } else {
        $error = "Invalid credentials.";
    }
}

$stmt = $pdo->query("SELECT school_name, school_logo FROM settings WHERE id=1");
$settings = $stmt->fetch();
$school_name = $settings['school_name'] ?? 'EduResult Pro';
$school_logo = $settings['school_logo'] ?? 'uploads/logo.png';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?php echo $school_name ?> - Login</title>
  <link rel="icon" type="image/x-icon" href="<?php echo $school_logo; ?>">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
  <style>
    body {
      background: linear-gradient(135deg, #1e293b, #0f172a);
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      font-family: 'Segoe UI', sans-serif;
      overflow: hidden;
    }
    .login-card {
      width: 100%;
      max-width: 400px;
      border-radius: 16px;
      background-color: #ffffff;
      box-shadow: 0 10px 25px rgba(0,0,0,0.2);
      padding: 2rem;
      animation: fadeIn 1s ease-in-out;
    }
    @keyframes fadeIn {
      from {opacity: 0; transform: translateY(20px);}
      to {opacity: 1; transform: translateY(0);}
    }
    .login-card img {
      border-radius: 50%;
      margin-bottom: 1rem;
      border: 3px solid #1e293b;
    }
    .form-control {
      border-radius: 10px;
      padding-left: 2.5rem;
    }
    .input-group-text {
      background: transparent;
      border: none;
      position: absolute;
      left: 12px;
      top: 50%;
      transform: translateY(-50%);
      color: #6b7280;
    }
    .btn-gradient {
      background: linear-gradient(90deg, #1e293b, #0f172a);
      color: #fff;
      border-radius: 10px;
      font-weight: 600;
      transition: all 0.3s ease;
    }
    .btn-gradient:hover {
      background: linear-gradient(90deg, #0f172a, #1e293b);
      transform: scale(1.02);
    }
    .developer {
      font-size: 0.85rem;
      margin-top: 1rem;
      color: #6b7280;
    }
    .developer a {
      color: #1e293b;
      text-decoration: none;
      font-weight: 500;
    }
    .toggle-password {
      position: absolute;
      right: 12px;
      top: 50%;
      transform: translateY(-50%);
      cursor: pointer;
      color: #6b7280;
    }
  </style>
</head>
<body>
  <div class="login-card">
    <div class="text-center">
      <img src="<?php echo $school_logo; ?>" alt="School Logo" style="max-width: 90px; max-height: 90px;">
      <h4 class="fw-bold text-dark"><?php echo $school_name; ?></h4>
      <p class="text-muted">Welcome back! Please log in to continue.</p>
    </div>
    <?php if($error): ?>
      <div class="alert alert-danger mt-3 py-2"><?php echo $error; ?></div>
    <?php endif; ?>
    <form method="POST" class="mt-3">
      <div class="mb-3 position-relative">
        <span class="input-group-text"><i class="fa-solid fa-user"></i></span>
        <input type="text" name="username" class="form-control" placeholder="Username" required>
      </div>
      <div class="mb-3 position-relative">
        <span class="input-group-text"><i class="fa-solid fa-lock"></i></span>
        <input type="password" name="password" class="form-control" id="password" placeholder="Password" required>
        <span class="toggle-password" onclick="togglePassword()"><i class="fa-solid fa-eye"></i></span>
      </div>
      <button type="submit" class="btn btn-gradient w-100"><i class="fa-solid fa-right-to-bracket me-2"></i>Log In</button>
    </form>
    <div class="developer text-center">
      <hr>
      <p>Developed by <a href="mailto:kowshiqueroy@gmail.com">Kowshique Roy</a>  <i class="fa-solid fa-phone"></i> <a href="tel:01632950179">01632950179</a></p>
    </div>
  </div>

  <script>
    function togglePassword() {
      const passwordField = document.getElementById("password");
      const toggleIcon = document.querySelector(".toggle-password i");
      if (passwordField.type === "password") {
        passwordField.type = "text";
        toggleIcon.classList.remove("fa-eye");
        toggleIcon.classList.add("fa-eye-slash");
      } else {
        passwordField.type = "password";
        toggleIcon.classList.remove("fa-eye-slash");
        toggleIcon.classList.add("fa-eye");
      }
    }
  </script>
</body>
</html>