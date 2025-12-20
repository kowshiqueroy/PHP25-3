<?php
// 1. Configuration
include '../config.php';

if (!isset($conn) || $conn->connect_error) {
    die("Database connection failed. Please check config.php");
}

// 2. Input Processing
$order_ids_input = $_GET['order_ids'] ?? '';
if (!$order_ids_input) die("No Order IDs provided.");

$ids_array = explode(',', $order_ids_input);
$clean_ids = [];
foreach ($ids_array as $id) {
    $val = (int)$id;
    if ($val > 0) $clean_ids[] = $val;
}
// Keep strict input order for sorting later
$unique_requested_ids = array_values(array_unique($clean_ids));

if (empty($unique_requested_ids)) die("Invalid Order IDs.");
$sql_id_list = implode(',', $unique_requested_ids);

// 3. Fetch Data

// A. Orders
$sql_orders = "
    SELECT 
        o.id as order_id, o.order_date, o.delivery_date, o.shop_id,
        s.shop_name, 
        r.route_name,
        c.name as company_name, c.address as company_address, c.phone as company_phone, c.logo as company_logo
    FROM orders o
    JOIN shops s ON o.shop_id = s.id
    JOIN routes r ON o.route_id = r.id
    JOIN companies c ON o.company_id = c.id
    WHERE o.id IN ($sql_id_list)
";
$orders_res = $conn->query($sql_orders);

// B. Items
$sql_items = "
    SELECT 
        oi.order_id, oi.item_id, oi.quantity, oi.price, 
        (oi.quantity * oi.price) as total,
        i.item_name
    FROM order_items oi
    JOIN items i ON oi.item_id = i.id
    WHERE oi.order_id IN ($sql_id_list)
";
$items_res = $conn->query($sql_items);

// 4. Data Mapping & Merging Logic
$items_map = [];
while ($row = $items_res->fetch_assoc()) {
    $items_map[$row['order_id']][] = $row;
}

// Global Totals (for warehouse loading list - ignores price differences)
$global_item_totals = []; 
$grand_total_amount = 0;
$grand_total_qty = 0;

$merged_groups = []; 
$order_shop_map = []; 

while ($row = $orders_res->fetch_assoc()) {
    $oid = $row['order_id'];
    $shop_id = $row['shop_id'];
    
    // Map Order ID to Shop ID for sorting later
    $order_shop_map[$oid] = $shop_id;

    $current_items = $items_map[$oid] ?? [];

    if (!isset($merged_groups[$shop_id])) {
        $merged_groups[$shop_id] = [
            'shop_name' => $row['shop_name'],
            'route_name' => $row['route_name'],
            'company_name' => $row['company_name'],
            'company_address' => $row['company_address'],
            'company_phone' => $row['company_phone'],
            'company_logo' => $row['company_logo'],
            'order_ids' => [], 
            'dates' => [],
            'items' => [], 
            'invoice_total' => 0
        ];
    }

    $merged_groups[$shop_id]['order_ids'][] = $oid;
    $merged_groups[$shop_id]['dates'][] = $row['order_date']; 

    foreach ($current_items as $item) {
        $itm_id = $item['item_id'];
        $itm_name = $item['item_name'];
        $qty = $item['quantity'];
        $total = $item['total'];
        $price = $item['price'];

        // KEY FIX: Generate a unique key based on ID AND PRICE
        // This ensures items with different prices are not merged into one row
        $merge_key = $itm_id . '_' . (string)$price;

        // Add to Merged Invoice Items
        if (!isset($merged_groups[$shop_id]['items'][$merge_key])) {
            $merged_groups[$shop_id]['items'][$merge_key] = [
                'item_name' => $itm_name,
                'quantity' => 0,
                'price' => $price, 
                'total' => 0
            ];
        }
        $merged_groups[$shop_id]['items'][$merge_key]['quantity'] += $qty;
        $merged_groups[$shop_id]['items'][$merge_key]['total'] += $total;

        // Invoice Total
        $merged_groups[$shop_id]['invoice_total'] += $total;

        // Global Product Summary (Warehouse doesn't care about price, just name/qty)
        if (!isset($global_item_totals[$itm_name])) {
            $global_item_totals[$itm_name] = 0;
        }
        $global_item_totals[$itm_name] += $qty;
        
        // Grand Totals
        $grand_total_qty += $qty;
        $grand_total_amount += $total;
    }
}

// 5. Sorting & Formatting
$final_sorted_groups = [];
$processed_shops = [];

foreach ($unique_requested_ids as $req_id) {
    if (isset($order_shop_map[$req_id])) {
        $sid = $order_shop_map[$req_id];
        
        if (!isset($processed_shops[$sid]) && isset($merged_groups[$sid])) {
            $group = $merged_groups[$sid];
            
            // Format Items for view
            $group['items'] = array_values($group['items']);
            $group['display_ids'] = implode(', ', $group['order_ids']);
            
            // Build summary string (Item Name (Qty))
            // Note: If same item appears twice due to diff prices, it shows up twice here too, which is correct
            $item_summary_parts = [];
            foreach($group['items'] as $itm) {
                $price_str = number_format($itm['price'], 2);
                if (substr($price_str, -3) == '.00') {
                    $price_str = substr($price_str, 0, -3);
                }
                $item_summary_parts[] =  $itm['item_name'] .   " (" . $itm['quantity'] . "@" . $price_str . ")";
            }
            $group['item_summary_str'] = implode(', ', $item_summary_parts);
            
            $final_sorted_groups[] = $group;
            $processed_shops[$sid] = true;
        }
    }
}

ksort($global_item_totals);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Invoices Print</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap');
        
        body { font-family: 'Inter', sans-serif; font-size: 10pt; color: #111; margin: 0; background: #e5e5e5; }

        .page {
            background: white; width: 210mm; min-height: 297mm; margin: 10mm auto; padding: 10mm;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1); box-sizing: border-box; position: relative;
        }

        /* Utils */
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .bold { font-weight: 700; }
        .uppercase { text-transform: uppercase; }
        .flex { display: flex; justify-content: space-between; }
        
        /* Tables */
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 4px 6px; border-bottom: 1px solid #eee; text-align: left; vertical-align: top; }
        
        /* Summary Page Styles */
        .summary-header { text-align: center; margin-bottom: 10px; border-bottom: 2px solid #b1afafff; padding-bottom: 5px; }
        .compact-table th { background: #adababff; color: white; font-size: 8pt; padding: 4px; border: 1px solid #000;}
        .compact-table td { font-size: 8pt; border: 1px solid #ccc; }
        .compact-table tr:nth-child(even) { background-color: #f9f9f9; }

        /* Product Breakdown Table */
        .breakdown-container { display: flex; flex-wrap: wrap; gap: 10px; margin-top: 15px; }
        .breakdown-col { width: 48%; }
        .breakdown-table th { background: #b4b3b3ff; color: #fff; font-size: 8pt; }
        .breakdown-table td { font-size: 8pt; border: 1px solid #ccc; padding: 2px 5px; }

        /* Invoice Styles */
        .invoice-container { border: 1px solid #999; padding: 15px; margin-bottom: 15px; background: #fff; page-break-inside: avoid; }
        .inv-top { display: flex; align-items: center; border-bottom: 2px solid #000; padding-bottom: 10px; margin-bottom: 10px; }
        .company-logo { height: 40px; margin-right: 15px; }
        .inv-meta { margin-left: auto; text-align: right; font-size: 9pt; }
        .bill-strip { background: #eee; padding: 5px 10px; font-weight: 600; margin-bottom: 10px; display: flex; justify-content: space-between; }
        
        .inv-table th { background: #eee; border-bottom: 1px solid #999; font-size: 9pt; }
        .inv-table td { font-size: 9pt; }

        .signatures { margin-top: 35px; display: flex; justify-content: space-between; page-break-inside: avoid; color: #bebbbbff; }
        .sign-box { border-top: 1px dashed #000; width: 30%; text-align: center; padding-top: 5px; font-size: 8pt; }

        @media print {
            body { background: white; }
            .page { width: 100%; margin: 0; padding: 0; box-shadow: none; min-height: auto; }
            .page-break { page-break-after: always; }
            @page { size: A4; margin: 0.5cm; }
            .invoice-container { border: 1px dashed #aaa; }
        }
    </style>
</head>
<body>

    <div class="page">
        <div class="summary-header">
            <h2 style="margin:0; text-transform: uppercase;">Delivery Report</h2>
            <div style="font-size: 8pt; color: #444;">Online <?php echo APP_NAME; ?> APP Generated @ <?php echo date('d M Y, h:i A'); ?></div>
        </div>

        <div style="margin-bottom: 5px; font-size: 9pt; font-weight: bold;">ORDER LIST:</div>
        <table class="compact-table" >
            <thead >
                <tr>
                    <th class="text-center" style="width: 5%;">#</th>
                    <th style="width: 5%;">Ref IDs</th>
                    <th style="width: 30%;">Shop & Route</th>
                    <th style="width: 45%;">Items (Qty)</th>
                    <th class="text-right" style="width: 15%;">Amount</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $sl = 1;
                foreach($final_sorted_groups as $group): ?>
                <tr>
                    <td class="text-center"><?php echo $sl++; ?></td>
                    <td><?php echo $group['display_ids']; ?></td>
                    <td>
                        <div class="bold"><?php echo htmlspecialchars($group['shop_name']); ?></div>
                        <div style="font-size: 7pt;"><?php echo htmlspecialchars($group['route_name']); ?></div>
                    </td>
                    <td style="color: #000000ff; line-height: 1.1;">
                        <?php echo htmlspecialchars($group['item_summary_str']); ?>
                    </td>
                    <td class="text-right bold">
                        <?php echo number_format($group['invoice_total'], 2); ?>
                    </td>
                </tr>
                <?php endforeach; ?>
                <tr style="background: #919090ff; color: white; font-weight: bold;">
                    <td colspan="4" class="text-right" style="border:none;">TOTAL INVOICE VALUE:</td>
                    <td class="text-right" style="border:1px solid #c4bebeff;">
                        <?php echo number_format($grand_total_amount, 2); ?>
                    </td>
                </tr>
            </tbody>
        </table>

        <div style="margin-top: 20px;">
            <div style="font-size: 9pt; font-weight: bold; border-bottom: 1px solid #dbd1d1ff; padding-bottom: 2px;">
                TOTAL PRODUCT LOADING LIST (Unique Items: <?php echo count($global_item_totals); ?> | Total Qty: <?php echo $grand_total_qty; ?>)
            </div>
            
            <div class="breakdown-container">
                <?php
                $chunks = array_chunk($global_item_totals, ceil(count($global_item_totals) / 2), true);
                if(empty($chunks)) $chunks = [[]];
                ?>

                <div class="breakdown-col">
                    <table class="breakdown-table" style="width:100%">
                        <thead><tr><th>Product Name</th><th class="text-center">Total Qty</th></tr></thead>
                        <tbody>
                            <?php foreach($chunks[0] as $name => $qty): ?>
                            <tr><td><?php echo htmlspecialchars($name); ?></td><td class="text-center bold"><?php echo $qty; ?></td></tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <?php if(isset($chunks[1])): ?>
                <div class="breakdown-col">
                    <table class="breakdown-table" style="width:100%">
                        <thead><tr><th>Product Name</th><th class="text-center">Total Qty</th></tr></thead>
                        <tbody>
                            <?php foreach($chunks[1] as $name => $qty): ?>
                            <tr><td><?php echo htmlspecialchars($name); ?></td><td class="text-center bold"><?php echo $qty; ?></td></tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="signatures">
            <div class="sign-box">Prepared By</div>
            <div class="sign-box">Supervisor / Driver</div>
            <div class="sign-box">Manager</div>
        </div>
    </div>

    <div class="page-break"></div>

    <div class="page" style="padding:0;">
        <?php foreach($final_sorted_groups as $inv): ?>
        
        <div class="invoice-container">
            <div class="inv-top">
                <?php if(!empty($inv['company_logo'])): ?>
                    <img src="<?php echo htmlspecialchars($inv['company_logo']); ?>" class="company-logo" alt="Logo">
                <?php endif; ?>
                <div>
                    <div class="bold" style="font-size: 1.2em;"><?php echo htmlspecialchars($inv['company_name']); ?></div>
                    <div style="font-size: 0.8em; color: #555;">
                        <?php echo htmlspecialchars($inv['company_address']).' - '.htmlspecialchars($inv['company_phone']); ?>
                    </div>
                </div>
                <div class="inv-meta">
                    <div class="bold" style="font-size: 1.2em;">INVOICE</div>
                    <div style="font-size: 0.8em;">Ref IDs: <?php echo $inv['display_ids']; ?></div>
                    <div style="font-size: 0.8em;">Date: <?php echo date('d-M-Y'); ?></div>
                </div>
            </div>

            <div class="bill-strip">
                <div>TO: <span class="uppercase"><?php echo htmlspecialchars($inv['shop_name']); ?></span></div>
                <div>ROUTE: <?php echo htmlspecialchars($inv['route_name']); ?></div>
            </div>

            <table class="inv-table">
                <thead>
                    <tr>
                        <th style="width: 55%;">Item Description</th>
                        <th class="text-center" style="width: 15%;">Qty</th>
                        <th class="text-right" style="width: 15%;">Price</th>
                        <th class="text-right" style="width: 15%;">Total</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $total_qty_inv = 0;
                    foreach($inv['items'] as $item): 
                        $total_qty_inv += $item['quantity'];
                    ?>
                    <tr>
                        <td><?php echo htmlspecialchars($item['item_name']); ?></td>
                        <td class="text-center"><?php echo $item['quantity']; ?></td>
                        <td class="text-right"><?php echo number_format($item['price'], 2); ?></td>
                        <td class="text-right"><?php echo number_format($item['total'], 2); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <div class="flex" style="align-items: flex-start; margin-top: 10px;">
                <div style="width: 60%; font-size: 0.8em; color: #666;">
                    <strong>Total Items:</strong> <?php echo $total_qty_inv; ?> units. Remarks: Received goods in good condition.<br>
                    <em style="font-size: 0.6em; color: #9c9090ff;">Online Web/Cloud generated invoice Developed by kowshiqueroy@gmail.com</em>
                </div>
                <div style="width: 35%;">
                    <table style="width: 100%;">
                        <tr>
                            <td class="text-right bold" style="border:none; font-size: 1.1em;">TOTAL:</td>
                            <td class="text-right bold" style="border:none; font-size: 1.1em; background: #eee;">
                                <?php echo number_format($inv['invoice_total'], 2); ?>
                            </td>
                        </tr>
                    </table>
                </div>
            </div>

            <div class="signatures">
                <div class="sign-box">Prepared By</div>
                <div class="sign-box">Authorized By</div>
                <div class="sign-box">Received By</div>
            </div>
        </div>

        <?php endforeach; ?>
    </div>

</body>
</html>