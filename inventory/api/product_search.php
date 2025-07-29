<?php
require_once '../includes/db_connect.php';

header('Content-Type: application/json');

$conn = connect_db();

$search_query = $_GET['q'] ?? '';
$products = [];

if (strlen($search_query) > 0) {
    $stmt = $conn->prepare("SELECT id, name, sku FROM products WHERE name LIKE ? OR sku LIKE ? LIMIT 10");
    $param = "%" . $search_query . "%";
    $stmt->bind_param("ss", $param, $param);
    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        $products[] = $row;
    }
    $stmt->close();
}

$conn->close();

echo json_encode($products);
?>