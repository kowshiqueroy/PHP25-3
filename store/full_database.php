<?php require_once 'header.php'; ?>

<?php
// Default to Today's Range
$from = $_GET['from'] ?? date('Y-m-d');
$to   = $_GET['to']   ?? date('Y-m-d');
?>

<div class="no-print">
    <div style="display:flex; justify-content:space-between; align-items:center;">
        <h2>📂 Full Database View</h2>
        <a href="index.php" class="btn">Back to Dashboard</a>
    </div>

    <form method="GET" style="background:#fff; padding:10px; margin-bottom:15px; border-radius:4px;">
        <label>Date Range:</label>
        <input type="date" name="from" value="<?= $from ?>"> to 
        <input type="date" name="to" value="<?= $to ?>">
        <button type="submit" class="btn">Load Data</button>
        <button type="button" class="btn" style="background:#4b5563" onclick="window.print()">Print View</button>
    </form>
</div>

<div style="overflow-x: auto;">
    <table class="rpt-table" style="font-size:0.75rem; width:100%;">
        <thead>
            <tr style="background:#333; color:white;">
                <th>ID</th>
                <th>Date</th>
                <th>Type</th>
                <th>Product Info</th>
                <th>Reason</th>
                <th>From/To</th>
                <th>Section</th>
                <th>Slip No</th>
                <th>Location</th>
                <th>Qty</th>
                <th>Unit Val</th>
                <th>Total</th>
                <th>MFG</th>
                <th>EXP</th>
                <th>Cond.</th>
                <th>Remarks</th>
                <th>By</th>
                <th>User ID</th>
                <th>Timestamp</th>
            </tr>
        </thead>
        <tbody>
        <?php
        // Query to fetch everything
        $sql = "SELECT t.*, p.p_name, p.p_type 
                FROM transactions t 
                JOIN products p ON t.product_id = p.id
                WHERE t.entry_date BETWEEN ? AND ?
                ORDER BY t.entry_date DESC, t.id DESC";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$from, $to]);

        while($row = $stmt->fetch()):
            $bg = $row['txn_type'] == 'IN' ? '#ecfdf5' : '#fef2f2';
        ?>
            <tr style="background:<?= $bg ?>">
                <td><?= $row['id'] ?></td>
                <td><?= $row['entry_date'] ?></td>
                <td><strong><?= $row['txn_type'] ?></strong></td>
                <td><?= $row['p_name'] ?> <br> (<?= $row['p_type'] ?>)</td>
                <td><?= $row['reason'] ?></td>
                <td><?= $row['from_to'] ?></td>
                <td><?= $row['section'] ?></td>
                <td><?= $row['slip_no'] ?></td>
                <td><?= $row['location'] ?></td>
                <td><strong><?= $row['quantity'] ?></strong></td>
                <td><?= $row['unit_value'] ?></td>
                <td><?= $row['total_value'] ?></td>
                <td><?= $row['mfg_date'] ?></td>
                <td><?= $row['exp_date'] ?></td>
                <td><?= $row['px_condition'] ?></td>
                <td><?= $row['remarks'] ?></td>
                <td><?= $row['handled_by'] ?></td>
                <td><?= $row['user_id'] ?></td>
                <td><?= $row['created_at'] ?></td>
            </tr>
        <?php endwhile; ?>
        </tbody>
    </table>
</div>

<style>
    @media print {
        @page { size: landscape; } /* Force landscape for this wide table */
        .no-print { display:none; }
        table { font-size: 8px !important; }
    }
    /* Reuse table styles from reports */
    .rpt-table { width:100%; border-collapse: collapse; }
    .rpt-table th, .rpt-table td { border: 1px solid #ccc; padding: 4px; }
</style>

<?php require_once 'footer.php'; ?>