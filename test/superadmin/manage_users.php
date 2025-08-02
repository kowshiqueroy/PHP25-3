<?php
require_once '../config/config.php';
require_once '../includes/functions.php';

require_login();

if (!has_role(1)) {
    header('Location: ../index.php');
    exit;
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['add'])) {
        $username = $_POST['username'];
        $password = $_POST['password'];
        $department_id = $_POST['department_id'];
        $role_id = $_POST['role_id'];

        $sql = "INSERT INTO users (username, password, department_id, role_id) VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('ssii', $username, $password, $department_id, $role_id);
        $stmt->execute();
    } elseif (isset($_POST['update']) && has_permission('users', 'can_edit')) {
        $id = $_POST['id'];
        $username = $_POST['username'];
        $department_id = $_POST['department_id'];
        $role_id = $_POST['role_id'];

        if (!empty($_POST['password'])) {
            $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
            $sql = "UPDATE users SET username = ?, password = ?, department_id = ?, role_id = ? WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param('ssiii', $username, $password, $department_id, $role_id, $id);
        } else {
            $sql = "UPDATE users SET username = ?, department_id = ?, role_id = ? WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param('siii', $username, $department_id, $role_id, $id);
        }

        $stmt->execute();
    } elseif (isset($_POST['delete'])) {
        $id = $_POST['id'];
        $sql = "DELETE FROM users WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('i', $id);
        $stmt->execute();
    }
}

include '../templates/header.php';
?>

<div class="d-flex">
    <?php include '../templates/sidebar.php'; ?>

    <div class="content flex-grow-1">
        <?php include '../templates/topbar.php'; ?>

        <main class="main-content container-fluid">
            <h1 class="mb-4">Manage Users</h1>

            <div class="card mb-4">
                <div class="card-header"><i class="fas fa-user-plus me-2"></i>Add New User</div>
                <div class="card-body">
                    <form method="post">
                        <div class="mb-3">
                            <label for="username" class="form-label">Username</label>
                            <input type="text" name="username" id="username" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label for="password" class="form-label">Password</label>
                            <input type="password" name="password" id="password" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label for="department_id" class="form-label">Department</label>
                            <select name="department_id" id="department_id" class="form-select">
                                <option value="">Select Department</option>
                                <?php
                                $sql = "SELECT * FROM departments";
                                $result = $conn->query($sql);
                                while ($row = $result->fetch_assoc()) {
                                    echo "<option value='" . $row['id'] . "'>" . $row['name'] . "</option>";
                                }
                                ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="role_id" class="form-label">Role</label>
                            <select name="role_id" id="role_id" class="form-select" required>
                                <option value="">Select Role</option>
                                <?php
                                $sql = "SELECT * FROM roles";
                                $result = $conn->query($sql);
                                while ($row = $result->fetch_assoc()) {
                                    echo "<option value='" . $row['id'] . "'>" . $row['name'] . "</option>";
                                }
                                ?>
                            </select>
                        </div>
                        <?php if (has_permission('users', 'can_create')): ?>
                        <button type="submit" name="add" class="btn btn-primary"><i class="fas fa-plus me-2"></i>Add User</button>
                        <?php endif; ?>
                    </form>
                </div>
            </div>

            <div class="card">
                <div class="card-header"><i class="fas fa-list me-2"></i>Existing Users</div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Username</th>
                                    <th>Department</th>
                                    <th>Role</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $sql = "SELECT users.id, users.username, departments.name AS department_name, roles.name AS role_name FROM users LEFT JOIN departments ON users.department_id = departments.id LEFT JOIN roles ON users.role_id = roles.id";
                                $result = $conn->query($sql);

                                if ($result->num_rows > 0) {
                                    while ($row = $result->fetch_assoc()) {
                                        echo "<tr>";
                                        echo "<td>" . $row['id'] . "</td>";
                                        echo "<form method='post'>";
                                        echo "<td><input type='text' name='username' value='" . $row['username'] . "' class='form-control'></td>";
                                        echo "<td>";
                                        echo "<select name='department_id' class='form-select'>";
                                        $dept_sql = "SELECT * FROM departments";
                                        $dept_result = $conn->query($dept_sql);
                                        while ($dept_row = $dept_result->fetch_assoc()) {
                                            $selected = ($dept_row['name'] == $row['department_name']) ? 'selected' : '';
                                            echo "<option value='" . $dept_row['id'] . "' " . $selected . ">" . $dept_row['name'] . "</option>";
                                        }
                                        echo "</select>";
                                        echo "</td>";
                                        echo "<td>";
                                        echo "<select name='role_id' class='form-select'>";
                                        $role_sql = "SELECT * FROM roles";
                                        $role_result = $conn->query($role_sql);
                                        while ($role_row = $role_result->fetch_assoc()) {
                                            $selected = ($role_row['name'] == $row['role_name']) ? 'selected' : '';
                                            echo "<option value='" . $role_row['id'] . "' " . $selected . ">" . $role_row['name'] . "</option>";
                                        }
                                        echo "</select>";
                                        echo "</td>";
                                        echo "<td>";
                                        echo "<input type='hidden' name='id' value='" . $row['id'] . "'>";
                                        echo "<div class='d-flex'>";
                                        if (has_permission('users', 'can_edit')) {
                                            echo "<button type='submit' name='update' class='btn btn-sm btn-success me-2'><i class='fas fa-edit'></i> Update</button>";
                                        }
                                        if (has_permission('users', 'can_delete')) {
                                            echo "<button type='submit' name='delete' class='btn btn-sm btn-danger' onclick=\"return confirm('Are you sure you want to delete this user?');\"><i class='fas fa-trash-alt'></i> Delete</button>";
                                        }
                                        echo "</div>";
                                        echo "</td>";
                                        echo "</form>";
                                        echo "</tr>";
                                    }
                                } else {
                                    echo "<tr><td colspan=\"5\">No users found.</td></tr>";
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<?php include '../templates/footer.php'; ?>