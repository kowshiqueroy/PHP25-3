<?php
require_once __DIR__ . '/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username && $password) {
        $pdo = getPDO();
        $hash = password_hash($password, PASSWORD_BCRYPT);
        $stmt = $pdo->prepare("INSERT INTO users (username,password_hash) VALUES (?,?)");
        try {
            $stmt->execute([$username, $hash]);
            header('Location: login.php');
            exit;
        } catch (PDOException $e) {
            $error = "Username already taken.";
        }
    } else {
        $error = "Fill all fields.";
    }
}
?>
<!DOCTYPE html>
<html>
<head><title>Register</title></head>
<body>
<h2>Register</h2>
<?php if (!empty($error)) echo "<p style='color:red;'>$error</p>"; ?>
<form method="post">
    Username:<br><input name="username"><br>
    Password:<br><input name="password" type="password"><br>
    <button>Register</button>
</form>
<a href="login.php">Login</a>
</body>
</html>