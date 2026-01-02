<?php require 'includes/header.php'; 

// --- 1. FILTERS ---
$start_date = $_GET['start'] ?? date('Y-m-d');
$end_date   = date('Y-m-d', strtotime($_GET['end'] ?? date('Y-m-d') . ' +1 days'));
$dept       = $_GET['dept']  ?? '';
$pos        = $_GET['pos']   ?? '';
$emp_id     = $_GET['emp_id']?? '';
$name       = $_GET['name']  ?? '';
$view_type  = $_GET['view']  ?? 'summary'; // 'summary' or 'all'

// --- 2. BUILD QUERY ---
$where = "DATE(l.log_time) BETWEEN '$start_date' AND '$end_date'";

if ($dept)   $where .= " AND e.department_id = " . intval($dept);
if ($pos)    $where .= " AND e.position LIKE '%" . $conn->real_escape_string($pos) . "%'";
if ($emp_id) $where .= " AND e.emp_id = '" . $conn->real_escape_string($emp_id) . "'";
if ($name)   $where .= " AND e.name LIKE '%" . $conn->real_escape_string($name) . "%'";

if ($view_type == 'summary') {
    $sql = "SELECT e.emp_id, e.name, e.position, d.name as dept_name, 
                   DATE(l.log_time) as work_date, 
                   MIN(l.log_time) as start_time, 
                   MAX(l.log_time) as end_time
            FROM logs l
            JOIN employees e ON l.emp_id = e.id
            LEFT JOIN departments d ON e.department_id = d.id
            WHERE $where
            GROUP BY e.id, DATE(l.log_time)
            ORDER BY work_date DESC, e.name ASC";
} else {
    $sql = "SELECT e.emp_id, e.name, e.position, d.name as dept_name, 
                   l.log_time as scan_time
            FROM logs l
            JOIN employees e ON l.emp_id = e.id
            LEFT JOIN departments d ON e.department_id = d.id
            WHERE $where
            ORDER BY l.log_time DESC";
}

$res = $conn->query($sql);
?>

<style>
    /* A4 Printing Optimization */
    @media print {
        body { background: white !important; color: black !important; padding: 0; margin: 0; }
        .no-print, .bottom-nav { display: none !important; }
        .glass-card { border: none !important; backdrop-filter: none !important; background: none !important; box-shadow: none !important; color: black !important; }
        table { font-size: 10px; width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #ddd !important; padding: 2px 4px !important; color: black !important; }
        tr { height: 20px; page-break-inside: avoid; }
        h4 { color: black !important; text-align: center; margin-bottom: 10px; }
    }
</style>

<div class="glass-card no-print mb-4">
    <h5 class="mb-3"><i class="fa fa-filter text-info"></i> Report Filters</h5>
    <form method="GET" class="row g-2">
        <div class="col-6 col-md-2">
            <label class="small text-white-50">Start Date</label>
            <input type="date" name="start" class="form-control form-control-sm" value="<?= $start_date ?>">
        </div>
        <div class="col-6 col-md-2">
            <label class="small text-white-50">End Date</label>
            <input type="date" name="end" class="form-control form-control-sm" value="<?= $end_date ?>">
        </div>
        <div class="col-6 col-md-2">
            <label class="small text-white-50">Dept</label>
            <select name="dept" class="form-select form-select-sm">
                <option value="">All</option>
                <?php $d=$conn->query("SELECT * FROM departments"); while($r=$d->fetch_assoc()) echo "<option value='{$r['id']}' ".($dept==$r['id']?'selected':'').">{$r['name']}</option>"; ?>
            </select>
        </div>
        <div class="col-6 col-md-2">
            <label class="small text-white-50">Position</label>
            <input type="text" name="pos" class="form-control form-control-sm" value="<?= $pos ?>" placeholder="Manager..">
        </div>
        <div class="col-6 col-md-2">
            <label class="small text-white-50">Emp ID</label>
            <input type="text" name="emp_id" class="form-control form-control-sm" value="<?= $emp_id ?>">
        </div>
        <div class="col-6 col-md-2">
            <label class="small text-white-50">Name</label>
            <input type="text" name="name" class="form-control form-control-sm" value="<?= $name ?>">
        </div>
        <div class="col-12 mt-3 d-flex gap-2">
            <select name="view" class="form-select form-select-sm w-auto">
                <option value="summary" <?= $view_type=='summary'?'selected':'' ?>>Daily Summary (In/Out)</option>
                <option value="all" <?= $view_type=='all'?'selected':'' ?>>Detailed Log (All Scans)</option>
            </select>
            <button class="btn btn-primary btn-sm px-4">Apply</button>
            <button type="button" onclick="window.print()" class="btn btn-light btn-sm px-4">Print A4</button>
        </div>
    </form>
</div>

<div class="glass-card">
    <h4 class="mb-3 d-none d-print-block">Attendance Report (<?= $start_date ?> to <?= $end_date ?>)</h4>
    <div class="table-responsive">
        <table class="table table-sm text-white border-secondary">
            <thead class="bg-black bg-opacity-25">
                <tr>
                    <th>Date</th>
                    <th>ID</th>
                    <th>Employee Name</th>
                    <th>Dept</th>
                    <th>Position</th>
                    <?php if($view_type == 'summary'): ?>
                        <th>In Time</th>
                        <th>Out Time</th>
                        <th>Duration</th>
                    <?php else: ?>
                        <th>Scan Time</th>
                    <?php endif; ?>
                </tr>
            </thead>
            <tbody>
                <?php if($res->num_rows > 0): while($row = $res->fetch_assoc()): ?>
                <tr>
                    <td><?= date('d-m-Y', strtotime($view_type == 'summary' ? $row['work_date'] : $row['scan_time'])) ?></td>
                    <td><?= $row['emp_id'] ?></td>
                    <td><?= $row['name'] ?></td>
                    <td><?= $row['dept_name'] ?></td>
                    <td><?= $row['position'] ?></td>
                    <?php if($view_type == 'summary'): ?>
                        <td class="text-info"><?= date('h:i A', strtotime($row['start_time'])) ?></td>
                        <td class="text-warning">
                            <?= ($row['start_time'] == $row['end_time']) ? '--' : date('h:i A', strtotime($row['end_time'])) ?>
                        </td>
                        <td>
                            <?php 
                                if ($row['start_time'] == $row['end_time']) {
                                    echo '--';
                                } else {
                                    $duration = strtotime($row['end_time']) - strtotime($row['start_time']);
                                    $hours = floor($duration / 3600);
                                    $minutes = floor(($duration % 3600) / 60);
                                    echo sprintf("%02d:%02d hrs", $hours, $minutes);
                                }
                            ?>
                        </td>
                    <?php else: ?>
                        <td><?= date('h:i A', strtotime($row['scan_time'])) ?></td>
                    <?php endif; ?>
                </tr>
                <?php endwhile; else: ?>
                    <tr><td colspan="7" class="text-center py-4">No data found.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<div style="height: 80px;"></div>
</body>
</html>