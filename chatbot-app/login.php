<?php
require_once __DIR__ . '/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $u = $_POST['username'] ?? '';
    $p = $_POST['password'] ?? '';
    $pdo = getPDO();
    $stmt = $pdo->prepare("SELECT id,password_hash,is_admin FROM users WHERE username=?");
    $stmt->execute([$u]);
    $user = $stmt->fetch();
    if ($user && password_verify($p, $user['password_hash'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['is_admin'] = $user['is_admin'];
        // New thread if none
        if (empty($_SESSION['thread_id'])) {
            $_SESSION['thread_id'] = bin2hex(random_bytes(16));
        }
        header('Location: chat.php');
        exit;
    }
    $error = "Invalid credentials.";
}
?>
<!DOCTYPE html>
<html>
<head><title>Login</title></head>
<body>
<h2>Login</h2>
<?php if (!empty($error)) echo "<p style='color:red;'>$error</p>"; ?>
<form method="post">
    Username:<br><input name="username"><br>
    Password:<br><input name="password" type="password"><br>
    <button>Login</button>
</form>
<a href="register.php">Register</a>
</body>
</html>