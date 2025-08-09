<?php
require_once '../includes/session.php';
require_once '../includes/functions.php';

if (!is_hr()) {
    header("Location: ../login.php");
    exit();
}

function get_attendance_logs() {
    global $conn;
    $query = "SELECT attendance_logs.*, staff.name AS staff_name, cameras.name AS camera_name 
              FROM attendance_logs 
              LEFT JOIN staff ON attendance_logs.staff_id = staff.id 
              LEFT JOIN cameras ON attendance_logs.camera_id = cameras.id 
              ORDER BY timestamp DESC";
    $result = $conn->query($query);
    return $result->fetch_all(MYSQLI_ASSOC);
}

$logs = get_attendance_logs();

?>
<?php require_once 'header.php'; ?>

<div class="container mt-4">
    <h2>Attendance Logs</h2>
    <div class="card">
        <div class="card-body">
            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>Timestamp</th>
                        <th>Staff Name</th>
                        <th>Type</th>
                        <th>Camera</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($logs as $log): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($log['timestamp']); ?></td>
                            <td><?php echo htmlspecialchars($log['staff_name']); ?></td>
                            <td><?php echo htmlspecialchars($log['type']); ?></td>
                            <td><?php echo htmlspecialchars($log['camera_name']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once 'footer.php'; ?>
