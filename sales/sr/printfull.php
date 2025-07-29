<?php
require_once("../conn.php");

if (!isset($_GET["idall"]) OR empty($_GET["idall"])) {
    echo "<div style='text-align: center;'>ID Not Found</div>";
    exit;
}

$ids = explode(",", $_GET["idall"]);
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
    <title>Modern Invoices</title>
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
              margin: 5px auto;
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
              margin: 5px auto;
            padding: 0 5px;
        }

        .invoice-details {
            display: flex;
            justify-content: space-between;
            font-size: 14px; /* Smaller font size for compactness */
            margin-top: 2px;
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
            onclick="window.location.href='orders.php'">Go to Orders</button>
    </div>
    <button class="no-print print-hidden"
            style="background-color: #007bff; color: white; border-radius: 5px; padding: 10px 20px; border: none; cursor: pointer;" 
            onclick="window.print()">Print Invoice</button>
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