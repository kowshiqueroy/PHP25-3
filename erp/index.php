<?php
// Start session
session_start();

// Check if user is logged in
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    header("location: login.php");
    exit;
}

// Include database connection
require_once "config/conn.php";

// Get user information
$user_id = $_SESSION["id"];
$sql = "SELECT * FROM users WHERE id = ?";

if($stmt = mysqli_prepare($conn, $sql)){
    mysqli_stmt_bind_param($stmt, "i", $user_id);
    
    if(mysqli_stmt_execute($stmt)){
        $result = mysqli_stmt_get_result($stmt);
        
        if(mysqli_num_rows($result) == 1){
            $user = mysqli_fetch_assoc($result);
        } else{
            // User not found
            session_destroy();
            header("location: login.php");
            exit;
        }
    } else{
        echo "Oops! Something went wrong. Please try again later.";
    }
    
    mysqli_stmt_close($stmt);
}

// Function to check if user has access to a module
function hasAccess($user, $module) {
    if ($user['role'] == 'admin') {
        return true;
    }
    
    if ($user['module_access'] == 'all') {
        return true;
    }
    
    $modules = explode(',', $user['module_access']);
    return in_array($module, $modules);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ERP System - Dashboard</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
</head>
<body>
    <div class="wrapper">
        <!-- Sidebar -->
        <nav id="sidebar">
            <div class="sidebar-header">
                <h3>ERP System</h3>
            </div>

            <ul class="list-unstyled components">
                <li class="active">
                    <a href="index.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
                </li>
                
                <?php if(hasAccess($user, 'admin')): ?>
                <li>
                    <a href="#adminSubmenu" data-toggle="collapse" aria-expanded="false" class="dropdown-toggle">
                        <i class="fas fa-user-shield"></i> Administration
                    </a>
                    <ul class="collapse list-unstyled" id="adminSubmenu">
                        <li>
                            <a href="admin/users.php">User Management</a>
                        </li>
                        <li>
                            <a href="admin/roles.php">Role Management</a>
                        </li>
                        <li>
                            <a href="admin/settings.php">System Settings</a>
                        </li>
                    </ul>
                </li>
                <?php endif; ?>
                
                <?php if(hasAccess($user, 'hr')): ?>
                <li>
                    <a href="#hrSubmenu" data-toggle="collapse" aria-expanded="false" class="dropdown-toggle">
                        <i class="fas fa-users"></i> HR Management
                    </a>
                    <ul class="collapse list-unstyled" id="hrSubmenu">
                        <li>
                            <a href="modules/hr/employees.php">Employees</a>
                        </li>
                        <li>
                            <a href="modules/hr/attendance.php">Attendance</a>
                        </li>
                        <li>
                            <a href="modules/hr/id_cards.php">ID Cards</a>
                        </li>
                        <li>
                            <a href="modules/hr/reports.php">Reports</a>
                        </li>
                    </ul>
                </li>
                <?php endif; ?>
                
                <?php if(hasAccess($user, 'accounts')): ?>
                <li>
                    <a href="#accountsSubmenu" data-toggle="collapse" aria-expanded="false" class="dropdown-toggle">
                        <i class="fas fa-calculator"></i> Accounts
                    </a>
                    <ul class="collapse list-unstyled" id="accountsSubmenu">
                        <li>
                            <a href="modules/accounts/chart_of_accounts.php">Chart of Accounts</a>
                        </li>
                        <li>
                            <a href="modules/accounts/journal_entries.php">Journal Entries</a>
                        </li>
                        <li>
                            <a href="modules/accounts/ledger.php">General Ledger</a>
                        </li>
                        <li>
                            <a href="modules/accounts/financial_statements.php">Financial Statements</a>
                        </li>
                    </ul>
                </li>
                <?php endif; ?>
                
                <?php if(hasAccess($user, 'store')): ?>
                <li>
                    <a href="#storeSubmenu" data-toggle="collapse" aria-expanded="false" class="dropdown-toggle">
                        <i class="fas fa-warehouse"></i> Store Management
                    </a>
                    <ul class="collapse list-unstyled" id="storeSubmenu">
                        <li>
                            <a href="modules/store/inventory.php">Inventory</a>
                        </li>
                        <li>
                            <a href="modules/store/godowns.php">Godowns</a>
                        </li>
                        <li>
                            <a href="modules/store/requisitions.php">Requisitions</a>
                        </li>
                        <li>
                            <a href="modules/store/transfers.php">Transfers</a>
                        </li>
                    </ul>
                </li>
                <?php endif; ?>
                
                <?php if(hasAccess($user, 'purchase')): ?>
                <li>
                    <a href="#purchaseSubmenu" data-toggle="collapse" aria-expanded="false" class="dropdown-toggle">
                        <i class="fas fa-shopping-cart"></i> Purchase
                    </a>
                    <ul class="collapse list-unstyled" id="purchaseSubmenu">
                        <li>
                            <a href="modules/purchase/suppliers.php">Suppliers</a>
                        </li>
                        <li>
                            <a href="modules/purchase/purchase_orders.php">Purchase Orders</a>
                        </li>
                        <li>
                            <a href="modules/purchase/goods_receipt.php">Goods Receipt</a>
                        </li>
                    </ul>
                </li>
                <?php endif; ?>
                
                <?php if(hasAccess($user, 'gate')): ?>
                <li>
                    <a href="#gateSubmenu" data-toggle="collapse" aria-expanded="false" class="dropdown-toggle">
                        <i class="fas fa-door-open"></i> Gate Management
                    </a>
                    <ul class="collapse list-unstyled" id="gateSubmenu">
                        <li>
                            <a href="modules/gate/inward.php">Inward Register</a>
                        </li>
                        <li>
                            <a href="modules/gate/outward.php">Outward Register</a>
                        </li>
                        <li>
                            <a href="modules/gate/reports.php">Gate Reports</a>
                        </li>
                    </ul>
                </li>
                <?php endif; ?>
                
                <?php if(hasAccess($user, 'distribution')): ?>
                <li>
                    <a href="#distributionSubmenu" data-toggle="collapse" aria-expanded="false" class="dropdown-toggle">
                        <i class="fas fa-truck"></i> Distribution
                    </a>
                    <ul class="collapse list-unstyled" id="distributionSubmenu">
                        <li>
                            <a href="modules/distribution/customers.php">Customers</a>
                        </li>
                        <li>
                            <a href="modules/distribution/sales.php">Sales</a>
                        </li>
                        <li>
                            <a href="modules/distribution/returns.php">Returns</a>
                        </li>
                    </ul>
                </li>
                <?php endif; ?>
                
                <?php if(hasAccess($user, 'qc')): ?>
                <li>
                    <a href="#qcSubmenu" data-toggle="collapse" aria-expanded="false" class="dropdown-toggle">
                        <i class="fas fa-check-circle"></i> Quality Control
                    </a>
                    <ul class="collapse list-unstyled" id="qcSubmenu">
                        <li>
                            <a href="modules/qc/inspections.php">Inspections</a>
                        </li>
                        <li>
                            <a href="modules/qc/returns.php">Return Verification</a>
                        </li>
                        <li>
                            <a href="modules/qc/reports.php">QC Reports</a>
                        </li>
                    </ul>
                </li>
                <?php endif; ?>
                
                <li>
                    <a href="#reportsSubmenu" data-toggle="collapse" aria-expanded="false" class="dropdown-toggle">
                        <i class="fas fa-chart-bar"></i> Reports
                    </a>
                    <ul class="collapse list-unstyled" id="reportsSubmenu">
                        <li>
                            <a href="reports/financial.php">Financial Reports</a>
                        </li>
                        <li>
                            <a href="reports/inventory.php">Inventory Reports</a>
                        </li>
                        <li>
                            <a href="reports/sales.php">Sales Reports</a>
                        </li>
                        <li>
                            <a href="reports/purchase.php">Purchase Reports</a>
                        </li>
                    </ul>
                </li>
            </ul>
        </nav>

        <!-- Page Content -->
        <div id="content">
            <nav class="navbar navbar-expand-lg navbar-light bg-light">
                <div class="container-fluid">
                    <button type="button" id="sidebarCollapse" class="btn btn-info">
                        <i class="fas fa-align-left"></i>
                        <span>Toggle Sidebar</span>
                    </button>
                    
                    <div class="collapse navbar-collapse" id="navbarSupportedContent">
                        <ul class="nav navbar-nav ml-auto">
                            <li class="nav-item">
                                <a class="nav-link" href="#">
                                    <i class="fas fa-bell"></i>
                                    <span class="badge badge-warning">3</span>
                                </a>
                            </li>
                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                    <i class="fas fa-user"></i> <?php echo htmlspecialchars($user["full_name"]); ?>
                                </a>
                                <div class="dropdown-menu dropdown-menu-right" aria-labelledby="navbarDropdown">
                                    <a class="dropdown-item" href="profile.php">My Profile</a>
                                    <a class="dropdown-item" href="change_password.php">Change Password</a>
                                    <div class="dropdown-divider"></div>
                                    <a class="dropdown-item" href="logout.php">Logout</a>
                                </div>
                            </li>
                        </ul>
                    </div>
                </div>
            </nav>

            <div class="container-fluid">
                <h2>Dashboard</h2>
                <p>Welcome to the ERP System Dashboard, <?php echo htmlspecialchars($user["full_name"]); ?>!</p>
                
                <div class="row">
                    <!-- Dashboard widgets will go here -->
                    <div class="col-md-3">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title">Employees</h5>
                                <h1 class="card-text text-center">0</h1>
                                <p class="card-text">Total employees in the system</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-3">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title">Products</h5>
                                <h1 class="card-text text-center">0</h1>
                                <p class="card-text">Total products in inventory</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-3">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title">Sales</h5>
                                <h1 class="card-text text-center">$0</h1>
                                <p class="card-text">Total sales this month</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-3">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title">Purchases</h5>
                                <h1 class="card-text text-center">$0</h1>
                                <p class="card-text">Total purchases this month</p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="row mt-4">
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                Recent Activities
                            </div>
                            <div class="card-body">
                                <p>No recent activities found.</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                Quick Links
                            </div>
                            <div class="card-body">
                                <div class="list-group">
                                    <a href="modules/hr/employees.php" class="list-group-item list-group-item-action">Manage Employees</a>
                                    <a href="modules/store/inventory.php" class="list-group-item list-group-item-action">Check Inventory</a>
                                    <a href="modules/purchase/purchase_orders.php" class="list-group-item list-group-item-action">Create Purchase Order</a>
                                    <a href="modules/distribution/sales.php" class="list-group-item list-group-item-action">Record Sales</a>
                                    <a href="reports/financial.php" class="list-group-item list-group-item-action">View Financial Reports</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- JavaScript Libraries -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/main.js"></script>
</body>
</html>