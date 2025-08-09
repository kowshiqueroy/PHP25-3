<?php
require_once '../includes/session.php';
require_once '../includes/functions.php';

if (!is_admin()) {
    header("Location: ../login.php");
    exit();
}

$message = '';
$error = '';

// Handle Add, Edit, Delete actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        die('Invalid CSRF token');
    }

    // Add Department
    if (isset($_POST['add_department'])) {
        $name = trim($_POST['name']);
        if (!empty($name)) {
            global $conn;
            $stmt = $conn->prepare("INSERT INTO departments (name) VALUES (?)");
            $stmt->bind_param("s", $name);
            if ($stmt->execute()) {
                $message = "Department added successfully.";
            } else {
                $error = "Failed to add department. It may already exist.";
            }
        } else {
            $error = "Department name cannot be empty.";
        }
    }

    // Edit Department
    if (isset($_POST['edit_department'])) {
        $id = $_POST['id'];
        $name = trim($_POST['name']);
        if (!empty($name) && !empty($id)) {
            global $conn;
            $stmt = $conn->prepare("UPDATE departments SET name = ? WHERE id = ?");
            $stmt->bind_param("si", $name, $id);
            if ($stmt->execute()) {
                $message = "Department updated successfully.";
            } else {
                $error = "Failed to update department.";
            }
        } else {
            $error = "Department name and ID cannot be empty.";
        }
    }

    // Delete Department
    if (isset($_POST['delete_department'])) {
        $id = $_POST['id'];
        if (!empty($id)) {
            global $conn;
            $stmt = $conn->prepare("DELETE FROM departments WHERE id = ?");
            $stmt->bind_param("i", $id);
            if ($stmt->execute()) {
                $message = "Department deleted successfully.";
            } else {
                $error = "Failed to delete department. Make sure no staff are assigned to it.";
            }
        } else {
            $error = "Department ID cannot be empty.";
        }
    }
}

$departments = get_all_departments();

?>
<?php require_once 'header.php'; ?>

<div class="container mt-4">
    <h2>Departments</h2>

    <?php if ($message): ?>
        <div class="alert alert-success"><?php echo $message; ?></div>
    <?php endif; ?>
    <?php if ($error): ?>
        <div class="alert alert-danger"><?php echo $error; ?></div>
    <?php endif; ?>

    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">Add New Department</h5>
        </div>
        <div class="card-body">
            <form action="departments.php" method="post">
                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                <div class="form-row">
                    <div class="col">
                        <input type="text" name="name" class="form-control" placeholder="Enter department name" required>
                    </div>
                    <div class="col">
                        <button type="submit" name="add_department" class="btn btn-primary">Add Department</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="card mt-4">
        <div class="card-header">
            <h5 class="mb-0">Existing Departments</h5>
        </div>
        <div class="card-body">
            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($departments as $d): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($d['id']); ?></td>
                            <td><?php echo htmlspecialchars($d['name']); ?></td>
                            <td>
                                <button class="btn btn-sm btn-info" data-toggle="modal" data-target="#editModal-<?php echo $d['id']; ?>">Edit</button>
                                <form action="departments.php" method="post" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this department?');">
                                    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                                    <input type="hidden" name="id" value="<?php echo $d['id']; ?>">
                                    <button type="submit" name="delete_department" class="btn btn-sm btn-danger">Delete</button>
                                </form>
                            </td>
                        </tr>

                        <!-- Edit Modal -->
                        <div class="modal fade" id="editModal-<?php echo $d['id']; ?>" tabindex="-1">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title">Edit Department</h5>
                                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                                    </div>
                                    <div class="modal-body">
                                        <form action="departments.php" method="post">
                                            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                                            <input type="hidden" name="id" value="<?php echo $d['id']; ?>">
                                            <div class="form-group">
                                                <label for="name-<?php echo $d['id']; ?>">Name</label>
                                                <input type="text" name="name" id="name-<?php echo $d['id']; ?>" class="form-control" value="<?php echo htmlspecialchars($d['name']); ?>" required>
                                            </div>
                                            <button type="submit" name="edit_department" class="btn btn-primary">Save Changes</button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once 'footer.php'; ?>
