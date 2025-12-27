<?php
require_once 'config.php';
checkAuth();

// Set Headers to force download
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=inventory_report_' . date('Y-m-d') . '.csv');

// Open output stream
$output = fopen('php://output', 'w');

// 1. GET PARAMETERS (Same as reports.php)
$search = "%" . ($_GET['search'] ?? '') . "%";
$rpt_type = $_GET['rpt_type'] ?? 'stock';

// 2. LOGIC
if ($rpt_type == 'stock') {
    // HEADERS
    fputcsv($output, array('Product Type', 'Product Name', 'Current Stock', 'Unit', 'Total Value Invested'));

    // QUERY
    $sql = "SELECT p_type, p_name, p_unit, 
            SUM(CASE WHEN txn_type = 'IN' THEN quantity ELSE 0 END) - 
            SUM(CASE WHEN txn_type = 'OUT' THEN quantity ELSE 0 END) as stock,
            SUM(CASE WHEN txn_type = 'IN' THEN total_value ELSE 0 END) as total_in_val
            FROM transactions 
            JOIN products p ON transactions.product_id = p.id
            WHERE p.p_name LIKE ? OR p.p_type LIKE ?
            GROUP BY p.id";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$search, $search]);

    while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        fputcsv($output, $row);
    }

} else {
    // TRANSACTION LOG HEADERS
    fputcsv($output, array('Date', 'Type', 'Product', 'Qty', 'Unit Val', 'Total Val', 'Reason', 'From/To', 'Section', 'Slip #', 'Location', 'By'));

    // QUERY
    $sql = "SELECT t.entry_date, t.txn_type, p.p_name, t.quantity, t.unit_value, t.total_value, 
            t.reason, t.from_to, t.section, t.slip_no, t.location, t.handled_by 
            FROM transactions t 
            JOIN products p ON t.product_id = p.id 
            WHERE (p.p_name LIKE ? OR t.slip_no LIKE ?) ";
            
    $params = [$search, $search];
    if(!empty($_GET['from_date'])) { $sql .= " AND entry_date >= ?"; $params[] = $_GET['from_date']; }
    if(!empty($_GET['to_date'])) { $sql .= " AND entry_date <= ?"; $params[] = $_GET['to_date']; }
    
    $sql .= " ORDER BY entry_date DESC";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);

    while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        fputcsv($output, $row);
    }
}

fclose($output);
exit;
?>