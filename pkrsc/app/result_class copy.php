<?php
require_once 'db.php';
if (session_status() === PHP_SESSION_NONE) { session_start(); }

// 1. GET INPUTS
$year     = $_GET['year'] ?? date('Y');
$class_id = $_GET['class_id'] ?? null;
$term     = $_GET['term'] ?? null;

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






<div class="container-fluid py-3">
    <div class="card no-print mb-4 shadow-sm border-0 bg-light">
        <div class="card-body">
            <form method="GET" id="filterForm" class="row g-3 align-items-end">
                <div class="col-md-2">
                    <label class="small fw-bold">Academic Year</label>
                    <select name="year" class="form-select form-select-sm" onchange="this.form.submit()">
                        <?php
                        $years = $pdo->query("SELECT DISTINCT academic_year FROM classes ORDER BY academic_year DESC")->fetchAll(PDO::FETCH_COLUMN);
                        foreach ($years as $y) echo "<option value='$y' ".($year==$y?'selected':'').">$y</option>";
                        ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="small fw-bold">Select Class</label>
                    <select name="class_id" class="form-select form-select-sm" onchange="this.form.submit()">
                        <option value="">-- Choose Class --</option>
                        <?php
                        $classes = $pdo->prepare("SELECT id, class_name FROM classes WHERE academic_year = ?");
                        $classes->execute([$year]);
                        while($c = $classes->fetch()) {
                            echo "<option value='{$c['id']}' ".($class_id==$c['id']?'selected':'').">{$c['class_name']}</option>";
                        }
                        ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="small fw-bold">Exam Term</label>
                    <select name="term" class="form-select form-select-sm" onchange="this.form.submit()">
                        <option value="">-- Select Term --</option>
                        <?php
                        if ($class_id) {
                            $terms = $pdo->prepare("SELECT DISTINCT exam_term FROM marks WHERE class_id = ?");
                            $terms->execute([$class_id]);
                            while($t = $terms->fetch()) {
                                echo "<option value='{$t['exam_term']}' ".($term==$t['exam_term']?'selected':'').">{$t['exam_term']}</option>";
                            }
                        }
                        ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="button" onclick="window.print()" class="btn btn-dark btn-sm w-100">
                        <i class="fa fa-print"></i> Print A4
                    </button>
                </div>
            </form>
        </div>
    </div>

    <?php 
    if ($class_id && $term): 
        // 3. DATA FETCHING
        $cls_stmt = $pdo->prepare("SELECT * FROM classes WHERE id = ?");
        $cls_stmt->execute([$class_id]);
        $cls = $cls_stmt->fetch();

        $struct_sql = "SELECT s.id as sub_id, s.subject_name, s.is_optional,
                              c.id as comp_id, c.component_name, c.max_marks
                       FROM subjects s
                       JOIN subject_parts p ON s.id = p.subject_id
                       JOIN subject_components c ON p.id = c.part_id
                       WHERE s.class_id = ? ORDER BY s.id, p.id, c.id";
        $st_stmt = $pdo->prepare($struct_sql);
        $st_stmt->execute([$class_id]);
        $structure = $st_stmt->fetchAll(PDO::FETCH_GROUP|PDO::FETCH_ASSOC);

        $m_stmt = $pdo->prepare("SELECT student_roll, component_id, marks_obtained FROM marks WHERE class_id = ? AND exam_term = ?");
        $m_stmt->execute([$class_id, $term]);
        $marks_data = [];
        while($r = $m_stmt->fetch()) { $marks_data[$r['student_roll']][$r['component_id']] = $r['marks_obtained']; }

        // 4. RANKING PRE-CALC
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
    .tab-sheet { 
        width: 100%; 
        border-collapse: collapse; 
        font-family: 'Arial Narrow', sans-serif;
        table-layout: fixed; 
    }

    /* Header styling */
    .tab-sheet th { 
        border: 1px solid #000; 
        text-align: center; 
        padding: 2px 0;
        font-size: 7px;
        background: linear-gradient(180deg, #ffffffff, #ffffffff); /* Blue gradient */
        color: #020202ff;
    }

    /* Cell styling */
    .tab-sheet td { 
        border: 1px solid #000; 
        text-align: center; 
        padding: 2px 0;
        font-size: 7.5px;
    }

    /* Narrow for integer marks */
    .col-mark { 
        width: 14px; 
        background-color: #ffeaa7; /* Soft yellow */
    }

    /* Grade Letters */
    .col-lg { 
        width: 16px; 
        font-weight: bold;
        background-color: #55efc4; /* Mint green */
    }

    /* GPA/CGPA summary */
    .col-summary { 
        width: 32px; 
        font-weight: bold;
        background-color: #fab1a0; /* Coral */
        color: #000;
    }

    /* Vertical text */
    .v-text { 
        writing-mode: vertical-rl; 
        transform: rotate(180deg); 
        white-space: nowrap; 
        font-size: 8px; 
        font-weight: bold;
        height: 40px;
        color: #000000ff;
    }

    /* Component text */
    .comp-text {
        font-size: 6px !important;
        height: 22px;
        background-color: #dfe6e9; /* Light gray-blue */
    }

    /* First column wider */
    .tab-sheet th:first-child { min-width: 100px; background-color: #ffffffff; color: #000000ff; !important;}
    .tab-sheet td:first-child { min-width: 100px; text-align: left; background-color: #ffffffff; color: #000000ff; padding-left: 2px;}

    /* Alternate row colors */
    .tab-sheet tr:nth-child(2n) { background-color: #f1f2f6; }
    .tab-sheet tr:nth-child(2n+1) { background-color: #ffffff; }

    /* Hover effect */
    .tab-sheet tr:hover { background-color: #ffeaa7; transition: 0.3s; }

    /* Print adjustments */
    @media print {
        @page { size: A4 landscape; margin: 3mm; }
        .tab-sheet { font-size: 7px; }
        .col-summary { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        .no-mg { margin: 0 !important;}
    }
</style>
<?php 
//if isset get change the Title of the page
if (isset($_GET['year']) && isset($_GET['class_id']) && isset($_GET['term'])) {
    echo "<script>document.title = 'Tabulation Sheet - " . strtoupper($term) . "-" . $cls['class_name'] . "-" . $year . "';</script>";
}


?>
    <div class="printable-area">
        <div class="text-center mb-3 no-mg">
          
            <p class="m-0"><b> <?= strtoupper($term) ?> TABULATION SHEET Class:</b> <?= $cls['class_name'] ?> | <b>Session:</b> <?= $year ?></p>
        </div>

        <table class="tab-sheet">
            <thead>
                <tr class="bg-dark text-white">
                    <th rowspan="3" >Roll</th>
                    <?php foreach($structure as $sid => $comps): ?>
                        <th colspan="<?= count($comps) + 1 ?>" class="<?= $comps[0]['is_optional']?'bg-info':'' ?>">
                            <?= $comps[0]['subject_name'] ?>
                        </th>
                    <?php endforeach; ?>
                    <th colspan="5" class="summary-header">FINAL SUMMARY</th>
                </tr>
                <tr>
                    <?php foreach($structure as $sid => $comps): 
                        foreach($comps as $c) echo "<th class='v-text'>{$c['component_name']}</th>";
                        echo "<th rowspan='2' class='bg-sub-total'>Total LG</th>";
                    endforeach; ?>
                    <th rowspan="2">Grand<br>Marks</th>
                    <th rowspan="2">GPA<br>(No 4th)</th>
                    <th rowspan="2">Final<br>GPA</th>
                    <th rowspan="2">LG</th>
                    <th rowspan="2">Rank</th>
                </tr>
            </thead>
            <tbody>
                <?php for($r=$cls['start_roll']; $r<=$cls['end_roll']; $r++): 
                    $gtm=0; $gps=0; $cnt=0; $ob=0; $f=false; ?>
                <tr  >
                    <td class="fw-bold"><?= $r ?> 
                        <?php
                            //get the student name
                            $stmt = $pdo->prepare("SELECT student_name FROM students WHERE class_id = ? AND roll_no = ?");
                            $stmt->execute([$class_id, $r]);
                            $data = $stmt->fetch();
                            echo $data['student_name'];
                        ?>
                        
                    
                    
                    </td>
                    <?php foreach($structure as $sid => $comps): 
                        $sm=0; $smax=0; $is_opt=$comps[0]['is_optional'];
                        foreach($comps as $c) {
                            $mv=$marks_data[$r][$c['comp_id']]??0;
                            $mv = (int) $mv;
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
                        <td <?= ($f?'style="background-color: #f8a6a6ff;"':'') ?> class="bg-sub-total <?= ($sgp==0 && !$is_opt)?'fail':'' ?>"><?= $sm ?> 
                        <?= $slg ?></td>
                    <?php endforeach; ?>

                    <?php 
                        $raw = $gps/max(1,$cnt); 
                        $fgpa = $f ? 0 : min(5.00, $raw + ($ob/$cnt));
                    ?>
                    <td class=""><?= $gtm ?></td>
                    <td><?= number_format($raw, 2) ?></td>
                    <td class="fw-bold <?= $f?'fail':'' ?>"><?= number_format($fgpa, 2) ?></td>
                    <td class="fw-bold"><?= getGrade($fgpa) ?></td>
                    <td class=" fw-bold"><?= $merit[$r] ?></td>
                </tr>
                <?php endfor; ?>
            </tbody>
        </table>
    </div>

    <?php else: ?>
        <div class="alert alert-info text-center mt-5">
            <i class="fa fa-info-circle"></i> Please select <b>Year</b>, <b>Class</b>, and <b>Exam Term</b> to view results.
        </div>
    <?php endif; ?>
</div>

<?php require 'footer.php'; ?>