<?php
require_once '../includes/db_connect.php';

header('Content-Type: application/json');

$conn = connect_db();

$product_id = $_GET['product_id'] ?? '';
$store_id = $_GET['store_id'] ?? '';
$suggestions = [];

if (!empty($product_id) && !empty($store_id)) {
    // Get the category of the requested product
    $category_id = null;
    $stmt = $conn->prepare("SELECT category_id FROM products WHERE id = ?");
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $stmt->bind_result($category_id);
    $stmt->fetch();
    $stmt->close();

    if ($category_id) {
        // Find other products in the same category, excluding the original product
        $sql = "SELECT p.id, p.name, p.sku, SUM(pb.quantity) as total_quantity
                FROM products p
                LEFT JOIN product_batches pb ON p.id = pb.product_id AND pb.store_id = ?
                WHERE p.category_id = ? AND p.id != ?
                GROUP BY p.id, p.name, p.sku
                HAVING total_quantity > 0 OR total_quantity IS NULL
                ORDER BY p.name ASC
                LIMIT 5";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param("iii", $store_id, $category_id, $product_id);
        $stmt->execute();
        $result = $stmt->get_result();

        while ($row = $result->fetch_assoc()) {
            $suggestions[] = $row;
        }
        $stmt->close();
    }
}

$conn->close();

echo json_encode($suggestions);
?>