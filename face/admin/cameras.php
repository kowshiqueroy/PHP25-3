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

    // Add Camera
    if (isset($_POST['add_camera'])) {
        $name = trim($_POST['name']);
        $type = $_POST['type'];
        $source = trim($_POST['source']);

        if (!empty($name) && !empty($type)) {
            global $conn;
            $stmt = $conn->prepare("INSERT INTO cameras (name, type, source) VALUES (?, ?, ?)");
            $stmt->bind_param("sss", $name, $type, $source);
            if ($stmt->execute()) {
                $message = "Camera added successfully.";
            } else {
                $error = "Failed to add camera.";
            }
        } else {
            $error = "Camera name and type are required.";
        }
    }

    // Delete Camera
    if (isset($_POST['delete_camera'])) {
        $id = $_POST['id'];
        if (!empty($id)) {
            global $conn;
            $stmt = $conn->prepare("DELETE FROM cameras WHERE id = ?");
            $stmt->bind_param("i", $id);
            if ($stmt->execute()) {
                $message = "Camera deleted successfully.";
            } else {
                $error = "Failed to delete camera.";
            }
        }
    }
}

$cameras = get_all_cameras();

?>
<?php require_once 'header.php'; ?>

<div class="container mt-4">
    <h2>Camera Management</h2>

    <?php if ($message): ?>
        <div class="alert alert-success"><?php echo $message; ?></div>
    <?php endif; ?>
    <?php if ($error): ?>
        <div class="alert alert-danger"><?php echo $error; ?></div>
    <?php endif; ?>

    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">Add New Camera</h5>
        </div>
        <div class="card-body">
            <form action="cameras.php" method="post">
                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                <div class="form-row">
                    <div class="form-group col-md-4">
                        <label>Name</label>
                        <input type="text" name="name" class="form-control" required>
                    </div>
                    <div class="form-group col-md-4">
                        <label>Type</label>
                        <select name="type" class="form-control">
                            <option value="webcam">Webcam</option>
                            <option value="ipcam">IP Camera</option>
                        </select>
                    </div>
                    <div class="form-group col-md-4">
                        <label>Source (e.g., IP address)</label>
                        <input type="text" name="source" class="form-control">
                    </div>
                </div>
                <button type="submit" name="add_camera" class="btn btn-primary">Add Camera</button>
            </form>
        </div>
    </div>

    <div class="card mt-4">
        <div class="card-header">
            <h5 class="mb-0">Existing Cameras</h5>
        </div>
        <div class="card-body">
            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Type</th>
                        <th>Source</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($cameras as $c): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($c['id']); ?></td>
                            <td><?php echo htmlspecialchars($c['name']); ?></td>
                            <td><?php echo htmlspecialchars($c['type']); ?></td>
                            <td><?php echo htmlspecialchars($c['source']); ?></td>
                            <td>
                                <form action="cameras.php" method="post" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this camera?');">
                                    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                                    <input type="hidden" name="id" value="<?php echo $c['id']; ?>">
                                    <button type="submit" name="delete_camera" class="btn btn-sm btn-danger">Delete</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once 'footer.php'; ?>
