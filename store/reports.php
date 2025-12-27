<?php 
require_once 'header.php'; 

// 1. Fetch Company Settings
$settings = [];
$stmt = $pdo->query("SELECT * FROM settings");
while($r = $stmt->fetch()){ $settings[$r['setting_key']] = $r['setting_value']; }

// 2. Initialize Filters
$from_date = $_GET['from_date'] ?? date('Y-m-d');
$to_date   = $_GET['to_date'] ?? date('Y-m-d');
$rpt_type  = $_GET['rpt_type'] ?? 'txn_in'; // Default

// Helper for inputs
function val($key) { return htmlspecialchars($_GET[$key] ?? ''); }
?>

<div class="no-print">
    <h2>Advanced Reports</h2>
    <form method="GET" style="background:white; padding:15px; border-radius:8px; box-shadow:0 2px 5px rgba(0,0,0,0.1);">
        
        <div class="form-grid">
            <div class="form-group">
                <label>Report Type</label>
                <select name="rpt_type" onchange="this.form.submit()" style="font-weight:bold; border: 2px solid #2563eb; padding: 8px;">
                    <option value="txn_in" <?= $rpt_type=='txn_in'?'selected':'' ?>>Transaction Log (IN Only)</option>
                    <option value="txn_out" <?= $rpt_type=='txn_out'?'selected':'' ?>>Transaction Log (OUT Only)</option>
                    <option value="stock" <?= $rpt_type=='stock'?'selected':'' ?>>Stock Summary (With Date Range)</option>
                </select>
            </div>
            <div class="form-group"><label>From Date</label><input type="date" name="from_date" value="<?= $from_date ?>"></div>
            <div class="form-group"><label>To Date</label><input type="date" name="to_date" value="<?= $to_date ?>"></div>
            
            <div class="form-group"><label>Product Type</label><input type="text" name="p_type" value="<?= val('p_type') ?>" placeholder="Search Type..."></div>
            <div class="form-group"><label>Product Name</label><input type="text" name="p_name" value="<?= val('p_name') ?>" placeholder="Search Name..."></div>
        </div>

        <?php if($rpt_type != 'stock'): ?>
        <hr style="margin:10px 0; border:0; border-top:1px dashed #ccc;">
        <div class="form-grid" style="grid-template-columns: repeat(auto-fill, minmax(140px, 1fr)); font-size:0.9rem;">
            <div class="form-group"><label>Slip No</label><input type="text" name="slip_no" value="<?= val('slip_no') ?>"></div>
            <div class="form-group"><label>Section</label><input type="text" name="section" value="<?= val('section') ?>"></div>
            <div class="form-group"><label>Location</label><input type="text" name="location" value="<?= val('location') ?>"></div>
            <div class="form-group"><label>From/To</label><input type="text" name="from_to" value="<?= val('from_to') ?>"></div>
            <div class="form-group"><label>Reason</label><input type="text" name="reason" value="<?= val('reason') ?>"></div>
            <div class="form-group"><label>Handled By</label><input type="text" name="handled_by" value="<?= val('handled_by') ?>"></div>
        </div>
        <?php endif; ?>

        <div style="margin-top:15px;">
            <button type="submit" class="btn">🔍 Generate Report</button>
            <button type="button" class="btn" style="background:#4b5563" onclick="window.print()">🖨️ Print</button>
            <button type="button" class="btn" style="background:#059669" onclick="exportReport()">Export CSV</button>
            <a href="index.php" class="btn" style="background:#ef4444; float:right;">Exit</a>
        </div>
    </form>
</div>

<div class="print-area">
    
    <div class="rpt-header" style="text-align:center; margin-bottom:20px; border-bottom:2px solid #000; padding-bottom:10px;">
        <?php if(!empty($settings['company_logo'])): ?>
            <img src="<?= $settings['company_logo'] ?>" style="height:60px; float:left;">
        <?php endif; ?>
        <h2 style="margin:0; text-transform:uppercase;"><?= $settings['company_name'] ?? 'Inventory System' ?></h2>
        <p style="margin:0; font-size:0.9rem;"><?= $settings['company_address'] ?? '' ?></p>
        
        <div style="margin-top:10px; font-weight:bold; border-top:1px solid #eee; padding-top:5px;">
            <?php 
                if($rpt_type == 'stock') echo "STOCK SUMMARY REPORT";
                elseif($rpt_type == 'txn_in') echo "TRANSACTION LOG (IN)";
                else echo "TRANSACTION LOG (OUT)";
            ?>
            <br>
            <span style="font-size:0.8rem; font-weight:normal;">
                Period: <?= date('d M Y', strtotime($from_date)) ?> - <?= date('d M Y', strtotime($to_date)) ?>
            </span>
        </div>
    </div>

    <?php
    $params = [];
    $where = " WHERE 1=1 ";

    // Basic Product Filters
    if(!empty($_GET['p_type'])) { $where .= " AND p.p_type LIKE ?"; $params[] = "%".val('p_type')."%"; }
    if(!empty($_GET['p_name'])) { $where .= " AND p.p_name LIKE ?"; $params[] = "%".val('p_name')."%"; }

    // --- LOGIC 1: STOCK REPORT ---
    if ($rpt_type == 'stock') {
        
        // We filter transactions strictly by date range for the IN/OUT columns
        // Note: This report shows activity WITHIN the date range. 
        
        $sql = "SELECT 
                    p.p_type, p.p_name, p.p_unit,
                    SUM(CASE WHEN t.txn_type = 'IN' AND t.entry_date BETWEEN ? AND ? THEN t.quantity ELSE 0 END) as qty_in,
                    SUM(CASE WHEN t.txn_type = 'OUT' AND t.entry_date BETWEEN ? AND ? THEN t.quantity ELSE 0 END) as qty_out,
                    SUM(CASE WHEN t.txn_type = 'IN' AND t.entry_date BETWEEN ? AND ? THEN t.total_value ELSE 0 END) as val_in,
                    SUM(CASE WHEN t.txn_type = 'OUT' AND t.entry_date BETWEEN ? AND ? THEN t.total_value ELSE 0 END) as val_out
                FROM products p
                LEFT JOIN transactions t ON p.id = t.product_id
                $where
                GROUP BY p.id
                HAVING (qty_in > 0 OR qty_out > 0)"; // Only show items that moved or have stock
        
        // Push date params 4 times (for the 4 SUM cases)
        $params_stock = array_merge([$from_date, $to_date, $from_date, $to_date, $from_date, $to_date, $from_date, $to_date], $params);

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params_stock);

        echo "<table class='rpt-table'>
                <thead>
                    <tr>
                        <th rowspan='2'>Type</th>
                        <th rowspan='2'>Product Name</th>
                        <th colspan='2' style='text-align:center; background:#dcfce7'>IN (Receive)</th>
                        <th colspan='2' style='text-align:center; background:#fee2e2'>OUT (Issue)</th>
                        <th rowspan='2'>Net Change</th>
                    </tr>
                    <tr>
                        <th style='background:#f0fdf4'>Qty</th>
                        <th style='background:#f0fdf4'>Value</th>
                        <th style='background:#fef2f2'>Qty</th>
                        <th style='background:#fef2f2'>Value</th>
                    </tr>
                </thead>
                <tbody>";

        $tot_qty_in = 0; $tot_val_in = 0;
        $tot_qty_out = 0; $tot_val_out = 0;

        while($row = $stmt->fetch()) {
            $net = $row['qty_in'] - $row['qty_out'];
            $tot_qty_in += $row['qty_in'];
            $tot_val_in += $row['val_in'];
            $tot_qty_out += $row['qty_out'];
            $tot_val_out += $row['val_out'];

            echo "<tr>
                    <td>{$row['p_type']}</td>
                    <td>{$row['p_name']}</td>
                    <td class='text-right'>".($row['qty_in']>0 ? $row['qty_in'] : '-')."</td>
                    <td class='text-right'>".($row['val_in']>0 ? number_format($row['val_in'],2) : '-')."</td>
                    <td class='text-right'>".($row['qty_out']>0 ? $row['qty_out'] : '-')."</td>
                    <td class='text-right'>".($row['val_out']>0 ? number_format($row['val_out'],2) : '-')."</td>
                    <td class='text-right' style='font-weight:bold'>".($net > 0 ? "+$net" : $net)."</td>
                  </tr>";
        }

        // Footer Totals
        echo "<tr style='background:#f3f4f6; font-weight:bold'>
                <td colspan='2'>TOTALS</td>
                <td class='text-right'>$tot_qty_in</td>
                <td class='text-right'>".number_format($tot_val_in,2)."</td>
                <td class='text-right'>$tot_qty_out</td>
                <td class='text-right'>".number_format($tot_val_out,2)."</td>
                <td></td>
              </tr>";
        echo "</tbody></table>";

    // --- LOGIC 2: TRANSACTION LOGS (Split IN/OUT) ---
    } else {
        
        $target_type = ($rpt_type == 'txn_in') ? 'IN' : 'OUT';
        
        $where .= " AND t.txn_type = ? AND t.entry_date BETWEEN ? AND ? ";
        $params[] = $target_type;
        $params[] = $from_date;
        $params[] = $to_date;

        // Dynamic filters for logs
        $text_filters = ['slip_no', 'section', 'location', 'from_to', 'reason', 'handled_by'];
        foreach($text_filters as $f) {
            if(!empty(val($f))) {
                $where .= " AND t.$f LIKE ? ";
                $params[] = "%".val($f)."%";
            }
        }

        $sql = "SELECT t.*, p.p_name, p.p_type, p.p_unit 
                FROM transactions t 
                JOIN products p ON t.product_id = p.id 
                $where 
                ORDER BY t.entry_date DESC, t.id DESC";

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);

        echo "<table class='rpt-table'>
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Slip#</th>
                        <th>Product</th>
                        <th>From/To</th>
                        <th>Reason</th>
                        <th>Qty</th>
                        <th>Unit Val</th>
                        <th>Total Val</th>
                        <th>By</th>
                    </tr>
                </thead>
                <tbody>";
        
        $sum_qty = 0; $sum_val = 0;

        while($row = $stmt->fetch()) {
            $sum_qty += $row['quantity'];
            $sum_val += $row['total_value'];

            echo "<tr>
                    <td>".date('Y-m-d', strtotime($row['entry_date']))."</td>
                    <td>{$row['slip_no']}</td>
                    <td>{$row['p_name']}<br><small>{$row['p_type']}</small></td>
                    <td>{$row['from_to']}</td>
                    <td>{$row['reason']}</td>
                    <td style='font-weight:bold'>{$row['quantity']}</td>
                    <td>{$row['unit_value']}</td>
                    <td>".number_format($row['total_value'], 2)."</td>
                    <td>{$row['handled_by']}</td>
                  </tr>";
        }
        
        echo "<tr style='background:#f3f4f6; font-weight:bold'>
                <td colspan='5' class='text-right'>TOTAL:</td>
                <td>$sum_qty</td>
                <td></td>
                <td>".number_format($sum_val, 2)."</td>
                <td></td>
              </tr>";
        echo "</tbody></table>";
    }
    ?>

    <div class="print-footer" style="margin-top:60px; display:flex; justify-content: space-between;">
        <?php if(!empty($settings['sign_1'])): ?>
            <div style="text-align:center; width:200px; border-top:1px solid #000; padding-top:5px;"><?= $settings['sign_1'] ?></div>
        <?php endif; ?>
        <?php if(!empty($settings['sign_2'])): ?>
            <div style="text-align:center; width:200px; border-top:1px solid #000; padding-top:5px;"><?= $settings['sign_2'] ?></div>
        <?php endif; ?>
           <?php if(!empty($settings['sign_3'])): ?>
            <div style="text-align:center; width:200px; border-top:1px solid #000; padding-top:5px;"><?= $settings['sign_3'] ?></div>
        <?php endif; ?>
    </div>
</div>

<script>
function exportReport() {
    var params = new URLSearchParams(new FormData(document.querySelector('form')));
    window.location.href = 'export.php?' + params.toString();
}
</script>

<style>
.rpt-table { width:100%; border-collapse: collapse; font-size: 0.8rem; border:1px solid #ccc; }
.rpt-table th, .rpt-table td { border: 1px solid #ddd; padding: 6px; }
.rpt-table th { background: #f9fafb; text-align: left; }
.text-right { text-align: right; }
@media print {
    .no-print { display: none !important; }
    .rpt-table th, .rpt-table td { border: 1px solid #000 !important; }
}
</style>

<?php require_once 'footer.php'; ?>