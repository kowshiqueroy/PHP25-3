

<?php
require_once("../conn.php");

if (!isset($_GET["idall"]) OR empty($_GET["idall"])) {
    echo "<div style='text-align: center;'>ID Not Found</div>";
    exit;
}

$ids = explode(",", str_replace('%2C', ',', $_GET["idall"]));


if (isset($_GET["status"]) && in_array($_GET["status"], [6, 7, 8])) {
    $status = $_GET["status"];

    
    foreach ($ids as $id) {
        $sql = "UPDATE orders SET order_status = $status WHERE id = '$id'";
        if ($conn->query($sql) === TRUE) {
        } else {
            echo "Error updating record for ID " . htmlspecialchars($id) . ": " . $conn->error;
        }
    }


}

?>
<?php
function convertNumberToWords($number) {
    if (!is_numeric($number) || $number < 0) {
        return 'Invalid number';
    }

    $dictionary = [
        0 => 'zero', 1 => 'one', 2 => 'two', 3 => 'three', 4 => 'four', 5 => 'five',
        6 => 'six', 7 => 'seven', 8 => 'eight', 9 => 'nine', 10 => 'ten',
        11 => 'eleven', 12 => 'twelve', 13 => 'thirteen', 14 => 'fourteen', 
        15 => 'fifteen', 16 => 'sixteen', 17 => 'seventeen', 18 => 'eighteen',
        19 => 'nineteen', 20 => 'twenty', 30 => 'thirty', 40 => 'forty', 
        50 => 'fifty', 60 => 'sixty', 70 => 'seventy', 80 => 'eighty', 90 => 'ninety',
        100 => 'hundred', 1000 => 'thousand', 100000 => 'lakh', 10000000 => 'crore'
    ];



    if ($number < 21) {
        return $dictionary[$number] ?? '';
    }

    $words = '';
    foreach (array_reverse($dictionary, true) as $value => $word) {
        if ($value && $number >= $value) {
            if ($value >= 100) {
                $words .= convertNumberToWords(floor($number / $value)) . ' ';
            }
            $words .= $word;
            $number %= $value;
            if ($number) {
                $words .= ' ';
            }
        }
    }
    
    return trim($words);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoices - <?php echo htmlspecialchars($_SESSION['username'] . ' | ' . date('Y-m-d H:i:s')); ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@400;600&display=swap" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">

    <style>
        body {
            font-family: 'Open Sans', sans-serif;
            background-color: #f5f5f5;
            margin: 0;
            padding: 20px;
            width: 95%;
        }

        .invoice-header, .invoice-footer {
            text-align: center;
            padding: 10px 20px;
        }

        .invoice-header {
            display: flex;
            align-items: center;
            justify-content: center;
              width: 95%;
              margin: 1px auto;
        }

        .invoice-header img {
            max-width: 100px; /* Smaller logo for compactness */
            margin-right: 10px;
        }

        .invoice-header h1 {
            margin: 0;
            font-size: 24px; /* Smaller font size for compactness */
        }

        .invoice-details, .invoice-items, .signature-block {
            width: 95%;
              margin: 1px auto;
            padding: 0 5px;
        }

        .invoice-details {
            display: flex;
            justify-content: space-between;
            font-size: 14px; /* Smaller font size for compactness */
            margin-top: 0px;
        }
        .invoice-billto {

    text-align: center;
    font-size: 14px; /* Smaller font size for compactness */
    margin-top: 20px;


            font-size: 14px; /* Smaller font size for compactness */
            margin-top: 20px;
        }

        .invoice-items table, .invoice-summary table {
            width: 80%;
            border-collapse: collapse;
            margin: 20px auto;
        }

        .invoice-items th, .invoice-items td, .invoice-summary th, .invoice-summary td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
            font-size: 14px; /* Smaller font size for compactness */
        }

        .signature-block {
            display: flex;
            justify-content: space-between;
            margin-top: 30px;
            padding-bottom: 60px; /* Space for signatures */
        }

        .signature {
            border-top: 1px solid #000;
            text-align: center;
            margin-top: 40px;
            padding-top: 5px;
            flex-basis: 30%;
        }

        @media print {
            body, .invoice{
                padding: 0;
                margin: 0;
                box-shadow: none;
            }

            .no-print, .print-hidden {
                display: none;
            }

            .invoice {
                page-break-after: always;
            }

            .invoice:last-child {
                page-break-after: auto;
            }
        }
    </style>
</head>
<body>

<div class="no-print print-hidden" style="display: flex; justify-content: space-between; align-items: center;">
    <div style="margin-left: 10px;">
        <button type="button" class="no-print print-hidden"
            style="background-color: #007bff; color: white; border-radius: 5px; padding: 10px 20px; border: none; cursor: pointer;"
            onclick="window.location.href='idlist.php'">Back</button>
    </div>
    <button class="no-print print-hidden"
            style="background-color: #007bff; color: white; border-radius: 5px; padding: 10px 20px; border: none; cursor: pointer;" 
            onclick="window.print()">Print</button>
</div>
<br>
<div class="no-print print-hidden" style="display: flex; justify-content: space-between; align-items: center;">
    <div style="margin-left: 10px;">
        <button type="button" class="no-print print-hidden"
            style="background-color: #a2ff00ff; color: white; border-radius: 5px; padding: 10px 20px; border: none; cursor: pointer;"
            onclick="window.location.href='idprint.php?status=6&idall=<?= implode(',', $ids) ?>'">Processing</button>
    </div>
    <button class="no-print print-hidden"
            style="background-color: #00ff00ff; color: white; border-radius: 5px; padding: 10px 20px; border: none; cursor: pointer;" 
            onclick="window.location.href='idprint.php?status=7&idall=<?= implode(',', $ids) ?>'">Delivery</button>

            <button class="no-print print-hidden"
            style="background-color: #ff0033ff; color: white; border-radius: 5px; padding: 10px 20px; border: none; cursor: pointer;" 
            onclick="window.location.href='idprint.php?status=8&idall=<?= implode(',', $ids) ?>'">Returned</button>



        </div>


     <div class="no-print print-hidden" style="display: flex; justify-content: center; margin-top: 20px; gap: 10px;">

         <form class="no-print print-hidden" action="idprint.php" method="GET" style="width: 100%; display: flex; justify-content: center;">
            <input class="no-print print-hidden" style="width: 80%;" type="text" <?php if (isset($_GET['idall'])) echo 'value="' . $_GET['idall'] . '"'; ?> name="idall" placeholder="1,2,3,4,5" pattern="\d+(,\d+)*" required>
            <button class="no-print print-hidden"
                    style="background-color: #007bff; color: white; border-radius: 5px; padding: 10px 20px; border: none; cursor: pointer;" 
                    type="submit">Search</button>
        </form>
     </div>


<?php
$sql = "SELECT * FROM companyinfo WHERE id = 1";
$result = $conn->query($sql);
if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $companyname = htmlspecialchars($row['companyname']);
    $tagline = htmlspecialchars($row['tagline']);
    $address = htmlspecialchars($row['address']);
    $phone = htmlspecialchars($row['phone']);
    $logo = htmlspecialchars($row['logo']);
    $email = htmlspecialchars($row['email']);
} else {
    $companyname = "";
    $tagline = "";
    $address = "";
    $phone = "";
    $logo = "";
     $email = "";
}
?>








<?php foreach ($ids as $id): ?>
    <div class="invoice">
        <div class="invoice-header">
            <img src="<?= $logo ?>" alt="Company Logo">
            <h1><?= $companyname ?></h1>
           
        </div>




        <?php

        $orderdate = "";
            $route_name = "";
            $order_serial = "";
            $person_name = "";
            $total="";
        $sql = "SELECT * FROM orders WHERE id = '$id'";
        $result = $conn->query($sql);
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $orderdate = $row['order_date'];
            $route_id = $row['route_id'];
            $total= $row['total'];
            $sql2 = "SELECT route_name FROM routes WHERE id = '$route_id'";
            $result2 = $conn->query($sql2);
            if ($result2->num_rows > 0) {
                $row2 = $result2->fetch_assoc();
                $route_name = htmlspecialchars($row2['route_name']);
            } 
            $order_serial = $row['order_serial'];
            $person_id = $row['person_id'];
            $sql2 = "SELECT person_name FROM persons WHERE id = '$person_id'";
            $result2 = $conn->query($sql2);
            if ($result2->num_rows > 0) {
                $row2 = $result2->fetch_assoc();
                $person_name = htmlspecialchars($row2['person_name']);
            } 
        } 
        ?>
        <div class="invoice-details">
            <p><i class="fas fa-map-marker-alt"></i> <?= htmlspecialchars($address) ?></p>
            <p><i class="fas fa-phone"></i> <?= htmlspecialchars($phone) ?></p>
            <p><i class="fas fa-envelope"></i> <?= htmlspecialchars($email) ?></p>
        </div>
        <div class="invoice-details">
             <p>Invoice #: <?= date("ymd", strtotime($orderdate)) ?>-<strong><?= htmlspecialchars($id) ?></strong></p>
             <p>Route: <?= htmlspecialchars($route_name) ?> <?= htmlspecialchars($order_serial) ?></p>
            <p>Date: <?= date("Y-m-d") ?></p>
        </div>






        <div class="invoice-billto">
                <strong>Bill To: </strong>  <strong><?= htmlspecialchars($person_name) ?></strong>  
            </div>
        <br>
        <div class="invoice-items">
            <table>
                <thead>
                    <tr>
                        <th>Description</th>
                        <th>Quantity</th>
                        <th>Price</th>
                        <th>Amount</th>
                    </tr>
                </thead>
                <tbody>
                                                <?php
                            
                                $sql = "SELECT order_product.*, products.product_name 
                                        FROM order_product 
                                        LEFT JOIN products ON order_product.product_id = products.id 
                                        WHERE order_product.order_id = '$id'";
                                $result = $conn->query($sql);

                                if ($result->num_rows > 0) {
                                    while ($row = $result->fetch_assoc()) {
                                        echo '<tr>';
                                        echo '<td>' . htmlspecialchars($row['product_name']) . '</td>';
                                        echo '<td>' . htmlspecialchars($row['quantity']) . '</td>';
                                        echo '<td>' . htmlspecialchars($row['price']) . '</td>';
                                        echo '<td>' . htmlspecialchars($row['total']) . '</td>';
                                        echo '</tr>';
                                    }
                                } else {
                                    echo '<tr><td colspan="4" style="text-align: center;">No items found for this invoice.</td></tr>';
                                }
                            
                            ?>
                </tbody>
            </table>
        </div>
        <div class="invoice-summary">
            <table>


             

            

                <tr>
                    <th><?= strtoupper(convertNumberToWords((int)$total)) . ($total - (int)$total > 0 ? ' Taka ' . strtoupper(convertNumberToWords((int)(($total - (int)$total) * 100))). ' Paisa' : 'Taka') ?></th>
                    <td><strong>Total: <?= $total ?></strong></td>
                </tr>





            </table>
        </div>
        <!-- <div class="invoice-footer">
            <p>Thank you for your business!</p>
        </div> -->
        <div class="signature-block ">
            <div class="signature">
                Prepared by
            </div>
             <div class="signature">
                Approved by
            </div>
            <div class="signature">
                Customer
            </div>
        </div>
    </div>
<?php endforeach; ?>

</body>
</html>


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
<br>
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
$ids = explode(",", str_replace('%2C', ',', $_GET["idall"]));
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

</body>
</html>

