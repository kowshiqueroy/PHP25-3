<?php
// api/data.php
// This script fetches data for the frontend, like for populating dropdowns and lists.

require_once '../config/config.php';

session_start();
header('Content-Type: application/json');

// --- Security Check ---
if (!isset($_SESSION['user_id'])) {
    http_response_code(403); // Forbidden
    echo json_encode(['success' => false, 'message' => 'Access denied.']);
    exit;
}

$type = $_GET['type'] ?? '';
$company_id = $_SESSION['company_id'];

if (empty($company_id) && $_SESSION['user_role'] !== 'Admin') {
    echo json_encode(['success' => false, 'message' => 'You are not associated with a company.']);
    exit;
}

try {
    $pdo = get_db_connection();
    $data = [];

    switch ($type) {
        case 'routes':
            $stmt = $pdo->prepare("SELECT id, name FROM routes WHERE company_id = ? ORDER BY name");
            $stmt->execute([$company_id]);
            $data = $stmt->fetchAll();
            break;

        case 'shops':
            $route_id = $_GET['route_id'] ?? null;
            $sql = "SELECT s.id, s.name, r.name AS route_name 
                    FROM shops s
                    JOIN routes r ON s.route_id = r.id
                    WHERE s.company_id = ?";
            $params = [$company_id];

            if ($route_id) {
                $sql .= " AND s.route_id = ?";
                $params[] = $route_id;
            }
            
            $sql .= " ORDER BY r.name, s.name";
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            $data = $stmt->fetchAll();
            break;

        case 'items':
            $stmt = $pdo->prepare("SELECT id, name, rate FROM items WHERE company_id = ? ORDER BY name");
            $stmt->execute([$company_id]);
            $data = $stmt->fetchAll();
            break;

        // --- Admin-specific data fetching ---
        case 'companies_admin':
            if ($_SESSION['user_role'] !== 'Admin') throw new Exception('Access Denied');
            $stmt = $pdo->query("SELECT id, name FROM companies ORDER BY name");
            $data = $stmt->fetchAll();
            break;

        case 'users_admin':
            if ($_SESSION['user_role'] !== 'Admin') throw new Exception('Access Denied');
            $stmt = $pdo->query(
                "SELECT u.id, u.username, u.role, c.name as company_name 
                 FROM users u 
                 LEFT JOIN companies c ON u.company_id = c.id 
                 WHERE u.role != 'Admin'
                 ORDER BY c.name, u.username"
            );
            $data = $stmt->fetchAll();
            break;
        
        case 'sales_reps':
            $stmt = $pdo->prepare("SELECT id, username FROM users WHERE company_id = ? AND role = 'SR' ORDER BY username");
            $stmt->execute([$company_id]);
            $data = $stmt->fetchAll();
            break;

        default:
            echo json_encode(['success' => false, 'message' => 'Invalid data type requested.']);
            exit;
    }

    echo json_encode(['success' => true, 'data' => $data]);

} catch (PDOException $e) {
    error_log("Database Error in data.php: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'A database error occurred.']);
} catch (Exception $e) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
