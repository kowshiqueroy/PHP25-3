<?php
session_start();
require_once 'config.php';

$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    if (isset($_POST['register'])) {
        // Register user
        $password_hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("INSERT INTO users (username, password_hash) VALUES (?, ?)");
        $stmt->bind_param("ss", $username, $password_hash);
        if ($stmt->execute()) {
            $message = "Registration successful! Please log in.";
        } else {
            $message = "Registration failed. Username might already exist.";
        }
        $stmt->close();
    } elseif (isset($_POST['login'])) {
        // Login user
        $stmt = $conn->prepare("SELECT id, password_hash FROM users WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows === 1) {
            $row = $result->fetch_assoc();
            if (password_verify($password, $row['password_hash'])) {
                $_SESSION['user_id'] = $row['id'];
                $_SESSION['username'] = $username;
                header('Location: index.html');
                exit();
            } else {
                $message = "Invalid password.";
            }
        } else {
            $message = "User not found.";
        }
        $stmt->close();
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login / Register - Simple AI</title>
    <link rel="stylesheet" href="style.css">
    <style>
        body {
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
        }
        #login-container {
            background-color: #2a2a2a;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.5);
            width: 300px;
            text-align: center;
        }
        #login-container h2 {
            color: #d4d4d4;
            margin-bottom: 20px;
        }
        #login-container input[type="text"],
        #login-container input[type="password"] {
            width: calc(100% - 20px);
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #555;
            border-radius: 4px;
            background-color: #3a3a3a;
            color: #d4d4d4;
        }
        #login-container button {
            background-color: #569cd6;
            color: white;
            padding: 10px 15px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            margin: 5px;
        }
        #login-container button:hover {
            background-color: #4a8acb;
        }
        #message {
            color: #e0b050;
            margin-top: 15px;
        }
    </style>
</head>
<body>
    <div id="login-container">
        <h2>Simple AI</h2>
        <form method="POST" action="login.php">
            <input type="text" name="username" placeholder="Username" required>
            <input type="password" name="password" placeholder="Password" required>
            <button type="submit" name="login">Login</button>
            <button type="submit" name="register">Register</button>
        </form>
        <?php if ($message): ?>
            <p id="message"><?= $message ?></p>
        <?php endif; ?>
    </div>
</body>
</html>