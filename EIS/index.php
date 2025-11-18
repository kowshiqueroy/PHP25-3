<?php
include_once 'config.php'; // Include your database connection and configuration file
?>
<?php
if (isset($_POST['login'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Prepare and execute the query to fetch user details
    $stmt = $conn->prepare("SELECT * FROM user WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        // Verify the password
        if (password_verify($password, $user['password']) && $user['status'] == 1) {
            // Password is correct, start a session
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];
            // Redirect to dashboard or home page
            header("Location:". $user['role']);
            exit();
        } else {
            $error_message = "Invalid username or password or inactive account.";
        }
    } else {
        $error_message = "Invalid username or password.";
    }
    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $website_name; ?></title>
    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: system-ui, -apple-system, sans-serif;
            background-color: #f3f4f6;
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100vh;
        }

        .login-card {
            background: white;
            padding: 2.5rem;
            border-radius: 12px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 360px;
            text-align: center;
        }

        .title {
            margin: 0;
            font-size: 1.8rem;
            color: #6366f1; /* Indigo */
            font-weight: 800;
        }

        .message {
            color: #6b7280;
            margin-top: 0.5rem;
            margin-bottom: 2rem;
            font-size: 0.95rem;
        }

        input {
            width: 100%;
            padding: 0.8rem;
            margin-bottom: 1rem;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            font-size: 1rem;
            box-sizing: border-box; /* Ensures padding doesn't break width */
            outline: none;
            transition: border-color 0.2s;
        }

        input:focus {
            border-color: #6366f1;
        }

        .btn {
            width: 100%;
            padding: 0.8rem;
            background-color: #6366f1;
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: background-color 0.2s;
        }

        .btn:hover {
            background-color: #4f46e5;
        }

        .developer-credit {
            margin-top: 2rem;
            font-size: 0.75rem;
            color: #9ca3af;
            font-family: monospace;
        }
    </style>
</head>
<body>

    <div class="login-card">
        <h1 class="title"><?php echo $website_name; ?></h1>
        
        <?php if (!empty($error_message)) : ?>
            <p class="message" style="color: red;"><?php echo $error_message; ?></p>
        <?php else : ?>
            <p class="message">Enterprise Information System Login</p>
        <?php endif; ?>

        <form id="simpleLogin" method="POST" >
            <input type="text" name="username" placeholder="Username" required>
            <input type="password" name= "password" placeholder="Password" required>
            <button type="submit" name="login" class="btn">Sign In</button> 
        </form>

        <div class="developer-credit">
            Developer: kowshiqueroy@gmail.com
        </div>
    </div>



</body>
</html>