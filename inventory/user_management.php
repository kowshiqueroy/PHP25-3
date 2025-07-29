<?php
require_once 'includes/auth_check.php';
require_once 'includes/db_connect.php';

check_login();
check_role(['Admin']);

$conn = connect_db();

// Handle Add/Edit User
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['add_user'])) {
        $username = $_POST['username'];
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
        $role_id = $_POST['role_id'];
        $store_id = $_POST['store_id'];

        $stmt = $conn->prepare("INSERT INTO users (username, password_hash, role_id, store_id) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssii", $username, $password, $role_id, $store_id);
        if ($stmt->execute()) {
            $_SESSION['message'] = "User added successfully!";
            $_SESSION['message_type'] = "success";
        } else {
            $_SESSION['message'] = "Error adding user: " . $conn->error;
            $_SESSION['message_type'] = "danger";
        }
        $stmt->close();
    } elseif (isset($_POST['edit_user'])) {
        $id = $_POST['user_id'];
        $username = $_POST['username'];
        $role_id = $_POST['role_id'];
        $store_id = $_POST['store_id'];

        // Only update password if a new one is provided
        if (!empty($_POST['password'])) {
            $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
            $stmt = $conn->prepare("UPDATE users SET username = ?, password_hash = ?, role_id = ?, store_id = ? WHERE id = ?");
            $stmt->bind_param("ssiii", $username, $password, $role_id, $store_id, $id);
        } else {
            $stmt = $conn->prepare("UPDATE users SET username = ?, role_id = ?, store_id = ? WHERE id = ?");
            $stmt->bind_param("siii", $username, $role_id, $store_id, $id);
        }
        
        if ($stmt->execute()) {
            $_SESSION['message'] = "User updated successfully!";
            $_SESSION['message_type'] = "success";
        } else {
            $_SESSION['message'] = "Error updating user: " . $conn->error;
            $_SESSION['message_type'] = "danger";
        }
        $stmt->close();
    }
    header("location: user_management.php");
    exit();
}

// Handle Delete User
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];

    $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
    $stmt->bind_param("i", $id);
    if ($stmt->execute()) {
        $_SESSION['message'] = "User deleted successfully!";
        $_SESSION['message_type'] = "success";
    } else {
        $_SESSION['message'] = "Error deleting user: " . $conn->error;
        $_SESSION['message_type'] = "danger";
    }
    $stmt->close();
    header("location: user_management.php");
    exit();
}

// Fetch all users with their role and store names
$users = [];
$result = $conn->query("SELECT u.id, u.username, r.name as role_name, s.name as store_name, u.role_id, u.store_id FROM users u JOIN roles r ON u.role_id = r.id JOIN stores s ON u.store_id = s.id");
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $users[] = $row;
    }
}

// Fetch roles for dropdown
$roles = [];
$result_roles = $conn->query("SELECT id, name FROM roles");
if ($result_roles->num_rows > 0) {
    while ($row = $result_roles->fetch_assoc()) {
        $roles[] = $row;
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
    <title>User Management - <?php echo APP_NAME; ?></title>
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
                        <a class="nav-link active" aria-current="page" href="user_management.php">User Management</a>
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
        <h2>User Management</h2>

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

        <!-- Add New User Form -->
        <div class="card mb-4">
            <div class="card-header">
                Add New User
            </div>
            <div class="card-body">
                <form action="user_management.php" method="POST">
                    <div class="mb-3">
                        <label for="username" class="form-label">Username</label>
                        <input type="text" class="form-control" id="username" name="username" required>
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label">Password</label>
                        <input type="password" class="form-control" id="password" name="password" required>
                    </div>
                    <div class="mb-3">
                        <label for="role_id" class="form-label">Role</label>
                        <select class="form-select" id="role_id" name="role_id" required>
                            <?php foreach ($roles as $role): ?>
                                <option value="<?php echo htmlspecialchars($role['id']); ?>"><?php echo htmlspecialchars($role['name']); ?></option>
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
                    <button type="submit" name="add_user" class="btn btn-primary">Add User</button>
                </form>
            </div>
        </div>

        <!-- Existing Users Table -->
        <div class="card">
            <div class="card-header">
                Existing Users
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Username</th>
                                <th>Role</th>
                                <th>Store</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($users)): ?>
                                <tr>
                                    <td colspan="5" class="text-center">No users found.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($users as $user): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($user['id']); ?></td>
                                        <td><?php echo htmlspecialchars($user['username']); ?></td>
                                        <td><?php echo htmlspecialchars($user['role_name']); ?></td>
                                        <td><?php echo htmlspecialchars($user['store_name']); ?></td>
                                        <td>
                                            <button type="button" class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#editUserModal" 
                                                    data-id="<?php echo $user['id']; ?>" 
                                                    data-username="<?php echo htmlspecialchars($user['username']); ?>" 
                                                    data-role_id="<?php echo htmlspecialchars($user['role_id']); ?>" 
                                                    data-store_id="<?php echo htmlspecialchars($user['store_id']); ?>">
                                                Edit
                                            </button>
                                            <a href="user_management.php?delete=<?php echo $user['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this user?');">Delete</a>
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

    <!-- Edit User Modal -->
    <div class="modal fade" id="editUserModal" tabindex="-1" aria-labelledby="editUserModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editUserModalLabel">Edit User</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="user_management.php" method="POST">
                    <div class="modal-body">
                        <input type="hidden" id="edit_user_id" name="user_id">
                        <div class="mb-3">
                            <label for="edit_username" class="form-label">Username</label>
                            <input type="text" class="form-control" id="edit_username" name="username" required>
                        </div>
                        <div class="mb-3">
                            <label for="edit_password" class="form-label">New Password (leave blank to keep current)</label>
                            <input type="password" class="form-control" id="edit_password" name="password">
                        </div>
                        <div class="mb-3">
                            <label for="edit_role_id" class="form-label">Role</label>
                            <select class="form-select" id="edit_role_id" name="role_id" required>
                                <?php foreach ($roles as $role): ?>
                                    <option value="<?php echo htmlspecialchars($role['id']); ?>"><?php echo htmlspecialchars($role['name']); ?></option>
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
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" name="edit_user" class="btn btn-primary">Save changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        var editUserModal = document.getElementById('editUserModal');
        editUserModal.addEventListener('show.bs.modal', function (event) {
            var button = event.relatedTarget;
            var id = button.getAttribute('data-id');
            var username = button.getAttribute('data-username');
            var role_id = button.getAttribute('data-role_id');
            var store_id = button.getAttribute('data-store_id');

            var modalBodyInputId = editUserModal.querySelector('#edit_user_id');
            var modalBodyInputUsername = editUserModal.querySelector('#edit_username');
            var modalBodySelectRole = editUserModal.querySelector('#edit_role_id');
            var modalBodySelectStore = editUserModal.querySelector('#edit_store_id');

            modalBodyInputId.value = id;
            modalBodyInputUsername.value = username;
            modalBodySelectRole.value = role_id;
            modalBodySelectStore.value = store_id;

            // Clear password field when modal opens for security
            editUserModal.querySelector('#edit_password').value = '';
        });
    </script>
</body>
</html>