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

    // Add Staff
    if (isset($_POST['add_staff'])) {
        $staff_id = trim($_POST['staff_id']);
        $name = trim($_POST['name']);
        $email = trim($_POST['email']);
        $phone = trim($_POST['phone']);
        $department_id = $_POST['department_id'];

        if (!empty($staff_id) && !empty($name) && !empty($email)) {
            global $conn;
            $stmt = $conn->prepare("INSERT INTO staff (staff_id, name, email, phone, department_id) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("ssssi", $staff_id, $name, $email, $phone, $department_id);
            if ($stmt->execute()) {
                $message = "Staff added successfully.";
            } else {
                $error = "Failed to add staff. The Staff ID or Email may already exist.";
            }
        } else {
            $error = "Staff ID, Name, and Email are required.";
        }
    }

    // Edit Staff
    if (isset($_POST['edit_staff'])) {
        $id = $_POST['id'];
        $staff_id = trim($_POST['staff_id']);
        $name = trim($_POST['name']);
        $email = trim($_POST['email']);
        $phone = trim($_POST['phone']);
        $department_id = $_POST['department_id'];

        if (!empty($id) && !empty($staff_id) && !empty($name) && !empty($email)) {
            global $conn;
            $stmt = $conn->prepare("UPDATE staff SET staff_id = ?, name = ?, email = ?, phone = ?, department_id = ? WHERE id = ?");
            $stmt->bind_param("ssssii", $staff_id, $name, $email, $phone, $department_id, $id);
            if ($stmt->execute()) {
                $message = "Staff updated successfully.";
            } else {
                $error = "Failed to update staff.";
            }
        } else {
            $error = "Staff ID, Name, and Email are required.";
        }
    }

    // Delete Staff
    if (isset($_POST['delete_staff'])) {
        $id = $_POST['id'];
        if (!empty($id)) {
            global $conn;
            $stmt = $conn->prepare("DELETE FROM staff WHERE id = ?");
            $stmt->bind_param("i", $id);
            if ($stmt->execute()) {
                $message = "Staff deleted successfully.";
            } else {
                $error = "Failed to delete staff.";
            }
        }
    }
}

$staff = get_all_staff();
$departments = get_all_departments();

?>
<?php require_once 'header.php'; ?>

<div class="container mt-4">
    <h2>Staff Management</h2>

    <?php if ($message): ?>
        <div class="alert alert-success"><?php echo $message; ?></div>
    <?php endif; ?>
    <?php if ($error): ?>
        <div class="alert alert-danger"><?php echo $error; ?></div>
    <?php endif; ?>

    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">Add New Staff</h5>
        </div>
        <div class="card-body">
            <form action="staff.php" method="post">
                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label for="staff_id">Staff ID</label>
                        <input type="text" name="staff_id" class="form-control" required>
                    </div>
                    <div class="form-group col-md-6">
                        <label for="name">Name</label>
                        <input type="text" name="name" class="form-control" required>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label for="email">Email</label>
                        <input type="email" name="email" class="form-control" required>
                    </div>
                    <div class="form-group col-md-6">
                        <label for="phone">Phone</label>
                        <input type="text" name="phone" class="form-control">
                    </div>
                </div>
                <div class="form-group">
                    <label for="department_id">Department</label>
                    <select name="department_id" class="form-control">
                        <option value="">Select Department</option>
                        <?php foreach ($departments as $d): ?>
                            <option value="<?php echo $d['id']; ?>"><?php echo htmlspecialchars($d['name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <button type="submit" name="add_staff" class="btn btn-primary">Add Staff</button>
            </form>
        </div>
    </div>

    <div class="card mt-4">
        <div class="card-header">
            <h5 class="mb-0">Existing Staff</h5>
        </div>
        <div class="card-body">
            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>Staff ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Department</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($staff as $s): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($s['staff_id']); ?></td>
                            <td><?php echo htmlspecialchars($s['name']); ?></td>
                            <td><?php echo htmlspecialchars($s['email']); ?></td>
                            <td><?php echo htmlspecialchars($s['phone']); ?></td>
                            <td><?php echo htmlspecialchars($s['department_name']); ?></td>
                            <td>
                                <button class="btn btn-sm btn-info" data-toggle="modal" data-target="#editModal-<?php echo $s['id']; ?>">Edit</button>
                                <form action="staff.php" method="post" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this staff member?');">
                                    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                                    <input type="hidden" name="id" value="<?php echo $s['id']; ?>">
                                    <button type="submit" name="delete_staff" class="btn btn-sm btn-danger">Delete</button>
                                </form>
                            </td>
                        </tr>

                        <!-- Edit Modal -->
                        <div class="modal fade" id="editModal-<?php echo $s['id']; ?>" tabindex="-1">
                            <div class="modal-dialog modal-lg">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title">Edit Staff</h5>
                                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                                    </div>
                                    <div class="modal-body">
                                        <form action="staff.php" method="post">
                                            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                                            <input type="hidden" name="id" value="<?php echo $s['id']; ?>">
                                            <div class="form-row">
                                                <div class="form-group col-md-6">
                                                    <label>Staff ID</label>
                                                    <input type="text" name="staff_id" class="form-control" value="<?php echo htmlspecialchars($s['staff_id']); ?>" required>
                                                </div>
                                                <div class="form-group col-md-6">
                                                    <label>Name</label>
                                                    <input type="text" name="name" class="form-control" value="<?php echo htmlspecialchars($s['name']); ?>" required>
                                                </div>
                                            </div>
                                            <div class="form-row">
                                                <div class="form-group col-md-6">
                                                    <label>Email</label>
                                                    <input type="email" name="email" class="form-control" value="<?php echo htmlspecialchars($s['email']); ?>" required>
                                                </div>
                                                <div class="form-group col-md-6">
                                                    <label>Phone</label>
                                                    <input type="text" name="phone" class="form-control" value="<?php echo htmlspecialchars($s['phone']); ?>">
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <label>Department</label>
                                                <select name="department_id" class="form-control">
                                                    <option value="">Select Department</option>
                                                    <?php foreach ($departments as $d): ?>
                                                        <option value="<?php echo $d['id']; ?>" <?php echo ($d['id'] == $s['department_id']) ? 'selected' : ''; ?>><?php echo htmlspecialchars($d['name']); ?></option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                            <button type="submit" name="edit_staff" class="btn btn-primary">Save Changes</button>
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
