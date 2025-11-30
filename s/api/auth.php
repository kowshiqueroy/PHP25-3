<?php
// api/auth.php
require_once '../config/config.php';

session_start();

header('Content-Type: application/json');

// Basic input validation
if (empty($_POST['username']) || empty($_POST['password'])) {
    echo json_encode(['success' => false, 'message' => 'Username and password are required.']);
    exit;
}

$username = $_POST['username'];
$password = $_POST['password'];

try {
    $pdo = get_db_connection();

    // Find the user by username
    $stmt = $pdo->prepare("SELECT id, username, password_hash, role, company_id FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    // Verify user exists and password is correct
    if ($user && password_verify($password, $user['password_hash'])) {
        // Password is correct, start a new session
        session_regenerate_id(true); // Prevent session fixation

        // Store data in session variables
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['user_role'] = $user['role'];
        $_SESSION['company_id'] = $user['company_id'];

        // --- Log the login action ---
        // We will implement the log_action function later.
        // For now, we can insert directly.
        $logStmt = $pdo->prepare("INSERT INTO system_logs (user_id, action_type, details) VALUES (?, 'LOGIN', ?)");
        $logStmt->execute([$user['id'], 'User logged in successfully.']);
        
        echo json_encode(['success' => true]);

    } else {
        // Invalid credentials
        echo json_encode(['success' => false, 'message' => 'Invalid username or password.']);
    }

} catch (PDOException $e) {
    // Database error
    // In production, log this error and show a generic message.
    error_log('Authentication error: ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'A server error occurred. Please try again later.']);
}
