<?php
require_once 'db.php';
if (session_status() === PHP_SESSION_NONE) { session_start(); }

// 1. INPUTS
$year      = $_GET['year'] ?? date('Y');
$class_ids = $_GET['class_ids'] ?? [];
$term      = $_GET['term'] ?? null;
if (!is_array($class_ids) && $class_ids) { $class_ids = [$class_ids]; }

// 2. HELPER FUNCTIONS
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
    /* --- CSS VARIABLES --- */
    :root {
        --border-color: #000;
        --fail-bg: #ffebeb;
        --fail-txt: #b71c1c;
        --pass-bg: #e8f5e9;
        --pass-txt: #1b5e20;
        --sub-fail-bg: #f8d39bff; /* Blue for Subject Fail */
    }

    /* Print Break */
    .class-section { page-break-after: always; margin-bottom: 20px; display: block; }
    .class-section:last-child { page-break-after: auto; }

    /* TABLE STYLES */
    .auto-table {
        width: 100%;
        border-collapse: collapse;
        font-family: 'Arial Narrow', sans-serif;
        font-size: 11px;
        border: 1px solid #000;
        table-layout: auto; /* Columns take what they need */
    }

    .auto-table th, .auto-table td {
        border: 1px solid #000;
        padding: 2px 4px; /* Slight padding for readability */
        text-align: center;
        white-space: nowrap; /* Prevent wrapping */
    }

    /* First Column Specifics */
    .col-fixed-name {
        width: 150px !important;
        min-width: 150px !important;
        max-width: 150px !important;
        text-align: left !important;
        padding-left: 5px !important;
        white-space: normal !important; /* Allow name to wrap if very long, or hidden */
        overflow: hidden;
    }

    /* Header Styling */
    .th-subject { background: #333; color: white; }
    .th-part    { background: #eee; font-size: 10px; }
    .th-vertical { 
        writing-mode: vertical-rl; 
        transform: rotate(180deg); 
        height: 80px; 
        font-size: 10px;
    }

    /* Row Coloring */
    tr.row-fail { background-color: var(--fail-bg) !important; color: var(--fail-txt); }
    tr.row-aplus { background-color: var(--pass-bg) !important; color: var(--pass-txt); }
    
    /* Cell Highlighting */
    td.sub-fail { 
        background-color: var(--sub-fail-bg) !important; 
        color: #000; 
        border: 2px solid #01579b !important; 
        font-weight: bold;
    }
    
    td.sub-total { background-color: #f5f5f5; font-weight: bold; }

    /* Summary Table */
    .sum-table { width: 100%; font-size: 10px; border-collapse: collapse; margin-top: 10px; }
    .sum-table th, .sum-table td { border: 1px solid #ccc; padding: 4px; text-align: left; }
    .sum-list { font-size: 9px; color: #555; line-height: 1.2; }

    /* --- PRINT OPTIMIZATION --- */
    @media print {
        @page { size: A4 landscape; margin: 5mm; }
        body { margin: 0; padding: 0; font-size: 9pt; }
        .no-print, .navbar, footer { display: none !important; }
        .container-fluid, .card { width: 100% !important; margin: 0 !important; padding: 0 !important; border: none; box-shadow: none; }
        
        /* Force Colors */
        tr.row-fail { background-color: #ffebeb !important; -webkit-print-color-adjust: exact; }
        td.sub-fail { background-color: #d5edf8ff !important; -webkit-print-color-adjust: exact; }
        tr.row-aplus { background-color: #e8f5e9 !important; -webkit-print-color-adjust: exact; }
        
        thead { display: table-header-group; }
        tr { page-break-inside: avoid; }
    }
</style>

<div class="container-fluid p-0">

    <div class="card no-print bg-light border-bottom mb-2">
        <div class="card-body py-2">
            <form method="GET" class="row g-2 align-items-end">
                <div class="col-md-2">
                    <label class="small fw-bold">Year</label>
                    <select name="year" class="form-select form-select-sm" onchange="this.form.submit()">
                        <?php $years = $pdo->query("SELECT DISTINCT academic_year FROM classes ORDER BY academic_year DESC")->fetchAll(PDO::FETCH_COLUMN);
                        foreach ($years as $y) echo "<option value='$y' ".($year==$y?'selected':'').">$y</option>"; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="small fw-bold">Select Classes (Ctrl+Click)</label>
                    <select name="class_ids[]" class="form-select form-select-sm" multiple size="3">
                        <?php 
                        $classes = $pdo->prepare("SELECT id, class_name FROM classes WHERE academic_year = ?");
                        $classes->execute([$year]);
                        while($c = $classes->fetch()) {
                            $sel = in_array($c['id'], $class_ids) ? 'selected' : '';
                            echo "<option value='{$c['id']}' $sel>{$c['class_name']}</option>";
                        } ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="small fw-bold">Term</label>
                    <select name="term" class="form-select form-select-sm">
                        <option value="">-- Term --</option>
                        <?php $t_stmt = $pdo->query("SELECT DISTINCT exam_term FROM marks"); 
                        while($t = $t_stmt->fetch()) echo "<option value='{$t['exam_term']}' ".($term==$t['exam_term']?'selected':'').">{$t['exam_term']}</option>"; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <button class="btn btn-dark btn-sm w-100">Load</button>
                </div>
                <div class="col-md-2">
                    <button type="button" onclick="window.print()" class="btn btn-success btn-sm w-100">Print</button>
                </div>
            </form>
        </div>
    </div>

    <?php if (!empty($class_ids) && $term): 
        foreach ($class_ids as $current_class_id):
            
            // 1. Fetch Class & Structure
            $cls_stmt = $pdo->prepare("SELECT * FROM classes WHERE id = ?");
            $cls_stmt->execute([$current_class_id]);
            $cls = $cls_stmt->fetch();
            if(!$cls) continue;

            $struct_sql = "SELECT s.id as sub_id, s.subject_name, s.is_optional,
                                  p.id as part_id, p.part_name,
                                  c.id as comp_id, c.component_name, c.max_marks
                           FROM subjects s
                           JOIN subject_parts p ON s.id = p.subject_id
                           JOIN subject_components c ON p.id = c.part_id
                           WHERE s.class_id = ? ORDER BY s.id, p.id, c.id";
            $st_stmt = $pdo->prepare($struct_sql);
            $st_stmt->execute([$current_class_id]);
            $raw_struct = $st_stmt->fetchAll(PDO::FETCH_ASSOC);

            $structure = [];
            foreach($raw_struct as $row) {
                $structure[$row['sub_id']]['info'] = ['name'=>$row['subject_name'], 'is_opt'=>$row['is_optional']];
                $structure[$row['sub_id']]['parts'][$row['part_id']]['name'] = $row['part_name'];
                $structure[$row['sub_id']]['parts'][$row['part_id']]['comps'][] = [
                    'id' => $row['comp_id'], 'name' => $row['component_name'], 'max' => $row['max_marks']
                ];
            }

            // 2. Fetch Marks
            $m_stmt = $pdo->prepare("SELECT student_roll, component_id, marks_obtained FROM marks WHERE class_id = ? AND exam_term = ?");
            $m_stmt->execute([$current_class_id, $term]);
            $marks_data = [];
            while($r = $m_stmt->fetch()) { $marks_data[$r['student_roll']][$r['component_id']] = $r['marks_obtained']; }

            // 3. Pre-Calculate Logic (for Ranks)
            $rank_list = [];
            for ($r = $cls['start_roll']; $r <= $cls['end_roll']; $r++) {
                $gps=0; $cnt=0; $opt=0; $fail=false; $tm=0;
                foreach($structure as $sub) {
                    $sm=0; $smax=0;
                    foreach($sub['parts'] as $part) foreach($part['comps'] as $c) {
                        $sm += ($marks_data[$r][$c['id']] ?? 0);
                        $smax += $c['max'];
                    }
                    $gp = calculateGP($sm, $smax);
                    if($gp==0 && !$sub['info']['is_opt']) $fail=true;
                    if($sub['info']['is_opt']) $opt = max(0, $gp - 2); else { $gps+=$gp; $cnt++; }
                    $tm+=$sm;
                }
                $fgpa = $fail ? 0 : min(5.00, ($gps/max(1,$cnt)) + $opt);
                $rank_list[] = ['roll'=>$r, 'gpa'=>$fgpa, 'marks'=>$tm];
            }
            usort($rank_list, function($a, $b){ return ($b['gpa'] <=> $a['gpa']) ?: ($b['marks'] <=> $a['marks']); });
            $merit = []; foreach($rank_list as $i => $v) $merit[$v['roll']] = ($v['gpa']>0?($i+1):'-');

            $grade_counts = ['A+'=>0, 'A'=>0, 'A-'=>0, 'B'=>0, 'C'=>0, 'D'=>0, 'F'=>0];
            $grade_rolls  = ['A+'=>[], 'A'=>[], 'A-'=>[], 'B'=>[], 'C'=>[], 'D'=>[], 'F'=>[]];
    ?>

    <div class="class-section">
        <div class="text-center mb-2">
            <h4 class="m-0 fw-bold"><?= strtoupper($term) ?> RESULT</h4>
            <div>Class: <b><?= $cls['class_name'] ?></b> | Year: <?= $year ?></div>
        </div>

        <table class="auto-table">
            <thead>
                <tr>
                    <th rowspan="3" class="col-fixed-name text-center">ID & Name</th>
                    <?php foreach($structure as $sid => $sub): 
                        $cols = 0; foreach($sub['parts'] as $p) $cols += count($p['comps']); ?>
                        <th <?= $sub['info']['is_opt']?'style="background-color: #78fa78ff"':'' ?> colspan="<?= $cols + 1 ?>" class="th-subject"><?= $sub['info']['name'] ?> </th>
                    <?php endforeach; ?>
                    <th colspan="5" class="th-subject">SUMMARY</th>
                </tr>

                <tr>
                    <?php foreach($structure as $sid => $sub): 
                        foreach($sub['parts'] as $pid => $part): ?>
                            <th colspan="<?= count($part['comps']) ?>" class="th-part"><?= $part['name'] ?></th>
                        <?php endforeach; ?>
                        <th rowspan="2" class="th-vertical bg-light">Total & LG</th>
                    <?php endforeach; ?>
                    
                    <th rowspan="2" class="th-vertical">Grand Total</th>
                    <th rowspan="2" class="th-vertical">GPA</th>
                    <th rowspan="2" class="th-vertical">Final GPA</th>
                    <th rowspan="2" class="th-vertical">Grade</th>
                    <th rowspan="2" class="th-vertical">Rank</th>
                </tr>

                <tr>
                    <?php foreach($structure as $sid => $sub): 
                        foreach($sub['parts'] as $part):
                            foreach($part['comps'] as $c): ?>
                                <th class="th-vertical"><?= $c['name'] ?></th>
                            <?php endforeach;
                        endforeach;
                    endforeach; ?>
                </tr>
            </thead>
            <tbody>
                <?php 
                for($r=$cls['start_roll']; $r<=$cls['end_roll']; $r++): 
                    $gtm=0; $gps=0; $cnt=0; $ob=0; $f=false;
                    
                    $n_stmt = $pdo->prepare("SELECT student_name FROM students WHERE class_id = ? AND roll_no = ?");
                    $n_stmt->execute([$current_class_id, $r]);
                    $std = $n_stmt->fetch();
                    $s_name = $std['student_name'] ?? '-';
                    
                    // Calculation & HTML Generation
                    $cells = "";
                    foreach($structure as $sub) {
                        $sm=0; $smax=0;
                        foreach($sub['parts'] as $part) {
                            foreach($part['comps'] as $c) {
                                $mv = (int)($marks_data[$r][$c['id']] ?? 0);
                                $cells .= "<td>$mv</td>"; // No fixed width, auto layout
                                $sm += $mv; $smax += $c['max'];
                            }
                        }
                        $sgp = calculateGP($sm, $smax);
                        $slg = getGrade($sgp);
                        
                        $is_fail_sub = ($sgp==0 && !$sub['info']['is_opt']);
                        if($is_fail_sub) $f=true;

                        if($sub['info']['is_opt']) $ob = max(0, $sgp - 2); else { $gps+=$sgp; $cnt++; }
                        $gtm+=$sm;

                        // Subtotal Cell: Blue if fail, Standard otherwise. Side by side (No BR)
                        $sub_cls = $is_fail_sub ? 'sub-fail' : 'sub-total';
                        $cells .= "<td class='$sub_cls'>$sm $slg</td>";
                    }

                    $raw = $cnt > 0 ? $gps/$cnt : 0;
                    $fgpa = $f ? 0 : min(5.00, $raw + ($ob/max(1,$cnt)));
                    $flg = getGrade($fgpa);
                    
                    $row_cls = $f ? 'row-fail' : ($fgpa>=5 ? 'row-aplus' : '');
                    $grade_counts[$flg]++;
                    $grade_rolls[$flg][] = $r;
                ?>
                <tr class="<?= $row_cls ?>">
                    <td class="col-fixed-name"><b><?= $r ?>.</b> <?= $s_name ?></td>
                    <?= $cells ?>
                    <td class="fw-bold"><?= $gtm ?></td>
                    <td><?= number_format($raw, 2) ?></td>
                    <td class="fw-bold"><?= number_format($fgpa, 2) ?></td>
                    <td class="fw-bold"><?= $flg ?></td>
                    <td class="fw-bold"><?= $merit[$r] ?></td>
                </tr>
                <?php endfor; ?>
            </tbody>
        </table>
<style>
.grade-summary {
    display: flex;
    flex-wrap: wrap;
    justify-content: center;   /* center horizontally */
    gap: 6px;
    font-family: Arial, sans-serif;
    font-size: 12px;
    margin-top: 10px;
}

.grade-box {
    padding: 4px 8px;
    border-radius: 4px;
    font-weight: bold;
    color: #fff;
    margin: 2px;
    display: inline-block;
    white-space: nowrap;
}

/* Colors for each grade */
.grade-Aplus   { background-color: #27ae60; }  /* Dark green */
.grade-A       { background-color: #2ecc71; }  /* Green */
.grade-Aminus  { background-color: #2980b9; }  /* Dark blue */
.grade-Bplus   { background-color: #3498db; }  /* Blue */
.grade-B       { background-color: #5dade2; }  /* Light blue */
.grade-Cplus   { background-color: #f39c12; }  /* Amber */
.grade-C       { background-color: #f1c40f; color:#000; } /* Yellow */
.grade-Dplus   { background-color: #d35400; }  /* Burnt orange */
.grade-D       { background-color: #e67e22; }  /* Orange */
.grade-F       { background-color: #e74c3c; }  /* Red */

/* Optional: total badge */
.grade-total   { background-color: #9b59b6; }  /* Purple */
</style>

<div class="grade-summary">
<?php foreach($grade_counts as $g => $count): 
      if($count == 0) continue;   // skip any grade with 0 count
      // map A+ → grade-Aplus, A- → grade-Aminus, etc.
      $cls = "grade-box grade-".str_replace(['+','-'], ['plus','minus'], $g);
?>
    <span class="<?= $cls ?>">
        <?= $g ?>: <?= $count ?> → <?= implode(', ', $grade_rolls[$g]) ?>
    </span>
<?php endforeach; ?>

 
</div>
 
    </div>

    <?php endforeach; ?>
    <?php else: ?>
        <div class="alert alert-warning text-center m-5 no-print">Please select Year, Classes, and Term.</div>
    <?php endif; ?>
</div>

<?php require 'footer.php'; ?>