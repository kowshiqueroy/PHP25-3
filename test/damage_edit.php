<?php
include_once 'header.php';
?>
<?php
// Include database connection
$id = isset($_GET['id']) ? $_GET['id'] : 0;

// Fetch damage report details from the database
$stmt = $conn->prepare("SELECT * FROM damage_details WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $shop_type = $row['shop_type'];
    $received_date = $row['received_date'];
    $inspection_date = $row['inspection_date'];
    $trader_name = $row['trader_name'];
    $shop_total_qty = $row['shop_total_qty'];
    $received_total_qty = $row['received_total_qty'];
    $shop_total_amount = $row['shop_total_amount'];
    $received_total_amount = $row['received_total_amount'];
    $actual_total_qty = $row['actual_total_qty'];
    $actual_total_amount = $row['actual_total_amount'];
    $status = $row['status'];
} else {
    // Handle case where no report is found
    echo "No damage report found.";
    exit();
}
?>
<main class="printable">
    <h2>Damage Report</h2>
    <p style="text-align: center;">
        <strong>ID:</strong> <?php echo $id; ?> |
        <strong>Type:</strong> <?php echo $shop_type; ?> |
        <strong>Received Date:</strong> <?php echo $received_date; ?> |
        <strong>Inspection Date:</strong> <?php echo $inspection_date; ?> |
        <strong>Trader:</strong> <?php echo $trader_name; ?>
    </p>
    <p style="text-align: center;">
        <strong>Shop </strong> <?php echo $shop_total_qty." = ".$shop_total_amount; ?>/-
        <strong>Received </strong> <?php echo $received_total_qty." = ".$received_total_amount; ?>/-
        <strong>Actual </strong> <?php echo $actual_total_qty." = ".$actual_total_amount; ?>/-
        <strong></strong> <?php echo $status ? "" : "Draft"; ?>
    </p>

    <form action="damage_edit.php" method="POST" style="display: flex; flex-wrap: wrap;" class="no-print">
        <div class="form-group" style="flex: 1 0 20%; margin: 0.5rem;">
            <label for="shop_total_qty">Total Total Quantity</label>
            <input type="number" class="form-control" id="shop_total_qty" name="shop_total_qty" value="<?= $total_total_qty ?>">
        </div>
        <div class="form-group" style="flex: 1 0 20%; margin: 0.5rem;">
            <label for="shop_total_amount">Shop Total Amount</label>
            <input type="number" step="0.01" class="form-control" id="shop_total_amount" name="shop_total_amount" value="<?= $shop_total_amount ?>">
        </div>
        <div class="form-group" style="flex: 1 0 20%; margin: 0.5rem;">
            <label for="received_total_qty">Received Total Quantity</label>
            <input type="number" class="form-control" id="received_total_qty" name="received_total_qty" value="<?= $received_total_qty ?>">
        </div>
        <div class="form-group" style="flex: 1 0 20%; margin: 0.5rem;">
            <label for="received_total_amount">Received Total Amount</label>
            <input type="number" step="0.01" class="form-control" id="received_total_amount" name="received_total_amount" value="<?= $received_total_amount ?>">
        </div>
        <button type="submit" class="btn btn-primary" style="flex: 1 0 20%; margin: 0.5rem;">Update</button>
    </form>


</main>

<?php
include_once 'footer.php';
?>