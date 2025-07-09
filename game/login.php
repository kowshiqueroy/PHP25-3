<?php
session_start();
include('includes/db.php');
$msg="";
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['login'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT * FROM players WHERE username=? AND is_blocked=0");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        if (password_verify($password, $row['password'])) {
            $_SESSION['user_id'] = $row['id'];
            $_SESSION['username'] = $row['username'];
            $_SESSION['is_admin'] = ($row['username'] === 'admin'); // treat "admin" as admin
            header("Location: dashboard.php");
            exit();
        } else {
            $msg= "Wrong password.";
        }
    } else {
        $msg= "User not found or blocked.";
    }
}



if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['register'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Simple hash and insert
    $hashed = password_hash($password, PASSWORD_DEFAULT);

  $stmt = $conn->prepare("SELECT * FROM players WHERE username=?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $msg= "User already exists.";
    } else {

    $stmt = $conn->prepare("INSERT INTO players (username, password) VALUES (?, ?)");
    $stmt->bind_param("ss", $username, $hashed);

    if ($stmt->execute()) {
        $msg= "Registration successful! <a href='login.php'>Login</a>";
    } else {
        $msg= "Error: " . $stmt->error;
    }

}
}
?>



<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title><?php echo $site_name; ?> | Login & Register</title>

  <!-- Bootstrap + Google Fonts -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@500&display=swap" rel="stylesheet">

  

  <style>
    body.auth-bg {
  background: linear-gradient(135deg, #0f0f0f 0%, #1b1b2f 100%);
  font-family: 'Orbitron', sans-serif;
  color: #f0f0f0;
}

.auth-card {
  background-color: #121212;
  border-radius: 12px;
  transition: transform 0.2s ease;
}

.auth-card:hover {
  transform: scale(1.02);
  box-shadow: 0 0 15px rgba(255,255,255,0.1);
}

.neon-title {
  color: #0dcaf0;
  text-shadow: 0 0 8px #0dcaf0;
}
  </style>
</head>
<body class="auth-bg text-light">

  <div class="container d-flex flex-column align-items-center justify-content-center min-vh-100">
    <h1 class="mb-4 neon-title">🎮 <?php echo $site_name; ?></h1>

    <div class="row w-100 justify-content-center g-4">
      <?php if (isset($msg)): ?>
        <div class="col-md-12">
          <div class="alert alert-<?php echo $msg_type ?? 'info' ?> text-center" role="alert">
            <?php echo isset($_GET['msg']) ?  $_GET['msg']: $msg;  ?>
          </div>
        </div>
      <?php endif; ?>
      <!-- Login -->
      <div class="col-md-5">
        <div class="card auth-card border-info shadow-lg">
          <div class="card-header text-info fw-bold fs-5 text-center">🔐 Login</div>
          <div class="card-body">
            <form method="post" action="login.php">
              <input type="text" name="username" placeholder="Username" class="form-control mb-3" required>
              <input type="password" name="password" placeholder="Password" class="form-control mb-3" required>
              <button type="submit" name="login" class="btn btn-info w-100 fw-bold">Login</button>
            </form>
          </div>
        </div>
      </div>

      <!-- Registration -->
      <div class="col-md-5">
        <div class="card auth-card border-warning shadow-lg">
          <div class="card-header text-warning fw-bold fs-5 text-center">📝 Register</div>
          <div class="card-body">
            <form method="post" action="">
              <input type="text" name="username" placeholder="Username" class="form-control mb-3" required>
              <input type="password" name="password" placeholder="Password" class="form-control mb-3" required>
              <button type="submit" name="register" class="btn btn-warning w-100 fw-bold">Register</button>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>

</body>
</html>