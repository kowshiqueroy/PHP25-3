<?php
session_start();

require_once "connection.php";

$sql = "CREATE TABLE IF NOT EXISTS users (
    id INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL
)";
if ($conn->query($sql) === TRUE) {
    // echo "Table users created successfully";
} else {
    echo "Error creating table: " . $conn->error;
}

$sql = "CREATE TABLE IF NOT EXISTS clients (
    id INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    job VARCHAR(255) NOT NULL,
    star INT(1) NOT NULL,
    comment TEXT NOT NULL
)";
if ($conn->query($sql) === TRUE) {
    // echo "Table clients created successfully";
} else {
    echo "Error creating table: " . $conn->error;
}


if ($conn->query("SELECT COUNT(*) FROM users")->fetch_array()[0] == 0) {
    $stmt = $conn->prepare("INSERT INTO users (username, password) VALUES (?, ?)");
    $stmt->bind_param("ss", $username, $password);
    $username = 'kowshiqueroy';
    $password = password_hash('5877', PASSWORD_DEFAULT);
    $stmt->execute();
    $stmt->close();
}

$sql = "CREATE TABLE IF NOT EXISTS banner (
    id INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
    link VARCHAR(255) NOT NULL
)";
if ($conn->query($sql) === TRUE) {
    // echo "Table banner created successfully";
} else {
    echo "Error creating table: " . $conn->error;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Hardcoded admin credentials (for demo purposes)
   

    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    $stmt = $conn->prepare("SELECT id, username, password FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    $stmt->close();
    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        if (password_verify($password, $user['password'])) {
            $_SESSION['admin_logged_in'] = true;
            $_SESSION['admin_user_id'] = $user['id'];
            header('Location: dashboard.php');
            exit;
        }
    }
    $error = 'Invalid username or password.';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Login</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
        :root {
            --primary: #4f8cff;
            --primary-light: #74ebd5;
            --secondary: #ACB6E5;
            --danger: #e74c3c;
            --bg: #e0e7ef;
            --white: #fff;
            --glass: rgba(255,255,255,0.25);
            --shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.18);
        }
        html, body {
            height: 100%;
            margin: 0;
            padding: 0;
        }
        body {
            min-height: 100vh;
            background: linear-gradient(135deg, var(--primary-light) 0%, var(--secondary) 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', Arial, sans-serif;
        }
        .login-container {
            width: 100%;
            max-width: 370px;
            background: var(--glass);
            border-radius: 22px;
            box-shadow: var(--shadow);
            padding: 44px 32px 32px 32px;
            display: flex;
            flex-direction: column;
            gap: 18px;
            backdrop-filter: blur(16px) saturate(180%);
            border: 1.5px solid rgba(255,255,255,0.18);
            animation: fadeIn 0.7s;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(30px);}
            to { opacity: 1; transform: none;}
        }
        .logo {
            width: 60px;
            height: 60px;
            margin: 0 auto 10px auto;
            background: linear-gradient(135deg, var(--primary) 60%, var(--secondary) 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2.2em;
            color: var(--white);
            box-shadow: 0 2px 12px rgba(79, 140, 255, 0.13);
            font-weight: bold;
            letter-spacing: 2px;
        }
        .login-container h2 {
            text-align: center;
            margin-bottom: 10px;
            color: var(--primary);
            font-weight: 700;
            letter-spacing: 1px;
        }
        .error {
            color: var(--danger);
            background: #fdecea;
            border: 1px solid #f5c6cb;
            border-radius: 5px;
            padding: 10px;
            text-align: center;
            margin-bottom: 10px;
            font-size: 1em;
        }
        label {
            font-size: 1em;
            color: #444;
            margin-bottom: 4px;
            font-weight: 500;
        }
        .input-group {
            display: flex;
            flex-direction: column;
            gap: 4px;
            position: relative;
        }
        input[type="text"], input[type="password"] {
            width: 85%;
            padding: 12px 44px 12px 14px;
            border: 1.5px solid #e0e6ed;
            border-radius: 7px;
            background: rgba(255,255,255,0.7);
            font-size: 1em;
            transition: border 0.2s, box-shadow 0.2s;
            margin-bottom: 8px;
        }
        input[type="text"]:focus, input[type="password"]:focus {
            border: 1.5px solid var(--primary);
            outline: none;
            background: #fff;
            box-shadow: 0 2px 8px rgba(79, 140, 255, 0.07);
        }
        .toggle-password {
            position: absolute;
            right: 14px;
            top: 36px;
            cursor: pointer;
            color: #888;
            font-size: 1.1em;
        }
        input[type="submit"] {
            width: 100%;
            padding: 12px;
            background: linear-gradient(90deg, var(--primary) 0%, var(--secondary) 100%);
            color: var(--white);
            border: none;
            border-radius: 7px;
            font-size: 1.1em;
            font-weight: bold;
            cursor: pointer;
            box-shadow: 0 2px 8px rgba(79, 140, 255, 0.12);
            transition: background 0.2s, box-shadow 0.2s;
            letter-spacing: 0.5px;
        }
        input[type="submit"]:hover {
            background: linear-gradient(90deg, var(--secondary) 0%, var(--primary) 100%);
            box-shadow: 0 4px 16px rgba(79, 140, 255, 0.18);
        }
        @media (max-width: 600px) {
            .login-container {
                padding: 24px 8px 18px 8px;
                max-width: 98vw;
            }
            body {
                padding: 0 2px;
            }
        }
        @media (max-width: 400px) {
            .login-container {
                padding: 12px 2px 10px 2px;
            }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="logo">
            <img src="img/logo.png" alt="Logo" style="width:48px;height:48px;border-radius:50%;object-fit:cover;">
        </div>
        <h2>Admin Login</h2>
        <?php if ($error): ?>
            <div class="error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        <form method="post" action="">
            <div class="input-group">
                <label for="username">Username</label>
                <input type="text" name="username" id="username" required autofocus autocomplete="username">
            </div>
            <div class="input-group">
                <label for="password">Password</label>
                <input type="password" name="password" id="password" required autocomplete="current-password">
                <span class="toggle-password" onclick="togglePassword()" title="Show/Hide Password">&#128065;</span>
            </div>
            <input type="submit" value="Login">
        </form>
    </div>
    <script>
        function togglePassword() {
            const pwd = document.getElementById('password');
            pwd.type = pwd.type === 'password' ? 'text' : 'password';
        }
    </script>
</body>
</html>