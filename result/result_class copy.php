<?php
require_once 'db.php';
if (session_status() === PHP_SESSION_NONE) { session_start(); }

$class_id = $_GET['class_id'] ?? null;
$term     = $_GET['term'] ?? 'Final Exam';

// Logic Toggles
$enforce_sub  = isset($_GET['enforce_sub']) || !isset($_GET['class_id']);

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

if ($class_id):
    $cls_stmt = $pdo->prepare("SELECT * FROM classes WHERE id = ?");
    $cls_stmt->execute([$class_id]);
    $cls = $cls_stmt->fetch();

    // Fetch Full Structure
    $struct_sql = "SELECT s.id as sub_id, s.subject_name, s.is_optional,
                          c.id as comp_id, c.component_name, c.max_marks, c.pass_mark
                   FROM subjects s
                   JOIN subject_parts p ON s.id = p.subject_id
                   JOIN subject_components c ON p.id = c.part_id
                   WHERE s.class_id = ? ORDER BY s.id, p.id, c.id";
    $st_stmt = $pdo->prepare($struct_sql);
    $st_stmt->execute([$class_id]);
    $structure = $st_stmt->fetchAll(PDO::FETCH_GROUP|PDO::FETCH_ASSOC);

    // Fetch Marks
    $m_stmt = $pdo->prepare("SELECT student_roll, component_id, marks_obtained FROM marks WHERE class_id = ? AND exam_term = ?");
    $m_stmt->execute([$class_id, $term]);
    $marks_data = [];
    while($r = $m_stmt->fetch()) { $marks_data[$r['student_roll']][$r['component_id']] = $r['marks_obtained']; }

    // Pre-calculate Ranking
    $rank_list = [];
    for ($r = $cls['start_roll']; $r <= $cls['end_roll']; $r++) {
        $tm=0; $gps=0; $cnt=0; $opt=0; $fail=false;
        foreach($structure as $sid => $comps) {
            $sm=0; $smax=0;
            foreach($comps as $c) { $mv=$marks_data[$r][$c['comp_id']]??0; $sm+=$mv; $smax+=$c['max_marks']; }
            $gp = calculateGP($sm, $smax);
            if($gp==0 && !$comps[0]['is_optional']) $fail=true;
            if($comps[0]['is_optional']) $opt = ($gp > 2) ? ($gp - 2) : 0;
            else { $gps+=$gp; $cnt++; }
            $tm+=$sm;
        }
        $fgpa = $fail ? 0 : min(5.00, ($gps/max(1,$cnt)) + $opt);
        $rank_list[] = ['roll'=>$r, 'gpa'=>$fgpa, 'marks'=>$tm];
    }
    usort($rank_list, function($a, $b){ return ($b['gpa'] <=> $a['gpa']) ?: ($b['marks'] <=> $a['marks']); });
    $merit = []; foreach($rank_list as $i => $v) $merit[$v['roll']] = ($v['gpa']>0?($i+1):'-');
?>

<style>
    .tab-sheet { width: 100%; border-collapse: collapse; font-size: 9px; line-height: 1.1; }
    .tab-sheet th, .tab-sheet td { border: 1px solid #000; text-align: center; padding: 2px; }
    .v-text { writing-mode: vertical-rl; transform: rotate(180deg); white-space: nowrap; padding: 5px 2px; font-size: 8px; max-height: 80px; }
    .bg-sub-total { background-color: #eee; font-weight: bold; }
    .bg-opt { background-color: #f0faff; }
    .fail { color: red; font-weight: bold; }
    @media print { 
        @page { size: A4 landscape; margin: 5mm; } 
        .no-print { display: none; }
        .tab-sheet { font-size: 8px; }
    }
</style>

<div class="container-fluid py-3">
    <div class="card no-print mb-3 bg-light">
        <form method="GET" class="card-body row g-2">
            <input type="hidden" name="class_id" value="<?= $class_id ?>">
            <div class="col-md-2"><input type="text" name="term" class="form-control form-control-sm" value="<?= $term ?>"></div>
            <div class="col-md-2"><button type="submit" class="btn btn-primary btn-sm w-100">Refresh Data</button></div>
            <div class="col-md-2"><button type="button" onclick="window.print()" class="btn btn-dark btn-sm w-100">Print A4</button></div>
        </form>
    </div>

    <div class="printable-area">
        <div class="text-center mb-2">
            <h5 class="fw-bold m-0">TABULATION SHEET: <?= strtoupper($term) ?></h5>
            <p class="small m-0">Class: <?= $cls['class_name'] ?> | Session: <?= $cls['academic_year'] ?></p>
        </div>

        <table class="tab-sheet">
            <thead>
                <tr class="bg-dark text-white">
                    <th rowspan="3">Roll</th>
                    <?php foreach($structure as $sid => $comps): ?>
                        <th colspan="<?= count($comps) + 2 ?>" class="<?= $comps[0]['is_optional']?'bg-info':'' ?>">
                            <?= $comps[0]['subject_name'] ?>
                        </th>
                    <?php endforeach; ?>
                    <th colspan="5" class="bg-primary">Final Summary</th>
                </tr>
                <tr>
                    <?php foreach($structure as $sid => $comps): 
                        foreach($comps as $c) echo "<th class='v-text'>{$c['component_name']}</th>";
                        echo "<th rowspan='2' class='bg-sub-total'>Total</th>";
                        echo "<th rowspan='2' class='bg-sub-total'>LG</th>";
                    endforeach; ?>
                    <th rowspan="2">Grand</th>
                    <th rowspan="2">GPA<br>(Raw)</th>
                    <th rowspan="2">Final<br>GPA</th>
                    <th rowspan="2">LG</th>
                    <th rowspan="2">Rank</th>
                </tr>
            </thead>
            <tbody>
                <?php for($r=$cls['start_roll']; $r<=$cls['end_roll']; $r++): 
                    $gtm=0; $gps=0; $cnt=0; $ob=0; $f=false; ?>
                <tr>
                    <td class="fw-bold"><?= $r ?></td>
                    <?php foreach($structure as $sid => $comps): 
                        $sm=0; $smax=0; $is_opt=$comps[0]['is_optional'];
                        foreach($comps as $c) {
                            $mv=$marks_data[$r][$c['comp_id']]??0;
                            echo "<td class='".($is_opt?'bg-opt':'')."'>$mv</td>";
                            $sm+=$mv; $smax+=$c['max_marks'];
                        }
                        $sgp = calculateGP($sm, $smax);
                        $slg = getGrade($sgp);
                        if($sgp==0 && !$is_opt) $f=true;
                        if($is_opt) $ob = ($sgp > 2)?($sgp - 2):0;
                        else { $gps+=$sgp; $cnt++; }
                        $gtm+=$sm;
                    ?>
                        <td class="bg-sub-total <?= ($sgp==0 && !$is_opt)?'fail':'' ?>"><?= $sm ?></td>
                        <td class="bg-sub-total"><?= $slg ?></td>
                    <?php endforeach; ?>

                    <?php 
                        $raw = $gps/max(1,$cnt); 
                        $fgpa = $f ? 0 : min(5.00, $raw + $ob);
                    ?>
                    <td class="fw-bold"><?= $gtm ?></td>
                    <td><?= number_format($raw, 2) ?></td>
                    <td class="fw-bold <?= $f?'fail':'' ?>"><?= number_format($fgpa, 2) ?></td>
                    <td class="fw-bold"><?= getGrade($fgpa) ?></td>
                    <td class="bg-dark text-white fw-bold"><?= $merit[$r] ?></td>
                </tr>
                <?php endfor; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; require 'footer.php'; ?>