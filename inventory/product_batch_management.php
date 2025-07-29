<?php
require_once 'includes/auth_check.php';
require_once 'includes/db_connect.php';

check_login();
check_role(['Manager', 'Data Entry']);

$conn = connect_db();

// Handle Add/Edit Product Batch
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['add_batch'])) {
        $product_id = $_POST['product_id'];
        $store_id = $_POST['store_id'];
        $expiry_date = empty($_POST['expiry_date']) ? NULL : $_POST['expiry_date'];
        $storage_location = $_POST['storage_location'];
        $quantity = $_POST['quantity'];

        $stmt = $conn->prepare("INSERT INTO product_batches (product_id, store_id, expiry_date, storage_location, quantity) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("iissi", $product_id, $store_id, $expiry_date, $storage_location, $quantity);
        if ($stmt->execute()) {
            $_SESSION['message'] = "Product batch added successfully!";
            $_SESSION['message_type'] = "success";
        } else {
            $_SESSION['message'] = "Error adding product batch: " . $conn->error;
            $_SESSION['message_type'] = "danger";
        }
        $stmt->close();
    } elseif (isset($_POST['edit_batch'])) {
        $id = $_POST['batch_id'];
        $product_id = $_POST['product_id'];
        $store_id = $_POST['store_id'];
        $expiry_date = empty($_POST['expiry_date']) ? NULL : $_POST['expiry_date'];
        $storage_location = $_POST['storage_location'];
        $quantity = $_POST['quantity'];

        $stmt = $conn->prepare("UPDATE product_batches SET product_id = ?, store_id = ?, expiry_date = ?, storage_location = ?, quantity = ? WHERE id = ?");
        $stmt->bind_param("iissii", $product_id, $store_id, $expiry_date, $storage_location, $quantity, $id);
        if ($stmt->execute()) {
            $_SESSION['message'] = "Product batch updated successfully!";
            $_SESSION['message_type'] = "success";
        } else {
            $_SESSION['message'] = "Error updating product batch: " . $conn->error;
            $_SESSION['message_type'] = "danger";
        }
        $stmt->close();
    }
    header("location: product_batch_management.php");
    exit();
}

// Handle Delete Product Batch
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];

    $stmt = $conn->prepare("DELETE FROM product_batches WHERE id = ?");
    $stmt->bind_param("i", $id);
    if ($stmt->execute()) {
        $_SESSION['message'] = "Product batch deleted successfully!";
        $_SESSION['message_type'] = "success";
    } else {
        $_SESSION['message'] = "Error deleting product batch: " . $conn->error;
        $_SESSION['message_type'] = "danger";
    }
    $stmt->close();
    header("location: product_batch_management.php");
    exit();
}

// Fetch all product batches with product and store names
$batches = [];
$result = $conn->query("SELECT pb.id, p.name as product_name, s.name as store_name, pb.expiry_date, pb.storage_location, pb.qc_status, pb.damage_status, pb.quantity, pb.product_id, pb.store_id FROM product_batches pb JOIN products p ON pb.product_id = p.id JOIN stores s ON pb.store_id = s.id");
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $batches[] = $row;
    }
}

// Fetch products for dropdown
$products = [];
$result_products = $conn->query("SELECT id, name FROM products");
if ($result_products->num_rows > 0) {
    while ($row = $result_products->fetch_assoc()) {
        $products[] = $row;
    }
}

// Fetch stores for dropdown
$stores = [];
$result_stores = $conn->query("SELECT id, name FROM stores");
if ($result_stores->num_rows > 0) {
    while ($row = $result_stores->fetch_assoc()) {
        $stores[] = $row;
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
    <title>Product Batch Management - <?php echo APP_NAME; ?></title>
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
                        <a class="nav-link active" aria-current="page" href="product_batch_management.php">Product Batch Management</a>
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
        <h2>Product Batch Management</h2>

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

        <!-- Add New Product Batch Form -->
        <div class="card mb-4">
            <div class="card-header">
                Add New Product Batch
            </div>
            <div class="card-body">
                <form action="product_batch_management.php" method="POST">
                    <div class="mb-3">
                        <label for="product_id" class="form-label">Product</label>
                        <select class="form-select" id="product_id" name="product_id" required>
                            <?php foreach ($products as $product): ?>
                                <option value="<?php echo htmlspecialchars($product['id']); ?>"><?php echo htmlspecialchars($product['name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="store_id" class="form-label">Store</label>
                        <select class="form-select" id="store_id" name="store_id" required>
                            <?php foreach ($stores as $store): ?>
                                <option value="<?php echo htmlspecialchars($store['id']); ?>"><?php echo htmlspecialchars($store['name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="expiry_date" class="form-label">Expiry Date</label>
                        <input type="date" class="form-control" id="expiry_date" name="expiry_date">
                    </div>
                    <div class="mb-3">
                        <label for="storage_location" class="form-label">Storage Location</label>
                        <input type="text" class="form-control" id="storage_location" name="storage_location">
                    </div>
                    <div class="mb-3">
                        <label for="quantity" class="form-label">Quantity</label>
                        <input type="number" class="form-control" id="quantity" name="quantity" required min="0">
                    </div>
                    <button type="submit" name="add_batch" class="btn btn-primary">Add Batch</button>
                </form>
            </div>
        </div>

        <!-- Existing Product Batches Table -->
        <div class="card">
            <div class="card-header">
                Existing Product Batches
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Product</th>
                                <th>Store</th>
                                <th>Expiry Date</th>
                                <th>Location</th>
                                <th>QC Status</th>
                                <th>Damage Status</th>
                                <th>Quantity</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($batches)): ?>
                                <tr>
                                    <td colspan="9" class="text-center">No product batches found.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($batches as $batch): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($batch['id']); ?></td>
                                        <td><?php echo htmlspecialchars($batch['product_name']); ?></td>
                                        <td><?php echo htmlspecialchars($batch['store_name']); ?></td>
                                        <td><?php echo htmlspecialchars($batch['expiry_date'] ?? 'N/A'); ?></td>
                                        <td><?php echo htmlspecialchars($batch['storage_location']); ?></td>
                                        <td><?php echo htmlspecialchars($batch['qc_status']); ?></td>
                                        <td><?php echo htmlspecialchars($batch['damage_status']); ?></td>
                                        <td><?php echo htmlspecialchars($batch['quantity']); ?></td>
                                        <td>
                                            <button type="button" class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#editBatchModal" 
                                                    data-id="<?php echo $batch['id']; ?>" 
                                                    data-product_id="<?php echo htmlspecialchars($batch['product_id']); ?>" 
                                                    data-store_id="<?php echo htmlspecialchars($batch['store_id']); ?>" 
                                                    data-expiry_date="<?php echo htmlspecialchars($batch['expiry_date'] ?? ''); ?>" 
                                                    data-storage_location="<?php echo htmlspecialchars($batch['storage_location']); ?>" 
                                                    data-quantity="<?php echo htmlspecialchars($batch['quantity']); ?>">
                                                Edit
                                            </button>
                                            <a href="product_batch_management.php?delete=<?php echo $batch['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this batch?');">Delete</a>
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

    <!-- Edit Product Batch Modal -->
    <div class="modal fade" id="editBatchModal" tabindex="-1" aria-labelledby="editBatchModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editBatchModalLabel">Edit Product Batch</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="product_batch_management.php" method="POST">
                    <div class="modal-body">
                        <input type="hidden" id="edit_batch_id" name="batch_id">
                        <div class="mb-3">
                            <label for="edit_product_id" class="form-label">Product</label>
                            <select class="form-select" id="edit_product_id" name="product_id" required>
                                <?php foreach ($products as $product): ?>
                                    <option value="<?php echo htmlspecialchars($product['id']); ?>"><?php echo htmlspecialchars($product['name']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="edit_store_id" class="form-label">Store</label>
                            <select class="form-select" id="edit_store_id" name="store_id" required>
                                <?php foreach ($stores as $store): ?>
                                    <option value="<?php echo htmlspecialchars($store['id']); ?>"><?php echo htmlspecialchars($store['name']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="edit_expiry_date" class="form-label">Expiry Date</label>
                            <input type="date" class="form-control" id="edit_expiry_date" name="expiry_date">
                        </div>
                        <div class="mb-3">
                            <label for="edit_storage_location" class="form-label">Storage Location</label>
                            <input type="text" class="form-control" id="edit_storage_location" name="storage_location">
                        </div>
                        <div class="mb-3">
                            <label for="edit_quantity" class="form-label">Quantity</label>
                            <input type="number" class="form-control" id="edit_quantity" name="quantity" required min="0">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" name="edit_batch" class="btn btn-primary">Save changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        var editBatchModal = document.getElementById('editBatchModal');
        editBatchModal.addEventListener('show.bs.modal', function (event) {
            var button = event.relatedTarget;
            var id = button.getAttribute('data-id');
            var product_id = button.getAttribute('data-product_id');
            var store_id = button.getAttribute('data-store_id');
            var expiry_date = button.getAttribute('data-expiry_date');
            var storage_location = button.getAttribute('data-storage_location');
            var quantity = button.getAttribute('data-quantity');

            var modalBodyInputId = editBatchModal.querySelector('#edit_batch_id');
            var modalBodySelectProductId = editBatchModal.querySelector('#edit_product_id');
            var modalBodySelectStoreId = editBatchModal.querySelector('#edit_store_id');
            var modalBodyInputExpiryDate = editBatchModal.querySelector('#edit_expiry_date');
            var modalBodyInputStorageLocation = editBatchModal.querySelector('#edit_storage_location');
            var modalBodyInputQuantity = editBatchModal.querySelector('#edit_quantity');

            modalBodyInputId.value = id;
            modalBodySelectProductId.value = product_id;
            modalBodySelectStoreId.value = store_id;
            modalBodyInputExpiryDate.value = expiry_date;
            modalBodyInputStorageLocation.value = storage_location;
            modalBodyInputQuantity.value = quantity;
        });
    </script>
</body>
</html>