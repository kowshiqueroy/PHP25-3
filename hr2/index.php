<?php
session_start();

require_once 'config.php'; // Assumes config.php sets up $conn

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username === '' || $password === '') {
        $error = "Please enter both username and password.";
    } else {
        // Prepare statement to prevent SQL injection
        $stmt = $conn->prepare('SELECT password FROM user WHERE username = ?');
        $stmt->bind_param('s', $username);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows === 1) {
            $stmt->bind_result($hashed_password);
            $stmt->fetch();

            // If the password in DB is not hashed, hash it now for the user
            if (password_needs_rehash($hashed_password, PASSWORD_DEFAULT)) {
                $new_hash = password_hash($password, PASSWORD_DEFAULT);
                $update = $conn->prepare('UPDATE user SET password = ? WHERE username = ?');
                $update->bind_param('ss', $new_hash, $username);
                $update->execute();
                $update->close();
                $hashed_password = $new_hash;
            }

            if (password_verify($password, $hashed_password)) {
                $_SESSION['username'] = $username;
                header('Location: staffs.php');
                exit;
            } else {
                $error = "Invalid credentials.";
            }
        } else {
            $error = "Invalid credentials.";
        }
        $stmt->close();
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Login</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
        body {
            background: #f4f6fb;
            font-family: 'Segoe UI', Arial, sans-serif;
            margin: 0;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .login-container {
            background: #fff;
            padding: 2.5rem 2rem;
            border-radius: 12px;
            box-shadow: 0 4px 24px rgba(0,0,0,0.08);
            width: 100%;
            max-width: 350px;
        }
        .login-container h2 {
            margin-bottom: 1.5rem;
            color: #22223b;
            text-align: center;
            font-weight: 600;
        }
        .form-group {
            margin-bottom: 1.2rem;
        }
        label {
            display: block;
            margin-bottom: 0.4rem;
            color: #4a4e69;
            font-size: 1rem;
        }
        input[type="text"], input[type="password"] {
            width: 100%;
            padding: 0.7rem;
            border: 1px solid #c9c9c9;
            border-radius: 6px;
            font-size: 1rem;
            background: #f8f9fa;
            transition: border 0.2s;
        }
        input[type="text"]:focus, input[type="password"]:focus {
            border-color: #5f6fff;
            outline: none;
        }
        .login-btn {
            width: 100%;
            padding: 0.8rem;
            background: linear-gradient(90deg, #5f6fff 0%, #3a3dff 100%);
            color: #fff;
            border: none;
            border-radius: 6px;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.2s;
        }
        .login-btn:hover {
            background: linear-gradient(90deg, #3a3dff 0%, #5f6fff 100%);
        }
        .error-message {
            color: #e63946;
            background: #ffe5e9;
            border: 1px solid #e63946;
            border-radius: 6px;
            padding: 0.7rem;
            margin-bottom: 1rem;
            text-align: center;
        }
    </style>
</head>
<body>
<div class="login-container">
    <h2>Login</h2>
    <?php if (!empty($error)) echo "<div class='error-message'>$error</div>"; ?>
    <form method="post" autocomplete="off">
        <div class="form-group">
            <label for="username">Username</label>
            <input type="text" name="username" id="username" required autofocus>
        </div>
        <div class="form-group">
            <label for="password">Password</label>
            <input type="password" name="password" id="password" required>
        </div>
        <button class="login-btn" type="submit">Login</button>
    </form>
</div>
</body>
</html>