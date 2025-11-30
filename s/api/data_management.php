<?php
// api/data_management.php

require_once '../config/config.php';
require_once '../utils/log_action.php'; // We will create this helper soon

session_start();
header('Content-Type: application/json');

// --- Security Check ---
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'SR') {
    http_response_code(403); // Forbidden
    echo json_encode(['success' => false, 'message' => 'Access denied.']);
    exit;
}

$action = $_POST['action'] ?? '';
$company_id = $_SESSION['company_id'];
$user_id = $_SESSION['user_id'];

if (empty($company_id)) {
    echo json_encode(['success' => false, 'message' => 'You are not associated with a company.']);
    exit;
}

try {
    $pdo = get_db_connection();
    
    switch ($action) {
        case 'add_route':
            $name = trim($_POST['name'] ?? '');
            if (empty($name)) {
                throw new Exception('Route name is required.');
            }
            
            // Check for duplicates
            $stmt = $pdo->prepare("SELECT id FROM routes WHERE name = ? AND company_id = ?");
            $stmt->execute([$name, $company_id]);
            if ($stmt->fetch()) {
                throw new Exception('This route already exists for your company.');
            }

            $stmt = $pdo->prepare("INSERT INTO routes (name, company_id) VALUES (?, ?)");
            $stmt->execute([$name, $company_id]);
            log_action($pdo, $user_id, 'ADD_ROUTE', "Added new route: $name");
            echo json_encode(['success' => true, 'message' => 'Route added successfully.']);
            break;

        case 'add_shop':
            $name = trim($_POST['name'] ?? '');
            $route_id = $_POST['route_id'] ?? null;
            if (empty($name) || empty($route_id)) {
                throw new Exception('Route and shop name are required.');
            }

            // Check for duplicates
            $stmt = $pdo->prepare("SELECT id FROM shops WHERE name = ? AND route_id = ?");
            $stmt->execute([$name, $route_id]);
            if ($stmt->fetch()) {
                throw new Exception('This shop already exists in this route.');
            }

            $stmt = $pdo->prepare("INSERT INTO shops (name, route_id, company_id) VALUES (?, ?, ?)");
            $stmt->execute([$name, $route_id, $company_id]);
            log_action($pdo, $user_id, 'ADD_SHOP', "Added new shop: $name");
            echo json_encode(['success' => true, 'message' => 'Shop added successfully.']);
            break;

        case 'add_item':
            $name = trim($_POST['name'] ?? '');
            $rate = $_POST['rate'] ?? null;
            if (empty($name) || !is_numeric($rate) || $rate < 0) {
                throw new Exception('A valid item name and non-negative rate are required.');
            }

            // Check for duplicates
            $stmt = $pdo->prepare("SELECT id FROM items WHERE name = ? AND company_id = ?");
            $stmt->execute([$name, $company_id]);
            if ($stmt->fetch()) {
                throw new Exception('This item already exists for your company.');
            }

            $stmt = $pdo->prepare("INSERT INTO items (name, rate, company_id) VALUES (?, ?, ?)");
            $stmt->execute([$name, $rate, $company_id]);
            log_action($pdo, $user_id, 'ADD_ITEM', "Added new item: $name");
            echo json_encode(['success' => true, 'message' => 'Item added successfully.']);
            break;

        default:
            throw new Exception('Invalid action specified.');
    }

} catch (PDOException $e) {
    error_log("Database Error in data_management.php: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'A database error occurred.']);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
