<?php
require_once 'config.php';
if(isset($_GET['logout'])) { session_destroy(); header("Location: login.php"); exit; }
if($_SERVER['REQUEST_METHOD']=='POST'){
    $u = $_POST['username'];
    $stmt = $pdo->prepare("SELECT * FROM users WHERE username=?");
    $stmt->execute([$u]);
    $user = $stmt->fetch();
    if($user && password_verify($_POST['password'], $user['password'])){
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['role'] = $user['role'];
        header("Location: index.php");
    } else { $error = "Invalid credentials"; }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
    <title>Login - Store App</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="style.css">
    
    <style>
        body {
            background: #f1f5f9; /* Slate 100 */
            background-image: linear-gradient(135deg, #f1f5f9 0%, #e2e8f0 100%);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        /* Card Design */
        .login-card {
            background: white;
            width: 100%;
            max-width: 380px;
            padding: 40px 30px;
            border-radius: 20px;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
            text-align: center;
            margin-bottom: auto; /* Pushes card up slightly */
            margin-top: auto;
        }

        /* Branding */
        .app-icon {
            font-size: 3rem;
            color: var(--primary, #6366f1);
            margin-bottom: 10px;
            display: inline-block;
        }

        .login-header h2 {
            margin: 0;
            font-size: 1.5rem;
            font-weight: 800;
            color: #1e293b;
        }

        .login-header p {
            color: #64748b;
            margin-top: 5px;
            font-size: 0.9rem;
            margin-bottom: 30px;
        }

        /* Form Styling */
        .form-group {
            margin-bottom: 20px;
            text-align: left;
            position: relative;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-size: 0.85rem;
            font-weight: 600;
            color: #475569;
        }

        /* Input with Icons */
        .input-wrapper {
            position: relative;
        }

        .input-wrapper i {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #94a3b8;
            font-size: 1rem;
        }

        .form-control {
            width: 100%;
            padding: 12px 15px 12px 45px; /* Left padding for icon */
            border: 2px solid #e2e8f0;
            border-radius: 12px;
            font-size: 1rem;
            outline: none;
            transition: all 0.2s;
            background: #f8fafc;
            color: #334155;
        }

        .form-control:focus {
            border-color: var(--primary, #6366f1);
            background: white;
            box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.1);
        }

        /* Button */
        .btn-login {
            width: 100%;
            padding: 14px;
            background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%);
            color: white;
            border: none;
            border-radius: 12px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            box-shadow: 0 4px 6px -1px rgba(79, 70, 229, 0.2);
            transition: transform 0.1s;
        }

        .btn-login:active {
            transform: scale(0.98);
        }

        /* Error Message */
        .error-msg {
            background: #fee2e2;
            color: #dc2626;
            padding: 10px;
            border-radius: 8px;
            font-size: 0.9rem;
            margin-bottom: 20px;
            border: 1px solid #fecaca;
        }

        /* Developer Footer */
        .dev-footer {
            margin-top: 30px;
            text-align: center;
            font-size: 0.75rem;
            color: #94a3b8;
            padding-bottom: 10px;
        }

        .dev-footer a {
            color: #64748b;
            text-decoration: none;
            font-weight: 500;
        }
        
        .version-badge {
            background: #e2e8f0;
            padding: 2px 6px;
            border-radius: 4px;
            font-size: 0.7rem;
            margin-left: 5px;
            color: #475569;
        }
    </style>
</head>
<body>

    <div class="login-card">
        <div class="login-header">
            <div class="app-icon"><i class="fa-solid fa-box-open"></i></div>
            <h2>Store App</h2>
            <p>Please sign in to continue</p>
        </div>

        <?php if(isset($error) && !empty($error)): ?>
            <div class="error-msg">
                <i class="fa-solid fa-circle-exclamation"></i> <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-group">
                <label>Username</label>
                <div class="input-wrapper">
                    <i class="fa-solid fa-user"></i>
                    <input type="text" name="username" class="form-control" placeholder="Enter username" required autofocus>
                </div>
            </div>

            <div class="form-group">
                <label>Password</label>
                <div class="input-wrapper">
                    <i class="fa-solid fa-lock"></i>
                    <input type="password" name="password" class="form-control" placeholder="Enter password" required>
                </div>
            </div>

            <button class="btn-login">
                Sign In <i class="fa-solid fa-arrow-right" style="margin-left:8px"></i>
            </button>
        </form>
    </div>

    <div class="dev-footer">
        dev by <a href="mailto:kowshiqueroy@gmail.com">kowshiqueroy@gmail.com</a>
        <span class="version-badge">v3.2</span>
    </div>

</body>
</html>
