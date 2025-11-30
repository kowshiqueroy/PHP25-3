<?php
// api/admin.php

require_once '../config/config.php';
require_once '../utils/log_action.php';

session_start();
header('Content-Type: application/json');

// --- Security: Admin Only ---
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'Admin') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Access denied.']);
    exit;
}

$action = $_POST['action'] ?? '';
$admin_user_id = $_SESSION['user_id'];

try {
    $pdo = get_db_connection();

    switch ($action) {
        case 'add_company':
            $name = trim($_POST['name'] ?? '');
            if (empty($name)) {
                throw new Exception('Company name is required.');
            }
            $stmt = $pdo->prepare("INSERT INTO companies (name) VALUES (?)");
            $stmt->execute([$name]);
            log_action($pdo, $admin_user_id, 'ADD_COMPANY', "Created new company: $name");
            echo json_encode(['success' => true, 'message' => 'Company added successfully.']);
            break;

        case 'add_user':
            $company_id = $_POST['company_id'] ?? null;
            $username = trim($_POST['username'] ?? '');
            $password = $_POST['password'] ?? '';
            $role = $_POST['role'] ?? '';

            if (empty($username) || empty($password) || empty($role) || empty($company_id)) {
                throw new Exception('Company, username, password, and role are required.');
            }
            if (!in_array($role, ['Manager', 'SR', 'Viewer'])) {
                throw new Exception('Invalid role specified.');
            }

            $password_hash = password_hash($password, PASSWORD_DEFAULT);

            $stmt = $pdo->prepare("INSERT INTO users (company_id, username, password_hash, role) VALUES (?, ?, ?, ?)");
            $stmt->execute([$company_id, $username, $password_hash, $role]);
            log_action($pdo, $admin_user_id, 'ADD_USER', "Created new user: $username with role: $role");
            echo json_encode(['success' => true, 'message' => 'User added successfully.']);
            break;

        default:
            throw new Exception('Invalid admin action specified.');
    }

} catch (PDOException $e) {
    if ($e->errorInfo[1] == 1062) { // Duplicate entry
        echo json_encode(['success' => false, 'message' => 'This name or username already exists.']);
    } else {
        error_log("Admin API Error: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'A database error occurred.']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
