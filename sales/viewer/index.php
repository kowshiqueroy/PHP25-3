<?php


?>
<div class="no-print" style="display: flex; justify-content: center; gap: 10px; margin-top: 20px;">
    <button class="no-print"  type="button" style="background-color: #4CAF50; color: white; border-radius: 5px; padding: 10px 20px; border: none; cursor: pointer;">Viewer</button>
    <button class="no-print"  type="button" style="background-color: #f44336; color: white; border-radius: 5px; padding: 10px 20px; border: none; cursor: pointer;" onclick="window.location.href='../logout.php'">Logout</button>
</div>


<div class="no-print" style="margin-top: 20px;">
    <form  method="get" style="display: flex; flex-direction: column; align-items: center;">
        <label style="margin-right: 10px;">Delivery Date From: </label>
        <input type="date" name="delivery_date_from" value="<?php if (isset($_GET['delivery_date_from'])) echo $_GET['delivery_date_from']; ?>" style="margin-bottom: 10px;">
        <label style="margin-right: 10px;">Delivery Date To: </label>
        <input type="date" name="delivery_date_to" value="<?php if (isset($_GET['delivery_date_to'])) echo $_GET['delivery_date_to']; ?>" style="margin-bottom: 10px;">
        <label style="margin-right: 10px;">Order Status: </label>
        <select name="order_status" style="margin-bottom: 10px;">
            <option value="0" <?php if (isset($_GET['order_status']) && $_GET['order_status'] == 0) echo 'selected'; ?>>Draft</option>
            <option value="1" <?php if (isset($_GET['order_status']) && $_GET['order_status'] == 1) echo 'selected'; ?>>Submit</option>
            <option value="2" <?php if (isset($_GET['order_status']) && $_GET['order_status'] == 2) echo 'selected'; ?>>Approve</option>
            <option value="3" <?php if (isset($_GET['order_status']) && $_GET['order_status'] == 3) echo 'selected'; ?>>Reject</option>
            <option value="4" <?php if (isset($_GET['order_status']) && $_GET['order_status'] == 4) echo 'selected'; ?>>Edit</option>
            <option value="5" <?php if (isset($_GET['order_status']) && $_GET['order_status'] == 5) echo 'selected'; ?>>Serial</option>
            <option value="6" <?php if (isset($_GET['order_status']) && $_GET['order_status'] == 6) echo 'selected'; ?>>Processing</option>
            <option value="7" <?php if (isset($_GET['order_status']) && $_GET['order_status'] == 7) echo 'selected';  ?>>Delivered</option>
            <option value="8" <?php if (isset($_GET['order_status']) && $_GET['order_status'] == 8) echo 'selected'; ?>>Returned</option>
        </select>
        <input type="submit" value="Search" style="background-color: #4CAF50; color: white; border-radius: 5px; padding: 10px 20px; border: none; cursor: pointer;">
    </form>
</div>

<?php
require_once("../conn.php");
if (!isset($_SESSION['rolename']) || $_SESSION['rolename'] !== 'viewer') {
    header("Location: ../index.php");
    exit();
}
?>

<?php
if (isset($_GET['delivery_date_from']) && isset($_GET['delivery_date_to']) && isset($_GET['order_status']) && $_GET['stop'] != 1) {
    $idall='';

    $delivery_date_from = $_GET['delivery_date_from'];
    $delivery_date_to = $_GET['delivery_date_to'];
    $order_status = $_GET['order_status'];
    $sql = "SELECT * FROM orders WHERE delivery_date >= '$delivery_date_from' AND delivery_date <= '$delivery_date_to' AND order_status = '$order_status' ORDER BY id DESC";
    $result = $conn->query($sql);
    while ($row = $result->fetch_assoc()) {
        if (empty($idall)) {
            $idall = $row['id'];
        } else {
            $idall .= "," . $row['id'];
        }
    }
    header("Location: index.php?idall=$idall&delivery_date_from=$delivery_date_from&delivery_date_to=$delivery_date_to&order_status=$order_status&stop=1");
    exit();
}

?>

<?php
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
<div class="no-print print-hidden" style="display: flex; justify-content: center; align-items: center;">
    
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