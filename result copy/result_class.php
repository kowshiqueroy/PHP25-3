<?php
require_once 'db.php';
if (session_status() === PHP_SESSION_NONE) { session_start(); }

$year = $_GET['year'] ?? date('Y');
$class_id = $_GET['class_id'] ?? null;
$term = $_GET['term'] ?? 'Final Exam';

require 'header.php';

// Grading Function
function getGrade($mark, $max) {
    if ($max <= 0) return ['LG' => 'N/A', 'GP' => 0];
    $percent = ($mark / $max) * 100;
    if ($percent >= 80) return ['LG' => 'A+', 'GP' => 5.0];
    if ($percent >= 70) return ['LG' => 'A',  'GP' => 4.0];
    if ($percent >= 60) return ['LG' => 'A-', 'GP' => 3.5];
    if ($percent >= 50) return ['LG' => 'B',  'GP' => 3.0];
    if ($percent >= 40) return ['LG' => 'C',  'GP' => 2.0];
    if ($percent >= 33) return ['LG' => 'D',  'GP' => 1.0];
    return ['LG' => 'F', 'GP' => 0.0];
}
?>

<div class="no-print mb-4">
    <h2 class="fw-bold"><i class="fa-solid fa-ranking-star text-warning me-2"></i>Class Tabulation Sheet</h2>
    
    <div class="card shadow-sm border-0 bg-light">
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-2">
                    <label class="small fw-bold">Year</label>
                    <input type="number" name="year" class="form-control" value="<?= $year ?>">
                </div>
                <div class="col-md-3">
                    <label class="small fw-bold">Class</label>
                    <select name="class_id" class="form-select" required>
                        <option value="">-- Select Class --</option>
                        <?php 
                        $cls_stmt = $pdo->prepare("SELECT * FROM classes WHERE academic_year = ?");
                        $cls_stmt->execute([$year]);
                        foreach($cls_stmt->fetchAll() as $c): ?>
                            <option value="<?= $c['id'] ?>" <?= $class_id == $c['id'] ? 'selected' : '' ?>><?= $c['class_name'] ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="small fw-bold">Term</label>
                    <input type="text" name="term" class="form-control" value="<?= htmlspecialchars($term) ?>">
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100">Generate Result</button>
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="button" onclick="window.print()" class="btn btn-outline-dark w-100"><i class="fa-solid fa-print"></i> Print</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php if ($class_id): 
    // 1. Fetch all subjects and their components for this class
    $sub_sql = "SELECT s.id as sub_id, s.subject_name, s.overall_pass_mark, 
                       c.id as comp_id, c.max_marks 
                FROM subjects s
                JOIN subject_parts p ON s.id = p.subject_id
                JOIN subject_components c ON p.id = c.part_id
                WHERE s.class_id = ? ORDER BY s.id, c.id";
    $stmt = $pdo->prepare($sub_sql);
    $stmt->execute([$class_id]);
    $structure = $stmt->fetchAll(PDO::FETCH_GROUP|PDO::FETCH_ASSOC);

    // 2. Fetch class metadata
    $cls_meta = $pdo->prepare("SELECT * FROM classes WHERE id = ?");
    $cls_meta->execute([$class_id]);
    $class = $cls_meta->fetch();

    // 3. Fetch all marks for this class/term
    $m_stmt = $pdo->prepare("SELECT student_roll, component_id, marks_obtained FROM marks WHERE class_id = ? AND exam_term = ?");
    $m_stmt->execute([$class_id, $term]);
    $marks_data = [];
    while($row = $m_stmt->fetch()){
        $marks_data[$row['student_roll']][$row['component_id']] = $row['marks_obtained'];
    }
?>

<div class="card shadow-sm border-0">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-bordered table-sm text-center mb-0" style="font-size: 0.8rem;">
                <thead class="table-dark align-middle">
                    <tr>
                        <th rowspan="2">Roll</th>
                        <?php foreach($structure as $sub_id => $comps): ?>
                            <th colspan="2"><?= $comps[0]['subject_name'] ?></th>
                        <?php endforeach; ?>
                        <th rowspan="2">Total Marks</th>
                        <th rowspan="2">GPA</th>
                        <th rowspan="2">Result</th>
                    </tr>
                    <tr>
                        <?php foreach($structure as $sub_id => $comps): ?>
                            <th>Mark</th>
                            <th>GP</th>
                        <?php endforeach; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $merit_list = [];
                    for($r = $class['start_roll']; $r <= $class['end_roll']; $r++): 
                        $total_marks = 0;
                        $total_gp = 0;
                        $is_failed = false;
                        $subject_count = count($structure);
                    ?>
                    <tr>
                        <td class="fw-bold bg-light"><?= $r ?></td>
                        <?php foreach($structure as $sub_id => $comps): 
                            $sub_total = 0;
                            $sub_max = 0;
                            foreach($comps as $cp) {
                                $mark = $marks_data[$r][$cp['comp_id']] ?? 0;
                                $sub_total += $mark;
                                $sub_max += $cp['max_marks'];
                            }
                            $grade = getGrade($sub_total, $sub_max);
                            if ($grade['GP'] == 0) $is_failed = true;
                            
                            $total_marks += $sub_total;
                            $total_gp += $grade['GP'];
                        ?>
                            <td><?= $sub_total ?></td>
                            <td class="<?= $grade['GP'] == 0 ? 'text-danger fw-bold' : '' ?>"><?= number_format($grade['GP'], 2) ?></td>
                        <?php endforeach; ?>

                        <?php 
                        $final_gpa = $is_failed ? 0.00 : ($total_gp / $subject_count);
                        ?>
                        <td class="fw-bold"><?= $total_marks ?></td>
                        <td class="fw-bold <?= $is_failed ? 'text-danger' : 'text-success' ?>"><?= number_format($final_gpa, 2) ?></td>
                        <td>
                            <?php if($is_failed): ?>
                                <span class="badge bg-danger">F</span>
                            <?php else: ?>
                                <span class="badge bg-success">P</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endfor; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<style>
    @media print {
        .no-print { display: none !important; }
        .card { border: none !important; box-shadow: none !important; }
        .table { width: 100% !important; border-collapse: collapse !important; }
        th, td { border: 1px solid #000 !important; }
        .table-dark { background-color: #eee !important; color: #000 !important; }
    }
</style>

<?php endif; ?>

<?php require 'footer.php'; ?>