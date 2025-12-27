<?php
// api.php
header('Content-Type: application/json');
require 'config.php';
session_start();

$action = $_GET['action'] ?? '';

// Basic Auth Check
function checkAuth() {
    if (!isset($_SESSION['user_id'])) {
        echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
        exit;
    }
}

// 1. Login
if ($action === 'login') {
    $data = json_decode(file_get_contents('php://input'), true);
    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->execute([$data['username']]);
    $user = $stmt->fetch();

    if ($user && password_verify($data['password'], $user['password'])) {
        if(!$user['is_active']) die(json_encode(['status'=>'error', 'message'=>'User blocked']));
        
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['role'] = $user['role'];
        echo json_encode(['status' => 'success', 'role' => $user['role']]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Invalid credentials']);
    }
    exit;
}

// 2. Fetch Data for Offline Cache (Products, Parties)
if ($action === 'fetch_metadata') {
    checkAuth();
    // Get unique existing values to populate dropdowns
    $types = $pdo->query("SELECT DISTINCT prod_type FROM transactions")->fetchAll(PDO::FETCH_COLUMN);
    $names = $pdo->query("SELECT DISTINCT prod_name, prod_type FROM transactions")->fetchAll(PDO::FETCH_ASSOC);
    $units = $pdo->query("SELECT DISTINCT unit, prod_name FROM transactions")->fetchAll(PDO::FETCH_ASSOC);
    $parties = $pdo->query("SELECT DISTINCT party_name FROM transactions")->fetchAll(PDO::FETCH_COLUMN);
    $shops = $pdo->query("SELECT * FROM shops")->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'types' => $types,
        'names' => $names,
        'units' => $units,
        'parties' => $parties,
        'shops' => $shops
    ]);
    exit;
}

// 3. Sync Data (Receive Offline Data)
if ($action === 'sync') {
    checkAuth();
    $payload = json_decode(file_get_contents('php://input'), true);
    $savedCount = 0;

    $stmt = $pdo->prepare("INSERT INTO transactions 
    (shop_id, actual_date, prod_type, prod_name, unit, party_name, qty_in, qty_out, reason, handled_by, slip_no, location, rate, total_price, extra_charge, condition_text, remarks, sync_id) 
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ON DUPLICATE KEY UPDATE sync_id=sync_id"); // Prevent double entry if sync retries

    foreach ($payload as $row) {
        try {
            $stmt->execute([
                $row['shop_id'], $row['actual_date'], $row['prod_type'], $row['prod_name'], 
                $row['unit'], $row['party_name'], $row['qty_in'], $row['qty_out'], 
                $row['reason'], $row['handled_by'], $row['slip_no'], $row['location'], 
                $row['rate'], $row['total_price'], $row['extra_charge'], $row['condition_text'], 
                $row['remarks'], $row['temp_id'] // Use temp_id as unique sync_id
            ]);
            $savedCount++;
        } catch (Exception $e) {
            // Log error
        }
    }
    echo json_encode(['status' => 'success', 'synced' => $savedCount]);
    exit;
}
?>