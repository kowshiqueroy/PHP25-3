<?php
require_once '../conn.php';
// require_once 'header.php';
?>

<!-- <div class="card p-1 text-center">Reports</div> -->

<style>
@media print {
    .no-print {
        display: none;
    visibility: hidden;
    opacity: 0;
    height: 0;
    width: 0;
    overflow: hidden;
    margin: 0;
        padding: 0;
    border: none;
    outline: none;
    }


}



/* Modern form styling */
.form-inline {
    display: flex;
    flex-wrap: wrap;
    justify-content: space-between;
    gap: 20px;
    max-width: 600px;
    margin: 0 auto;
    padding: 20px;
    background: #f9f9f9;
    border-radius: 8px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.form-inline .form-group {
    flex: 1 1 calc(50% - 10px);
    display: flex;
    flex-direction: column;
}

.form-inline label {
    margin-bottom: 6px;
    font-weight: 600;
    color: #333;
}

.form-inline .form-control {
    padding: 8px 12px;
    border-radius: 4px;
    border: 1px solid #ccc;
    font-size: 14px;
}

.form-inline button {
    flex: 1 1 100%;
    padding: 10px;
    font-size: 16px;
    margin-top: 10px;
    background-color: #007bff;
    color: white;
    border: none;
    border-radius: 4px;
    cursor: pointer;
}

.form-inline button:hover {
    background-color: #0056b3;
}

</style>

<form action="reports.php" method="get" class="form-inline mt-4 no-print">

    <div class="form-group">
        <label for="date_from">Date From</label>
        <input type="date" class="form-control" name="date_from" required 
               value="<?php echo isset($_GET['date_from']) ? $_GET['date_from'] : date('Y-m-d', strtotime('-1 year')); ?>">
    </div>
    <div class="form-group">
        <label for="date_to">Date To</label>
        <input type="date" class="form-control" name="date_to" required 
               value="<?php echo isset($_GET['date_to']) ? $_GET['date_to'] : date('Y-m-d'); ?>">
    </div>

        <div class="form-group">
        <label for="sr_id">SR Name</label>
        <select class="form-control" name="sr_id" >
            <option value="">Select SR</option>
            <?php
            $sql = "SELECT id, username FROM users";
            $result = $conn->query($sql);
            if ($result->num_rows > 0) {
                while($row = $result->fetch_assoc()) {
                    echo "<option value='" . $row['id'] . "' " . 
                         (isset($_GET['sr_id']) && $_GET['sr_id'] == $row['id'] ? "selected" : "") . 
                         ">" . $row['username'] . "</option>";
                }
            }
            ?>
        </select>
    </div>
    <div class="form-group">
    <div class="form-check">
        <input class="form-check-input" type="checkbox" value="" id="visit" name="visit">
        <label class="form-check-label" for="visit">
            Visit
        </label>
    </div>
    </div>
    <button type="submit" class="btn btn-primary">Sales Report</button>
</form>

<?php

// if the order status is not 0 (draft) or 4 (edit), then we cannot edit this order
// this is because the order has been finalized and we cannot make any changes
// 0: Draft
// 1: Submit
// 2: Approve
// 3: Reject
// 4: Edit
// 5: Serial
// 6: Processing
// 7: Delivered
// 8: Returned
if (isset($_GET['sr_id']) && isset($_GET['date_from']) && isset($_GET['date_to']) && !isset($_GET['visit'])) {
    $sr_id = $_GET['sr_id'];
    $date_from = $_GET['date_from'];
    $date_to = $_GET['date_to'];





    $sql = "SELECT username FROM users WHERE id = '$sr_id'";
    $result = $conn->query($sql);
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        echo "<div style='text-align: center;'>Showing sales report for SR <b>" . $row['username'] . "</b> from $date_from to $date_to.<br></div>";

    }

    $sql = "SELECT id FROM orders WHERE created_by = '$sr_id'" .
           " AND order_date BETWEEN '$date_from' AND '$date_to'".
           " AND order_status  IN (1,2,5,6,7)  ORDER BY id DESC";
    $result = $conn->query($sql);
    $ids = [];
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            array_push($ids, $row['id']);
        }
    }
   // $ids = implode(",", $ids);
    ?>


<hr class="no-print">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
        <div>Date: <?php echo date("d-m-Y"); ?></div>
        <div>Time: <?php echo date("h:i:s a"); ?></div>
    </div>
<table>
    <thead>
        <tr>
            <th>ID</th>
            <th>Dates</th>
            <th>Route Shop Serial</th>
           
            <th>Products</th>
            <th>Q</th>
            <th>Total</th>
        </tr>
    </thead>
    <tbody>

<?php
//$ids = explode(",", $_GET["idall"]);
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

        echo "<tr >
                    <td>{$row['id']} $order_status_text</td>
                    <td style='font-size: 8px;'>".date('y-m-d', strtotime($row['order_date']))."<br> ".date('y-m-d', strtotime($row['delivery_date']))." <br>by ";

                    $userQuery = $conn->query("SELECT username FROM users WHERE id = '{$row['created_by']}'");
                    $username = ($userQuery->num_rows > 0) ? $userQuery->fetch_assoc()['username'] : "User";
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



<?php

}
?>


<?php

if ( isset($_GET['date_from']) && isset($_GET['date_to']) && isset($_GET['visit'])) {
    $date_from = $_GET['date_from'];
    $date_to = $_GET['date_to'];

    ?>

<hr class="no-print">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
        <div>Date: <?php echo date("d-m-Y"); ?></div>
        <div>Time: <?php echo date("h:i:s a"); ?></div>
    </div>
<?php
$sql = "SELECT created_by FROM orders GROUP BY created_by";
$result = $conn->query($sql);
if ($result->num_rows > 0) {
    echo "<table  border='1' cellpadding='5' cellspacing='0' style='width: 100%; border-collapse: collapse;'>";
    echo "<thead>";
    echo "<tr><th>SR</th> <th></th></tr>";
    echo "</thead>";
    echo "<tbody>";

    while ($row = $result->fetch_assoc()) {
        $created_by = $row['created_by'];

        echo "<tr>";
        echo "<td><small>{$created_by}</small>
        
       ";


        $userQuery = $conn->query("SELECT username FROM users WHERE id = '$created_by'");
        $username = ($userQuery->num_rows > 0) ? $userQuery->fetch_assoc()['username'] : "User";
        echo $username;
        


        $sql3 = "SELECT COUNT(DISTINCT id) AS count FROM orders WHERE created_by = '$created_by'";
        $result3 = $conn->query($sql3);
        $count = ($result3->num_rows > 0) ? $result3->fetch_assoc()['count'] : 0;
        echo "<br><small>{$count}</small> orders";
 
        echo "</td>";
               echo "<td>";
               $sql2 = "SELECT DISTINCT route_id FROM orders WHERE created_by = '$created_by'";
               $result2 = $conn->query($sql2);
               if ($result2->num_rows > 0) {
                   echo "<table  border='1' cellpadding='5' cellspacing='0' style='width: 100%; border-collapse: collapse;'>";
                   echo "<thead><tr><th>Route</th><th>Shop</th></tr></thead><tbody>";
                   while ($row2 = $result2->fetch_assoc()) {
                       echo "<tr>
                       
                       
                       <td>
                       <small>{$row2['route_id']}</small>
                          ";
                         $routeQuery = $conn->query("SELECT route_name FROM routes WHERE id = '{$row2['route_id']}'");
                         $routeName = ($routeQuery->num_rows > 0) ? $routeQuery->fetch_assoc()['route_name'] : "Unknown Route";
                         echo "{$routeName} ";
                      
                       
                       echo "</td>
                       
                       <td>
                           ";
                           $sql3 = "SELECT DISTINCT person_id FROM orders WHERE created_by = '$created_by' AND route_id = '{$row2['route_id']}'";
                           $result3 = $conn->query($sql3);
                           if ($result3->num_rows > 0) {
                               while ($row3 = $result3->fetch_assoc()) {
                                   echo "<small>{$row3['person_id']}</small>";


                                   $personQuery = $conn->query("SELECT person_name FROM persons WHERE id = '{$row3['person_id']}'");
                                   $personName = ($personQuery->num_rows > 0) ? $personQuery->fetch_assoc()['person_name'] : "Unknown Person";
                                   echo " {$personName}";


                                   echo"<br>";
                               }
                           }
                            echo "
                       </td>
                       
                       
                       
                       
                       </tr>";
                   }
                   echo "</tbody></table>";
               }
               echo "</td>";
        echo "</tr>";
       
    }
    echo "</tbody>";
    echo "</table>";

}

?>
<?php

}
    ?>











<?php
if (!isset($_GET['date_from'])) {
  require_once 'stat.php';
}

?>