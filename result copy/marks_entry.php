<?php
require_once 'db.php';
if (session_status() === PHP_SESSION_NONE) { session_start(); }

// 1. INPUT PARAMETERS
$year = $_GET['year'] ?? null;
$class_id = $_GET['class_id'] ?? null;
$term = $_GET['term'] ?? 'Annual';
$selected_subjects = $_GET['subject_ids'] ?? []; 
$exam_terms = ["CT1", "CT2", "CT3", "CT4", "Half Yearly", "Annual", "Final", "Pre-Test", "Test", "Scholarship", "Model Test"];

// Determine if selection is locked (Table is loaded)
$is_loaded = ($class_id && !empty($selected_subjects));

// 2. SAVE & CONFIRM LOGIC
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['bulk_data_json'])) {
    try {
        $decoded_data = json_decode($_POST['bulk_data_json'], true);
        $c_id = $_POST['class_id'];
        $e_term = $_POST['exam_term'];
        $is_confirm = (isset($_POST['confirm_action']) && $_POST['confirm_action'] == "1") ? 1 : 0;

        $pdo->beginTransaction();
        foreach ($decoded_data as $row) {
            $roll = $row['roll'];
            foreach ($row as $key => $val) {
                if (strpos($key, 'comp_') === 0) {
                    $comp_id = str_replace('comp_', '', $key);
                    $mark = ($val === '' || $val === null) ? null : $val;
                    
                    $stmt = $pdo->prepare("INSERT INTO marks (class_id, student_roll, component_id, exam_term, marks_obtained, is_confirmed) 
                                           VALUES (?, ?, ?, ?, ?, ?) 
                                           ON DUPLICATE KEY UPDATE marks_obtained = VALUES(marks_obtained), is_confirmed = VALUES(is_confirmed)");
                    $stmt->execute([$c_id, $roll, $comp_id, $e_term, $mark, $is_confirm]);
                }
            }
        }
        $pdo->commit();
        header("Location: marks_entry.php?year=$year&class_id=$c_id&term=$e_term&success=1&" . http_build_query(['subject_ids' => $selected_subjects]));
        exit;
    } catch (Exception $e) { if($pdo->inTransaction()) $pdo->rollBack(); $error = $e->getMessage(); }
}

require 'header.php';
?>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/handsontable/dist/handsontable.full.min.css">
<script src="https://cdn.jsdelivr.net/npm/handsontable/dist/handsontable.full.min.js"></script>

<div class="px-3 mt-3 no-print">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="fw-bold text-dark mb-0"><i class="fa-solid fa-graduation-cap text-primary me-2"></i>Marks Entry Sheet</h4>
        <div class="d-flex gap-2">
            <?php if($is_loaded): ?>
                <a href="marks_entry.php" class="btn btn-outline-secondary fw-bold px-3">Change Class/Term</a>
                <button type="button" onclick="saveData(0)" class="btn btn-primary fw-bold px-3">Save Draft</button>
                <button type="button" onclick="saveData(1)" class="btn btn-danger fw-bold px-3">Confirm & Lock</button>
            <?php endif; ?>
        </div>
    </div>
</div>

<div class="card shadow-sm border-0 mb-4 bg-light mx-3 no-print">
    <div class="card-body py-3">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-md-2">
                <label class="small fw-bold">Academic Year</label>
                <select name="year" class="form-select form-select-sm" onchange="this.form.submit()" <?= $is_loaded ? 'disabled' : '' ?>>
                    <option value="">-- Select Year --</option>
                    <?php 
                    $year_stmt = $pdo->query("SELECT DISTINCT academic_year FROM classes ORDER BY academic_year DESC");
                    while($yr = $year_stmt->fetch()): ?>
                        <option value="<?= $yr['academic_year'] ?>" <?= $year == $yr['academic_year'] ? 'selected' : '' ?>><?= $yr['academic_year'] ?></option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div class="col-md-2">
                <label class="small fw-bold">Class</label>
                <select name="class_id" class="form-select form-select-sm" onchange="this.form.submit()" <?= $is_loaded ? 'disabled' : '' ?>>
                    <option value="">-- Select --</option>
                    <?php if($year):
                        $cls_stmt = $pdo->prepare("SELECT * FROM classes WHERE academic_year = ?");
                        $cls_stmt->execute([$year]);
                        foreach($cls_stmt->fetchAll() as $c): ?>
                            <option value="<?= $c['id'] ?>" <?= $class_id == $c['id'] ? 'selected' : '' ?>><?= htmlspecialchars($c['class_name']) ?></option>
                    <?php endforeach; endif; ?>
                </select>
            </div>
            <div class="col-md-2">
                <label class="small fw-bold">Term</label>
                <select name="term" class="form-select form-select-sm" <?= $is_loaded ? 'disabled' : '' ?>>
                    <?php foreach($exam_terms as $et): ?>
                        <option value="<?= $et ?>" <?= $term == $et ? 'selected' : '' ?>><?= $et ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-4">
                <label class="small fw-bold">Select Subjects</label>
                <select name="subject_ids[]" class="form-select form-select-sm" multiple size="1" <?= $is_loaded ? 'disabled' : '' ?>>
                    <?php if($class_id): 
                        $sub_stmt = $pdo->prepare("SELECT id, subject_name FROM subjects WHERE class_id = ?");
                        $sub_stmt->execute([$class_id]);
                        foreach($sub_stmt->fetchAll() as $s): ?>
                            <option value="<?= $s['id'] ?>" <?= in_array($s['id'], $selected_subjects) ? 'selected' : '' ?>><?= htmlspecialchars($s['subject_name']) ?></option>
                    <?php endforeach; endif; ?>
                </select>
            </div>
            <div class="col-md-2">
                <?php if(!$is_loaded): ?>
                    <button type="submit" class="btn btn-dark btn-sm w-100 fw-bold">Load Table</button>
                <?php else: ?>
                    <div class="badge bg-success w-100 py-2">LOADED <i class="fa-solid fa-lock ms-1"></i></div>
                <?php endif; ?>
            </div>

            <?php if($is_loaded): ?>
                <input type="hidden" name="year" value="<?= $year ?>">
                <input type="hidden" name="class_id" value="<?= $class_id ?>">
                <input type="hidden" name="term" value="<?= $term ?>">
                <?php foreach($selected_subjects as $sid): ?>
                    <input type="hidden" name="subject_ids[]" value="<?= $sid ?>">
                <?php endforeach; ?>
            <?php endif; ?>
        </form>
    </div>
</div>

<?php if ($is_loaded): 
    $in_query = implode(',', array_map('intval', $selected_subjects));
    $comp_sql = "SELECT c.id as comp_id, c.component_name, c.max_marks, c.pass_mark, p.part_name, s.subject_name, s.id as sub_id 
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

    $m_stmt = $pdo->prepare("SELECT student_roll, component_id, marks_obtained, is_confirmed FROM marks WHERE class_id = ? AND exam_term = ?");
    $m_stmt->execute([$class_id, $term]);
    $marks_db = []; $is_confirmed = false;
    while($m = $m_stmt->fetch()){ 
        $marks_db[$m['student_roll']][$m['component_id']] = $m['marks_obtained']; 
        if($m['is_confirmed'] == 1) $is_confirmed = true;
    }

    $cls_info = $pdo->prepare("SELECT start_roll, end_roll FROM classes WHERE id = ?");
    $cls_info->execute([$class_id]); $meta = $cls_info->fetch();

    $table_json = [];
    for($r = $meta['start_roll']; $r <= $meta['end_roll']; $r++) {
        $row = ['roll' => $r];
        foreach($all_comps as $c) { $row['comp_'.$c['comp_id']] = $marks_db[$r][$c['comp_id']] ?? null; }
        $table_json[] = $row;
    }
?>

<div id="htTable" class="mx-3 mb-5 border rounded shadow-sm bg-white overflow-hidden"></div>

<form id="saveForm" method="POST">
    <input type="hidden" name="class_id" value="<?= $class_id ?>">
    <input type="hidden" name="exam_term" value="<?= $term ?>">
    <input type="hidden" id="confirm_action" name="confirm_action" value="0">
    <input type="hidden" id="json_input" name="bulk_data_json">
</form>

<script>
const compsMeta = <?= json_encode($all_comps) ?>;
const isConfirmed = <?= $is_confirmed ? 'true' : 'false' ?>;

const hot = new Handsontable(document.getElementById('htTable'), {
    data: <?= json_encode($table_json) ?>,
    height: '60vh',
    licenseKey: 'non-commercial-and-evaluation',
    fixedColumnsLeft: 1,
    readOnly: isConfirmed,
    nestedHeaders: [
        ['Roll', <?php foreach($subjects_map as $s) echo "{label: '".addslashes($s['name'])."', colspan: ".(count($s['comps']))."},"; ?>],
        ['', <?php foreach($all_comps as $c) echo "'".addslashes($c['part_name'])."',"; foreach($subjects_map as $s) echo "'".addslashes($s['name'])." Total',"; ?> 'Grand Total'],
        ['#', <?php foreach($all_comps as $c) echo "'".addslashes($c['component_name'])." (".$c['max_marks'].")',"; foreach($subjects_map as $s) echo "'Sum',"; ?> 'All']
    ],
    columns: [
        { data: 'roll', readOnly: true, className: 'htCenter htMiddle bg-light fw-bold' },
        <?php foreach($all_comps as $c): ?>
        { 
            data: 'comp_<?= $c['comp_id'] ?>', 
            type: 'numeric', 
            className: 'htCenter',
            renderer: function(instance, td, row, col, prop, value, cellProperties) {
                Handsontable.renderers.NumericRenderer.apply(this, arguments);
                const max = <?= $c['max_marks'] ?>;
                const pass = <?= $c['pass_mark'] ?? 0 ?>;
                if (value !== null && value !== "") {
                    let v = parseFloat(value);
                    if (v > max) td.style.background = '#fff3cd'; 
                    else if (v < pass) td.style.background = '#f8d7da'; 
                    else td.style.background = '';
                }
                if (isConfirmed) { td.style.color = '#777'; td.style.background = '#f9f9f9'; }
            }
        },
        <?php endforeach; ?>
        <?php foreach($subjects_map as $sid => $s): ?>
        {
            readOnly: true, className: 'htCenter fw-bold sub-total-cell',
            renderer: function(instance, td, row, col, prop, value, cellProperties) {
                const rowData = instance.getSourceDataAtRow(row);
                let sum = 0;
                <?= json_encode(array_column($s['comps'], 'comp_id')) ?>.forEach(id => { sum += parseFloat(rowData['comp_' + id] || 0); });
                td.innerText = sum.toFixed(2);
                td.style.background = '#f1f5f9';
                return td;
            }
        },
        <?php endforeach; ?>
        {
            readOnly: true, className: 'htCenter fw-bold bg-dark text-white',
            renderer: function(instance, td, row, col, prop, value, cellProperties) {
                const rowData = instance.getSourceDataAtRow(row);
                let gSum = 0;
                compsMeta.forEach(c => { gSum += parseFloat(rowData['comp_' + c.comp_id] || 0); });
                td.innerText = gSum.toFixed(2);
                return td;
            }
        }
    ]
});

function saveData(confirmMode) {
    if(isConfirmed) return alert("Sheet is locked and cannot be modified.");
    if(confirmMode === 1 && !confirm("Lock marks forever? This cannot be undone.")) return;
    document.getElementById('confirm_action').value = confirmMode;
    document.getElementById('json_input').value = JSON.stringify(hot.getSourceData());
    document.getElementById('saveForm').submit();
}
</script>

<style>
    .handsontable th { background-color: #f8fafc !important; font-size: 11px !important; border: 1px solid #cbd5e1 !important; }
    .sub-total-cell { color: #2563eb !important; }
    <?php if($is_confirmed): ?> #htTable { opacity: 0.7; pointer-events: none; } <?php endif; ?>
</style>
<?php endif; ?>