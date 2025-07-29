<?php
session_start();
// যদি ইতোমধ্যেই লগইন রয়েছে, চ্যাট পেজে রিডাইরেক্ট করুন
if (isset($_SESSION['user_id'])) {
    header('Location: chat.php');
    exit;
}

require 'db.php'; // ডাটাবেস সংযোগ

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    if ($username === '' || $password === '') {
        $message = 'Please enter both username and password.';
    } else {
        // ১) ইউজার আছে কি না চেক
        $stmt = $pdo->prepare('SELECT id, password_hash FROM users WHERE username = ?');
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        if ($user) {
            // ২) যদি থাকে, পাসওয়ার্ড চেক
            if (password_verify($password, $user['password_hash'])) {
                $_SESSION['user_id']   = $user['id'];
                $_SESSION['username']  = $username;
                header('Location: chat.php');
                exit;
            } else {
                $message = 'Incorrect password.';
            }
        } else {
            // ৩) যদি না থাকে, নতুন ইউজার তৈরি করুন
            $hash = password_hash($password, PASSWORD_BCRYPT);
            $insert = $pdo->prepare('INSERT INTO users (username, password_hash) VALUES (?, ?)');
            $insert->execute([$username, $hash]);
            $_SESSION['user_id']   = $pdo->lastInsertId();
            $_SESSION['username']  = $username;
            header('Location: chat.php');
            exit;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login or Register</title>
    <style>
        /* সোজা সিএসএস: কেন্দ্র করে ফর্ম, বেসিক স্টাইল */
        body { display: flex; justify-content: center; align-items: center; height: 100vh; }
        form { border: 1px solid #ccc; padding: 2rem; border-radius: 8px; }
        input { display: block; width: 100%; margin-bottom: 1rem; padding: .5rem; }
    </style>
</head>
<body>
    <form method="post">
        <h2>Login or Register</h2>
        <?php if ($message): ?>
            <p style="color:red;"><?php echo htmlspecialchars($message); ?></p>
        <?php endif; ?>

        <input type="text" name="username" placeholder="Username" required>
        <input type="password" name="password" placeholder="Password" required>
        <button type="submit">Go to Chat</button>
    </form>
</body>
</html>