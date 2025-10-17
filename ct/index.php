<?php
session_start();
if(isset($_GET['register'])) {
 header("Location: register.php?ref=".$_GET['register']);
  exit;
}
if (isset($_SESSION['user_id'])) {
  header("Location: dashboard.php");
  exit;
}


?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>CivicThinkers – Survey & Earn</title>
  <link rel="icon" href="logo.png" type="image/png" />
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet" />
  <style>
    :root {
      --primary: #845ef7;
      --primary-dark: #5c7cfa;
      --bg: #f1f3f5;
      --white: #ffffff;
      --text: #343a40;
      --muted: #868e96;
      --shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
    }

    * {
      box-sizing: border-box;
      margin: 0;
      padding: 0;
    }

    body {
      font-family: 'Inter', sans-serif;
      background: var(--bg);
      color: var(--text);
      display: flex;
      flex-direction: column;
      min-height: 100vh;
    }

    header {
      text-align: center;
      padding: 2rem 1rem 1rem;
    }

    header img {
      height: 300px;
      margin-bottom: 0.5rem;
    }

    header h1 {
      font-size: 1.75rem;
      font-weight: 600;
    }

    .main {
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      flex: 1;
      padding: 2rem 1rem;
    }

    .hero {
      text-align: center;
      margin-bottom: 2rem;
    }

    .hero h2 {
      font-size: 2rem;
      margin-bottom: 0.5rem;
      color: var(--primary);
    }

    .hero p {
      font-size: 1rem;
      color: var(--muted);
    }

    .form-card {
      background: var(--white);
      padding: 2rem;
      border-radius: 12px;
      box-shadow: var(--shadow);
      width: 100%;
      max-width: 400px;
    }

    .form-card input {
      width: 100%;
      padding: 0.75rem;
      margin-bottom: 1rem;
      border: 1.5px solid #dee2e6;
      border-radius: 8px;
      font-size: 1rem;
    }

    .form-card input:focus {
      border-color: var(--primary);
      outline: none;
    }

    .form-card button {
      width: 100%;
      padding: 0.75rem;
      background: var(--primary);
      color: var(--white);
      border: none;
      border-radius: 8px;
      font-size: 1rem;
      font-weight: 600;
      cursor: pointer;
      transition: background 0.3s ease;
    }

    .form-card button:hover {
      background: var(--primary-dark);
    }

    .footer {
      text-align: center;
      padding: 1rem;
      font-size: 0.875rem;
      color: var(--muted);
    }

    @media (max-width: 600px) {
      .hero h2 {
        font-size: 1.5rem;
      }

      .form-card {
        padding: 1.5rem;
      }
    }
  </style>
</head>
<body>
  <header>
    <img src="logo.png" alt="CivicThinkers Logo" />
    <h1>CivicThinkers</h1>
  </header>

  <div class="main">
    <div class="hero">
      <h2>We make the world a better place</h2>
      <?php if (isset($_GET['error']) && $_GET['error'] != NULL): ?>
        <p style="color: red;">ERROR: <?php echo $_GET['error']; ?></p>
      <?php else: ?>
        <p>Take part in a global survey of the world.</p>
      <?php endif; ?>
    </div>

    <div class="form-card">
      <form id="loginForm" action="login.php" method="POST">
        <input type="text" name="username" placeholder="Username" required />
        <input type="password" name="password" placeholder="Password" required />
        <button type="submit">Login</button>
      </form>
    </div>
  </div>

  <div class="footer">
    &copy; 2025 CivicThinkers. All rights reserved.
  </div>

 
</body>
</html>