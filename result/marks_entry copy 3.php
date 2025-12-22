<?php
require_once 'db.php';
if (session_status() === PHP_SESSION_NONE) { session_start(); }

// 1. INPUT PARAMETERS
$year = $_GET['year'] ?? date('Y');
$class_id = $_GET['class_id'] ?? null;
$term = $_GET['term'] ?? 'Annual';
$selected_subjects = $_GET['subject_ids'] ?? []; 
$exam_terms = ["CT1", "CT2", "CT3", "CT4", "Half Yearly", "Annual", "Final", "Pre-Test", "Test", "Scholarship", "Model Test"];

// 2. SAVE LOGIC
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['bulk_data_json'])) {
    try {
        $decoded_data = json_decode($_POST['bulk_data_json'], true);
        $c_id = $_POST['class_id'];
        $e_term = $_POST['exam_term'];

        $pdo->beginTransaction();
        foreach ($decoded_data as $row) {
            $roll = $row['roll'];
            foreach ($row as $key => $val) {
                if (strpos($key, 'comp_') === 0) {
                    $comp_id = str_replace('comp_', '', $key);
                    if ($val === '' || $val === null) {
                        $pdo->prepare("DELETE FROM marks WHERE class_id=? AND student_roll=? AND component_id=? AND exam_term=?")
                            ->execute([$c_id, $roll, $comp_id, $e_term]);
                    } else {
                        $pdo->prepare("INSERT INTO marks (class_id, student_roll, component_id, exam_term, marks_obtained) 
                                       VALUES (?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE marks_obtained = VALUES(marks_obtained)")
                            ->execute([$c_id, $roll, $comp_id, $e_term, $val]);
                    }
                }
            }
        }
        $pdo->commit();
        header("Location: marks_entry.php?year=$year&class_id=$c_id&term=$e_term&success=1&" . http_build_query(['subject_ids' => $selected_subjects]));
        exit;
    } catch (Exception $e) { 
        if($pdo->inTransaction()) $pdo->rollBack(); 
        $error = $e->getMessage(); 
    }
}

require 'header.php';
?>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/handsontable/dist/handsontable.full.min.css">
<script src="https://cdn.jsdelivr.net/npm/handsontable/dist/handsontable.full.min.js"></script>

<div class="d-flex justify-content-between align-items-center mb-3 no-print px-3 mt-3">
    <h4 class="fw-bold text-dark mb-0"><i class="fa-solid fa-table-list text-primary me-2"></i>Marks Entry Sheet</h4>
    <div class="d-flex gap-2">
        <?php if($class_id): ?>
            <button type="button" onclick="saveSpreadsheet()" class="btn btn-success shadow-sm px-4 fw-bold">
                <i class="fa-solid fa-floppy-disk me-2"></i>Save Changes
            </button>
        <?php endif; ?>
    </div>
</div>

<div class="card shadow-sm border-0 mb-4 bg-light mx-3 no-print">
    <div class="card-body py-3">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-md-2">
                <label class="small fw-bold">Year</label>
                <input type="number" name="year" class="form-control form-control-sm" value="<?= $year ?>">
            </div>
            <div class="col-md-2">
                <label class="small fw-bold">Class</label>
                <select name="class_id" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="">-- Select --</option>
                    <?php 
                    $cls_stmt = $pdo->prepare("SELECT * FROM classes WHERE academic_year = ?");
                    $cls_stmt->execute([$year]);
                    foreach($cls_stmt->fetchAll() as $c): ?>
                        <option value="<?= $c['id'] ?>" <?= $class_id == $c['id'] ? 'selected' : '' ?>><?= htmlspecialchars($c['class_name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                <label class="small fw-bold">Exam Term</label>
                <select name="term" class="form-select form-select-sm">
                    <?php foreach($exam_terms as $et): ?>
                        <option value="<?= $et ?>" <?= $term == $et ? 'selected' : '' ?>><?= $et ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                <label class="small fw-bold">Subjects (Multiple)</label>
                <select name="subject_ids[]" class="form-select form-select-sm" multiple size="1">
                    <?php if($class_id): 
                        $sub_stmt = $pdo->prepare("SELECT id, subject_name FROM subjects WHERE class_id = ?");
                        $sub_stmt->execute([$class_id]);
                        foreach($sub_stmt->fetchAll() as $s): ?>
                            <option value="<?= $s['id'] ?>" <?= in_array($s['id'], $selected_subjects) ? 'selected' : '' ?>><?= htmlspecialchars($s['subject_name']) ?></option>
                    <?php endforeach; endif; ?>
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary btn-sm w-100 fw-bold">Load Sheet</button>
            </div>
        </form>
    </div>
</div>

<?php if ($class_id && !empty($selected_subjects)): 
    // Logic to fetch structural headers
    $in_query = implode(',', array_map('intval', $selected_subjects));
    $comp_sql = "SELECT c.id as comp_id, c.component_name, c.max_marks, p.part_name, s.subject_name, s.id as sub_id 
                 FROM subject_components c 
                 JOIN subject_parts p ON c.part_id = p.id 
                 JOIN subjects s ON p.subject_id = s.id 
                 WHERE s.id IN ($in_query) ORDER BY s.id, p.id, c.id";
    $all_comps = $pdo->query($comp_sql)->fetchAll();

    $subjects_map = [];
    foreach($all_comps as $row) {
        $subjects_map[$row['sub_id']]['name'] = $row['subject_name'];
        $subjects_map[$row['sub_id']]['comps'][] = $row;
    }

    // Fetch marks
    $m_stmt = $pdo->prepare("SELECT student_roll, component_id, marks_obtained FROM marks WHERE class_id = ? AND exam_term = ?");
    $m_stmt->execute([$class_id, $term]);
    $marks_db = [];
    while($m = $m_stmt->fetch()){ $marks_db[$m['student_roll']][$m['component_id']] = $m['marks_obtained']; }

    // Table Data
    $cls_info = $pdo->prepare("SELECT start_roll, end_roll FROM classes WHERE id = ?");
    $cls_info->execute([$class_id]); $meta = $cls_info->fetch();

    $table_json = [];
    for($r = $meta['start_roll']; $r <= $meta['end_roll']; $r++) {
        $row = ['roll' => $r];
        foreach($all_comps as $c) { $row['comp_'.$c['comp_id']] = $marks_db[$r][$c['comp_id']] ?? null; }
        $table_json[] = $row;
    }
?>

<div id="marksContainer" class="mx-3 border rounded shadow-sm bg-white overflow-hidden">
    <div id="htTable"></div>
</div>

<form id="bulkForm" method="POST">
    <input type="hidden" name="class_id" value="<?= $class_id ?>">
    <input type="hidden" name="exam_term" value="<?= $term ?>">
    <input type="hidden" id="json_input" name="bulk_data_json">
</form>

<script>
const hot = new Handsontable(document.getElementById('htTable'), {
    data: <?= json_encode($table_json) ?>,
    height: '65vh',
    licenseKey: 'non-commercial-and-evaluation',
    stretchH: 'all',
    rowHeaders: true,
    fixedColumnsLeft: 1,
    nestedHeaders: [
        ['Roll', <?php foreach($subjects_map as $s) echo "{label: '".addslashes($s['name'])."', colspan: ".(count($s['comps'])+1)."},"; ?> 'Grand'],
        ['', <?php foreach($all_comps as $c) echo "'".addslashes($c['part_name'])."',"; foreach($subjects_map as $s) echo "'Total',"; ?> 'Total'],
        ['ID', <?php foreach($all_comps as $c) echo "'".addslashes($c['component_name'])." (".$c['max_marks'].")',"; foreach($subjects_map as $s) echo "'Sum',"; ?> 'All']
    ],
    columns: [
        { data: 'roll', readOnly: true, className: 'htCenter htMiddle fw-bold bg-light' },
        <?php foreach($all_comps as $c): ?>
        { data: 'comp_<?= $c['comp_id'] ?>', type: 'numeric', className: 'htCenter' },
        <?php endforeach; ?>
        <?php foreach($subjects_map as $sid => $s): ?>
        {
            readOnly: true, className: 'htCenter fw-bold sub-total-col',
            renderer: function(instance, td, row, col, prop, value, cellProperties) {
                const rowData = instance.getSourceDataAtRow(row);
                let sum = 0;
                <?= json_encode(array_column($s['comps'], 'comp_id')) ?>.forEach(id => { sum += parseFloat(rowData['comp_' + id] || 0); });
                td.innerText = sum.toFixed(2);
                return td;
            }
        },
        <?php endforeach; ?>
        {
            readOnly: true, className: 'htCenter fw-bold grand-total-col',
            renderer: function(instance, td, row, col, prop, value, cellProperties) {
                const rowData = instance.getSourceDataAtRow(row);
                let gSum = 0;
                <?php foreach($all_comps as $rc) echo "gSum += parseFloat(rowData['comp_".$rc['comp_id']."'] || 0);"; ?>
                td.innerText = gSum.toFixed(2);
                return td;
            }
        }
    ]
});

function saveSpreadsheet() {
    document.getElementById('json_input').value = JSON.stringify(hot.getSourceData());
    document.getElementById('bulkForm').submit();
}
</script>

<style>
    .handsontable th { background-color: #f8fafc !important; color: #475569 !important; font-weight: bold !important; font-size: 11px !important; }
    .sub-total-col { background-color: #f0f9ff !important; color: #0369a1 !important; }
    .grand-total-col { background-color: #1e293b !important; color: #ffffff !important; }
    .handsontable td { vertical-align: middle !important; }
</style>
<?php endif; ?>

<?php require 'footer.php'; ?>