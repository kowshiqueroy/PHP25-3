<?php
ob_start();
header('Content-Type: application/json');

require_once '../config/db.php';

$response = ['success' => false, 'message' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    if (empty($username) || empty($password)) {
        $response['message'] = "Please enter both username and password.";
    } else {
        try {
            // Check if user exists
            $stmt = $pdo->prepare("SELECT * FROM users WHERE username = :username");
            $stmt->execute(['username' => $username]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user) {
                // User exists, verify password
                if (password_verify($password, $user['password'])) {
                    session_start();
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['username'] = $user['username'];
                    $response['success'] = true;
                    $response['redirect'] = 'shishubot/chat.php';
                } else {
                    $response['message'] = "Invalid username or password.";
                }
            } else {
                // User does not exist, auto-create
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("INSERT INTO users (username, password) VALUES (:username, :password)");
                if ($stmt->execute(['username' => $username, 'password' => $hashed_password])) {
                    session_start();
                    $_SESSION['user_id'] = $pdo->lastInsertId();
                    $_SESSION['username'] = $username;
                    $response['success'] = true;
                    $response['redirect'] = 'shishubot/chat.php';
                } else {
                    $response['message'] = "Error creating user account.";
                }
            }
        } catch (PDOException $e) {
            $response['message'] = "Database error: " . $e->getMessage();
        }
    }
} else {
    $response['message'] = "Invalid request method.";
}

echo json_encode($response);
ob_end_flush();
?>