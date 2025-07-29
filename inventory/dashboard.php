<?php
require_once 'includes/auth_check.php';
require_once 'includes/db_connect.php';
require_once 'includes/functions.php';

check_login();

// Get user role name for display
$user_role_name = get_user_role_name($_SESSION['role_id']);
$store_id = $_SESSION['store_id'];

$pending_qc_count = 0;
$low_stock_count = 0;
$expired_batches_count = 0;

// Fetch counts based on user role
if ($user_role_name == 'Admin' || $user_role_name == 'Manager' || $user_role_name == 'QC') {
    $pending_qc_count = get_pending_qc_count($store_id);
}

if ($user_role_name == 'Admin' || $user_role_name == 'Manager' || $user_role_name == 'Data Entry' || $user_role_name == 'Purchaser') {
    $low_stock_count = get_low_stock_count($store_id);
}

if ($user_role_name == 'Admin' || $user_role_name == 'Manager' || $user_role_name == 'Data Entry') {
    $expired_batches_count = get_expired_batches_count($store_id);
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - <?php echo APP_NAME; ?></title>
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
                        <a class="nav-link active" aria-current="page" href="dashboard.php">Dashboard</a>
                    </li>
                    <?php if ($user_role_name == 'Admin' || $user_role_name == 'Manager'): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="user_management.php">User Management</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="store_management.php">Store Management</a>
                    </li>
                    <?php endif; ?>
                    <?php if ($user_role_name == 'Manager' || $user_role_name == 'Data Entry'): ?>
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
                    <?php if ($user_role_name == 'Manager' || $user_role_name == 'Data Entry' || $user_role_name == 'QC'): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="transactions.php">Transactions</a>
                    </li>
                    <?php endif; ?>
                    <?php if ($user_role_name == 'Manager' || $user_role_name == 'QC'): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="qc_workflow.php">QC Workflow</a>
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
        <div class="alert alert-success" role="alert">
            Welcome to your Dashboard, <?php echo htmlspecialchars($_SESSION['username']); ?>!
        </div>
        <p>Your role: <strong><?php echo htmlspecialchars($user_role_name); ?></strong></p>
        <p>Your store: <strong><?php echo htmlspecialchars($_SESSION['store_id']); ?></strong></p>

        <!-- Dashboard Widgets will go here -->
        <div class="row">
            <div class="col-md-4">
                <div class="card mb-3">
                    <div class="card-body">
                        <h5 class="card-title">Pending QC Actions</h5>
                        <p class="card-text"><?php echo $pending_qc_count; ?></p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card mb-3">
                    <div class="card-body">
                        <h5 class="card-title">Low Stock Alerts</h5>
                        <p class="card-text"><?php echo $low_stock_count; ?></p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card mb-3">
                    <div class="card-body">
                        <h5 class="card-title">Expired Batches</h5>
                        <p class="card-text"><?php echo $expired_batches_count; ?></p>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <!-- Bootstrap JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>