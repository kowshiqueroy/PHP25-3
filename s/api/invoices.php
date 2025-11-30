<?php
// api/invoices.php

require_once '../config/config.php';
require_once '../utils/log_action.php';

session_start();
header('Content-Type: application/json');

// --- Security & Permission Check ---
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['user_role'], ['SR', 'Manager'])) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Access denied.']);
    exit;
}

$request_method = $_SERVER['REQUEST_METHOD'];

switch ($request_method) {
    case 'POST':
        handle_create_invoice();
        break;
    case 'GET':
        handle_get_invoices();
        break;
    case 'PUT':
        handle_update_invoice_status();
        break;
    // Add cases for DELETE later
    default:
        http_response_code(405); // Method Not Allowed
        echo json_encode(['success' => false, 'message' => 'Method not allowed.']);
        break;
}

function handle_update_invoice_status() {
    // Check for Manager role
    if ($_SESSION['user_role'] !== 'Manager') {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'Only managers can perform this action.']);
        return;
    }

    $input = json_decode(file_get_contents('php://input'), true);
    $action = $input['action'] ?? 'single_update';

    $pdo = get_db_connection();
    try {
        $pdo->beginTransaction();

        if ($action === 'bulk_update_status') {
            $invoice_ids = $input['invoice_ids'] ?? [];
            $new_status = $input['status'] ?? null;
            if (empty($invoice_ids) || !$new_status) {
                throw new Exception('Invoice IDs and status are required for bulk update.');
            }

                $input = json_decode(file_get_contents('php://input'), true);
    $action = $input['action'] ?? 'single_update';

    if ($action === 'submit_for_printing') {
        handle_submit_for_printing();
        return;
    }

    // --- The rest of the function for Manager actions ---
    if ($_SESSION['user_role'] !== 'Manager') {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'Only managers can perform this action.']);
        return;
    }
 else { // Single update
            $invoice_id = $input['invoice_id'] ?? null;
            $new_status = $input['status'] ?? null;

            $allowed_statuses = ['Approved', 'Rejected', 'On Process', 'On Delivery', 'Delivered', 'Returned', 'Damaged'];
            if (!$invoice_id || !$new_status || !in_array($new_status, $allowed_statuses)) {
                throw new Exception('Invalid data provided for single update.');
            }

            $stmt = $pdo->prepare("SELECT company_id FROM invoices WHERE id = ?");
            $stmt->execute([$invoice_id]);
            $invoice = $stmt->fetch();

            if (!$invoice || $invoice['company_id'] != $_SESSION['company_id']) {
                throw new Exception('Invoice not found or access denied.');
            }

            $updateStmt = $pdo->prepare("UPDATE invoices SET status = ? WHERE id = ?");
            $updateStmt->execute([$new_status, $invoice_id]);

            log_action($pdo, $_SESSION['user_id'], 'UPDATE_INVOICE_STATUS', "Changed status of invoice #$invoice_id to $new_status");
            $message = "Invoice #$invoice_id has been $new_status.";
        }

        $pdo->commit();
        echo json_encode(['success' => true, 'message' => $message]);

    } catch (Exception $e) {
        $pdo->rollBack();
        error_log("Invoice status update failed: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Failed to update invoice status. ' . $e->getMessage()]);
    }
}

function handle_submit_for_printing() {
    if ($_SESSION['user_role'] !== 'SR') {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'Only Sales Reps can submit invoices.']);
        return;
    }

    $pdo = get_db_connection();
    try {
        $pdo->beginTransaction();

        // Find the next queue order number for the company
        $stmt = $pdo->prepare("SELECT MAX(print_queue_order) as max_order FROM invoices WHERE company_id = ?");
        $stmt->execute([$_SESSION['company_id']]);
        $max_order = $stmt->fetchColumn() ?? 0;

        // Find all confirmed invoices by this SR that haven't been submitted
        $invoicesToSubmit = $pdo->prepare(
            "SELECT id FROM invoices 
             WHERE sr_id = ? AND status = 'Confirmed' AND submitted_at IS NULL 
             ORDER BY created_at ASC"
        );
        $invoicesToSubmit->execute([$_SESSION['user_id']]);
        $invoices = $invoicesToSubmit->fetchAll();

        if (empty($invoices)) {
            throw new Exception('No confirmed invoices to submit.');
        }

        // Assign queue numbers
        $updateStmt = $pdo->prepare("UPDATE invoices SET submitted_at = NOW(), print_queue_order = ? WHERE id = ?");
        $current_order = $max_order;
        foreach ($invoices as $invoice) {
            $current_order++;
            $updateStmt->execute([$current_order, $invoice['id']]);
        }

        log_action($pdo, $_SESSION['user_id'], 'SUBMIT_INVOICES', 'Submitted ' . count($invoices) . ' invoices to the print queue.');
        
        $pdo->commit();
        echo json_encode(['success' => true, 'message' => count($invoices) . ' invoices have been submitted to the print queue.']);

    } catch (Exception $e) {
        $pdo->rollBack();
        error_log("Invoice submission failed: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
}

function handle_get_invoices() {
    $user_id = $_SESSION['user_id'];
    $role = $_SESSION['user_role'];
    $company_id = $_SESSION['company_id'];

    $pdo = get_db_connection();
    
    $sql = "SELECT i.id, i.status, i.grand_total, i.delivery_date, s.name as shop_name, u.username as sr_name
            FROM invoices i
            JOIN shops s ON i.shop_id = s.id
            JOIN users u ON i.sr_id = u.id
            WHERE i.company_id = ?";
    
    $params = [$company_id];

    // --- Handle Special Case for Print Queue ---
    if (isset($_GET['queue']) && $_GET['queue'] == 'true' && $role === 'Manager') {
        $sql .= " AND i.status = 'Approved' AND i.print_queue_order IS NOT NULL ORDER BY i.print_queue_order ASC";
    } else {
        // --- Default behavior ---
        if ($role === 'SR') {
            $sql .= " AND i.sr_id = ?";
            $params[] = $user_id;
        } elseif ($role === 'Viewer') {
            $sql .= " AND i.status = 'Approved'";
        }

        // Filtering for Viewer
        if (!empty($_GET['start_date'])) {
            $sql .= " AND i.delivery_date >= ?";
            $params[] = $_GET['start_date'];
        }
        if (!empty($_GET['end_date'])) {
            $sql .= " AND i.delivery_date <= ?";
            $params[] = $_GET['end_date'];
        }
        if (!empty($_GET['sr_id'])) {
            $sql .= " AND i.sr_id = ?";
            $params[] = $_GET['sr_id'];
        }
        if (!empty($_GET['route_id'])) {
            $sql .= " AND i.route_id = ?";
            $params[] = $_GET['route_id'];
        }
        if (!empty($_GET['shop_id'])) {
            $sql .= " AND i.shop_id = ?";
            $params[] = $_GET['shop_id'];
        }

        $sql .= " ORDER BY i.created_at DESC";
    }

    try {
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $invoices = $stmt->fetchAll();
        echo json_encode(['success' => true, 'data' => $invoices]);
    } catch (Exception $e) {
        error_log("Failed to fetch invoices: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Failed to fetch invoices.']);
    }
}


function handle_create_invoice() {
    $user_id = $_SESSION['user_id'];
    $company_id = $_SESSION['company_id'];

    // --- Form Data Validation ---
    $route_id = $_POST['route_id'] ?? null;
    $shop_id = $_POST['shop_id'] ?? null;
    $order_date = $_POST['order_date'] ?? null;
    $delivery_date = $_POST['delivery_date'] ?? null;
    $remarks = $_POST['remarks'] ?? '';
    $action = $_POST['action'] ?? 'save_draft'; // 'save_draft' or 'confirm_invoice'
    $items = $_POST['items'] ?? [];

    if (!$route_id || !$shop_id || !$order_date || !$delivery_date || empty($items)) {
        echo json_encode(['success' => false, 'message' => 'Missing required invoice data.']);
        return;
    }

    $status = ($action === 'confirm_invoice') ? 'Confirmed' : 'Draft';
    $grand_total = 0;

    // --- Calculate Grand Total & Validate Items ---
    foreach ($items as $item) {
        if (empty($item['item_id']) || !is_numeric($item['quantity']) || !is_numeric($item['rate'])) {
            echo json_encode(['success' => false, 'message' => 'Invalid item data provided.']);
            return;
        }
        $grand_total += (float)$item['quantity'] * (float)$item['rate'];
    }

    $pdo = get_db_connection();
    try {
        $pdo->beginTransaction();

        // 1. Insert into `invoices` table
        $stmt = $pdo->prepare(
            "INSERT INTO invoices (company_id, sr_id, route_id, shop_id, order_date, delivery_date, grand_total, remarks, status) 
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)"
        );
        $stmt->execute([$company_id, $user_id, $route_id, $shop_id, $order_date, $delivery_date, $grand_total, $remarks, $status]);
        $invoice_id = $pdo->lastInsertId();

        // 2. Insert into `invoice_items` table
        $itemStmt = $pdo->prepare(
            "INSERT INTO invoice_items (invoice_id, item_id, quantity, rate, total) VALUES (?, ?, ?, ?, ?)"
        );
        foreach ($items as $item) {
            $total = (float)$item['quantity'] * (float)$item['rate'];
            $itemStmt->execute([$invoice_id, $item['item_id'], $item['quantity'], $item['rate'], $total]);
        }

        // 3. Log the action
        $log_details = "Created new invoice #$invoice_id with status: $status";
        log_action($pdo, $user_id, 'CREATE_INVOICE', $log_details);

        $pdo->commit();
        echo json_encode(['success' => true, 'message' => "Invoice #$invoice_id saved successfully as $status."]);

    } catch (Exception $e) {
        $pdo->rollBack();
        error_log("Invoice creation failed: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Failed to create invoice. An error occurred.']);
    }
}
