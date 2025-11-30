<?php
// print_invoice.php

require_once 'config/config.php';
require_once 'templates/header.php'; // We include header for session check

// --- Security & Permission Check ---
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$invoice_id = $_GET['id'] ?? null;
if (!$invoice_id) {
    die('No invoice ID provided.');
}

$user_company_id = $_SESSION['company_id'];

try {
    $pdo = get_db_connection();

    // Fetch invoice details
    $stmt = $pdo->prepare(
        "SELECT i.*, s.name as shop_name, r.name as route_name, u.username as sr_name
         FROM invoices i
         JOIN shops s ON i.shop_id = s.id
         JOIN routes r ON i.route_id = r.id
         JOIN users u ON i.sr_id = u.id
         WHERE i.id = ? AND i.company_id = ?"
    );
    $stmt->execute([$invoice_id, $user_company_id]);
    $invoice = $stmt->fetch();

    if (!$invoice) {
        die('Invoice not found or access denied.');
    }

    // Fetch invoice items
    $itemStmt = $pdo->prepare(
        "SELECT ii.*, it.name as item_name
         FROM invoice_items ii
         JOIN items it ON ii.item_id = it.id
         WHERE ii.invoice_id = ?"
    );
    $itemStmt->execute([$invoice_id]);
    $items = $itemStmt->fetchAll();

} catch (Exception $e) {
    die('Error fetching invoice data: ' . $e->getMessage());
}
?>

<div class="invoice-print-container">
    <button class="no-print" onclick="window.print()">Print Invoice</button>
    
    <div class="invoice-header">
        <h2>Invoice #<?php echo htmlspecialchars($invoice['id']); ?></h2>
        <p>Status: <?php echo htmlspecialchars($invoice['status']); ?></p>
    </div>

    <div class="invoice-details">
        <p><strong>Route:</strong> <?php echo htmlspecialchars($invoice['route_name']); ?></p>
        <p><strong>Shop:</strong> <?php echo htmlspecialchars($invoice['shop_name']); ?></p>
        <p><strong>Order Date:</strong> <?php echo htmlspecialchars($invoice['order_date']); ?></p>
        <p><strong>Delivery Date:</strong> <?php echo htmlspecialchars($invoice['delivery_date']); ?></p>
        <p><strong>Sales Rep:</strong> <?php echo htmlspecialchars($invoice['sr_name']); ?></p>
    </div>

    <table class="invoice-table">
        <thead>
            <tr>
                <th>Item</th>
                <th>Quantity</th>
                <th>Rate</th>
                <th>Total</th>
            </tr>
        </thead>
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

    <div class="invoice-footer">
        <p><strong>Remarks:</strong> <?php echo nl2br(htmlspecialchars($invoice['remarks'])); ?></p>
    </div>
</div>

<style>
@media print {
    .invoice-print-container {
        width: 100%;
        margin: 0;
        padding: 0;
    }
    .invoice-header, .invoice-details, .invoice-table, .invoice-footer {
        page-break-inside: avoid;
    }
    body {
        font-size: 12pt;
    }
}
</style>

<?php
require_once 'templates/footer.php';
?>
