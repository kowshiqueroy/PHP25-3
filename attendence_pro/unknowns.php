<?php 
require 'includes/header.php'; 

// --- 1. ACTION HANDLERS ---

// Handle Individual Delete
if (isset($_GET['del'])) {
    $id = intval($_GET['del']);
    $conn->query("DELETE FROM unknown_logs WHERE id=$id");
    echo "<script>window.location='unknowns.php';</script>";
}

// Handle Auto-Cleanup (Older than 30 days)
if(isset($_GET['clear_old'])) {
    $conn->query("DELETE FROM unknown_logs WHERE log_time < (NOW() - INTERVAL 30 DAY)");
    echo "<script>window.location='unknowns.php';</script>";
}

// Handle Assign to Existing Employee
if (isset($_POST['assign_existing'])) {
    $unknown_id = intval($_POST['unknown_id']);
    $employee_id = intval($_POST['target_employee_id']);

    $res = $conn->query("SELECT face_descriptor FROM unknown_logs WHERE id=$unknown_id");
    if($u_data = $res->fetch_assoc()){
        $new_descriptor = json_decode($u_data['face_descriptor'])[0];

        $e_res = $conn->query("SELECT face_descriptors FROM employees WHERE id=$employee_id");
        $e_data = $e_res->fetch_assoc();
        $current_descriptors = json_decode($e_data['face_descriptors'], true);

        $current_descriptors[] = $new_descriptor;
        $updated_json = json_encode($current_descriptors);

        $stmt = $conn->prepare("UPDATE employees SET face_descriptors=? WHERE id=?");
        $stmt->bind_param("si", $updated_json, $employee_id);
        
        if($stmt->execute()) {
            $conn->query("DELETE FROM unknown_logs WHERE id=$unknown_id");
            echo "<script>alert('Recognition Improved!'); window.location='unknowns.php';</script>";
        }
    }
}

// Handle Add as New Employee
if (isset($_POST['add_new'])) {
    $unknown_id = intval($_POST['unknown_id']);
    $name = $conn->real_escape_string($_POST['name']);
    $emp_id_code = $conn->real_escape_string($_POST['emp_id_code']);

    $res = $conn->query("SELECT * FROM unknown_logs WHERE id=$unknown_id");
    $u = $res->fetch_assoc();

    $stmt = $conn->prepare("INSERT INTO employees (name, emp_id, face_descriptors, photo_path) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $name, $emp_id_code, $u['face_descriptor'], $u['image_path']);
    
    if($stmt->execute()) {
        $conn->query("DELETE FROM unknown_logs WHERE id=$unknown_id");
        echo "<script>alert('New Employee Added!'); window.location='unknowns.php';</script>";
    }
}

// --- 2. SEARCH & DATA FETCH ---
$start = $_GET['start'] ?? date('Y-m-d', strtotime('-7 days'));
$end = date('Y-m-d', strtotime($_GET['end'] ?? date('Y-m-d') . ' +1 days'));

$sql = "SELECT * FROM unknown_logs WHERE DATE(log_time) BETWEEN '$start' AND '$end' ORDER BY log_time DESC";
$result = $conn->query($sql);

$all_employees = $conn->query("SELECT id, name, emp_id FROM employees ORDER BY name ASC");
$emp_list = [];
while($row = $all_employees->fetch_assoc()) $emp_list[] = $row;
?>

<style>
    .unknown-img {
        width: 100%; height: 200px; object-fit: cover;
        border-radius: 12px 12px 0 0;
    }
    .visitor-badge {
        position: absolute; top: 10px; left: 10px;
        background: rgba(0,0,0,0.7); backdrop-filter: blur(5px);
        font-size: 0.7rem; padding: 4px 10px; border-radius: 20px; z-index: 5;
    }
    .action-overlay {
        position: absolute; top: 10px; right: 10px; z-index: 5;
    }
    @media print { .no-print { display: none !important; } }
</style>

<div class="container pt-3">
    <div class="d-flex justify-content-between align-items-center mb-4 no-print">
        <h4 class="fw-bold m-0"><i class="fa fa-user-secret text-warning"></i> Unknown Face Hub</h4>
        <div class="d-flex gap-2">
            <button onclick="window.print()" class="btn btn-sm btn-outline-light"><i class="fa fa-print"></i></button>
            <a href="unknowns.php?clear_old=1" class="btn btn-sm btn-outline-danger" onclick="return confirm('Clear logs older than 30 days?')">
                <i class="fa fa-broom"></i> Cleanup
            </a>
        </div>
    </div>

    <div class="glass-card mb-4 no-print">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-5 col-md-5">
                <label class="small text-white-50">From</label>
                <input type="date" name="start" class="form-control form-control-sm" value="<?= $start ?>">
            </div>
            <div class="col-5 col-md-5">
                <label class="small text-white-50">To</label>
                <input type="date" name="end" class="form-control form-control-sm" value="<?= $end ?>">
            </div>
            <div class="col-2 col-md-2">
                <button type="submit" class="btn btn-primary btn-sm w-100"><i class="fa fa-search"></i></button>
            </div>
        </form>
    </div>

    <div class="row g-3">
        <?php if($result->num_rows > 0): ?>
            <?php while($row = $result->fetch_assoc()): ?>
                <div class="col-6 col-md-4 col-lg-3">
                    <div class="glass-card p-0 h-100 overflow-hidden position-relative border-secondary">
                        
                        <div class="visitor-badge text-info">ID: <?= $row['id'] ?></div>
                        <div class="action-overlay no-print">
                            <a href="unknowns.php?del=<?= $row['id'] ?>" class="btn btn-danger btn-xs rounded-circle p-1" onclick="return confirm('Delete log?')">
                                <i class="fa fa-times px-1"></i>
                            </a>
                        </div>

                        <img src="<?= str_replace('../', '', $row['image_path']) ?>" class="unknown-img" onerror="this.src='assets/photos/default.jpg'">
                        
                        <div class="p-2">
                            <p class="x-small text-white-50 mb-2">
                                <i class="fa fa-clock me-1"></i> <?= date('M d, h:i A', strtotime($row['log_time'])) ?>
                            </p>
                            
                            <div class="d-grid gap-1 no-print">
                                <button class="btn btn-xs btn-outline-info" style="font-size: 0.65rem;" data-bs-toggle="collapse" data-bs-target="#assign<?= $row['id'] ?>">
                                    <i class="fa fa-plus"></i> Improve Recognition
                                </button>
                                <button class="btn btn-xs btn-outline-success" style="font-size: 0.65rem;" data-bs-toggle="collapse" data-bs-target="#new<?= $row['id'] ?>">
                                    <i class="fa fa-user-plus"></i> Add as New
                                </button>
                            </div>

                            <div class="collapse no-print" id="assign<?= $row['id'] ?>">
                                <form method="POST" class="mt-2 p-2 bg-black bg-opacity-25 rounded border border-info border-opacity-25">
                                    <input type="hidden" name="unknown_id" value="<?= $row['id'] ?>">
                                    <select name="target_employee_id" class="form-select form-select-sm mb-1 text-xs" required style="font-size: 0.7rem;">
                                        <option value="">Select Target...</option>
                                        <?php foreach($emp_list as $e): ?>
                                            <option value="<?= $e['id'] ?>"><?= $e['name'] ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                    <button name="assign_existing" class="btn btn-info btn-xs w-100" style="font-size: 0.6rem;">Merge Face</button>
                                </form>
                            </div>

                            <div class="collapse no-print" id="new<?= $row['id'] ?>">
                                <form method="POST" class="mt-2 p-2 bg-black bg-opacity-25 rounded border border-success border-opacity-25">
                                    <input type="hidden" name="unknown_id" value="<?= $row['id'] ?>">
                                    <input type="text" name="name" class="form-control form-control-sm mb-1" placeholder="Name" required style="font-size: 0.7rem;">
                                    <input type="text" name="emp_id_code" class="form-control form-control-sm mb-1" placeholder="Emp ID" required style="font-size: 0.7rem;">
                                    <button name="add_new" class="btn btn-success btn-xs w-100" style="font-size: 0.6rem;">Enroll</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="col-12 text-center py-5">
                <i class="fa fa-ghost fa-3x mb-3 text-white-50 opacity-25"></i>
                <p class="text-white-50">No unknown faces found in this range.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<div style="height: 100px;"></div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>