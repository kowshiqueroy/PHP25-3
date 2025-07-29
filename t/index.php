<?php
// This is a simple login page for QC Damage
include_once('config.php');
$msg="";
if (isset($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit;
}

if (isset($_POST['username']) && isset($_POST['password'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $sql = "SELECT * FROM users WHERE username = ? and status='active'";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        if (password_verify($password, $row['password'])) {
            $_SESSION['user_id'] = $row['id'];
            header('Location: dashboard.php');
            exit;
        }
    }
    $msg= "Invalid username or password.";
}

?>



<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background: linear-gradient(to right, #6a11cb, #2575fc);
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }
        .login-box {
            background: rgba(255, 255, 255, 0.9);
            padding: 40px;
            border-radius: 12px;
            box-shadow: 0 15px 25px rgba(0, 0, 0, 0.3);
            width: 360px;
            text-align: center;
        }
        .login-box h2 {
            margin-bottom: 30px;
            color: #333;
        }
        .user-box {
            position: relative;
            margin-bottom: 30px;
        }
        .user-box input {
            width: 100%;
            padding: 10px;
            background: transparent;
            border: none;
            border-bottom: 2px solid #333;
            outline: none;
            color: #333;
            font-size: 16px;
        }
        .user-box label {
            position: absolute;
            top: 0;
            left: 0;
            padding: -80px 0;
            font-size: 10px;
            color: #333;
            pointer-events: none;
            transition: 0.5s;
        }
        .user-box input:focus ~ label,
        .user-box input:valid ~ label {
            top: -20px;
            left: 0;
            color: #2196F3;
            font-size: 12px;
        }
        button {
            background: #2196F3;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            color: white;
            cursor: pointer;
            font-size: 16px;
            transition: background 0.3s;
        }
        button:hover {
            background: #1e87f0;
        }
        h3 {
            color: red;
            margin-bottom: 20px;
            animation: shake 0.5s ease-in-out;
        }
        
        @keyframes shake {
            0% { transform: translate(0, 0); }
            25% { transform: translate(-2px, 0); }
            50% { transform: translate(2px, 0); }
            75% { transform: translate(-2px, 0); }
            100% { transform: translate(0, 0); }
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="login-box">
            <h2>QC Damage Login</h2>
            <h3><?php echo $msg; ?></h3>
            <form action="index.php" method="post">
                <div class="user-box">
                    <input type="text" name="username" required>
                    <label>Username</label>
                </div>
                <div class="user-box">
                    <input type="password" name="password" required>
                    <label>Password</label>
                </div>
                <button type="submit"><i class="fas fa-sign-in-alt"></i> Login</button>
            </form>
        </div>
    </div>
    <script>
        // Add any JavaScript here if needed
    </script>
</body>

</html>

