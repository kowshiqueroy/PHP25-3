<?php
require_once 'includes/auth_check.php';
require_once 'includes/db_connect.php';
require_once 'includes/functions.php';

check_login();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $transaction_type = $_POST['transaction_type'] ?? '';
    $user_id = $_SESSION['id'];
    $store_id = $_SESSION['store_id'];
    $product_id = $_POST['product_id'] ?? '';
    $quantity = $_POST['quantity'] ?? '';
    $comments = $_POST['comments'] ?? '';
    $person_name = $_POST['person_name'] ?? NULL;
    $contact_text = $_POST['contact_text'] ?? NULL;
    $slip_number = $_POST['slip_number'] ?? NULL;

    $conn = connect_db();

    switch ($transaction_type) {
        case 'IN':
            $expiry_date = empty($_POST['expiry_date']) ? NULL : $_POST['expiry_date'];
            $storage_location = $_POST['storage_location'] ?? '';

            // Start a transaction
            $conn->begin_transaction();

            try {
                // Insert into product_batches
                $stmt_batch = $conn->prepare("INSERT INTO product_batches (product_id, store_id, expiry_date, storage_location, quantity) VALUES (?, ?, ?, ?, ?)");
                $stmt_batch->bind_param("iissi", $product_id, $store_id, $expiry_date, $storage_location, $quantity);
                $stmt_batch->execute();
                $batch_id = $conn->insert_id;
                $stmt_batch->close();

                // Insert into transactions
                // For IN transactions, qc_status is initially 'Pending'
                $qc_status = 'Pending';
                $stmt_transaction = $conn->prepare("INSERT INTO transactions (type, user_id, store_id, batch_id, product_id, quantity, comments, qc_status, person_name, contact_text, slip_number) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt_transaction->bind_param("siiiissssss", $transaction_type, $user_id, $store_id, $batch_id, $product_id, $quantity, $comments, $qc_status, $person_name, $contact_text, $slip_number);
                $stmt_transaction->execute();
                $transaction_id = $conn->insert_id;
                $stmt_transaction->close();

                log_audit_trail($user_id, "Recorded IN transaction", "transaction", $transaction_id, NULL, json_encode(['product_id' => $product_id, 'quantity' => $quantity, 'batch_id' => $batch_id]));

                $conn->commit();
                $_SESSION['message'] = "IN transaction recorded and batch created successfully!";
                $_SESSION['message_type'] = "success";

            } catch (mysqli_sql_exception $exception) {
                $conn->rollback();
                $_SESSION['message'] = "Error recording IN transaction: " . $exception->getMessage();
                $_SESSION['message_type'] = "danger";
            }
            break;
        case 'OUT':
            $batch_id = $_POST['batch_id'] ?? NULL;
            $qc_status = 'Pending';

            $conn->begin_transaction();
            try {
                // Check if batch exists and has enough quantity
                $stmt_check_batch = $conn->prepare("SELECT quantity FROM product_batches WHERE id = ? AND product_id = ? AND store_id = ?");
                $stmt_check_batch->bind_param("iii", $batch_id, $product_id, $store_id);
                $stmt_check_batch->execute();
                $stmt_check_batch->bind_result($available_quantity);
                $stmt_check_batch->fetch();
                $stmt_check_batch->close();

                if ($available_quantity < $quantity) {
                    throw new Exception("Insufficient quantity in selected batch.");
                }

                $stmt_transaction = $conn->prepare("INSERT INTO transactions (type, user_id, store_id, batch_id, product_id, quantity, comments, qc_status, person_name, contact_text, slip_number) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt_transaction->bind_param("siiiissssss", $transaction_type, $user_id, $store_id, $batch_id, $product_id, $quantity, $comments, $qc_status, $person_name, $contact_text, $slip_number);
                $stmt_transaction->execute();
                $transaction_id = $conn->insert_id;
                $stmt_transaction->close();

                log_audit_trail($user_id, "Recorded OUT transaction", "transaction", $transaction_id, NULL, json_encode(['product_id' => $product_id, 'quantity' => $quantity, 'batch_id' => $batch_id]));

                $conn->commit();
                $_SESSION['message'] = "OUT transaction recorded successfully and awaiting QC approval!";
                $_SESSION['message_type'] = "success";
            } catch (Exception $e) {
                $conn->rollback();
                $_SESSION['message'] = "Error recording OUT transaction: " . $e->getMessage();
                $_SESSION['message_type'] = "danger";
            }
            break;

        case 'Return to Store':
            $batch_id = $_POST['batch_id'] ?? NULL;
            $qc_status = 'Pending';

            $conn->begin_transaction();
            try {
                $stmt_transaction = $conn->prepare("INSERT INTO transactions (type, user_id, store_id, batch_id, product_id, quantity, comments, qc_status, person_name, contact_text, slip_number) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt_transaction->bind_param("siiiissssss", $transaction_type, $user_id, $store_id, $batch_id, $product_id, $quantity, $comments, $qc_status, $person_name, $contact_text, $slip_number);
                $stmt_transaction->execute();
                $transaction_id = $conn->insert_id;
                $stmt_transaction->close();

                log_audit_trail($user_id, "Recorded Return to Store transaction", "transaction", $transaction_id, NULL, json_encode(['product_id' => $product_id, 'quantity' => $quantity, 'batch_id' => $batch_id]));

                $conn->commit();
                $_SESSION['message'] = "Return to Store transaction recorded successfully and awaiting QC approval!";
                $_SESSION['message_type'] = "success";
            } catch (mysqli_sql_exception $exception) {
                $conn->rollback();
                $_SESSION['message'] = "Error recording Return to Store transaction: " . $exception->getMessage();
                $_SESSION['message_type'] = "danger";
            }
            break;

        case 'Return to Supplier':
            $batch_id = $_POST['batch_id'] ?? NULL;
            $qc_status = 'Pending';

            $conn->begin_transaction();
            try {
                $stmt_transaction = $conn->prepare("INSERT INTO transactions (type, user_id, store_id, batch_id, product_id, quantity, comments, qc_status, person_name, contact_text, slip_number) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt_transaction->bind_param("siiiissssss", $transaction_type, $user_id, $store_id, $batch_id, $product_id, $quantity, $comments, $qc_status, $person_name, $contact_text, $slip_number);
                $stmt_transaction->execute();
                $transaction_id = $conn->insert_id;
                $stmt_transaction->close();

                log_audit_trail($user_id, "Recorded Return to Supplier transaction", "transaction", $transaction_id, NULL, json_encode(['product_id' => $product_id, 'quantity' => $quantity, 'batch_id' => $batch_id]));

                $conn->commit();
                $_SESSION['message'] = "Return to Supplier transaction recorded successfully and awaiting QC approval!";
                $_SESSION['message_type'] = "success";
            } catch (mysqli_sql_exception $exception) {
                $conn->rollback();
                $_SESSION['message'] = "Error recording Return to Supplier transaction: " . $exception->getMessage();
                $_SESSION['message_type'] = "danger";
            }
            break;

        case 'Mark as Damaged':
            $batch_id = $_POST['batch_id'] ?? NULL;
            $qc_status = 'Pending';

            $conn->begin_transaction();
            try {
                $stmt_transaction = $conn->prepare("INSERT INTO transactions (type, user_id, store_id, batch_id, product_id, quantity, comments, qc_status, person_name, contact_text, slip_number) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt_transaction->bind_param("siiiissssss", $transaction_type, $user_id, $store_id, $batch_id, $product_id, $quantity, $comments, $qc_status, $person_name, $contact_text, $slip_number);
                $stmt_transaction->execute();
                $transaction_id = $conn->insert_id;
                $stmt_transaction->close();

                log_audit_trail($user_id, "Recorded Mark as Damaged transaction", "transaction", $transaction_id, NULL, json_encode(['product_id' => $product_id, 'quantity' => $quantity, 'batch_id' => $batch_id]));

                $conn->commit();
                $_SESSION['message'] = "Mark as Damaged transaction recorded successfully and awaiting QC approval!";
                $_SESSION['message_type'] = "success";
            } catch (mysqli_sql_exception $exception) {
                $conn->rollback();
                $_SESSION['message'] = "Error recording Mark as Damaged transaction: " . $exception->getMessage();
                $_SESSION['message_type'] = "danger";
            }
            break;

        case 'Expiry Isolation':
            $batch_id = $_POST['batch_id'] ?? NULL;
            $qc_status = 'Pending';

            $conn->begin_transaction();
            try {
                $stmt_transaction = $conn->prepare("INSERT INTO transactions (type, user_id, store_id, batch_id, product_id, quantity, comments, qc_status, person_name, contact_text, slip_number) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt_transaction->bind_param("siiiissssss", $transaction_type, $user_id, $store_id, $batch_id, $product_id, $quantity, $comments, $qc_status, $person_name, $contact_text, $slip_number);
                $stmt_transaction->execute();
                $transaction_id = $conn->insert_id;
                $stmt_transaction->close();

                log_audit_trail($user_id, "Recorded Expiry Isolation transaction", "transaction", $transaction_id, NULL, json_encode(['product_id' => $product_id, 'quantity' => $quantity, 'batch_id' => $batch_id]));

                $conn->commit();
                $_SESSION['message'] = "Expiry Isolation transaction recorded successfully and awaiting QC approval!";
                $_SESSION['message_type'] = "success";
            } catch (mysqli_sql_exception $exception) {
                $conn->rollback();
                $_SESSION['message'] = "Error recording Expiry Isolation transaction: " . $exception->getMessage();
                $_SESSION['message_type'] = "danger";
            }
            break;
        default:
            $_SESSION['message'] = "Invalid transaction type.";
            $_SESSION['message_type'] = "danger";
            break;
    }

    $conn->close();
    header("location: transactions.php");
    exit();
}

header("location: transactions.php");
exit();
?>