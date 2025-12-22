<?php
require_once 'db.php';
if (session_status() === PHP_SESSION_NONE) { session_start(); }

$year = $_GET['year'] ?? date('Y');
$class_id = $_GET['class_id'] ?? null;
$term = $_GET['term'] ?? 'Final Exam';
$search_roll = $_GET['roll'] ?? null;
$subject_filter = $_GET['subject_id'] ?? null; // Dynamic Subject Filter

// Policy Settings
$enforce_comp = isset($_GET['enforce_comp']);
$enforce_part = isset($_GET['enforce_part']);
$enforce_sub  = isset($_GET['enforce_sub']);

require 'header.php';

if ($class_id) {
    // 1. Fetch Class Metadata
    $cls_stmt = $pdo->prepare("SELECT * FROM classes WHERE id = ?");
    $cls_stmt->execute([$class_id]);
    $class_info = $cls_stmt->fetch();

    // 2. Dynamic Structure Query
    $sub_clause = $subject_filter ? "AND s.id = ?" : "";
    $struct_sql = "SELECT s.id as sub_id, s.subject_name, s.overall_pass_mark, 
                          p.id as part_id, p.part_name, p.part_pass_mark,
                          c.id as comp_id, c.component_name, c.max_marks, c.pass_mark as comp_pass
                   FROM subjects s
                   JOIN subject_parts p ON s.id = p.subject_id
                   JOIN subject_components c ON p.id = c.part_id
                   WHERE s.class_id = ? $sub_clause
                   ORDER BY s.id, p.id, c.id";
    
    $stmt = $pdo->prepare($struct_sql);
    $params = [$class_id];
    if($subject_filter) $params[] = $subject_filter;
    $stmt->execute($params);
    $structure = $stmt->fetchAll(PDO::FETCH_GROUP|PDO::FETCH_ASSOC);

    // 3. Marks Data Fetch
    $roll_clause = $search_roll ? "AND student_roll = ?" : "";
    $m_sql = "SELECT student_roll, component_id, marks_obtained FROM marks WHERE class_id = ? AND exam_term = ? $roll_clause";
    $m_stmt = $pdo->prepare($m_sql);
    $m_params = [$class_id, $term];
    if($search_roll) $m_params[] = $search_roll;
    $m_stmt->execute($m_params);
    
    $marks_map = [];
    while($row = $m_stmt->fetch()){ $marks_map[$row['student_roll']][$row['component_id']] = $row['marks_obtained']; }
}
?>

<div class="card shadow-sm border-0 mb-4 no-print bg-light">
    <div class="card-body">
        <form method="GET" class="row g-3">
            <input type="hidden" name="year" value="<?= $year ?>">
            <div class="col-md-3">
                <label class="small fw-bold">Class</label>
                <select name="class_id" class="form-select" onchange="this.form.submit()">
                    <option value="">-- Choose Class --</option>
                    <?php 
                    $classes = $pdo->query("SELECT * FROM classes ORDER BY academic_year DESC")->fetchAll();
                    foreach($classes as $c) echo "<option value='{$c['id']}' ".($class_id==$c['id']?'selected':'').">{$c['class_name']} ({$c['academic_year']})</option>";
                    ?>
                </select>
            </div>
            <div class="col-md-3">
                <label class="small fw-bold">View Mode (Subject)</label>
                <select name="subject_id" class="form-select" onchange="this.form.submit()">
                    <option value="">Full Class Result (All Subjects)</option>
                    <?php 
                    if($class_id){
                        $subs = $pdo->prepare("SELECT id, subject_name FROM subjects WHERE class_id = ?");
                        $subs->execute([$class_id]);
                        foreach($subs->fetchAll() as $s) echo "<option value='{$s['id']}' ".($subject_filter==$s['id']?'selected':'').">Subject: {$s['subject_name']}</option>";
                    }
                    ?>
                </select>
            </div>
            <div class="col-md-2">
                <label class="small fw-bold">Roll (Optional)</label>
                <input type="number" name="roll" class="form-control" placeholder="All" value="<?= $search_roll ?>">
            </div>
            <div class="col-md-2">
                <label class="small fw-bold">Term</label>
                <input type="text" name="term" class="form-control" value="<?= htmlspecialchars($term) ?>">
            </div>
            <div class="col-md-2 d-flex align-items-end">
                <button type="submit" class="btn btn-primary w-100">Process</button>
            </div>
            
            <div class="col-12 border-top pt-2 d-flex gap-4">
                <span class="small fw-bold text-muted">Enforce Pass Rules:</span>
                <div class="form-check form-switch"><input class="form-check-input" type="checkbox" name="enforce_comp" id="ec" <?= $enforce_comp?'checked':'' ?>><label class="small" for="ec">Components</label></div>
                <div class="form-check form-switch"><input class="form-check-input" type="checkbox" name="enforce_part" id="ep" <?= $enforce_part?'checked':'' ?>><label class="small" for="ep">Parts/Papers</label></div>
                <div class="form-check form-switch"><input class="form-check-input" type="checkbox" name="enforce_sub" id="es" <?= $enforce_sub?'checked':'' ?>><label class="small" for="es">Overall Subject</label></div>
            </div>
        </form>
    </div>
</div>

<?php if ($class_id && !empty($structure)): ?>
<div class="bg-white p-4 border rounded shadow-sm">
    <div class="text-center mb-4">
        <h2 class="fw-bold mb-0">TABULATION SHEET</h2>
        <div class="badge bg-dark px-3 py-2 mt-2 text-uppercase"><?= $term ?></div>
        <p class="mt-2 mb-0">Class: <strong><?= $class_info['class_name'] ?></strong> | Academic Year: <strong><?= $class_info['academic_year'] ?></strong></p>
        <?php if($subject_filter): ?>
            <p class="text-primary fw-bold mt-1">Subject Detailed Report: <?= $structure[array_key_first($structure)][0]['subject_name'] ?></p>
        <?php endif; ?>
    </div>

    <div class="table-responsive">
        <table class="table table-bordered table-sm text-center align-middle" style="font-size: 0.75rem;">
            <thead class="table-dark">
                <tr>
                    <th rowspan="3" style="width: 60px;">Roll</th>
                    <?php foreach($structure as $sub_id => $comps): ?>
                        <th colspan="<?= count($comps) + 1 ?>"><?= $comps[0]['subject_name'] ?></th>
                    <?php endforeach; ?>
                    <th rowspan="3">Result</th>
                </tr>
                <tr>
                    <?php foreach($structure as $sub_id => $comps): 
                        $current_part = "";
                        foreach($comps as $c):
                            if($current_part != $c['part_id']):
                                $count = 0; foreach($comps as $cx) if($cx['part_id']==$c['part_id']) $count++;
                                echo "<th colspan='$count' class='bg-secondary text-white small'>{$c['part_name']}</th>";
                                $current_part = $c['part_id'];
                            endif;
                        endforeach;
                        echo "<th rowspan='2' class='bg-light text-dark'>Total</th>";
                    endforeach; ?>
                </tr>
                <tr>
                    <?php foreach($structure as $sub_id => $comps): 
                        foreach($comps as $c) echo "<th class='fw-normal' style='font-size:0.65rem'>{$c['component_name']}</th>";
                    endforeach; ?>
                </tr>
            </thead>
            <tbody>
                <?php 
                $start = $search_roll ?: $class_info['start_roll'];
                $end = $search_roll ?: $class_info['end_roll'];

                for($r = $start; $r <= $end; $r++): 
                    $student_fail = false;
                ?>
                <tr>
                    <td class="fw-bold bg-light"><?= $r ?></td>
                    <?php foreach($structure as $sub_id => $comps): 
                        $sub_total = 0;
                        $sub_fail_flag = false;
                        $part_sums = [];

                        foreach($comps as $c):
                            $m = $marks_map[$r][$c['comp_id']] ?? 0;
                            $sub_total += $m;
                            $part_sums[$c['part_id']] = ($part_sums[$c['part_id']] ?? 0) + $m;
                            
                            $is_cf = ($m < $c['comp_pass']);
                            if($enforce_comp && $is_cf) $sub_fail_flag = true;
                        ?>
                            <td class="<?= ($enforce_comp && $is_cf) ? 'bg-danger text-white' : '' ?>"><?= $m ?></td>
                        <?php endforeach; 

                        // Part Level Logic
                        if($enforce_part){
                            foreach($comps as $c) if($part_sums[$c['part_id']] < $c['part_pass_mark']) $sub_fail_flag = true;
                        }
                        // Subject Overall Logic
                        if($enforce_sub && $sub_total < $comps[0]['overall_pass_mark']) $sub_fail_flag = true;
                        
                        if($sub_fail_flag) $student_fail = true;
                    ?>
                        <td class="fw-bold <?= $sub_fail_flag ? 'text-danger' : 'bg-light' ?>"><?= $sub_total ?></td>
                    <?php endforeach; ?>

                    <td class="fw-bold">
                        <span class="<?= $student_fail ? 'text-danger' : 'text-success' ?>">
                            <?= $student_fail ? 'FAILED' : 'PASSED' ?>
                        </span>
                    </td>
                </tr>
                <?php endfor; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>

<?php require 'footer.php'; ?>