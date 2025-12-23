<?php
require 'header.php';

$msg = '';
$error = '';

// Handle CRUD Operations
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Add Class
    if (isset($_POST['add_class'])) {
        $name = $_POST['class_name'];
        $year = $_POST['academic_year'];
        $start = $_POST['start_roll'];
        $end = $_POST['end_roll'];

        if ($start < $end) {
            $stmt = $pdo->prepare("INSERT INTO classes (class_name, academic_year, start_roll, end_roll) VALUES (?, ?, ?, ?)");
            $stmt->execute([$name, $year, $start, $end]);
            $msg = "Class added successfully.";
        } else {
            $error = "Start Roll must be less than End Roll.";
        }
    } 
    // Edit Class
    elseif (isset($_POST['edit_class'])) {
        $id = $_POST['class_id'];
        $name = $_POST['class_name'];
        $year = $_POST['academic_year'];
        $start = $_POST['start_roll'];
        $end = $_POST['end_roll'];

        if ($start < $end) {
            $stmt = $pdo->prepare("UPDATE classes SET class_name=?, academic_year=?, start_roll=?, end_roll=? WHERE id=?");
            $stmt->execute([$name, $year, $start, $end, $id]);
            $msg = "Class updated successfully.";
        } else {
            $error = "Update failed: Start Roll must be less than End Roll.";
        }
    }
    // Delete Class
    elseif (isset($_POST['delete_id'])) {
        $id = $_POST['delete_id'];
        $stmt = $pdo->prepare("DELETE FROM classes WHERE id = ?");
        $stmt->execute([$id]);
        $msg = "Class deleted successfully.";
    }
}

// Fetch Classes
$classes = $pdo->query("SELECT * FROM classes ORDER BY academic_year DESC, class_name ASC")->fetchAll();
?>

<div class="row">
    <div class="col-md-12 mb-4">
        <h2 class="fw-bold"><i class="fa-solid fa-chalkboard-user text-primary"></i> Class Management</h2>
        <p class="text-muted">Define classes and student roll ranges here. Changes update instantly across the system.</p>
    </div>
</div>

<?php if($error) echo "<div class='alert alert-danger alert-dismissible fade show'>$error <button type='button' class='btn-close' data-bs-dismiss='alert'></button></div>"; ?>
<?php if($msg) echo "<div class='alert alert-success alert-dismissible fade show'>$msg <button type='button' class='btn-close' data-bs-dismiss='alert'></button></div>"; ?>

<div class="row">
    <div class="col-md-4 mb-4">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white fw-bold py-3">Add New Class</div>
            <div class="card-body">
                <form method="POST">
                    <div class="mb-3">
                        <label class="form-label">Class Name</label>
                        <input type="text" name="class_name" class="form-control" placeholder="e.g. Class 10" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Academic Year</label>
                        <input type="text" name="academic_year" class="form-control" value="<?php echo date('Y'); ?>" required>
                    </div>
                    <div class="row">
                        <div class="col-6 mb-3">
                            <label class="form-label">Start Roll</label>
                            <input type="number" name="start_roll" class="form-control" value="1" required>
                        </div>
                        <div class="col-6 mb-3">
                            <label class="form-label">End Roll</label>
                            <input type="number" name="end_roll" class="form-control" placeholder="50" required>
                        </div>
                    </div>
                    <button type="submit" name="add_class" class="btn btn-primary w-100 mt-2">Create Class</button>
                </form>
            </div>
        </div>
    </div>

    <div class="col-md-8">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white fw-bold py-3">Existing Classes</div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-4">Class</th>
                                <th>Year</th>
                                <th>Roll Range</th>
                                <th>Students</th>
                                <th class="text-end pe-4">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($classes as $c): ?>
                                <tr>
                                    <td class="ps-4 fw-bold text-dark"><?php echo htmlspecialchars($c['class_name']); ?></td>
                                    <td><?php echo htmlspecialchars($c['academic_year']); ?></td>
                                    <td><span class="text-muted small">Roll:</span> <?php echo $c['start_roll'] . ' - ' . $c['end_roll']; ?></td>
                                    <td><span class="badge rounded-pill bg-info text-dark"><?php echo ($c['end_roll'] - $c['start_roll'] + 1); ?></span></td>
                                    <td class="text-end pe-4">
                                        <div class="btn-group">
                                            <button type="button" class="btn btn-sm btn-outline-primary" 
                                                onclick='openEditModal(<?php echo json_encode($c); ?>)'>
                                                <i class="fa-solid fa-pen-to-square"></i>
                                            </button>
                                            
                                            <form method="POST" class="d-inline" onsubmit="return confirm('Deleting this class will remove ALL associated marks. Continue?');">
                                                <input type="hidden" name="delete_id" value="<?php echo $c['id']; ?>">
                                                <button type="submit" class="btn btn-sm btn-outline-danger"><i class="fa-solid fa-trash"></i></button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="editModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form method="POST" class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Class Information</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" name="class_id" id="edit_id">
                <div class="mb-3">
                    <label class="form-label">Class Name</label>
                    <input type="text" name="class_name" id="edit_name" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Academic Year</label>
                    <input type="text" name="academic_year" id="edit_year" class="form-control" required>
                </div>
                <div class="row">
                    <div class="col-6 mb-3">
                        <label class="form-label">Start Roll</label>
                        <input type="number" name="start_roll" id="edit_start" class="form-control" required>
                    </div>
                    <div class="col-6 mb-3">
                        <label class="form-label">End Roll</label>
                        <input type="number" name="end_roll" id="edit_end" class="form-control" required>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" name="edit_class" class="btn btn-primary">Save Changes</button>
            </div>
        </form>
    </div>
</div>

<script>
function openEditModal(classData) {
    document.getElementById('edit_id').value = classData.id;
    document.getElementById('edit_name').value = classData.class_name;
    document.getElementById('edit_year').value = classData.academic_year;
    document.getElementById('edit_start').value = classData.start_roll;
    document.getElementById('edit_end').value = classData.end_roll;
    
    var myModal = new bootstrap.Modal(document.getElementById('editModal'));
    myModal.show();
}
</script>

<?php require 'footer.php'; ?>