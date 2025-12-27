<?php
require 'config.php';
session_start();
if (!isset($_SESSION['user_id'])) header("Location: index.php");

// Filter Logic
$start = $_GET['start'] ?? date('Y-m-01');
$end   = $_GET['end']   ?? date('Y-m-d');
$shop  = $_GET['shop']  ?? '';

$sql = "SELECT * FROM transactions WHERE actual_date BETWEEN ? AND ?";
$params = [$start, $end];

if ($shop) {
    $sql .= " AND shop_id = ?";
    $params[] = $shop;
}
$sql .= " ORDER BY actual_date DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$data = $stmt->fetchAll();

// Stock Logic (Simplified)
$stockSql = "SELECT prod_name, unit, SUM(qty_in) as total_in, SUM(qty_out) as total_out 
             FROM transactions GROUP BY prod_name, unit";
$stockData = $pdo->query($stockSql)->fetchAll();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Reports</title>
    <style>
        body { font-family: sans-serif; padding: 20px; }
        .no-print { background: #f3f4f6; padding: 15px; margin-bottom: 20px; border-radius: 8px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; font-size: 14px; }
        th { background-color: #f8f9fa; }
        
        @media print {
            .no-print { display: none; }
            table { width: 100%; }
            th, td { border: 1px solid #000; }
        }
    </style>
</head>
<body>

<div class="no-print">
    <a href="index.php">← Back to POS</a>
    <h2>Filter Reports</h2>
    <form>
        <input type="date" name="start" value="<?= $start ?>">
        <input type="date" name="end" value="<?= $end ?>">
        <input type="number" name="shop" placeholder="Shop ID" value="<?= $shop ?>">
        <button>Filter</button>
        <button type="button" onclick="window.print()">Print Report</button>
    </form>
</div>

<h3>Transaction Report (<?= $start ?> to <?= $end ?>)</h3>
<table>
    <thead>
        <tr>
            <th>Date</th>
            <th>Type</th>
            <th>Item</th>
            <th>In</th>
            <th>Out</th>
            <th>Rate</th>
            <th>Total</th>
            <th>Reason</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach($data as $row): ?>
        <tr>
            <td><?= $row['actual_date'] ?></td>
            <td><?= $row['prod_type'] ?></td>
            <td><?= $row['prod_name'] ?></td>
            <td><?= $row['qty_in'] > 0 ? $row['qty_in'] : '-' ?></td>
            <td><?= $row['qty_out'] > 0 ? $row['qty_out'] : '-' ?></td>
            <td><?= $row['rate'] ?></td>
            <td><?= $row['total_price'] ?></td>
            <td><?= $row['reason'] ?></td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<h3 style="margin-top:40px; page-break-before: always;">Current Stock Status</h3>
<table>
    <thead>
        <tr>
            <th>Item</th>
            <th>Unit</th>
            <th>Total In</th>
            <th>Total Out</th>
            <th>Balance</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach($stockData as $row): ?>
        <tr>
            <td><?= $row['prod_name'] ?></td>
            <td><?= $row['unit'] ?></td>
            <td><?= $row['total_in'] ?></td>
            <td><?= $row['total_out'] ?></td>
            <td><strong><?= $row['total_in'] - $row['total_out'] ?></strong></td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>

</body>
</html>