<?php
require_once 'db.php';
if (session_status() === PHP_SESSION_NONE) { session_start(); }

// 1. INPUTS
$year      = $_GET['year'] ?? date('Y');
$class_ids = $_GET['class_ids'] ?? [];
$term      = $_GET['term'] ?? null;

// 2. LOGIC HELPERS
function getGrade($gp) {
    if ($gp >= 5.00) return 'A+';
    if ($gp >= 4.00) return 'A';
    if ($gp >= 3.50) return 'A-';
    if ($gp >= 3.00) return 'B';
    if ($gp >= 2.00) return 'C';
    if ($gp >= 1.00) return 'D';
    return 'F';
}

function calculateGP($mark, $max) {
    if ($max <= 0) return 0.00;
    $p = ($mark / $max) * 100;
    if ($p >= 80) return 5.00;
    if ($p >= 70) return 4.00;
    if ($p >= 60) return 3.50;
    if ($p >= 50) return 3.00;
    if ($p >= 40) return 2.00;
    if ($p >= 33) return 1.00;
    return 0.00;
}

require 'header.php';
?>

<style>
    /* UI & Print Optimization */
    body { background-color: #fff;}
    .tab-summary { width: 100%; border-collapse: collapse; margin-bottom: 2px; }
    .tab-summary th, .tab-summary td { 
        border: 1px solid #000; 
        text-align: center; 
        padding: 2px; 
        font-size: 10px; 
        line-height: 1;
    }
    .tab-summary th { background-color: #f2f2f2 !important; font-weight: bold;  font-size: 8px;  }
    .class-header { 
        background: #000; 
        color: #fff; 
        padding: 2px 2px; 
        font-weight: bold; 
        margin-top: 2px;
        font-size: 8px;
    }
    .fail-text { color: red; font-weight: bold; }
    .bg-gpa { background-color: #f9f9f9 !important; font-weight: bold; }
    .sub-title { font-size: 8.5px; word-wrap: break-word; max-width: 25px; }

    .tab-summary th:first-child { min-width: 120px;  }
     .tab-summary td:first-child { min-width: 120px; text-align: left; }

    .tab-summary tr:nth-child(2n) { background-color: #e6e9e6ff; }
    
    @media print {
        @page { size: A4 landscape; margin: 10px 10px; }
            body { background-color: #fff; margin-top: -50px;}
        .no-print { display: none; }
        .class-header { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
    }
</style>

<div class="container-fluid py-3">
    <div class="card no-print mb-4 shadow-sm border-0 bg-light">
        <div class="card-body">
            <form method="GET" class="row g-2 align-items-end">
                <div class="col-md-2">
                    <label class="small fw-bold">Academic Year</label>
                    <select name="year" class="form-select form-select-sm">
                        <?php
                        $years = $pdo->query("SELECT DISTINCT academic_year FROM classes ORDER BY academic_year DESC")->fetchAll(PDO::FETCH_COLUMN);
                        foreach ($years as $y) echo "<option value='$y' ".($year==$y?'selected':'').">$y</option>";
                        ?>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="small fw-bold">Select Classes (Ctrl+Click)</label>
                    <select name="class_ids[]" class="form-select form-select-sm" multiple style="height: 100px;">
                        <?php
                        $stmt = $pdo->prepare("SELECT id, class_name FROM classes WHERE academic_year = ?");
                        $stmt->execute([$year]);
                        $all_classes = $stmt->fetchAll();
                        
                        // Custom Sorting: Non-numeric (KG, Play) first, then numbers
                        usort($all_classes, function($a, $b) {
                            $isA_Num = is_numeric($a['class_name']); $isB_Num = is_numeric($b['class_name']);
                            if (!$isA_Num && $isB_Num) return -1;
                            if ($isA_Num && !$isB_Num) return 1;
                            return strnatcasecmp($a['class_name'], $b['class_name']);
                        });

                        foreach ($all_classes as $c) {
                            $selected = in_array($c['id'], ($class_ids ?? [])) ? 'selected' : '';
                            echo "<option value='{$c['id']}' $selected>{$c['class_name']}</option>";
                        }
                        ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="small fw-bold">Term Name</label>
                    <input type="text" name="term" class="form-control form-control-sm" value="<?= htmlspecialchars($term) ?>" placeholder="e.g. 1st Term" required>
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-primary btn-sm px-4">Generate Report</button>
                    <button type="button" onclick="window.print()" class="btn btn-dark btn-sm"><i class="fa fa-print"></i> Print</button>
                </div>
            </form>
        </div>
    </div>

    <?php if (!empty($class_ids) && $term): ?>
        <div class="text-center">
        <?php
        $stmt = $pdo->prepare("SELECT school_name, school_address, school_phone, school_email, school_logo, established, emis, school_code, website FROM settings LIMIT 1");
        $stmt->execute();
        $school = $stmt->fetch(PDO::FETCH_ASSOC);

       
        $school_logo = $school['school_logo'] ? $school['school_logo'] : 'uploads/logo.png';
        $school_name = htmlspecialchars($school['school_name']);
        $school_address = htmlspecialchars($school['school_address']);
        $school_phone = htmlspecialchars($school['school_phone']);
        $school_email = htmlspecialchars($school['school_email']);
        $established = htmlspecialchars($school['established']);
        $emis = htmlspecialchars($school['emis']);
        $website = htmlspecialchars($school['website']);

        ?>

            <div>
                <img src="<?= $school_logo ?>" alt="School Logo" style="max-height: 80px;">
            </div>
            <h3 class="mb-0"><?= $school_name ?></h3>
            <p class="mb-0"><?= $school_address ?> | Phone: <?= $school_phone ?> | Email: <?= $school_email ?> | Website: <?= $website ?></p>
            <p class="mb-0 text-uppercase"><b>Exam:</b> <?= htmlspecialchars($term) ?> | <b>Session:</b> <?= $year ?></p>
        </div>

        <?php
        // Fetch and Sort only selected classes
        $in  = str_repeat('?,', count($class_ids) - 1) . '?';
        $cls_stmt = $pdo->prepare("SELECT * FROM classes WHERE id IN ($in)");
        $cls_stmt->execute($class_ids);
        $selected_classes = $cls_stmt->fetchAll();
        
        usort($selected_classes, function($a, $b) {
            $isA_Num = is_numeric($a['class_name']); $isB_Num = is_numeric($b['class_name']);
            if (!$isA_Num && $isB_Num) return -1;
            if ($isA_Num && !$isB_Num) return 1;
            return strnatcasecmp($a['class_name'], $b['class_name']);
        });

        foreach ($selected_classes as $cls):
            $cid = $cls['id'];

            // 1. Get Subjects and Total Possible Marks for this class
            $sub_stmt = $pdo->prepare("SELECT s.id, s.subject_name, s.is_optional, 
                        (SELECT SUM(sc.max_marks) FROM subject_components sc 
                         JOIN subject_parts sp ON sc.part_id = sp.id 
                         WHERE sp.subject_id = s.id) as total_max 
                        FROM subjects s WHERE s.class_id = ?");
            $sub_stmt->execute([$cid]);
            $subjects = $sub_stmt->fetchAll(PDO::FETCH_ASSOC);

            // 2. Pre-aggregate marks (Roll -> Subject_id -> Marks)
            $mark_sql = "SELECT m.student_roll, sp.subject_id, SUM(m.marks_obtained) as marks_sum
                         FROM marks m
                         JOIN subject_components sc ON m.component_id = sc.id
                         JOIN subject_parts sp ON sc.part_id = sp.id
                         WHERE m.class_id = ? AND m.exam_term = ?
                         GROUP BY m.student_roll, sp.subject_id";
            $m_stmt = $pdo->prepare($mark_sql);
            $m_stmt->execute([$cid, $term]);
            
            $marks_map = [];
            while($row = $m_stmt->fetch()) {
                $marks_map[$row['student_roll']][$row['subject_id']] = $row['marks_sum'];
            }
        ?>

        <div class="class-header">CLASS: <?= strtoupper($cls['class_name']) ?></div>
        <table class="tab-summary">
            <thead>
                <tr>
                    <th width="70">Roll</th>
                    <?php foreach($subjects as $s): ?>
                        <th class="sub-title"><?= $s['subject_name'] ?></th>
                    <?php endforeach; ?>
                    <th width="55">Total</th>
                    <th width="40">GPA</th>
                    <th width="30">LG</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                for ($r = $cls['start_roll']; $r <= $cls['end_roll']; $r++): 
                    if (!isset($marks_map[$r])) continue; // Skip rolls with no data

                    $gtm = 0; $gps = 0; $cnt = 0; $ob = 0; $fail = false;
                    $row_cells = "";

                    foreach ($subjects as $s) {
                        $m_obtained = $marks_map[$r][$s['id']] ?? 0;
                        $gp = calculateGP($m_obtained, $s['total_max']);
                        $lg = getGrade($gp);

                        if ($gp == 0 && !$s['is_optional']) $fail = true;
                        
                        if ($s['is_optional']) {
                            $ob = ($gp > 2) ? ($gp - 2) : 0;
                        } else {
                            $gps += $gp;
                            $cnt++;
                        }
                        $gtm += $m_obtained;

                        $f_style = ($gp == 0 && !$s['is_optional']) ? 'fail-text' : '';
                        $m_obtained=intval($m_obtained);
                        $row_cells .= "<td class='$f_style'><strong>$lg</strong> <span >$m_obtained</span></td>";
                    }

                    $raw_gpa = $gps / max(1, $cnt);
                    $final_gpa = $fail ? 0.00 : min(5.00, $raw_gpa + $ob/$cnt);
                ?>
                <tr>
                    <td class="fw-bold">
                    <?php
                        //get the student name
                        $stmt = $pdo->prepare("SELECT student_name FROM students WHERE class_id = ? AND roll_no = ?");
                        $stmt->execute([$cid, $r]);
                        $row = $stmt->fetch();
                        echo $r." <small class='text-muted' style='font-size: 7px;'>".$row['student_name']."</small>";
                    ?>
                
                
                
                </td>
                    <?= $row_cells ?>
                    <td class="fw-bold"><?= $gtm ?></td>
                    <td class="bg-gpa <?= $fail ? 'fail-text' : '' ?>"><?= number_format($final_gpa, 2) ?></td>
                    <td class="fw-bold"><?= getGrade($final_gpa) ?></td>
                </tr>
                <?php endfor; ?>
            </tbody>
        </table>
        <?php endforeach; ?>

    <?php else: ?>
        <div class="alert alert-info text-center mt-5 no-print">
            <i class="fa fa-search me-2"></i> Select <b>Year</b>, <b>Classes</b>, and <b>Term</b> to generate the summary report.
        </div>
    <?php endif; ?>
</div>

<?php require 'footer.php'; ?>