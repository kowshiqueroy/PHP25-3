<?php
// print_queue.php

require_once 'config/config.php';
require_once 'templates/header.php'; // For session check

// --- Security & Permission Check ---
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'Manager') {
    die('Access Denied.');
}

$user_company_id = $_SESSION['company_id'];

try {
    $pdo = get_db_connection();

    // Fetch all invoices in the print queue for the company
    $queueStmt = $pdo->prepare(
        "SELECT id FROM invoices 
         WHERE company_id = ? AND status = 'Approved' AND print_queue_order IS NOT NULL 
         ORDER BY print_queue_order ASC"
    );
    $queueStmt->execute([$user_company_id]);
    $invoice_ids = $queueStmt->fetchAll(PDO::FETCH_COLUMN);

    if (empty($invoice_ids)) {
        echo "<div class='container'><p>The print queue is empty.</p></div>";
        require_once 'templates/footer.php';
        exit;
    }

    // Prepare statements for fetching details
    $invoiceStmt = $pdo->prepare(
        "SELECT i.*, s.name as shop_name, r.name as route_name, u.username as sr_name
         FROM invoices i
         JOIN shops s ON i.shop_id = s.id
         JOIN routes r ON i.route_id = r.id
         JOIN users u ON i.sr_id = u.id
         WHERE i.id = ?"
    );
    $itemStmt = $pdo->prepare(
        "SELECT ii.*, it.name as item_name
         FROM invoice_items ii
         JOIN items it ON ii.item_id = it.id
         WHERE ii.invoice_id = ?"
    );

} catch (Exception $e) {
    die('Error fetching invoice data: ' . $e->getMessage());
}
?>

<div class="invoice-print-container">
    <button class="no-print" onclick="window.print()">Print All Invoices</button>
    
    <?php foreach ($invoice_ids as $invoice_id): ?>
        <?php
            $invoiceStmt->execute([$invoice_id]);
            $invoice = $invoiceStmt->fetch();
            $itemStmt->execute([$invoice_id]);
            $items = $itemStmt->fetchAll();
        ?>
        <div class="printable-invoice">
            <div class="invoice-header">
                <h2>Invoice #<?php echo htmlspecialchars($invoice['id']); ?> (Queue: <?php echo htmlspecialchars($invoice['print_queue_order']); ?>)</h2>
            </div>
            <div class="invoice-details">
                <p><strong>Shop:</strong> <?php echo htmlspecialchars($invoice['shop_name']); ?></p>
                <p><strong>Delivery Date:</strong> <?php echo htmlspecialchars($invoice['delivery_date']); ?></p>
                <p><strong>Sales Rep:</strong> <?php echo htmlspecialchars($invoice['sr_name']); ?></p>
            </div>
            <table class="invoice-table">
                <thead><tr><th>Item</th><th>Qty</th><th>Rate</th><th>Total</th></tr></thead>
                <tbody>
                    <?php foreach ($items as $item): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($item['item_name']); ?></td>
                        <td><?php echo htmlspecialchars($item['quantity']); ?></td>
                        <td><?php echo number_format($item['rate'], 2); ?></td>
                        <td><?php echo number_format($item['total'], 2); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="3" style="text-align:right; font-weight:bold;">Grand Total</td>
                        <td style="font-weight:bold;"><?php echo number_format($invoice['grand_total'], 2); ?></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    <?php endforeach; ?>
</div>

<style>
.printable-invoice {
    page-break-after: always;
    border-bottom: 2px dashed #ccc;
    padding-bottom: 20px;
    margin-bottom: 20px;
}
.printable-invoice:last-child {
    page-break-after: auto;
    border-bottom: none;
}
@media print {
    .printable-invoice {
        border-bottom: none;
    }
}
</style>

<?php
require_once 'templates/footer.php';
?>
