<?php
require_once 'includes/auth_check.php';
require_once 'includes/db_connect.php';

check_login();
check_role(['Admin', 'Manager', 'Viewer']);

$conn = connect_db();

// Fetch data for filters
$stores = [];
$result_stores = $conn->query("SELECT id, name FROM stores");
if ($result_stores->num_rows > 0) {
    while ($row = $result_stores->fetch_assoc()) {
        $stores[] = $row;
    }
}

$categories = [];
$result_categories = $conn->query("SELECT id, name FROM categories");
if ($result_categories->num_rows > 0) {
    while ($row = $result_categories->fetch_assoc()) {
        $categories[] = $row;
    }
}

$transaction_types = [
    'IN', 'OUT', 'Return to Store', 'Return to Supplier', 'Mark as Damaged', 'Expiry Isolation'
];

$report_data = [];

// Handle report generation
if ($_SERVER['REQUEST_METHOD'] == 'GET' && isset($_GET['generate_report'])) {
    $filter_store_id = $_GET['store_id'] ?? '';
    $filter_transaction_type = $_GET['transaction_type'] ?? '';
    $filter_product_id = $_GET['product_id'] ?? '';
    $filter_category_id = $_GET['category_id'] ?? '';
    $filter_start_date = $_GET['start_date'] ?? '';
    $filter_end_date = $_GET['end_date'] ?? '';

    $sql = "SELECT t.id as transaction_id, t.type, u.username, s.name as store_name, p.name as product_name, p.sku, t.quantity, t.comments, t.transaction_date, t.qc_status, t.person_name, t.contact_text, t.slip_number
            FROM transactions t
            JOIN users u ON t.user_id = u.id
            JOIN stores s ON t.store_id = s.id
            JOIN products p ON t.product_id = p.id
            WHERE 1=1";

    $params = [];
    $types = '';

    if (!empty($filter_store_id)) {
        $sql .= " AND t.store_id = ?";
        $params[] = $filter_store_id;
        $types .= "i";
    }
    if (!empty($filter_transaction_type) && $filter_transaction_type != 'All') {
        $sql .= " AND t.type = ?";
        $params[] = $filter_transaction_type;
        $types .= "s";
    }
    if (!empty($filter_product_id)) {
        $sql .= " AND t.product_id = ?";
        $params[] = $filter_product_id;
        $types .= "i";
    }
    if (!empty($filter_category_id)) {
        $sql .= " AND p.category_id = ?";
        $params[] = $filter_category_id;
        $types .= "i";
    }
    if (!empty($filter_start_date)) {
        $sql .= " AND t.transaction_date >= ?";
        $params[] = $filter_start_date . ' 00:00:00';
        $types .= "s";
    }
    if (!empty($filter_end_date)) {
        $sql .= " AND t.transaction_date <= ?";
        $params[] = $filter_end_date . ' 23:59:59';
        $types .= "s";
    }

    $sql .= " ORDER BY t.transaction_date DESC";

    $stmt = $conn->prepare($sql);
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        $report_data[] = $row;
    }
    $stmt->close();
}

$conn->close();

$user_role_name = get_user_role_name($_SESSION['role_id']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reports - <?php echo APP_NAME; ?></title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        @media print {
            body * {
                visibility: hidden;
            }
            .printable-area, .printable-area * {
                visibility: visible;
            }
            .printable-area {
                position: absolute;
                left: 0;
                top: 0;
                width: 100%;
            }
            .no-print {
                display: none;
            }
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark no-print">
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
                        <a class="nav-link" href="qc_workflow.php">QC Workflow</a>
                    </li>
                    <?php endif; ?>
                    <?php if ($user_role_name == 'Admin' || $user_role_name == 'Manager' || $user_role_name == 'Viewer'): ?>
                    <li class="nav-item">
                        <a class="nav-link active" aria-current="page" href="reports.php">Reports</a>
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

    <div class="container mt-4 no-print">
        <h2>Reports</h2>

        <div class="card mb-4">
            <div class="card-header">
                Filter Report
            </div>
            <div class="card-body">
                <form action="reports.php" method="GET">
                    <input type="hidden" name="generate_report" value="1">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="store_id" class="form-label">Store</label>
                            <select class="form-select" id="store_id" name="store_id">
                                <option value="">All Stores</option>
                                <?php foreach ($stores as $store): ?>
                                    <option value="<?php echo htmlspecialchars($store['id']); ?>" <?php echo (isset($_GET['store_id']) && $_GET['store_id'] == $store['id']) ? 'selected' : ''; ?>><?php echo htmlspecialchars($store['name']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="transaction_type" class="form-label">Transaction Type</label>
                            <select class="form-select" id="transaction_type" name="transaction_type">
                                <option value="All">All Types</option>
                                <?php foreach ($transaction_types as $type): ?>
                                    <option value="<?php echo htmlspecialchars($type); ?>" <?php echo (isset($_GET['transaction_type']) && $_GET['transaction_type'] == $type) ? 'selected' : ''; ?>><?php echo htmlspecialchars($type); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="product_search" class="form-label">Product (Name or SKU)</label>
                            <input type="text" class="form-control" id="product_search" placeholder="Start typing product name or SKU..." value="<?php echo htmlspecialchars($_GET['product_search'] ?? ''); ?>">
                            <input type="hidden" id="product_id" name="product_id" value="<?php echo htmlspecialchars($_GET['product_id'] ?? ''); ?>">
                            <div id="product_suggestions" class="list-group position-absolute w-50" style="z-index: 1000;"></div>
                        </div>
                        <div class="col-md-6">
                            <label for="category_id" class="form-label">Category</label>
                            <select class="form-select" id="category_id" name="category_id">
                                <option value="">All Categories</option>
                                <?php foreach ($categories as $category): ?>
                                    <option value="<?php echo htmlspecialchars($category['id']); ?>" <?php echo (isset($_GET['category_id']) && $_GET['category_id'] == $category['id']) ? 'selected' : ''; ?>><?php echo htmlspecialchars($category['name']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="start_date" class="form-label">Start Date</label>
                            <input type="date" class="form-control" id="start_date" name="start_date" value="<?php echo htmlspecialchars($_GET['start_date'] ?? ''); ?>">
                        </div>
                        <div class="col-md-6">
                            <label for="end_date" class="form-label">End Date</label>
                            <input type="date" class="form-control" id="end_date" name="end_date" value="<?php echo htmlspecialchars($_GET['end_date'] ?? ''); ?>">
                        </div>
                    </div>
                    <div class="mt-3">
                        <button type="submit" class="btn btn-primary">Generate Report</button>
                        <button type="button" class="btn btn-secondary" onclick="window.print()">Print Report</button>
                    </div>
                </form>
            </div>
        </div>

        <div class="card printable-area">
            <div class="card-header">
                Report Results
            </div>
            <div class="card-body">
                <?php if (empty($report_data)): ?>
                    <p class="text-center">No data found for the selected filters. Please generate a report.</p>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>Trans. ID</th>
                                    <th>Type</th>
                                    <th>Date</th>
                                    <th>Store</th>
                                    <th>Product</th>
                                    <th>SKU</th>
                                    <th>Quantity</th>
                                    <th>Initiated By</th>
                                    <th>QC Status</th>
                                    <th>Comments</th>
                                    <th>Person Name</th>
                                    <th>Contact</th>
                                    <th>Slip No.</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($report_data as $row): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($row['transaction_id']); ?></td>
                                        <td><?php echo htmlspecialchars($row['type']); ?></td>
                                        <td><?php echo htmlspecialchars($row['transaction_date']); ?></td>
                                        <td><?php echo htmlspecialchars($row['store_name']); ?></td>
                                        <td><?php echo htmlspecialchars($row['product_name']); ?></td>
                                        <td><?php echo htmlspecialchars($row['sku']); ?></td>
                                        <td><?php echo htmlspecialchars($row['quantity']); ?></td>
                                        <td><?php echo htmlspecialchars($row['username']); ?></td>
                                        <td><?php echo htmlspecialchars($row['qc_status']); ?></td>
                                        <td><?php echo htmlspecialchars($row['comments']); ?></td>
                                        <td><?php echo htmlspecialchars($row['person_name']); ?></td>
                                        <td><?php echo htmlspecialchars($row['contact_text']); ?></td>
                                        <td><?php echo htmlspecialchars($row['slip_number']); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const productSearch = document.getElementById('product_search');
            const productId = document.getElementById('product_id');
            const productSuggestions = document.getElementById('product_suggestions');

            productSearch.addEventListener('input', function() {
                const query = this.value;
                if (query.length > 2) {
                    fetch('api/product_search.php?q=' + query)
                        .then(response => response.json())
                        .then(data => {
                            productSuggestions.innerHTML = '';
                            if (data.length > 0) {
                                data.forEach(product => {
                                    const item = document.createElement('a');
                                    item.href = '#';
                                    item.classList.add('list-group-item', 'list-group-item-action');
                                    item.textContent = product.name + ' (SKU: ' + product.sku + ')';
                                    item.addEventListener('click', function(e) {
                                        e.preventDefault();
                                        productSearch.value = product.name + ' (SKU: ' + product.sku + ')';
                                        productId.value = product.id;
                                        productSuggestions.innerHTML = '';
                                    });
                                    productSuggestions.appendChild(item);
                                });
                            } else {
                                productSuggestions.innerHTML = '<div class="list-group-item">No products found.</div>';
                            }
                        })
                        .catch(error => console.error('Error fetching products:', error));
                } else {
                    productSuggestions.innerHTML = '';
                }
            });

            // Clear suggestions when clicking outside
            document.addEventListener('click', function(event) {
                if (!productSearch.contains(event.target) && !productSuggestions.contains(event.target)) {
                    productSuggestions.innerHTML = '';
                }
            });
        });
    </script>
</body>
</html>