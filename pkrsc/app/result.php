<?php
require_once 'db.php';

// --- 1. FETCH SCHOOL SETTINGS ---
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

// --- 2. INPUTS ---
$year = $_GET['year'] ?? null;
$class_id = $_GET['class_id'] ?? null;
$term = $_GET['term'] ?? null;
$roll_input = $_GET['roll'] ?? null;

// --- 3. HELPER FUNCTIONS ---
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

// --- 4. DATA PRE-PROCESSING ---
$results = []; 

// Only run query if we have a valid roll number input (not just empty string)
if ($class_id && $term && !empty($roll_input)) {
    $rolls = [];
    if (strpos($roll_input, '-') !== false) {
        list($start, $end) = explode('-', $roll_input);
        for ($i = (int)$start; $i <= (int)$end; $i++) $rolls[] = $i;
    } else { $rolls[] = (int)$roll_input; }

    // Fetch Subject Structure
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

    foreach ($rolls as $roll) {
        // Fetch Student Name
        $s_stmt = $pdo->prepare("SELECT student_name FROM students WHERE class_id = ? AND roll_no = ?");
        $s_stmt->execute([$class_id, $roll]);
        $student = $s_stmt->fetch();
        if(!$student) continue;

        // Fetch Marks
        $m_stmt = $pdo->prepare("SELECT component_id, marks_obtained FROM marks WHERE class_id = ? AND exam_term = ? AND student_roll = ?");
        $m_stmt->execute([$class_id, $term, $roll]);
        $marks = $m_stmt->fetchAll(PDO::FETCH_KEY_PAIR);
        if(!$marks) continue;

        // Calculate Logic
        $total_m = 0; $gp_sum = 0; $cnt = 0; $opt_b = 0; $fail = false;
        $subject_details = [];

        foreach($structure as $s_name => $data) {
            $sm=0; $smx=0; $det=[];
            foreach($data['components'] as $c) {
                $v = $marks[$c['comp_id']] ?? 0;
                $v= intval($v); 
                $sm += $v; $smx += $c['max_marks'];
                $pn= $c['part_name']=='-' ? '' : $c['part_name'].'-';
                $cn= $c['component_name']=='-' ? '' : '('.$c['component_name'].')';
                $mm=intval($c['max_marks']);
                $det[] = "{$pn}{$cn}<strong>{$v}</strong>";
            }
            $gp = calculateGP($sm, $smx);
            $is_fail = ($gp == 0 && !$data['is_optional']);
            if($is_fail) $fail = true;
            if($data['is_optional']) $opt_b = ($gp > 2) ? ($gp - 2) : 0;
            else { $gp_sum += $gp; $cnt++; }
            $total_m += $sm;

            $subject_details[] = [
                'name' => $s_name . ($data['is_optional'] ? ' (4th)' : ''),
                'breakdown' => implode(', ', $det),
                'total' => $sm,
                'grade' => getGrade($gp),
                'gp' => $gp,
                'is_fail' => $is_fail
            ];
        }

        $raw_gpa = ($cnt > 0) ? ($gp_sum / $cnt) : 0;
        $final_gpa = $fail ? 0 : min(5.00, $raw_gpa + $opt_b/$cnt);
        $final_grade = getGrade($final_gpa);
        
        $qr_url = $base_url . "?" . http_build_query(['year' => $year, 'class_id' => $class_id, 'term' => $term, 'roll' => $roll]);

        $results[] = [
            'roll' => $roll,
            'name' => $student['student_name'],
            'class' => $cls_name,
            'subjects' => $subject_details,
            'total_marks' => $total_m,
            'gpa' => $final_gpa,
            'raw_gpa' => $raw_gpa,
            'grade' => $final_grade,
            'fail' => $fail,
            'opt_bonus' => $opt_b,
            'qr' => $qr_url
        ];
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $school_name ?> Transcript App</title>
    
<link href="https://fonts.googleapis.com/css2?family=Roboto&display=swap" rel="stylesheet">    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
   

    <style>
        :root {
            --app-bg: #f3f4f6;
            --card-bg: #ffffff;
            --primary: #4361ee;
            --primary-light: #eff3ff;
            --text-dark: #1e293b;
            --text-muted: #64748b;
            --pass-color: #10b981;
            --fail-color: #ef4444;
            --gradient-head: linear-gradient(135deg, #4361ee 0%, #3f37c9 100%);
            --shadow-soft: 0 10px 30px -10px rgba(0, 0, 0, 0.1);
        }

        body {
          font-family: 'Roboto', sans-serif;



            background: var(--app-bg);
            color: var(--text-dark);
            margin: 0;
            padding-bottom: 60px;
        }

       /* --- APP VIEW STYLES (Mobile Optimized) --- */

.app-navbar {
    background: rgba(255, 255, 255, 0.95);
    backdrop-filter: blur(10px);
    position: sticky;
    top: 0;
    z-index: 100;
    border-bottom: 1px solid rgba(0,0,0,0.08);
    padding: 8px 12px; /* Reduced padding */
}

.btn-back-custom {
    border: 1px solid #e2e8f0;
    background: white;
    color: var(--text-dark);
    border-radius: 8px; /* Slightly tighter radius */
    padding: 6px 12px;  /* Compact button */
    font-weight: 600;
    font-size: 0.85rem;
    transition: all 0.2s;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 6px;
}
.btn-back-custom:hover { background: #f8fafc; }

.modern-result-card {
    background: var(--card-bg);
    border-radius: 16px; /* Reduced radius for more screen space */
    box-shadow: var(--shadow-soft);
    overflow: hidden;
    margin-bottom: 20px;
    border: 1px solid #fff;
}

/* --- Compact Header --- */
/* --- APP VIEW: LOGO LEFT / DETAILS RIGHT LAYOUT --- */
/* --- APP VIEW HEADER LAYOUT --- */

.card-school-header {
    display: flex;              /* flex row layout */
    align-items: center;        /* center vertically */
    justify-content: flex-start;
    padding: 15px;              /* Padding around the whole header */
    background: #ffffff;
    border-bottom: 1px dashed #e2e8f0;
    gap: 15px;                  /* Space between Logo and Text Column */
}

/* LEFT: Logo Styling */
.school-logo-app {
    width: 75px;                /* Fixed Width */
    height: 75px;               /* Fixed Height */
    object-fit: contain;
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    padding: 5px;
    flex-shrink: 0;             /* Prevents logo from shrinking */
}

/* RIGHT: Text Column Wrapper */
.header-text-content {
    flex: 1;                    /* Takes up all remaining width */
    display: flex;
    flex-direction: column;     /* Stacks items vertically */
    text-align: left;           /* Left align text */
    min-width: 0;               /* Prevents flex items from overflowing */
}

/* 1. School Name */
.school-name-app {
    font-size: 0.7rem;
    font-weight: 800;
    color: #1e293b;
    line-height: 1.2;
    margin-bottom: 2px;
}

/* 2. Address */
.school-addr-app {
    font-size: 0.8rem;
    color: #475569;
    font-weight: 500;
    line-height: 1.2;
    margin-bottom: 2px;
}

/* 3. Contact Details */
.school-contact-app {
    font-size: 0.75rem;
    color: #64748b;
    margin-bottom: 6px;
}

/* 4. Meta Badge */
.school-meta-badge {
    display: inline-block;
    width: fit-content;
    background: #f1f5f9;
    color: #475569;
    font-size: 0.65rem;
    padding: 2px 8px;
    border-radius: 4px;
    font-weight: 600;
    border: 1px solid #e2e8f0;
}
/* --- Compact Student Info --- */
.student-info-bar {
    background: var(--gradient-head);
    color: white;
    padding: 12px 15px; /* Compact padding */
    position: relative;
}
.student-name-lg { 
    font-size: 1.15rem; 
    font-weight: 700; 
    margin-bottom: 2px; 
}
.student-meta-row { 
    display: flex; 
    gap: 10px; 
    font-size: 0.8rem; 
    opacity: 0.95; 
}

/* --- Compact Table (Crucial for 12 Subjects) --- */
.modern-table-container { padding: 0; }
.table-modern { width: 100%; border-collapse: collapse; }

.table-modern th {
    text-align: left;
    padding: 8px 12px; /* Tighter headers */
    font-size: 0.7rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    color: #94a3b8;
    font-weight: 600;
    border-bottom: 1px solid #f1f5f9;
}

.table-modern td {
    padding: 6px 12px; /* Very tight rows to fit 12 subjects */
    vertical-align: middle;
    border-bottom: 1px solid #f8fafc;
    color: var(--text-dark);
    font-size: 0.85rem; /* Readable but small */
}
.table-modern tr:last-child td { border-bottom: none; }

.subject-name-cell { 
    font-weight: 600; 
    display: block; 
    line-height: 1.1;
}
.subject-breakdown { 
    font-size: 0.7rem; 
    color: #94a3b8; 
    display: block; 
    margin-top: 1px; 
    font-style: italic;
}

.grade-badge {
    background: #f1f5f9;
    color: var(--text-dark);
    padding: 3px 8px; /* Smaller badge */
    border-radius: 6px;
    font-weight: 700;
    font-size: 0.75rem;
}
.row-fail-modern { background: #fff1f2; }
.row-fail-modern .grade-badge { background: #fee2e2; color: #ef4444; }

/* --- Footer Stats --- */
.card-footer-summary { 
    background: #f8fafc; 
    padding: 15px; 
    margin: 0; /* Remove margin to save space */
    border-radius: 0 0 16px 16px; 
    border-top: 1px solid #e2e8f0;
}
.summary-grid { 
    display: grid; 
    grid-template-columns: repeat(4, 1fr); /* Force 4 columns on mobile to save vertical space */
    gap: 5px; 
}

.stat-box {
    background: white;
    padding: 8px 2px; /* Very tight padding */
    border-radius: 8px;
    text-align: center;
    box-shadow: 0 1px 2px rgba(0,0,0,0.03);
    border: 1px solid #e2e8f0;
}
.stat-val { 
    font-size: 0.95rem; 
    font-weight: 800; 
    color: var(--primary); 
    line-height: 1;
}
.stat-lbl { 
    font-size: 0.6rem; 
    color: #64748b; 
    text-transform: uppercase; 
    font-weight: 600; 
    margin-top: 3px; 
    white-space: nowrap; /* Prevent wrapping */
}

/* --- Search Hero --- */
.search-hero {
    background: white;
    padding: 20px 15px;
    border-radius: 16px;
    box-shadow: var(--shadow-soft);
    margin-bottom: 20px;
    text-align: center;
}

/* --- Print Button --- */
.fab-print {
    position: fixed;
    bottom: 20px;
    right: 20px;
    background: #0f172a;
    color: white;
    width: 50px; /* Smaller button */
    height: 50px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.2rem;
    box-shadow: 0 5px 15px rgba(0,0,0,0.2);
    border: none;
    transition: transform 0.2s, opacity 0.2s;
    z-index: 1000;
    opacity: 0.8; /* More visible */
}
.fab-print:active { transform: scale(0.9); opacity: 1; }

/* --- DESKTOP RESTORATION (Makes it look nice on big screens too) --- */
@media(min-width: 768px) {
    .card-school-header { padding: 30px; }
    .school-logo-app { width: 90px; height: 90px; }
    .school-name-app { font-size: 1.5rem; }
    
    .student-info-bar { padding: 25px; }
    .student-name-lg { font-size: 1.6rem; }
    
    .table-modern th, .table-modern td { padding: 15px 25px; font-size: 1rem; }
    .subject-breakdown { font-size: 0.8rem; }
    .grade-badge { padding: 6px 12px; font-size: 0.9rem; }
    
    .summary-grid { gap: 15px; }
    .stat-box { padding: 20px; }
    .stat-val { font-size: 1.4rem; }
}

        /* --- PRINT VIEW STYLES (HIDDEN ON APP) --- */
        .print-layout-container { display: none; }
        
        @media print {
            .no-print-area, .app-navbar, .fab-print { display: none !important; }
            body { background: white; margin: 0; padding: 0; }
            .print-layout-container { display: block !important; }
            
            /* Strict Print CSS */
            .card-preview { 
                width: 210mm; 
                height: 148mm; 
                padding: 8mm 10mm; 
                position: relative; 
                border-bottom: 1px dashed #ccc; 
                page-break-inside: avoid;
            }
            .card-preview:nth-child(2n) { page-break-after: always; border-bottom: none; }
            .card-preview:last-child { border-bottom: none; }
            .school-logo { width: auto; height: 90px; object-fit: contain; }
            .qr-code { width: auto; height: 80px; border: 1px solid #eee; padding: 2px; }
            .header-title { font-size: 18px; font-weight: 800; color: black; margin: 0; }
            .table-transcript { width: 100%; border-collapse: collapse; }
            .table-transcript th { background: #2d3748 !important; color: white !important; -webkit-print-color-adjust: exact; font-size: 9px; padding: 3px; border: 1px solid #000; }
            .table-transcript td { border: 1px solid #ccc; padding: 2px 4px; font-size: 9px; }
            .row-fail { background-color: #ffeaea !important; -webkit-print-color-adjust: exact; }
            .signature-row { position: absolute; bottom: 5mm; left: 10mm; right: 10mm; display: flex; justify-content: space-between; opacity: 0.7; }
            .sig-box { width: 130px; border-top: 1px solid black; text-align: center; font-size: 10px; font-weight: bold; }
            .print-footer { position: absolute; bottom: 2mm; width: 100%; text-align: center; font-size: 8px; color: #bbbabaff; left: 0; opacity: 0.9;}
        }
    </style>
</head>
<body>

    <div class="app-navbar d-flex justify-content-between align-items-center no-print-area">
        <a href="result.php" class="btn-back-custom">
            <i class="bi bi-chevron-left"></i> New Search
        </a>
        <div class="text-end">
            <span class="badge bg-primary bg-opacity-10 text-primary px-3 py-2 rounded-pill fw-bold" onClick="window.location.href='../index.php'"><?= $school_name ?></span>
        </div>
    </div>

    <div class="container mt-4 mb-5 no-print-area">
        
        <?php if (empty($results)): ?>
        <div class="row justify-content-center">
            <div class="col-md-8 col-lg-6">
                <div class="search-hero">
                    <img src="<?= $school_logo ?>" style="height: 80px; margin-bottom: 20px;">
                    <h3 class="fw-bold mb-4">Find Results</h3>
                    
                    <form method="GET" class="text-start">
                        <div class="mb-3">
                            <label class="small text-muted fw-bold mb-1">Year</label>
                            <select name="year" class="form-select form-select-lg" onchange="this.form.submit()">
                                <option value="">Select Year...</option>
                                <?php 
                                $years = $pdo->query("SELECT DISTINCT academic_year FROM classes ORDER BY academic_year DESC")->fetchAll(PDO::FETCH_COLUMN);
                                foreach ($years as $y) echo "<option value='$y' ".($year==$y?'selected':'').">$y</option>"; 
                                ?>
                            </select>
                        </div>

                        <div class="row g-2 mb-3">
                            <div class="col-6">
                                <label class="small text-muted fw-bold mb-1">Class</label>
                                <select name="class_id" class="form-select" onchange="this.form.submit()">
                                    <option value="">Select...</option>
                                    <?php if($year): 
                                        $stmt = $pdo->prepare("SELECT id, class_name FROM classes WHERE academic_year = ?"); $stmt->execute([$year]);
                                        while($c = $stmt->fetch()) echo "<option value='{$c['id']}' ".($class_id==$c['id']?'selected':'').">{$c['class_name']}</option>";
                                    endif; ?>
                                </select>
                            </div>
                            <div class="col-6">
                                <label class="small text-muted fw-bold mb-1">Term</label>
                                <select name="term" class="form-select">
                                    <option value="">Select...</option>
                                    <?php if($class_id): 
                                        $stmt = $pdo->prepare("SELECT DISTINCT exam_term FROM marks WHERE class_id = ?"); $stmt->execute([$class_id]);
                                        while($t = $stmt->fetch()) echo "<option value='{$t['exam_term']}' ".($term==$t['exam_term']?'selected':'').">{$t['exam_term']}</option>";
                                    endif; ?>
                                </select>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="small text-muted fw-bold mb-1">Roll Number</label>
                            <input type="text" name="roll" class="form-control form-control-lg" value="<?= htmlspecialchars($roll_input ?? '') ?>" placeholder="e.g. 5 or 1-10">
                        </div>

                        <button type="submit" class="btn btn-primary w-100 py-3 fw-bold rounded-pill">View Transcript</button>
                    </form>
                </div>
            </div>
        </div>
        <?php endif; ?>


        <?php if (!empty($results)): ?>
            <div class="row justify-content-center">
                <?php foreach ($results as $res): ?>
                <div class="col-12 col-lg-8">
                    <div class="modern-result-card">
                        
                       <div class="card-school-header">
                            <img src="<?= $school_logo ?>" class="school-logo-app">
                            
                            <div class="header-text-content">
                                <div class="school-name-app"><?= strtoupper($school_name) ?></div>
                                <div class="school-addr-app"><?= $school_addr ?></div>
                                <div class="school-contact-app">
                                    <i class="bi bi-telephone"></i> <?= $school_phone ?>
                                </div>
                                <div class="school-meta-badge">
                                    EMIS: <?= $emis ?> &bull; Code: <?= $school_code ?> Web: <?= $website ?>
                                </div>
                            </div>
                        </div>

                        <div class="student-info-bar">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <div class="student-name-lg"><?= $res['name'] ?></div>
                                    <div class="student-meta-row">
                                        <span><i class="bi bi-person-badge me-1"></i> Roll: <?= $res['roll'] ?></span>
                                        <span><i class="bi bi-backpack me-1"></i> Class: <?= $res['class'] ?></span>
                                    </div>
                                    <div class="mt-2 text-white-50" style="font-size: 0.8rem; text-transform: uppercase; letter-spacing: 1px;">
                                     <strong>   <?= $term ?> Exam <?= $year ?> </strong>
                                    </div>
                                </div>
                                <div class="bg-white rounded p-1">
                                    <img src="https://api.qrserver.com/v1/create-qr-code/?data=<?= urlencode($res['qr']) ?>&size=80x80" style="width: 60px; height: 60px;">
                                </div>
                            </div>
                        </div>

                        <div class="modern-table-container">
                            <table class="table-modern">
                                <thead>
                                    <tr>
                                        <th width="50%">Subject</th>
                                        <th width="20%">Mark</th>
                                        <th width="20%">GP</th>
                                        <th width="10%">G</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach($res['subjects'] as $sub): ?>
                                    <tr class="<?= $sub['is_fail'] ? 'row-fail-modern' : '' ?>">
                                        <td>
                                            <span class="subject-name-cell"><?= $sub['name'] ?></span>
                                            <span class="subject-breakdown"><?= $sub['breakdown'] ?></span>
                                        </td>
                                        <td><strong><?= $sub['total'] ?></strong></td>
                                        <td><?= number_format($sub['gp'], 2) ?></td>
                                        <td><span class="grade-badge"><?= $sub['grade'] ?></span></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>

                        <div class="card-footer-summary">
                            <div class="summary-grid">
                                <div class="stat-box">
                                    <div class="stat-val"><?= $res['total_marks'] ?></div>
                                    <div class="stat-lbl">Total Marks</div>
                                </div>
                                <div class="stat-box">
                                    <div class="stat-val"><?= $res['opt_bonus'] ? number_format($res['raw_gpa'],2) : '-' ?></div>
                                    <div class="stat-lbl">Without 4th Sub</div>
                                </div>
                                <div class="stat-box">
                                    <div class="stat-val"><?= number_format($res['gpa'], 2) ?></div>
                                    <div class="stat-lbl">GPA</div>
                                </div>
                                <div class="stat-box" style="<?= $res['fail'] ? 'border-color: #fecaca; background:#fef2f2;' : 'border-color:#bbf7d0; background:#f0fdf4;' ?>">
                                    <div class="stat-val" style="<?= $res['fail'] ? 'color:#dc2626;' : 'color:#16a34a;' ?>">
                                        <?= $res['fail'] ? 'FAIL' : 'PASS' ?>
                                    </div>
                                    <div class="stat-lbl">Result</div>
                                </div>
                            </div>
                            
                            <div class="text-center mt-3">
                                <span class="badge bg-light text-muted fw-normal">Final Grade: <strong><?= $res['grade'] ?></strong></span>
                            </div>
                        </div>

                    </div>
                </div>
                <?php endforeach; ?>
            </div>

            <button onclick="window.print()" class="fab-print">
                <i class="bi bi-printer"></i>
            </button>
        <?php endif; ?>
    </div>

    <div class="print-layout-container">
        <?php if (!empty($results)): foreach ($results as $res): ?>
            <div class="card-preview">
                <div class="d-flex justify-content-between align-items-center border-bottom pb-2 mb-2">
                    <img src="<?= $school_logo ?>" class="school-logo" >
                    <div class="text-center">
                        <h1 class="header-title"><?= strtoupper($school_name) ?></h1>
                        <p style="font-size: 12px; margin:0;"><?= $school_addr ?></p>
                        <p style="font-size: 9px; margin:0;">
                            EMIS: <?= $emis ?>  Code: <?= $school_code ?>  Est: <?= $established ?> 
                             <?= $school_phone ?>   <?= $school_email ?> <?= $website ?>
                        </p>
                        <div style="background:#a7d7fc; color:black; display:inline-block; padding:2px 10px; border-radius:10px; font-size:12px; margin-top:2px;">
                         <strong>   <?= strtoupper($term) ?> EXAMINATION - <?= $year ?> </strong>
                        </div>
                    </div>
                    <img src="https://api.qrserver.com/v1/create-qr-code/?data=<?= urlencode($res['qr']) ?>&size=100x100" class="qr-code">
                </div>

                <div class="row gx-0 mb-1" style="font-size: 11px; border-bottom: 1px solid #eee; padding-bottom: 5px;">
                    <div class="col-8 fw-bold">NAME: <?= strtoupper($res['name']) ?></div>
                    <div class="col-2">ROLL: <?= $res['roll'] ?></div>
                    <div class="col-2 text-end">CLASS: <?= $res['class'] ?></div>
                </div>

                <div class="row">
                    <div class="col-8">
                        <table class="table-transcript text-center">
                            <thead>
                                <tr>
                                    <th width="30%" class="text-start">Subject</th>
                                    <th width="40%">Marks</th>
                                    <th width="10%">Total</th>
                                    
                                     <th width="10%">Grade</th>
                                     <th width="10%">Point</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($res['subjects'] as $sub): ?>
                                <tr class="<?= $sub['is_fail'] ? 'row-fail' : '' ?>">
                                    <td class="text-start fw-bold"><?= $sub['name'] ?></td>
                                    <td class="text-start" style="font-size:8px;"><?= $sub['breakdown'] ?></td>
                                    <td class="fw-bold"><?= $sub['total'] ?></td>
                                  
                                    <td><?= $sub['grade'] ?></td>
                                      <td><?= number_format($sub['gp'], 2) ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    
                    <div class="col-4">
                        <table class="table-transcript text-center">
                            <thead><tr><th>Marks %</th><th>Grade</th><th>Point</th></tr></thead>
                            <tbody>
                                <tr><td>80-100 %</td><td>A+</td><td>5.00</td></tr>
                                <tr><td>70-79 %</td><td>A</td><td>4.00</td></tr>
                                <tr><td>60-69 %</td><td>A-</td><td>3.50</td></tr>
                                <tr><td>50-59 %</td><td>B</td><td>3.00</td></tr>
                                <tr><td>40-49 %</td><td>C</td><td>2.00</td></tr>
                                <tr><td>33-39 %</td><td>D</td><td>1.00</td></tr>
                                <tr><td>0-32 %</td><td>F</td><td>0.00</td></tr>
                                <tr>
                                    <td colspan="3" style="<?= $res['fail']?'color:#c00;':'color:#0a0;' ?>  font-size:30px; font-weight:bold; padding:2px;">
                                       <strong></strong> <?= $res['grade'] ?></strong>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="gpa-summary row gx-0 text-center fw-bold mt-2" style="font-size:11px; border:1px solid #ccc; background:#eee;">
                    <div class="col-3">Total Marks: <?= $res['total_marks'] ?></div>
                    <div class="col-3"><?= $res['opt_bonus'] ? 'Without 4th Sub: '.number_format($res['raw_gpa'],2) : '' ?></div>
                    <div class="col-3">GPA: <?= number_format($res['gpa'], 2) ?></div>
                    <div class="col-3 text-uppercase <?= $res['fail']?'text-danger':'' ?>">Result: <?= $res['fail']?'FAIL':'PASS' ?></div>
                </div>

                <div class="signature-row">
                    <div class="sig-box">Guardian</div>
                    <div class="sig-box">Class Teacher</div>
                    <div class="sig-box">Principal</div>
                </div>

                <div class="print-footer">
                    Scan the QR to verify | Cloud Based Online Result Management System | Developed by kowshiqueroy@gmail.com | 01632950179
                </div>
            </div>
        <?php endforeach; endif; ?>
    </div>

</body>
</html>