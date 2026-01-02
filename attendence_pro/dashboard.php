<?php 
require 'includes/header.php'; 

if (!isset($_SESSION['admin_id'])) {
    header("Location: index.php");
    exit();
}

// --- FETCH STATS ---

// 1. Total Employees
$total_emp = $conn->query("SELECT COUNT(*) as c FROM employees")->fetch_assoc()['c'];

// 2. Total Departments (Corrected)
$total_depts = $conn->query("SELECT COUNT(*) as c FROM departments")->fetch_assoc()['c'];

// 3. Present Today
$today = date('Y-m-d');
$present = $conn->query("SELECT COUNT(DISTINCT emp_id) as c FROM logs WHERE DATE(log_time) = '$today'")->fetch_assoc()['c'];

// 4. Unknown Faces
$unknowns = $conn->query("SELECT COUNT(*) as c FROM unknown_logs")->fetch_assoc()['c'];

// 5. Recent Activity
$recent = $conn->query("SELECT l.log_time, e.name, e.position 
                        FROM logs l 
                        JOIN employees e ON l.emp_id = e.id 
                        ORDER BY l.log_time DESC LIMIT 5");
?>

<style>
    .stat-card {
        transition: transform 0.3s ease, background 0.3s ease;
        cursor: pointer;
        border: 1px solid rgba(255, 255, 255, 0.1) !important;
    }
    .stat-card:hover {
        transform: translateY(-5px);
        background: rgba(255, 255, 255, 0.15) !important;
    }
    .quick-action-btn {
        transition: all 0.3s ease;
        border: none;
        overflow: hidden;
    }
    .quick-action-btn:active {
        transform: scale(0.95);
    }
    .activity-row:last-child {
        border-bottom: none !important;
    }
    .icon-box {
        width: 50px;
        height: 50px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 12px;
        margin-bottom: 15px;
    }
</style>

<div class="container pt-3">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold m-0 text-white">Dashboard</h2>
            <p class="text-white-50 m-0 small"><i class="far fa-calendar-alt me-1"></i> <?= date('l, F jS Y') ?></p>
        </div>
        <a href="logout.php" class="btn btn-sm btn-outline-danger px-4 rounded-pill shadow-sm">
            <i class="fas fa-power-off me-1"></i> Logout
        </a>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-6 col-lg-3">
            <div class="glass-card stat-card h-100" onclick="window.location.href='manage_employees.php'">
                <div class="icon-box bg-info bg-opacity-10 text-info">
                    <i class="fa fa-users fa-lg"></i>
                </div>
                <h2 class="fw-bold m-0 text-white"><?= $total_emp ?></h2>
                <div class="text-white-50 small">Total Employees</div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="glass-card stat-card h-100" onclick="window.location.href='manage_depts.php'">
                <div class="icon-box bg-primary bg-opacity-10 text-primary">
                    <i class="fa fa-building fa-lg"></i>
                </div>
                <h2 class="fw-bold m-0 text-white"><?= $total_depts ?></h2>
                <div class="text-white-50 small">Departments</div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="glass-card stat-card h-100" onclick="window.location.href='reports.php'">
                <div class="icon-box bg-success bg-opacity-10 text-success">
                    <i class="fa fa-user-check fa-lg"></i>
                </div>
                <h2 class="fw-bold m-0 text-white"><?= $present ?></h2>
                <div class="text-white-50 small">Present Today</div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="glass-card stat-card h-100 position-relative" onclick="window.location.href='unknowns.php'">
                <div class="icon-box bg-warning bg-opacity-10 text-warning">
                    <i class="fa fa-user-secret fa-lg"></i>
                </div>
                <h2 class="fw-bold m-0 text-warning"><?= $unknowns ?></h2>
                <div class="text-white-50 small">Unknown Faces</div>
                <?php if($unknowns > 0): ?>
                    <span class="position-absolute top-0 end-0 m-3 d-flex h-3 w-3">
                        <span class="animate-ping position-absolute inline-flex h-full w-full rounded-full bg-warning opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-2 w-2 bg-warning"></span>
                    </span>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-6">
            <a href="enroll.php" class="btn btn-primary quick-action-btn w-100 py-3 rounded-4 shadow d-flex flex-column align-items-center">
                <i class="fa fa-user-plus fa-lg mb-2"></i>
                <span>Enroll Employee</span>
            </a>
        </div>
        <div class="col-6">
            <a href="attendance.php" class="btn btn-success quick-action-btn w-100 py-3 rounded-4 shadow d-flex flex-column align-items-center">
                <i class="fa fa-camera fa-lg mb-2"></i>
                <span>Open AI Mode</span>
            </a>
        </div>
    </div>

    <div class="d-flex justify-content-between align-items-end mb-3 px-1">
        <h5 class="m-0 text-white">Recent Activity</h5>
        <a href="reports.php" class="text-info text-decoration-none small">View All Reports <i class="fas fa-chevron-right ms-1" style="font-size: 0.7rem;"></i></a>
    </div>

    <div class="glass-card p-0 overflow-hidden mb-5">
        <div class="list-group list-group-flush bg-transparent">
            <?php if ($recent->num_rows > 0): ?>
                <?php while($row = $recent->fetch_assoc()): ?>
                    <div class="list-group-item bg-transparent border-white border-opacity-10 text-white py-3 activity-row">
                        <div class="d-flex justify-content-between align-items-center">
                            <div class="d-flex align-items-center">
                                <div class="rounded-circle bg-success bg-opacity-25 p-2 me-3">
                                    <i class="fa fa-check text-success small"></i>
                                </div>
                                <div>
                                    <div class="fw-bold"><?= htmlspecialchars($row['name']) ?></div>
                                    <div class="text-white-50 x-small"><?= htmlspecialchars($row['position']) ?></div>
                                </div>
                            </div>
                            <div class="text-end">
                                <div class="badge bg-dark bg-opacity-50 text-info border border-info border-opacity-25 rounded-pill fw-normal">
                                    <i class="far fa-clock me-1"></i> <?= date('h:i A', strtotime($row['log_time'])) ?>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="text-center py-5 text-white-50">
                    <i class="fa fa-history fa-2x mb-2 opacity-25"></i>
                    <p>No biometric scans recorded today.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require 'includes/footer.php'; ?>