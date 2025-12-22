<?php
require_once 'db.php';
if (session_status() === PHP_SESSION_NONE) { session_start(); }

// 1. INPUT PARAMETERS
$year = $_GET['year'] ?? date('Y');
$class_id = $_GET['class_id'] ?? null;
$term = $_GET['term'] ?? 'Final Exam';
$selected_subjects = $_GET['subject_ids'] ?? []; 

// 2. SAVE LOGIC (Handles JSON from Spreadsheet)
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
                        $stmt = $pdo->prepare("DELETE FROM marks WHERE class_id=? AND student_roll=? AND component_id=? AND exam_term=?");
                        $stmt->execute([$c_id, $roll, $comp_id, $e_term]);
                    } else {
                        $stmt = $pdo->prepare("INSERT INTO marks (class_id, student_roll, component_id, exam_term, marks_obtained) 
                                               VALUES (?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE marks_obtained = VALUES(marks_obtained)");
                        $stmt->execute([$c_id, $roll, $comp_id, $e_term, $val]);
                    }
                }
            }
        }
        $pdo->commit();
        
        $subj_query = http_build_query(['subject_ids' => $selected_subjects]);
        header("Location: marks_entry.php?year=$year&class_id=$c_id&term=$e_term&$subj_query&success=1");
        exit;
    } catch (Exception $e) {
        $pdo->rollBack();
        $error = $e->getMessage();
    }
}

require 'header.php';
?>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/handsontable/dist/handsontable.full.min.css">
<script src="https://cdn.jsdelivr.net/npm/handsontable/dist/handsontable.full.min.js"></script>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="fw-bold"><i class="fa-solid fa-file-invoice text-success me-2"></i>Smart Marks Manager</h2>
    <?php if($class_id && !empty($selected_subjects)): ?>
        <button type="button" onclick="saveSpreadsheet()" class="btn btn-primary shadow px-4">
            <i class="fa-solid fa-save me-2"></i>Save All Changes
        </button>
    <?php endif; ?>
</div>

<?php if(isset($_GET['success'])): ?>
    <div class="alert alert-success border-0 shadow-sm mb-4">Marks updated successfully!</div>
<?php endif; ?>

<div class="card shadow-sm border-0 mb-4 bg-light">
    <div class="card-body">
        <form method="GET" class="row g-3">
            <div class="col-md-2">
                <label class="small fw-bold">Academic Year</label>
                <input type="number" name="year" class="form-control" value="<?= $year ?>" onchange="this.form.submit()">
            </div>
            <div class="col-md-2">
                <label class="small fw-bold">Class</label>
                <select name="class_id" class="form-select" onchange="this.form.submit()">
                    <option value="">-- Select Class --</option>
                    <?php 
                    $cls_stmt = $pdo->prepare("SELECT * FROM classes WHERE academic_year = ?");
                    $cls_stmt->execute([$year]);
                    foreach($cls_stmt->fetchAll() as $c): ?>
                        <option value="<?= $c['id'] ?>" <?= $class_id == $c['id'] ? 'selected' : '' ?>><?= htmlspecialchars($c['class_name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-4">
                <label class="small fw-bold">Subjects (Ctrl + Click to select multiple)</label>
                <select name="subject_ids[]" class="form-select" multiple size="3" onchange="this.form.submit()" <?= !$class_id ? 'disabled' : '' ?>>
                    <?php 
                    if($class_id){
                        $sub_stmt = $pdo->prepare("SELECT id, subject_name FROM subjects WHERE class_id = ?");
                        $sub_stmt->execute([$class_id]);
                        foreach($sub_stmt->fetchAll() as $s): ?>
                            <option value="<?= $s['id'] ?>" <?= in_array($s['id'], $selected_subjects) ? 'selected' : '' ?>><?= htmlspecialchars($s['subject_name']) ?></option>
                        <?php endforeach; 
                    } ?>
                </select>
            </div>
            <div class="col-md-2">
                <label class="small fw-bold">Exam Term</label>
                <input type="text" name="term" class="form-control" value="<?= htmlspecialchars($term) ?>">
            </div>
            <div class="col-md-2 d-flex align-items-end">
                <button type="submit" class="btn btn-dark w-100">Refresh Sheet</button>
            </div>
        </form>
    </div>
</div>

<?php if ($class_id && !empty($selected_subjects)): 
    // Fetch Structure
    $in_query = implode(',', array_map('intval', $selected_subjects));
    $comp_sql = "SELECT c.id, c.component_name, c.max_marks, p.part_name, s.subject_name, s.id as sub_id 
                 FROM subject_components c 
                 JOIN subject_parts p ON c.part_id = p.id 
                 JOIN subjects s ON p.subject_id = s.id 
                 WHERE s.id IN ($in_query) ORDER BY s.id, p.id, c.id";
    $comps = $pdo->query($comp_sql)->fetchAll();

    // Group subjects for totals
    $sub_groups = [];
    foreach($comps as $c) {
        $sub_groups[$c['sub_id']]['name'] = $c['subject_name'];
        $sub_groups[$c['sub_id']]['comp_ids'][] = $c['id'];
    }

    $cls_data = $pdo->prepare("SELECT start_roll, end_roll FROM classes WHERE id = ?");
    $cls_data->execute([$class_id]); $meta = $cls_data->fetch();

    $m_stmt = $pdo->prepare("SELECT student_roll, component_id, marks_obtained FROM marks WHERE class_id = ? AND exam_term = ?");
    $m_stmt->execute([$class_id, $term]);
    $marks_map = [];
    while($row = $m_stmt->fetch()){ $marks_map[$row['student_roll']][$row['component_id']] = $row['marks_obtained']; }

    $table_data = [];
    for($r = $meta['start_roll']; $r <= $meta['end_roll']; $r++) {
        $row = ['roll' => $r];
        foreach($comps as $cp) { $row['comp_'.$cp['id']] = $marks_map[$r][$cp['id']] ?? null; }
        $table_data[] = $row;
    }
?>

<div class="card shadow-sm border-0">
    <div id="marksTable"></div>
</div>

<form id="saveForm" method="POST">
    <input type="hidden" name="class_id" value="<?= $class_id ?>">
    <input type="hidden" name="exam_term" value="<?= $term ?>">
    <?php foreach($selected_subjects as $sid): ?>
        <input type="hidden" name="subject_ids[]" value="<?= $sid ?>">
    <?php endforeach; ?>
    <input type="hidden" id="bulk_data_json" name="bulk_data_json">
</form>

<script>
const comps = <?= json_encode($comps) ?>;
const subGroups = <?= json_encode($sub_groups) ?>;

const hot = new Handsontable(document.getElementById('marksTable'), {
    data: <?= json_encode($table_data) ?>,
    height: '600px',
    licenseKey: 'non-commercial-and-evaluation',
    rowHeaders: true,
    manualColumnResize: true,
    enterMoves: {row: 1, col: 0},
    nestedHeaders: [
        ['Roll', <?php foreach($sub_groups as $sg) { echo "{label: '".addslashes($sg['name'])."', colspan: ".(count($sg['comp_ids'])+1)."},"; } ?> 'Final'],
        ['', <?php foreach($comps as $c) echo "'".addslashes($c['part_name'])."',"; foreach($sub_groups as $sg) echo "'Total',"; ?> 'Grand Total'],
        ['#', <?php foreach($comps as $c) echo "'".addslashes($c['component_name'])." (".$c['max_marks'].")',"; foreach($sub_groups as $sg) echo "'Sum',"; ?> 'All']
    ],
    columns: [
        { data: 'roll', readOnly: true, className: 'htCenter htMiddle bg-light fw-bold' },
        <?php foreach($comps as $c): ?>
        { 
            data: 'comp_<?= $c['id'] ?>', 
            type: 'numeric', 
            validator: function(val, cb) { cb(val === null || val <= <?= $c['max_marks'] ?>); },
            className: 'htCenter'
        },
        <?php endforeach; ?>
        <?php foreach($sub_groups as $sid => $sg): ?>
        {
            readOnly: true, className: 'htCenter fw-bold',
            renderer: function(instance, td, row, col, prop, value, cellProperties) {
                const rowData = instance.getSourceDataAtRow(row);
                let sum = 0;
                <?= json_encode($sg['comp_ids']) ?>.forEach(id => { sum += parseFloat(rowData['comp_' + id] || 0); });
                td.innerText = sum.toFixed(2);
                td.style.background = '#e7f3ff';
                return td;
            }
        },
        <?php endforeach; ?>
        {
            readOnly: true, className: 'htCenter fw-bold',
            renderer: function(instance, td, row, col, prop, value, cellProperties) {
                const rowData = instance.getSourceDataAtRow(row);
                let gSum = 0;
                comps.forEach(c => { gSum += parseFloat(rowData['comp_' + c.id] || 0); });
                td.innerText = gSum.toFixed(2);
                td.style.background = '#333'; td.style.color = '#fff';
                return td;
            }
        }
    ]
});

function saveSpreadsheet() {
    document.getElementById('bulk_data_json').value = JSON.stringify(hot.getSourceData());
    document.getElementById('saveForm').submit();
}
</script>

<style>
    .handsontable .htInvalid { background-color: #ffcccc !important; }
    .handsontable th { font-size: 11px; font-weight: bold; background: #f8f9fa !important; border-bottom: 2px solid #ddd !important; }
    .handsontable td { border-right: 1px solid #eee !important; border-bottom: 1px solid #eee !important; }
</style>
<?php endif; ?>

<?php require 'footer.php'; ?>