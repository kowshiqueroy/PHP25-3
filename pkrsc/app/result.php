<?php
require_once 'db.php';

// 1. FETCH SCHOOL SETTINGS
$settings = $pdo->query("SELECT * FROM settings LIMIT 1")->fetch(PDO::FETCH_ASSOC);
$school_name  = $settings['school_name'] ?? 'Your School Name';
$school_addr  = $settings['school_address'] ?? 'School Address';
$school_phone = $settings['school_phone'] ?? '';
$school_email = $settings['school_email'] ?? '';
$school_logo  = $settings['school_logo'] ?? 'uploads/logo.png';
$website = $settings['website'] ?? '';
$emis= $settings['emis'] ??'';  
$school_code= $settings['school_code'] ??'';
$established= $settings['established'] ??'';

// 2. DYNAMIC INPUTS
$year = $_GET['year'] ?? null;
$class_id = $_GET['class_id'] ?? null;
$term = $_GET['term'] ?? null;
$roll_input = $_GET['roll'] ?? null;

// 3. LOGIC FUNCTIONS
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

$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https://" : "http://";
$base_url = $protocol . $_SERVER['HTTP_HOST'] . explode('?', $_SERVER['REQUEST_URI'])[0];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Transcripts - <?= $school_name ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: #f0f2f5; font-family: 'Segoe UI', Tahoma, sans-serif; }
        .no-print-area { max-width: 1000px; margin: 20px auto; }
        .card-preview { 
            background: #fff; width: 210mm; height: 148.5mm; 
            padding: 8mm 10mm; position: relative; box-sizing: border-box;
            margin: 0 auto; border: 1px dashed #ddd; overflow: hidden;
        }
        @media print {
            body { background: none; margin: 0; padding: 0; }
            .no-print-area { display: none !important; }
            .card-preview { border: none; border-bottom: 1px dashed #ccc; page-break-inside: avoid; }
            .card-preview:nth-child(even) { page-break-after: always; border-bottom: none; }
        }
        .school-logo { width: 55px; height: 55px; object-fit: contain; }
        .qr-code { width: 55px; height: 55px; border: 1px solid #eee; padding: 2px; }
        .header-title { font-size: 18px; font-weight: 800; color: #1a202c; margin-bottom: 0; }
        .table-transcript { width: 100%; border-collapse: collapse; margin-top: 5px; }
        .table-transcript th { background: #2d3748 !important; color: #fff !important; font-size: 9px; padding: 4px; border: 1px solid #2d3748; }
        .table-transcript td { border: 1px solid #cbd5e0 !important; padding: 2px 5px; font-size: 9px; vertical-align: middle; }
        .row-fail { background-color: #fff5f5 !important; color: #c53030 !important; }
        .gpa-summary { margin-top: 8px; background: #edf2f7; border: 1px solid #cbd5e0; padding: 5px 0; font-size: 11px; }
        .signature-row { position: absolute; bottom: 10mm; left: 10mm; right: 10mm; display: flex; justify-content: space-between; }
        .sig-box { width: 140px; text-align: center; border-top: 1px solid #1a202c; padding-top: 3px; font-size: 9px; font-weight: bold; }
    </style>
</head>
<body>

<div class="no-print-area card p-3 shadow-sm mb-4">

    <div class="text-center">
        <img src="<?= $school_logo ?>" class="school-logo" style="width: 150px; height: 150px; object-fit: contain;">
        <h3 class="header-title"><?= $school_name ?></h3>
        <p class="text-muted"><?= $school_addr . '<br>Phone: ' . $school_phone . '<br>Email: ' . $school_email ?></p>
    </div>


    <form method="GET" class="row g-2 align-items-end">
        <div class="col-md-1">
            <button type="button" onclick="window.location.href='index.php'" class="btn btn-dark btn-sm w-100 fw-bold">Back</button>
        </div>
        <div class="col-md-2">
            <label class="small fw-bold">Academic Year</label>
            <select name="year" class="form-select form-select-sm" onchange="this.form.submit()">
                <option value="">-- Year --</option>
                <?php
                $years = $pdo->query("SELECT DISTINCT academic_year FROM classes ORDER BY academic_year DESC")->fetchAll(PDO::FETCH_COLUMN);
                foreach ($years as $y) echo "<option value='$y' ".($year==$y?'selected':'').">$y</option>";
                ?>
            </select>
        </div>
        <div class="col-md-2">
            <label class="small fw-bold">Class</label>
            <select name="class_id" class="form-select form-select-sm" onchange="this.form.submit()">
                <option value="">-- Class --</option>
                <?php if($year):
                    $classes = $pdo->prepare("SELECT id, class_name FROM classes WHERE academic_year = ?");
                    $classes->execute([$year]);
                    while($c = $classes->fetch()) echo "<option value='{$c['id']}' ".($class_id==$c['id']?'selected':'').">{$c['class_name']}</option>";
                endif; ?>
            </select>
        </div>
        <div class="col-md-2">
            <label class="small fw-bold">Exam Term</label>
            <select name="term" class="form-select form-select-sm">
                <option value="">-- Term --</option>
                <?php if($class_id):
                    $terms = $pdo->prepare("SELECT DISTINCT exam_term FROM marks WHERE class_id = ?");
                    $terms->execute([$class_id]);
                    while($t = $terms->fetch()) echo "<option value='{$t['exam_term']}' ".($term==$t['exam_term']?'selected':'').">{$t['exam_term']}</option>";
                endif; ?>
            </select>
        </div>
        <div class="col-md-3">
            <label class="small fw-bold">Roll</label>
            <input type="text" name="roll" class="form-control form-control-sm" value="<?= htmlspecialchars($roll_input) ?>" placeholder="1-20">
        </div>
        <div class="col-md-1">
            <button type="submit" class="btn btn-primary btn-sm w-100 fw-bold">Load</button>
        </div>
        <div class="col-md-1">
            <button type="button" onclick="window.print()" class="btn btn-dark btn-sm w-100 fw-bold">Print All</button>
        </div>
    </form>
</div>
<?php 
if ($class_id && $term && $roll_input):
    $rolls = [];
    if (strpos($roll_input, '-') !== false) {
        list($start, $end) = explode('-', $roll_input);
        for ($i = (int)$start; $i <= (int)$end; $i++) $rolls[] = $i;
    } else { $rolls[] = (int)$roll_input; }

    // Fetching structure
    $st_stmt = $pdo->prepare("SELECT s.subject_name, s.is_optional, p.part_name, c.id as comp_id, c.component_name, c.max_marks 
                             FROM subjects s 
                             JOIN subject_parts p ON s.id = p.subject_id 
                             JOIN subject_components c ON p.id = c.part_id 
                             WHERE s.class_id = ? ORDER BY s.id, p.id, c.id");
    $st_stmt->execute([$class_id]);
    $raw_structure = $st_stmt->fetchAll(PDO::FETCH_ASSOC);

    $structure = [];
    foreach($raw_structure as $row) {
        $structure[$row['subject_name']]['is_optional'] = $row['is_optional'];
        $structure[$row['subject_name']]['components'][] = $row;
    }
    $cls_name = $pdo->query("SELECT class_name FROM classes WHERE id = " . (int)$class_id)->fetchColumn();

    foreach ($rolls as $roll):
        $m_stmt = $pdo->prepare("SELECT component_id, marks_obtained FROM marks WHERE class_id = ? AND exam_term = ? AND student_roll = ?");
        $m_stmt->execute([$class_id, $term, $roll]);
        $marks = $m_stmt->fetchAll(PDO::FETCH_KEY_PAIR);
        if(!$marks) continue;

        $total_m = 0; $gp_sum = 0; $cnt = 0; $opt_b = 0; $fail = false;
        
        // Dynamic QR URL
        $qr_url = $base_url . "?" . http_build_query([
            'year' => $year,
            'class_id' => $class_id,
            'term' => $term,
            'roll' => $roll
        ]);
?>
    <div class="card-preview">
        <div class="d-flex justify-content-between align-items-center border-bottom pb-2 mb-2">
            <img src="<?= $school_logo ?>" class="school-logo" >
            <div class="text-center">
                <h1 class="header-title"><?= strtoupper($school_name) ?></h1>
                <p class="header-sub" style="font-size:14px; margin-top:-2px;"><?= $school_addr ?></p>
                <p class="" style="font-size:10px; margin-top:-20px; margin-bottom:-2px;">
                    <?= "EMIS: ".$emis." School Code: ". $school_code." Established: ". $established." <br>Phone: ". $school_phone." Email: ". $school_email." Website: ". $website ?></p>
                <div class="badge bg-dark mt-1" style="font-size: 10px;"><?= strtoupper($term) ?> EXAMINATION - <?= $year ?></div>
            </div>
            <img src="https://api.qrserver.com/v1/create-qr-code/?data=<?= urlencode($qr_url) ?>&size=100x100" class="qr-code">
        </div>

        <div class="row mb-2 gx-0 d-flex align-items-center" style="font-size: 11px; margin-top: -5px;">
            <div class="col-6">   <div class="d-flex align-items-center">
                <div class="col-2 text-left">Name:</div>
                <div class="col-10 text-left">
                    <input type="text" class="form-control form-control-sm" value="<?php
                        //student name
                        $stmt = $pdo->prepare("SELECT student_name FROM students WHERE class_id = ? AND roll_no = ?");
                        $stmt->execute([$class_id, $roll]);
                        $row = $stmt->fetch();
                        echo htmlspecialchars($row['student_name']);
                    ?>">
                </div>
            </div></div>
            <div class="col-6 text-end"><strong>Roll:</strong> <?= $roll ?> | <strong>Class:</strong> <?= $cls_name ?></div>
        </div>

        <div class="row">
            <div class="col-8">


<table class="table-transcript text-center">
            <thead>
                <tr>
                    <th width="28%">Subject</th>
                    <th width="48%">Marks</th>
                    <th width="8%">Total</th>
                    <th width="8%">G</th>
                    <th width="8%">GP</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($structure as $s_name => $data): 
                    $sm=0; $smx=0; $det=[];
                    foreach($data['components'] as $c) {
                        $v = $marks[$c['comp_id']] ?? 0;
                        $v= intval($v); 
                        $sm += $v; $smx += $c['max_marks'];
                        $det[] = "{$c['part_name']}({$c['component_name']}):$v";
                    }
                    $gp = calculateGP($sm, $smx);
                    $is_fail = ($gp == 0 && !$data['is_optional']);
                    if($is_fail) $fail = true;
                    if($data['is_optional']) $opt_b = ($gp > 2) ? ($gp - 2) : 0;
                    else { $gp_sum += $gp; $cnt++; }
                    $total_m += $sm;
                ?>
                <tr class="<?= $is_fail ? 'row-fail' : '' ?>">
                    <td class="text-start fw-bold "><?= $s_name ?> <?= $data['is_optional'] ? '[4th]' : '' ?></td>
                    <td class="text-start" style="font-size: 8px; line-height: 1;"><?= implode(', ', $det) ?></td>
                    <td class="fw-bold"><?= $sm ?></td>
                    <td class="fw-bold"><?= getGrade($gp) ?></td>
                    <td><?= number_format($gp, 2) ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

            </div>

             <?php 
                $raw_gpa = ($cnt > 0) ? ($gp_sum / $cnt) : 0;
                $final_gpa = $fail ? 0 : min(5.00, $raw_gpa + $opt_b/$cnt);
             
            ?>
            <div class="col-4">
        
        <table class="table-transcript text-center ">
            <thead>
                <tr>
                    <th width="50%">Marks</th>
                    <th width="25%">Grade</th>
                    <th width="25%">GPA</th>
                
                </tr>
            </thead>
            <tbody style="background-color: #f4f5f7ff;">
                 <tr>
                    <td >80-100</td>
                    <td >A+</td>
                    <td >5.00</td>
                </tr>
                    <tr>
                        <td >70-79</td>
                        <td >A</td>
                        <td >4.00</td>
                    </tr>
                    <tr>
                        <td >60-69</td>
                        <td >A-</td>
                        <td >3.50</td>
                    </tr>
                    <tr>
                        <td >50-59</td>
                        <td >B</td>
                        <td >2.50</td>
                    </tr>
                    <tr>
                        <td >40-49</td>
                        <td >C</td>
                        <td >1.50</td>
                    </tr>
                    <tr>
                        <td >0-39</td>
                        <td >F</td>
                        <td >0.00</td>
                    </tr>

                    <tr >    
                    <td colspan="3"  
                    style="<?= $fail?'background-color: #ee1010ff;':'background-color: #2ee766ff; ' ?>  color: #ffffffff;
                     font-weight: bold; font-size: 25px" class="fw-bold"><?= getGrade($final_gpa) ?></td>
                    </tr>
            </tbody>
        </table>
        
        </div>
        </div>
        

        <div class="gpa-summary row gx-0 text-center fw-bold <?= $fail?'bg-danger text-white':'' ?>">
           
            <div class="col-3">Total: <?= $total_m ?></div>
            <div class="col-3">Without 4th Subject: <?= number_format($raw_gpa, 2) ?></div>
            <div class="col-3">Final GPA: <?= number_format($final_gpa, 2) ?></span></div>
            <div class="col-3">Grade: <?= getGrade($final_gpa) ?></div>
        </div>

        <div class="signature-row">
            <div class="sig-box">Guardian's Signature</div>
            <div class="sig-box">Class Teacher's Signature</div>
            <div class="sig-box">Principal's Signature</div>
        </div>
    </div>
<?php endforeach; endif; ?>

</body>
</html>