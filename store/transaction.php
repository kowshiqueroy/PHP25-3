<?php require_once 'header.php'; ?>

<?php
// Handle Form Submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $pdo->beginTransaction();
    try {
        // 1. Manage Product (Find or Create)
        $p_type = $_POST['p_type'];
        $p_name = $_POST['p_name'];
        $p_unit = $_POST['p_unit'];
        
        $stmt = $pdo->prepare("SELECT id FROM products WHERE p_type=? AND p_name=?");
        $stmt->execute([$p_type, $p_name]);
        $prod = $stmt->fetch();
        
        if ($prod) {
            $pid = $prod['id'];
        } else {
            // Auto create new product
            $stmt = $pdo->prepare("INSERT INTO products (p_type, p_name, p_unit) VALUES (?,?,?)");
            $stmt->execute([$p_type, $p_name, $p_unit]);
            $pid = $pdo->lastInsertId();
        }
        
        // 2. Insert Transaction
        $sql = "INSERT INTO transactions (entry_date, txn_type, product_id, reason, from_to, section, slip_no, location, quantity, unit_value, total_value, mfg_date, exp_date, px_condition, remarks, handled_by, user_id) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            $_POST['entry_date'], $_POST['txn_type'], $pid, $_POST['reason'], $_POST['from_to'],
            $_POST['section'], $_POST['slip_no'], $_POST['location'], $_POST['quantity'],
            $_POST['unit_value'], $_POST['quantity'] * $_POST['unit_value'],
            $_POST['mfg_date'], $_POST['exp_date'], $_POST['condition'], $_POST['remarks'],
            $_POST['handled_by'], $_SESSION['user_id']
        ]);
        
        $pdo->commit();
        echo "<div style='color:green; padding:10px; text-align:center;'>Entry Saved Successfully!</div>";
    } catch (Exception $e) {
        $pdo->rollBack();
        echo "<div style='color:red;'>Error: " . $e->getMessage() . "</div>";
    }
}
?>

<h3>New Entry (IN/OUT)</h3>
<form method="POST" id="txnForm">
    <div class="form-grid">
        <div class="form-group">
            <label>Txn Type</label>
            <select name="txn_type" required>
                <option value="IN">IN (Receive)</option>
                <option value="OUT">OUT (Issue)</option>
            </select>
        </div>
        <div class="form-group">
            <label>Date</label>
            <input type="date" name="entry_date" value="<?= date('Y-m-d') ?>" required>
        </div>
        
        <div class="form-group">
            <label>Product Type</label>
            <select name="p_type" id="p_type" class="select2-ajax" style="width:100%" required></select>
        </div>
        <div class="form-group">
            <label>Product Name</label>
            <select name="p_name" id="p_name" class="select2-ajax" style="width:100%" required disabled></select>
        </div>
        
        <div class="form-group">
            <label>Current Stock</label>
            <input type="text" id="current_stock" readonly style="background:#eee;">
        </div>
        <div class="form-group">
            <label>Unit</label>
            <input type="text" name="p_unit" id="p_unit" required>
        </div>
        
        <div class="form-group">
            <label>Quantity</label>
            <input type="number" step="0.01" name="quantity" id="quantity" required>
        </div>
        <div class="form-group">
            <label>Unit Value</label>
            <input type="number" step="0.01" name="unit_value" id="unit_value" required>
        </div>
        <div class="form-group">
            <label>Total Value</label>
            <input type="number" id="total_value" readonly style="background:#eee;">
        </div>

        <div class="form-group"><label>Reason</label><select name="reason" class="select2-tags"><option>Purchase</option><option>Sell</option></select></div>
        <div class="form-group"><label>From/To</label><select name="from_to" class="select2-tags"></select></div>
        <div class="form-group"><label>Section</label><select name="section" class="select2-tags"></select></div>
        <div class="form-group"><label>Slip #</label><input type="text" name="slip_no"></div>
        <div class="form-group"><label>Location</label><select name="location" class="select2-tags"></select></div>
        <div class="form-group"><label>MFG Date</label><input type="date" name="mfg_date"></div>
        <div class="form-group"><label>EXP Date</label><input type="date" name="exp_date"></div>
        <div class="form-group"><label>Condition</label><input type="text" name="condition"></div>
        <div class="form-group"><label>By</label><select name="handled_by" class="select2-tags"></select></div>
        <div class="form-group"><label>Remarks</label><input type="text" name="remarks"></div>
    </div>
    <br>
    <button type="submit" class="btn">Save Entry</button>
</form>

<hr>
<h4>My Last 5 Entries</h4>
<table>
    <tr><th>Date</th><th>Item</th><th>Type</th><th>Qty</th></tr>
    <?php
    $stmt = $pdo->prepare("SELECT * FROM transactions LEFT JOIN products ON transactions.product_id = products.id WHERE user_id = ? ORDER BY transactions.id DESC LIMIT 5");
    $stmt->execute([$_SESSION['user_id']]);
    while($row = $stmt->fetch()) {
        echo "<tr><td>{$row['entry_date']}</td><td>{$row['p_name']}</td><td>{$row['txn_type']}</td><td>{$row['quantity']}</td></tr>";
    }
    ?>
</table>

<?php require_once 'footer.php'; ?>