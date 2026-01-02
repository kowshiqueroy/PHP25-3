<?php require 'includes/header.php'; 

// --- 1. HANDLE DELETE ---
if(isset($_GET['del'])) {
    $id = intval($_GET['del']);
    $conn->query("DELETE FROM employees WHERE id=$id");
    // Redirect to remove 'del' from URL
    echo "<script>window.location='manage_employees.php';</script>";
}

// --- 2. SEARCH LOGIC ---
$where = "1"; // Default: Select All
$search_term = "";
$dept_filter = "";

// Check for text search
if(isset($_GET['q']) && !empty($_GET['q'])) {
    $search_term = $conn->real_escape_string($_GET['q']);
    $where .= " AND (e.name LIKE '%$search_term%' OR e.emp_id LIKE '%$search_term%')";
}

// Check for department filter
if(isset($_GET['dept']) && !empty($_GET['dept'])) {
    $dept_filter = intval($_GET['dept']);
    $where .= " AND e.department_id = $dept_filter";
}

// Run Query
$sql = "SELECT e.*, d.name as dept_name 
        FROM employees e 
        LEFT JOIN departments d ON e.department_id = d.id 
        WHERE $where 
        ORDER BY e.id DESC";

$res = $conn->query($sql);
?>

<div class="container pt-3">
    
    <div class="glass-card mb-4">
        <form method="GET" class="row g-2 align-items-center">
            <div class="col-6 col-md-5">
                <input type="text" name="q" class="form-control" placeholder="Name or ID..." value="<?= htmlspecialchars($search_term) ?>">
            </div>
            <div class="col-4 col-md-4">
                <select name="dept" class="form-select">
                    <option value="">All Depts</option>
                    <?php 
                    $d_res = $conn->query("SELECT * FROM departments"); 
                    while($r=$d_res->fetch_assoc()): 
                    ?>
                        <option value="<?= $r['id'] ?>" <?= ($dept_filter == $r['id']) ? 'selected' : '' ?>>
                            <?= $r['name'] ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div class="col-2 col-md-3">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="fa fa-search"></i>
                </button>
            </div>
        </form>
    </div>

    <div class="row g-3">
        <?php if($res->num_rows > 0): ?>
            <?php while($row = $res->fetch_assoc()): 
                // Count face samples safely
                $faces = json_decode($row['face_descriptors'] ?? '[]');
                $count = is_array($faces) ? count($faces) : 0;
            ?>
            <div class="col-12 col-md-6">
                <div class="glass-card position-relative p-3">
                    <div class="d-flex align-items-center">
                        <div class="me-3 position-relative">
                            <img src="<?= !empty($row['photo_path']) ? $row['photo_path'] : 'assets/default.png' ?>" 
                                 class="rounded-circle border border-info" 
                                 width="70" height="70" 
                                 style="object-fit: cover;">
                        </div>
                        
                        <div class="flex-grow-1">
                            <h5 class="mb-0 fw-bold text-white"><?= htmlspecialchars($row['name']) ?></h5>
                            <div class="small text-white-50 mb-1">
                                <?= htmlspecialchars($row['dept_name']) ?> • <?= htmlspecialchars($row['position']) ?>
                            </div>
                            <div class="d-flex gap-2">
                                <span class="badge bg-dark border border-secondary text-white-50">
                                    ID: <?= htmlspecialchars($row['emp_id']) ?>
                                </span>
                                <span class="badge bg-info text-dark">
                                    <i class="fa fa-face-smile"></i> <?= $count ?> Faces
                                </span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mt-3 d-flex gap-2 border-top border-secondary pt-3">
                        <a href="edit_employee.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-outline-primary flex-fill">
                            <i class="fa fa-edit"></i> Edit
                        </a>
                        <a href="manage_employees.php?del=<?= $row['id'] ?>" 
                           class="btn btn-sm btn-outline-danger flex-fill"
                           onclick="return confirm('Are you sure you want to delete <?= addslashes($row['name']) ?>? This will delete all attendance logs too.')">
                            <i class="fa fa-trash"></i> Delete
                        </a>
                    </div>

                </div>
            </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="col-12 text-center py-5 text-white-50">
                <i class="fa fa-users-slash fa-3x mb-3"></i><br>
                No employees found matching your search.
            </div>
        <?php endif; ?>
    </div>
    
    <div style="height: 100px;"></div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>