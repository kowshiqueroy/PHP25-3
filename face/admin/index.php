<?php
require_once '../includes/session.php';
require_once '../includes/functions.php';

if (!is_admin()) {
    header("Location: ../login.php");
    exit();
}

function get_total_staff() {
    global $conn;
    $result = $conn->query("SELECT COUNT(*) as count FROM staff");
    $row = $result->fetch_assoc();
    return $row['count'];
}

function get_total_departments() {
    global $conn;
    $result = $conn->query("SELECT COUNT(*) as count FROM departments");
    $row = $result->fetch_assoc();
    return $row['count'];
}

function get_today_attendance_count() {
    global $conn;
    $result = $conn->query("SELECT COUNT(*) as count FROM attendance_logs WHERE DATE(timestamp) = CURDATE() AND type = 'Check-In'");
    $row = $result->fetch_assoc();
    return $row['count'];
}

?>
<?php require_once 'header.php'; ?>

<div class="container mt-4">
    <h2>Admin Dashboard</h2>
    <div class="row">
        <div class="col-md-4">
            <div class="card text-white bg-primary mb-3">
                <div class="card-header">Total Staff</div>
                <div class="card-body">
                    <h5 class="card-title"><?php echo get_total_staff(); ?></h5>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card text-white bg-success mb-3">
                <div class="card-header">Total Departments</div>
                <div class="card-body">
                    <h5 class="card-title"><?php echo get_total_departments(); ?></h5>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card text-white bg-info mb-3">
                <div class="card-header">Today's Check-ins</div>
                <div class="card-body">
                    <h5 class="card-title"><?php echo get_today_attendance_count(); ?></h5>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'footer.php'; ?>
