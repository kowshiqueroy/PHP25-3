<?php
// api/logs.php

require_once '../config/config.php';

session_start();
header('Content-Type: application/json');

// --- Security: Admin Only ---
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'Admin') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Access denied.']);
    exit;
}

try {
    $pdo = get_db_connection();

    // Basic query
    $sql = "SELECT l.id, l.action_type, l.details, l.timestamp, u.username 
            FROM system_logs l
            LEFT JOIN users u ON l.user_id = u.id
            ORDER BY l.timestamp DESC
            LIMIT 100"; // Limit to recent 100 logs for now

    $stmt = $pdo->query($sql);
    $logs = $stmt->fetchAll();

    echo json_encode(['success' => true, 'data' => $logs]);

} catch (Exception $e) {
    error_log("Log fetching failed: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Failed to fetch system logs.']);
}
