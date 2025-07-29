<?php
require_once '../conn.php';
$id = $_GET['id'];
echo 'Invoice ID: '.$id.'<br>';
$total=0;
$sql = "SELECT op.id, op.product_id, op.quantity, op.price, op.total, p.product_name FROM order_product op JOIN products p ON op.product_id = p.id WHERE op.order_id = $id";
$result = $conn->query($sql);
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        echo $row['product_name'] . '-' . $row['quantity'] . 'X' . $row['price'] . '=' . $row['total'] . '<br>';
        $total+= $row['total'];
    }
echo 'Total: '.$total;

} else {
    echo 'No items found for this invoice.';
}
