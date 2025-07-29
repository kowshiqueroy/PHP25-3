<?php
require_once 'conn.php';
?>



<?php
$msg='';
if(isset($_POST['submitLogin'])){
    $username = $_POST['username'];
    $password = md5($_POST['password']);
    $sql = "SELECT * FROM users WHERE username='$username' AND password='$password' AND status=1";
    $result = $conn->query($sql);
    if ($result->num_rows > 0) {

        $sql = "UPDATE users SET last_login=NOW() WHERE username='$username'";
        $conn->query($sql);


       
        $user = $result->fetch_assoc();
        $_SESSION['username'] = $username;
        $_SESSION['role'] = $user['role'];
        $_SESSION['status'] = $user['status'];
        switch ($user['role']) {
            case 1:
                $_SESSION['rolename'] = 'admin';
                break;
            case 2:
                $_SESSION['rolename'] = 'sr';
                break;
            case 3:
                $_SESSION['rolename'] = 'sradmin';
                break;
            case 4:
                $_SESSION['rolename'] = 'viewer';
                break;
            default:
                $_SESSION['rolename'] = 'unknown';
                break;
        }
        $_SESSION['id'] = $user['id'];

    } else {
        $msg.="Invalid username or password";

    }
}





if(isset($_SESSION['status'])  && $_SESSION['status'] == 1){
    header("Location: ".$_SESSION['rolename']."/index.php");
}else{


    ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $sitename; ?></title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(to right, #141E30,rgb(59, 167, 59));
            font-family: 'Roboto', sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
            color: #fff;
        }
        .login-form {
            width: 100%;
            max-width: 400px;
            padding: 40px;
            background-color: #1C1C1C;
            border-radius: 12px;
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.3);
        }
        .login-form h1 {
            text-align: center;
            margin-bottom: 30px;
            font-size: 28px;
            color: #f0f2f5;
        }
        .form-control::placeholder {
            color: #8c96a6;
        }
        
        .form-control, .form-control:focus {
            border-radius: 8px;
            margin-bottom: 15px;
            background-color: #2D2D2D;
            border: none;
            color: #fff;
        }
        .btn-primary {
            width: 100%;
            padding: 12px;
            border-radius: 8px;
            background-color:rgb(255, 230, 0);
            border: none;
            transition: background-color 0.3s;
        }
        .btn-primary:hover {
            background-color: #0056b3;
        }
        .message {
            text-align: center;
            margin-top: 20px;
            color: #aaa;
        }
        .toggle-password {
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
        }
        .input-container {
            position: relative;
        }
    </style>
</head>
<body>
    <div class="login-form">
        <h1><?php echo $sitename; ?></h1>
        <form action="" method="post">
            <div class="input-container">
                <input type="text" name="username" class="form-control" placeholder="Username" required>
            </div>
            <div class="input-container">
                <input type="password" name="password" class="form-control" placeholder="Password" required>
                <i class="toggle-password fas fa-eye-slash"></i>
            </div>
            <input type="submit" name="submitLogin" class="btn btn-primary" value="Login">
        </form>
        <div class="message">
            <p>Developer: <b>kowshiqueroy@gmail.com</b><br>
            <?php echo $msg; ?>
            </p>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.querySelector('.toggle-password').addEventListener('click', function (e) {
            const passwordInput = document.querySelector('input[name="password"]');
            const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordInput.setAttribute('type', type);
            this.classList.toggle('fa-eye');
            this.classList.toggle('fa-eye-slash');
        });
    </script>
</body>
</html>

<?php
   
}
?>




