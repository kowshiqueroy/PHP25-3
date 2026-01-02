<?php 
require 'includes/header.php'; 

// --- DATABASE LOGIC ---
$msg = "";
$error = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['add_dept'])) {
        $name = trim($conn->real_escape_string($_POST['dept_name']));
        if (!empty($name)) {
            $conn->query("INSERT INTO departments (name) VALUES ('$name')");
            $msg = "Department created!";
        }
    }
    
    if (isset($_POST['update_dept'])) {
        $id = intval($_POST['dept_id']);
        $name = trim($conn->real_escape_string($_POST['dept_name']));
        if ($id > 0 && !empty($name)) {
            $conn->query("UPDATE departments SET name = '$name' WHERE id = $id");
            $msg = "Department updated!";
        }
    }
}

$depts = $conn->query("SELECT * FROM departments ORDER BY id DESC");
?>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

<div class="container pt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="text-white fw-bold">Department Manager</h3>
        <button type="button" class="btn btn-primary rounded-pill px-4" data-bs-toggle="modal" data-bs-target="#addModal">
            <i class="fa fa-plus me-1"></i> New Dept
        </button>
    </div>

    <?php if($msg): ?>
        <div class="alert alert-success bg-success bg-opacity-25 text-white border-0"><?= $msg ?></div>
    <?php endif; ?>

    <div class="row g-3">
        <?php while($row = $depts->fetch_assoc()): ?>
            <div class="col-md-4">
                <div class="glass-card d-flex justify-content-between align-items-center p-3">
                    <div class="text-truncate">
                        <span class="text-white-50 small">#<?= $row['id'] ?></span>
                        <h5 class="mb-0 fw-bold text-white"><?= htmlspecialchars($row['name']) ?></h5>
                    </div>
                    <button type="button" class="btn btn-sm btn-outline-info rounded-pill edit-btn" 
                            data-id="<?= $row['id'] ?>" 
                            data-name="<?= htmlspecialchars($row['name']) ?>"
                            data-bs-toggle="modal" data-bs-target="#editModal">
                        Edit
                    </button>
                </div>
            </div>
        <?php endwhile; ?>
    </div>
</div>

<div class="modal fade" id="addModal" tabindex="-1" aria-labelledby="addModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 bg-dark text-white shadow-lg">
            <div class="modal-header border-bottom border-secondary">
                <h5 class="modal-title">Create New Department</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" action="manage_depts.php">
                <div class="modal-body">
                    <label class="small text-white-50 mb-2">Name</label>
                    <input type="text" name="dept_name" class="form-control bg-black text-white border-secondary" required>
                </div>
                <div class="modal-footer border-0">
                    <button type="submit" name="add_dept" class="btn btn-primary w-100 rounded-pill">Save Department</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 bg-dark text-white shadow-lg">
            <div class="modal-header border-bottom border-secondary">
                <h5 class="modal-title">Update Department</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" action="manage_depts.php">
                <input type="hidden" name="dept_id" id="edit_id">
                <div class="modal-body">
                    <label class="small text-white-50 mb-2">Edit Name</label>
                    <input type="text" name="dept_name" id="edit_name" class="form-control bg-black text-white border-secondary" required>
                </div>
                <div class="modal-footer border-0">
                    <button type="submit" name="update_dept" class="btn btn-info w-100 rounded-pill text-dark fw-bold">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Fill Edit Modal Data
    const editBtns = document.querySelectorAll('.edit-btn');
    editBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            document.getElementById('edit_id').value = this.getAttribute('data-id');
            document.getElementById('edit_name').value = this.getAttribute('data-name');
        });
    });
});
</script>

<style>
    /* Ensure modals appear above everything */
    .modal { z-index: 1060 !important; }
    .modal-backdrop { z-index: 1050 !important; }
    .glass-card { background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); border-radius: 15px; }
</style>

<?php require 'includes/footer.php'; ?>