<?php
require_once '../includes/db_connect.php';

header('Content-Type: application/json');

$conn = connect_db();

$search_query = $_GET['q'] ?? '';
$product_id = $_GET['product_id'] ?? '';
$store_id = $_GET['store_id'] ?? '';
$batches = [];

$sql = "SELECT pb.id, pb.expiry_date, pb.storage_location, pb.quantity, p.name as product_name, p.sku FROM product_batches pb JOIN products p ON pb.product_id = p.id WHERE 1=1 AND pb.quantity > 0";
$params = [];
$types = '';

if (!empty($search_query)) {
    $sql .= " AND (pb.storage_location LIKE ? OR pb.expiry_date LIKE ? OR p.name LIKE ? OR p.sku LIKE ?)";
    $param_like = "%" . $search_query . "%";
    $params[] = $param_like;
    $params[] = $param_like;
    $params[] = $param_like;
    $params[] = $param_like;
    $types .= "ssss";
}

if (!empty($product_id)) {
    $sql .= " AND pb.product_id = ?";
    $params[] = $product_id;
    $types .= "i";
}

if (!empty($store_id)) {
    $sql .= " AND pb.store_id = ?";
    $params[] = $store_id;
    $types .= "i";
}

// Add logic for low expiry items if requested
$low_expiry_alert = $_GET['low_expiry_alert'] ?? 'false';
if ($low_expiry_alert === 'true' && !empty($store_id)) {
    require_once '../includes/functions.php';
    $store_config = get_store_config($store_id);
    $expiry_alert_days = $store_config['expiry_alert_days'] ?? 30;
    $alert_date = date('Y-m-d', strtotime("+{$expiry_alert_days} days"));
    $sql .= " AND pb.expiry_date IS NOT NULL AND pb.expiry_date <= ? ORDER BY pb.expiry_date ASC";
    $params[] = $alert_date;
    $types .= "s";
} else {
    $sql .= " ORDER BY pb.expiry_date ASC, pb.id DESC"; // Default sorting
}

$stmt = $conn->prepare($sql);

if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}

$stmt->execute();
$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    $batches[] = $row;
}
$stmt->close();

$conn->close();

echo json_encode($batches);
?>