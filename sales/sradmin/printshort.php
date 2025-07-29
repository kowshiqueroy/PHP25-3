<?php
require_once("../conn.php");

if (!isset($_GET["idall"]) || empty($_GET["idall"])) {
    echo "<div style='text-align: center;'>ID Not Found</div>";
    exit;
}

?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Print Short</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        @page { size: A4; margin: 0; }
        .no-print { display: block; }
        @media print { .no-print { display: none; } }
        table { width: 100%; border-collapse: collapse; margin: 10px 0; font-size: 14px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        tr { page-break-inside: avoid; margin-top: 100px;}
        tr:first-child { margin-top: 0 !important; }
    </style>
</head>
<body style="height: 100%; margin: 20px; padding: 0;">
<div class="no-print print-hidden" style="display: flex; justify-content: space-between; align-items: center;">
    <div style="margin-left: 10px;">
        <button type="button" class="no-print print-hidden"
            style="background-color: #007bff; color: white; border-radius: 5px; padding: 10px 20px; border: none; cursor: pointer;"
            onclick="window.location.href='orders.php'">Go to Orders</button>
    </div>
    <button class="no-print print-hidden"
            style="background-color: #007bff; color: white; border-radius: 5px; padding: 10px 20px; border: none; cursor: pointer;" 
            onclick="window.print()">Print List</button>
</div>
<hr class="no-print">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
        <div>Date: <?php echo date("d-m-Y"); ?></div>
        <div>Time: <?php echo date("h:i:s a"); ?></div>
    </div>
<table>
    <thead>
        <tr>
            <th>ID</th>
            <th>Order & Delivery</th>
            <th>Route Shop Serial</th>
           
            <th>Products</th>
            <th>Q</th>
            <th>Total</th>
        </tr>
    </thead>
    <tbody>

<?php
$ids = explode(",", $_GET["idall"]);
$grandAmount = 0.00;
$productTotals = [];

foreach ($ids as $id) {
    $sql = "SELECT * FROM orders WHERE id = '$id' ORDER BY order_serial";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
if ($row['order_status'] == 0) {
    $order_status_text = 'Draft';
} elseif ($row['order_status'] == 1) {
    $order_status_text = 'Submit';
} elseif ($row['order_status'] == 2) {
    $order_status_text = 'Approve';
} elseif ($row['order_status'] == 3) {
    $order_status_text = 'Reject';
} elseif ($row['order_status'] == 4) {
    $order_status_text = 'Edit';
} elseif ($row['order_status'] == 5) {
    $order_status_text = 'Serial';
} elseif ($row['order_status'] == 6) {
    $order_status_text = 'Processing';
} elseif ($row['order_status'] == 7) {
    $order_status_text = 'Delivered';
} elseif ($row['order_status'] == 8) {
    $order_status_text = 'Returned';
} else {
    $order_status_text = '';
}

            $routeResult = $conn->query("SELECT route_name FROM routes WHERE id = '{$row['route_id']}'");
            $routeName = ($routeResult->num_rows > 0) ? $routeResult->fetch_assoc()['route_name'] : "N/A";

            $personResult = $conn->query("SELECT person_name FROM persons WHERE id = '{$row['person_id']}'");
            $personName = ($personResult->num_rows > 0) ? $personResult->fetch_assoc()['person_name'] : "N/A";

            $productsList = "";
            $totalQuantity = 0;
            $totalTotal = 0;

            $productResult = $conn->query("SELECT * FROM order_product WHERE order_id = '$id'");
            while ($productRow = $productResult->fetch_assoc()) {
                $productQuery = $conn->query("SELECT product_name FROM products WHERE id = '{$productRow['product_id']}'");
                $productName = ($productQuery->num_rows > 0) ? $productQuery->fetch_assoc()['product_name'] : "Unknown Product";

                $productsList .= "{$productName} ({$productRow['quantity']} X {$productRow['price']} = {$productRow['total']}/=), ";
                $totalQuantity += $productRow['quantity'];
                $totalTotal += $productRow['total'];

                if (!isset($productTotals[$productName])) {
                    $productTotals[$productName] = $productRow['quantity'];
                } else {
                    $productTotals[$productName] += $productRow['quantity'];
                }
            }

            $grandAmount += $totalTotal;

            echo "<tr>
                    <td>{$row['id']}<br>$order_status_text</td>
                    <td>{$row['order_date']}<br>{$row['delivery_date']}<br>by ";
                    
                   
                    
                        $userQuery = $conn->query("SELECT username FROM users WHERE id = '{$row['created_by']}'");
                        $username = ($userQuery->num_rows > 0) ? $userQuery->fetch_assoc()['username'] : "Unknown User";
                        echo $username." ".$row['created_by'];
                   
                    echo "</td>
                    <td><strong>{$routeName}</strong> {$personName} #{$row['order_serial']}</td>
                    <td>{$productsList}</td>
                    <td>{$totalQuantity}</td>
                    <td>{$totalTotal}/=</td>
                  </tr>";
        }
    }
}
?>

    </tbody>
</table>


<table>
    <thead>
        <tr>
            <th>Unique Product</th>
            <th>Total Quantity</th>
        </tr>
    </thead>
    <tbody>

<?php
$grandQuantity = array_sum($productTotals);
foreach ($productTotals as $productName => $quantity) {
    echo "<tr><td>{$productName}</td><td>{$quantity}</td></tr>";
}
?>
<div style="display: flex; justify-content: space-between; align-items: center;">
    <div style="border: 1px solid #ddd; padding: 10px; margin: 10px; flex-basis: 45%;">
        <p style="text-align: center;"><strong>Grand Total Quantity:</strong> <?php echo $grandQuantity; ?></p>
    </div>
    <div style="border: 1px solid #ddd; padding: 10px; margin: 10px; flex-basis: 45%;">
        <p style="text-align: center;"><strong>Grand Total Amount:</strong> <?php echo $grandAmount; ?>/=</p>
    </div>
</div>

    </tbody>
</table>

</body>
</html>