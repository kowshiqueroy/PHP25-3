<?php
require_once 'includes/auth_check.php';
require_once 'includes/db_connect.php';
require_once 'includes/functions.php';

check_login();
check_role(['Manager', 'QC']);

$conn = connect_db();

// Handle QC Action (Approve/Reject)
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $transaction_id = $_POST['transaction_id'];
    $action = $_POST['action']; // 'approve' or 'reject'
    $comments = $_POST['comments'];
    $user_id = $_SESSION['id'];

    $conn->begin_transaction();

    try {
        // Fetch transaction details
        $stmt_fetch = $conn->prepare("SELECT type, product_id, quantity, batch_id FROM transactions WHERE id = ?");
        $stmt_fetch->bind_param("i", $transaction_id);
        $stmt_fetch->execute();
        $stmt_fetch->bind_result($type, $product_id, $quantity, $batch_id, $person_name, $contact_text, $slip_number);
        $stmt_fetch->fetch();
        $stmt_fetch->close();

        if ($action == 'approve') {
            // Update transaction status
            $stmt_update_trans = $conn->prepare("UPDATE transactions SET qc_status = 'Approved' WHERE id = ?");
            $stmt_update_trans->bind_param("i", $transaction_id);
            $stmt_update_trans->execute();
            $stmt_update_trans->close();

            // Update product_batches quantity based on transaction type
            if ($type == 'IN' || $type == 'Return to Store' || $type == 'Return to Supplier') {
                $stmt_update_batch = $conn->prepare("UPDATE product_batches SET quantity = quantity + ?, qc_status = 'Approved' WHERE id = ?");
                $stmt_update_batch->bind_param("ii", $quantity, $batch_id);
                $stmt_update_batch->execute();
                $stmt_update_batch->close();
                log_audit_trail($user_id, "Stock increased for batch", "product_batch", $batch_id, json_encode(['old_quantity' => 'N/A']), json_encode(['new_quantity_change' => $quantity, 'transaction_type' => $type]));
            } elseif ($type == 'OUT' || $type == 'Mark as Damaged' || $type == 'Expiry Isolation') {
                $stmt_update_batch = $conn->prepare("UPDATE product_batches SET quantity = quantity - ?, qc_status = 'Approved' WHERE id = ?");
                $stmt_update_batch->bind_param("ii", $quantity, $batch_id);
                $stmt_update_batch->execute();
                $stmt_update_batch->close();
                log_audit_trail($user_id, "Stock decreased for batch", "product_batch", $batch_id, json_encode(['old_quantity' => 'N/A']), json_encode(['new_quantity_change' => -$quantity, 'transaction_type' => $type]));
            }

            $_SESSION['message'] = "Transaction #{$transaction_id} approved successfully!";
            $_SESSION['message_type'] = "success";
        } elseif ($action == 'reject') {
            // Update transaction status
            $stmt_update_trans = $conn->prepare("UPDATE transactions SET qc_status = 'Rejected' WHERE id = ?");
            $stmt_update_trans->bind_param("i", $transaction_id);
            $stmt_update_trans->execute();
            $stmt_update_trans->close();

            $_SESSION['message'] = "Transaction #{$transaction_id} rejected.";
            $_SESSION['message_type'] = "warning";
        }

        // Log QC action
        $stmt_log = $conn->prepare("INSERT INTO qc_logs (transaction_id, user_id, status, comments) VALUES (?, ?, ?, ?)");
        $stmt_log->bind_param("iiss", $transaction_id, $user_id, $action, $comments);
        $stmt_log->execute();
        $stmt_log->close();

        // Log to audit trail
        if ($action == 'approve') {
            log_audit_trail($user_id, "Approved QC for transaction", "transaction", $transaction_id, json_encode(['old_qc_status' => 'Pending']), json_encode(['new_qc_status' => 'Approved', 'comments' => $comments]));
        } elseif ($action == 'reject') {
            log_audit_trail($user_id, "Rejected QC for transaction", "transaction", $transaction_id, json_encode(['old_qc_status' => 'Pending']), json_encode(['new_qc_status' => 'Rejected', 'comments' => $comments]));
        }

        $conn->commit();

    } catch (mysqli_sql_exception $exception) {
        $conn->rollback();
        $_SESSION['message'] = "Error processing QC action: " . $exception->getMessage();
        $_SESSION['message_type'] = "danger";
    }
    header("location: qc_workflow.php");
    exit();
}

// Fetch pending transactions for QC
$pending_transactions = [];
$result = $conn->query("SELECT t.id, t.type, u.username, p.name as product_name, t.quantity, t.comments, t.created_at, t.person_name, t.contact_text, t.slip_number FROM transactions t JOIN users u ON t.user_id = u.id JOIN products p ON t.product_id = p.id WHERE t.qc_status = 'Pending' ORDER BY t.created_at DESC");
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $pending_transactions[] = $row;
    }
}
$conn->close();

$user_role_name = get_user_role_name($_SESSION['role_id']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>QC Workflow - <?php echo APP_NAME; ?></title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="#"><?php echo APP_NAME; ?></a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                    <li class="nav-item">
                        <a class="nav-link" href="dashboard.php">Dashboard</a>
                    </li>
                    <?php if ($user_role_name == 'Admin' || $user_role_name == 'Manager'): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="user_management.php">User Management</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="store_management.php">Store Management</a>
                    </li>
                    <?php endif; ?>
                    <?php if ($user_role_name == 'Admin' || $user_role_name == 'Manager' || $user_role_name == 'Data Entry'): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="product_management.php">Product Management</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="category_management.php">Category Management</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="product_batch_management.php">Product Batch Management</a>
                    </li>
                    <?php endif; ?>
                    <?php if ($user_role_name == 'Admin' || $user_role_name == 'Manager' || $user_role_name == 'Data Entry' || $user_role_name == 'QC'): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="transactions.php">Transactions</a>
                    </li>
                    <?php endif; ?>
                    <?php if ($user_role_name == 'Admin' || $user_role_name == 'Manager' || $user_role_name == 'QC'): ?>
                    <li class="nav-item">
                        <a class="nav-link active" aria-current="page" href="qc_workflow.php">QC Workflow</a>
                    </li>
                    <?php endif; ?>
                    <?php if ($user_role_name == 'Admin' || $user_role_name == 'Manager' || $user_role_name == 'Viewer'): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="reports.php">Reports</a>
                    </li>
                    <?php endif; ?>
                </ul>
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <span class="navbar-text me-3">
                            Logged in as: <?php echo htmlspecialchars($_SESSION['username']); ?> (<?php echo htmlspecialchars($user_role_name); ?>)
                        </span>
                    </li>
                    <li class="nav-item">
                        <a class="btn btn-outline-light" href="logout.php">Logout</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <h2>QC Workflow - Pending Transactions</h2>

        <?php if (isset($_SESSION['message'])): ?>
            <div class="alert alert-<?php echo $_SESSION['message_type']; ?> alert-dismissible fade show" role="alert">
                <?php echo $_SESSION['message']; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            <?php 
            unset($_SESSION['message']);
            unset($_SESSION['message_type']);
            ?>
        <?php endif; ?>

        <div class="card">
            <div class="card-header">
                Transactions Awaiting QC Approval
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>Trans. ID</th>
                                <th>Type</th>
                                <th>Product</th>
                                <th>Quantity</th>
                                <th>Initiated By</th>
                                <th>Comments</th>
                                <th>Person Name</th>
                                <th>Contact</th>
                                <th>Slip No.</th>
                                <th>Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($pending_transactions)): ?>
                                <tr>
                                    <td colspan="11" class="text-center">No transactions awaiting QC approval.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($pending_transactions as $transaction): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($transaction['id']); ?></td>
                                        <td><?php echo htmlspecialchars($transaction['type']); ?></td>
                                        <td><?php echo htmlspecialchars($transaction['product_name']); ?></td>
                                        <td><?php echo htmlspecialchars($transaction['quantity']); ?></td>
                                        <td><?php echo htmlspecialchars($transaction['username']); ?></td>
                                        <td><?php echo htmlspecialchars($transaction['comments']); ?></td>
                                        <td><?php echo htmlspecialchars($transaction['person_name']); ?></td>
                                        <td><?php echo htmlspecialchars($transaction['contact_text']); ?></td>
                                        <td><?php echo htmlspecialchars($transaction['slip_number']); ?></td>
                                        <td><?php echo htmlspecialchars($transaction['created_at']); ?></td>
                                        <td>
                                            <button type="button" class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#qcActionModal" 
                                                    data-id="<?php echo $transaction['id']; ?>" data-action="approve">
                                                Approve
                                            </button>
                                            <button type="button" class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#qcActionModal" 
                                                    data-id="<?php echo $transaction['id']; ?>" data-action="reject">
                                                Reject
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- QC Action Modal -->
    <div class="modal fade" id="qcActionModal" tabindex="-1" aria-labelledby="qcActionModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="qcActionModalLabel">QC Action</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="qc_workflow.php" method="POST">
                    <div class="modal-body">
                        <input type="hidden" id="qc_transaction_id" name="transaction_id">
                        <input type="hidden" id="qc_action" name="action">
                        <p>Transaction ID: <strong id="modal_transaction_id"></strong></p>
                        <p>Action: <strong id="modal_action_type"></strong></p>
                        <div class="mb-3">
                            <label for="qc_comments" class="form-label">Comments (Required)</label>
                            <textarea class="form-control" id="qc_comments" name="comments" rows="3" required></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary" id="modal_submit_button">Confirm</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        var qcActionModal = document.getElementById('qcActionModal');
        qcActionModal.addEventListener('show.bs.modal', function (event) {
            var button = event.relatedTarget;
            var transactionId = button.getAttribute('data-id');
            var action = button.getAttribute('data-action');

            var modalTransactionId = qcActionModal.querySelector('#modal_transaction_id');
            var modalActionType = qcActionModal.querySelector('#modal_action_type');
            var qcTransactionIdInput = qcActionModal.querySelector('#qc_transaction_id');
            var qcActionInput = qcActionModal.querySelector('#qc_action');
            var modalSubmitButton = qcActionModal.querySelector('#modal_submit_button');

            modalTransactionId.textContent = transactionId;
            modalActionType.textContent = action.charAt(0).toUpperCase() + action.slice(1);
            qcTransactionIdInput.value = transactionId;
            qcActionInput.value = action;

            if (action === 'approve') {
                modalSubmitButton.classList.remove('btn-danger');
                modalSubmitButton.classList.add('btn-success');
                modalSubmitButton.textContent = 'Approve';
            } else if (action === 'reject') {
                modalSubmitButton.classList.remove('btn-success');
                modalSubmitButton.classList.add('btn-danger');
                modalSubmitButton.textContent = 'Reject';
            }

            qcActionModal.querySelector('#qc_comments').value = ''; // Clear comments
        });
    </script>
</body>
</html>