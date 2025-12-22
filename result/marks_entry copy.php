<?php
require_once 'db.php';
if (session_status() === PHP_SESSION_NONE) { session_start(); }

// 1. INPUT PARAMETERS
$year = $_GET['year'] ?? date('Y');
$class_id = $_GET['class_id'] ?? null;
$term = $_GET['term'] ?? 'Annual';
$selected_subjects = $_GET['subject_ids'] ?? []; 
$exam_terms = ["CT1", "CT2", "CT3", "CT4", "Half Yearly", "Annual", "Final", "Pre-Test", "Test", "Scholarship"];

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
    } catch (Exception $e) { $pdo->rollBack(); $error = $e->getMessage(); }
}

require 'header.php';
?>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/handsontable/dist/handsontable.full.min.css">
<script src="https://cdn.jsdelivr.net/npm/handsontable/dist/handsontable.full.min.js"></script>

<div class="card shadow-sm border-0 mb-4 bg-light no-print">
    <div class="card-body">
        <form method="GET" class="row g-3">
            <div class="col-md-2">
                <label class="small fw-bold">Academic Year</label>
                <input type="number" name="year" class="form-control" value="<?= $year ?>" onchange="this.form.submit()">
            </div>
            <div class="col-md-2">
                <label class="small fw-bold">Class</label>
                <select name="class_id" class="form-select" onchange="this.form.submit()">
                    <option value="">-- Select --</option>
                    <?php 
                    $cls_stmt = $pdo->prepare("SELECT * FROM classes WHERE academic_year = ?");
                    $cls_stmt->execute([$year]);
                    foreach($cls_stmt->fetchAll() as $c): ?>
                        <option value="<?= $c['id'] ?>" <?= $class_id == $c['id'] ? 'selected' : '' ?>><?= htmlspecialchars($c['class_name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-4">
                <label class="small fw-bold">Subjects</label>
                <select name="subject_ids[]" class="form-select" multiple size="3" onchange="this.form.submit()">
                    <?php if($class_id): 
                        $sub_stmt = $pdo->prepare("SELECT id, subject_name FROM subjects WHERE class_id = ?");
                        $sub_stmt->execute([$class_id]);
                        foreach($sub_stmt->fetchAll() as $s): ?>
                            <option value="<?= $s['id'] ?>" <?= in_array($s['id'], $selected_subjects) ? 'selected' : '' ?>><?= htmlspecialchars($s['subject_name']) ?></option>
                    <?php endforeach; endif; ?>
                </select>
            </div>
            <div class="col-md-2">
                <label class="small fw-bold">Exam Term</label>
                <select name="term" class="form-select">
                    <?php foreach($exam_terms as $et): ?>
                        <option value="<?= $et ?>" <?= $term == $et ? 'selected' : '' ?>><?= $et ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2 d-flex align-items-end gap-2">
                <button type="submit" class="btn btn-dark w-100">Load</button>
                <button type="button" onclick="saveSpreadsheet()" class="btn btn-primary w-100 <?= !$class_id ? 'disabled' : '' ?>">Save</button>
            </div>
        </form>
    </div>
</div>

<?php if ($class_id && !empty($selected_subjects)): 
    // Data Preparation Logic
    $in_query = implode(',', array_map('intval', $selected_subjects));
    $comp_sql = "SELECT c.id as comp_id, c.component_name, c.max_marks, p.part_name, s.subject_name, s.id as sub_id 
                 FROM subject_components c 
                 JOIN subject_parts p ON c.part_id = p.id 
                 JOIN subjects s ON p.subject_id = s.id 
                 WHERE s.id IN ($in_query) ORDER BY s.id, p.id, c.id";
    $raw_comps = $pdo->query($comp_sql)->fetchAll();

    $subjects_data = [];
    foreach($raw_comps as $row) {
        $subjects_data[$row['sub_id']]['name'] = $row['subject_name'];
        $subjects_data[$row['sub_id']]['comps'][] = $row;
    }

    $cls_meta = $pdo->prepare("SELECT start_roll, end_roll FROM classes WHERE id = ?");
    $cls_meta->execute([$class_id]); $meta = $cls_meta->fetch();

    $m_stmt = $pdo->prepare("SELECT student_roll, component_id, marks_obtained FROM marks WHERE class_id = ? AND exam_term = ?");
    $m_stmt->execute([$class_id, $term]);
    $marks_map = [];
    while($m = $m_stmt->fetch()){ $marks_map[$m['student_roll']][$m['component_id']] = $m['marks_obtained']; }

    $table_data = [];
    for($r = $meta['start_roll']; $r <= $meta['end_roll']; $r++) {
        $row = ['roll' => $r];
        foreach($raw_comps as $c) { $row['comp_'.$c['comp_id']] = $marks_map[$r][$c['comp_id']] ?? null; }
        $table_data[] = $row;
    }
?>

<div id="marksTable" class="border shadow-sm bg-white"></div>

<form id="saveForm" method="POST">
    <input type="hidden" name="class_id" value="<?= $class_id ?>">
    <input type="hidden" name="exam_term" value="<?= $term ?>">
    <input type="hidden" id="bulk_data_json" name="bulk_data_json">
</form>

<script>
const hot = new Handsontable(document.getElementById('marksTable'), {
    data: <?= json_encode($table_data) ?>,
    height: '600px',
    licenseKey: 'non-commercial-and-evaluation',
    rowHeaders: true,
    colHeaders: true,
    stretchH: 'all',
    enterMoves: {row: 1, col: 0},
    nestedHeaders: [
        ['Roll', <?php foreach($subjects_data as $sd) echo "{label: '".addslashes($sd['name'])."', colspan: ".(count($sd['comps'])+1)."},"; ?> 'Grand'],
        ['', <?php foreach($raw_comps as $c) echo "'".addslashes($c['part_name'])."',"; foreach($subjects_data as $sd) echo "'Total',"; ?> 'Total'],
        ['#', <?php foreach($raw_comps as $c) echo "'".addslashes($c['component_name'])." (".$c['max_marks'].")',"; foreach($subjects_data as $sd) echo "'Sum',"; ?> 'All']
    ],
    columns: [
        { data: 'roll', readOnly: true, className: 'htCenter htMiddle bg-light fw-bold' },
        <?php foreach($raw_comps as $c): ?>
        { data: 'comp_<?= $c['comp_id'] ?>', type: 'numeric', className: 'htCenter' },
        <?php endforeach; ?>
        <?php foreach($subjects_data as $sid => $sd): ?>
        {
            readOnly: true, className: 'htCenter fw-bold bg-info-subtle',
            renderer: function(instance, td, row, col, prop, value, cellProperties) {
                const rowData = instance.getSourceDataAtRow(row);
                let sum = 0;
                <?= json_encode(array_column($sd['comps'], 'comp_id')) ?>.forEach(id => { sum += parseFloat(rowData['comp_' + id] || 0); });
                td.innerText = sum > 0 ? sum.toFixed(2) : '-';
                return td;
            }
        },
        <?php endforeach; ?>
        {
            readOnly: true, className: 'htCenter fw-bold bg-dark text-white',
            renderer: function(instance, td, row, col, prop, value, cellProperties) {
                const rowData = instance.getSourceDataAtRow(row);
                let gSum = 0;
                <?php foreach($raw_comps as $rc) echo "gSum += parseFloat(rowData['comp_".$rc['comp_id']."'] || 0);"; ?>
                td.innerText = gSum > 0 ? gSum.toFixed(2) : '-';
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
<?php endif; ?>

<style>
    .handsontable .htCenter { text-align: center !important; vertical-align: middle !important; }
    .handsontable th { font-size: 11px; background: #f1f5f9 !important; border: 1px solid #cbd5e1 !important; color: #334155; }
    .bg-info-subtle { background-color: #e0f2fe !important; }
</style>