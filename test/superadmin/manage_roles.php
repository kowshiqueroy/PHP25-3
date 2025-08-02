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
        $name = $_POST['name'];
        $sql = "INSERT INTO roles (name) VALUES (?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('s', $name);
        $stmt->execute();
    } elseif (isset($_POST['update'])) {
        $id = $_POST['id'];
        $name = $_POST['name'];
        $sql = "UPDATE roles SET name = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('si', $name, $id);
        $stmt->execute();
    } elseif (isset($_POST['delete'])) {
        $id = $_POST['id'];
        $sql = "DELETE FROM roles WHERE id = ?";
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
            <h1 class="mb-4">Manage Roles</h1>

            <div class="card mb-4">
                <div class="card-header"><i class="fas fa-plus-circle me-2"></i>Add New Role</div>
                <div class="card-body">
                    <form method="post">
                        <div class="mb-3">
                            <label for="name" class="form-label">Role Name</label>
                            <input type="text" name="name" id="name" class="form-control" required>
                        </div>
                        <button type="submit" name="add" class="btn btn-primary"><i class="fas fa-plus me-2"></i>Add Role</button>
                    </form>
                </div>
            </div>

            <div class="card">
                <div class="card-header"><i class="fas fa-list me-2"></i>Existing Roles</div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Name</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $sql = "SELECT * FROM roles";
                                $result = $conn->query($sql);

                                if ($result->num_rows > 0) {
                                    while ($row = $result->fetch_assoc()) {
                                        echo "<tr>";
                                        echo "<td>" . sanitize($row['id']) . "</td>";
                                        echo "<form method='post' class='d-flex'>";
                                        echo "<td><input type='text' name='name' value='" . sanitize($row['name']) . "' class='form-control'></td>";
                                        echo "<td>";
                                        echo "<input type='hidden' name='id' value='" . sanitize($row['id']) . "'>";
                                        echo "<div class='d-flex'>";
                                        if (has_permission('roles', 'can_edit')) {
                                            echo "<button type='submit' name='update' class='btn btn-sm btn-success me-2'><i class='fas fa-edit'></i> Update</button>";
                                        }
                                        if (has_permission('roles', 'can_delete')) {
                                            echo "<button type='submit' name='delete' class='btn btn-sm btn-danger' onclick=\"return confirm('Are you sure you want to delete this role?');\"><i class='fas fa-trash-alt'></i> Delete</button>";
                                        }
                                        echo "</div>";
                                        echo "</td>";
                                        echo "</form>";
                                        echo "</tr>";
                                    }
                                } else {
                                    echo "<tr><td colspan=\"3\">No roles found.</td></tr>";
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