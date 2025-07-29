<?php
require_once 'includes/auth_check.php';
require_once 'includes/db_connect.php';

check_login();
check_role(['Admin']);

$conn = connect_db();

// Handle Add/Edit Store
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['add_store'])) {
        $name = $_POST['name'];
        $location = $_POST['location'];

        $stmt = $conn->prepare("INSERT INTO stores (name, location, config_json) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $name, $location, $config_json);
        if ($stmt->execute()) {
            $_SESSION['message'] = "Store added successfully!";
            $_SESSION['message_type'] = "success";
        } else {
            $_SESSION['message'] = "Error adding store: " . $conn->error;
            $_SESSION['message_type'] = "danger";
        }
        $stmt->close();
    } elseif (isset($_POST['edit_store'])) {
        $id = $_POST['store_id'];
        $name = $_POST['name'];
        $location = $_POST['location'];

        $config_json = $_POST['config_json'];
        $stmt = $conn->prepare("UPDATE stores SET name = ?, location = ?, config_json = ? WHERE id = ?");
        $stmt->bind_param("sssi", $name, $location, $config_json, $id);
        if ($stmt->execute()) {
            $_SESSION['message'] = "Store updated successfully!";
            $_SESSION['message_type'] = "success";
        } else {
            $_SESSION['message'] = "Error updating store: " . $conn->error;
            $_SESSION['message_type'] = "danger";
        }
        $stmt->close();
    }
    header("location: store_management.php");
    exit();
}

// Handle Delete Store
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];

    $stmt = $conn->prepare("DELETE FROM stores WHERE id = ?");
    $stmt->bind_param("i", $id);
    if ($stmt->execute()) {
        $_SESSION['message'] = "Store deleted successfully!";
        $_SESSION['message_type'] = "success";
    } else {
        $_SESSION['message'] = "Error deleting store: " . $conn->error;
        $_SESSION['message_type'] = "danger";
    }
    $stmt->close();
    header("location: store_management.php");
    exit();
}

// Fetch all stores
$stores = [];
$result = $conn->query("SELECT id, name, location, config_json FROM stores");
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
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
    <title>Store Management - <?php echo APP_NAME; ?></title>
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
                        <a class="nav-link active" aria-current="page" href="store_management.php">Store Management</a>
                    </li>
                    <?php endif; ?>
                    <?php if ($user_role_name == 'Admin' || $user_role_name == 'Manager' || $user_role_name == 'Data Entry'): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="product_management.php">Product Management</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="category_management.php">Category Management</a>
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
        <h2>Store Management</h2>

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

        <!-- Add New Store Form -->
        <div class="card mb-4">
            <div class="card-header">
                Add New Store
            </div>
            <div class="card-body">
                <form action="store_management.php" method="POST">
                    <div class="mb-3">
                        <label for="name" class="form-label">Store Name</label>
                        <input type="text" class="form-control" id="name" name="name" required>
                    </div>
                    <div class="mb-3">
                        <label for="location" class="form-label">Location</label>
                        <input type="text" class="form-control" id="location" name="location">
                    </div>
                    <div class="mb-3">
                        <label for="config_json" class="form-label">Configuration (JSON)</label>
                        <textarea class="form-control" id="config_json" name="config_json" rows="5">{}</textarea>
                    </div>
                    <button type="submit" name="add_store" class="btn btn-primary">Add Store</button>
                </form>
            </div>
        </div>

        <!-- Existing Stores Table -->
        <div class="card">
            <div class="card-header">
                Existing Stores
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Location</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($stores)): ?>
                                <tr>
                                    <td colspan="4" class="text-center">No stores found.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($stores as $store): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($store['id']); ?></td>
                                        <td><?php echo htmlspecialchars($store['name']); ?></td>
                                        <td><?php echo htmlspecialchars($store['location']); ?></td>
                                        <td>
                                            <button type="button" class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#editStoreModal" 
                                                    data-id="<?php echo $store['id']; ?>" 
                                                    data-name="<?php echo htmlspecialchars($store['name']); ?>" 
                                                    data-location="<?php echo htmlspecialchars($store['location']); ?>"
                                                    data-config_json="<?php echo htmlspecialchars($store['config_json']); ?>">
                                                Edit
                                            </button>
                                            <a href="store_management.php?delete=<?php echo $store['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this store?');">Delete</a>
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

    <!-- Edit Store Modal -->
    <div class="modal fade" id="editStoreModal" tabindex="-1" aria-labelledby="editStoreModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editStoreModalLabel">Edit Store</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="store_management.php" method="POST">
                    <div class="modal-body">
                        <input type="hidden" id="edit_store_id" name="store_id">
                        <div class="mb-3">
                            <label for="edit_name" class="form-label">Store Name</label>
                            <input type="text" class="form-control" id="edit_name" name="name" required>
                        </div>
                        <div class="mb-3">
                            <label for="edit_location" class="form-label">Location</label>
                            <input type="text" class="form-control" id="edit_location" name="location">
                        </div>
                        <div class="mb-3">
                            <label for="edit_config_json" class="form-label">Configuration (JSON)</label>
                            <textarea class="form-control" id="edit_config_json" name="config_json" rows="5"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" name="edit_store" class="btn btn-primary">Save changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        var editStoreModal = document.getElementById('editStoreModal');
        editStoreModal.addEventListener('show.bs.modal', function (event) {
            var button = event.relatedTarget;
            var id = button.getAttribute('data-id');
            var name = button.getAttribute('data-name');
            var location = button.getAttribute('data-location');
            var config_json = button.getAttribute('data-config_json');

            var modalBodyInputId = editStoreModal.querySelector('#edit_store_id');
            var modalBodyInputName = editStoreModal.querySelector('#edit_name');
            var modalBodyInputLocation = editStoreModal.querySelector('#edit_location');
            var modalBodyInputConfigJson = editStoreModal.querySelector('#edit_config_json');

            modalBodyInputId.value = id;
            modalBodyInputName.value = name;
            modalBodyInputLocation.value = location;
            modalBodyInputConfigJson.value = config_json;
        });
    </script>
</body>
</html>