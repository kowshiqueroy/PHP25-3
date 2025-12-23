<?php
require_once 'db.php';
if (session_status() === PHP_SESSION_NONE) { session_start(); }

// Security: Only allow logged-in users
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

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
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admit Card Generator</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        body { background-color: #f0f2f5; font-family: 'Segoe UI', sans-serif; }
        .no-print { padding-bottom: 50px; }
        .workspace-header { background: #1e293b; color: white; padding: 15px 30px; margin-bottom: 25px; }
        .config-card { background: white; border-radius: 12px; padding: 20px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); }
        .table-input { border: 1px solid #dee2e6; border-radius: 4px; padding: 2px 8px; width: 100%; font-size: 13px; }

        /* Print Engine */
        #printArea { display: none; }

        @media print {
            .no-print { display: none !important; }
            #printArea { display: block !important; background: white; }
            body { background: white; margin: 0; padding: 0; }
            @page { size: A4 portrait; margin: 0; }
            
            .admit-wrapper {
                width: 210mm;
                height: 148.5mm; /* Half A4 */
                padding: 10mm;
                box-sizing: border-box;
                border-bottom: 1px dashed #ccc;
                position: relative;
                overflow: hidden;
                page-break-inside: avoid;
            }
            
            .admit-inner {
                border: 2px solid #000;
                padding: 5mm;
                height: 100%;
                display: flex;
                flex-direction: column;
                position: relative;
            }

            .school-title { font-size: 18px; font-weight: bold; text-transform: uppercase; margin: 0; color: #000; }
            .exam-title { 
                font-size: 14px; border: 2px solid #000; display: inline-block; 
                padding: 2px 20px; margin-top: 5px; font-weight: bold; background: #eee !important;
            }
            
            .student-meta { border-top: 1px solid #000; border-bottom: 1px solid #000; margin: 10px 0; padding: 5px 0; font-size: 13px; }
            
            .schedule-grid { display: flex; gap: 10px; flex-grow: 1; }
            .schedule-col { flex: 1; }
            .table-admit { width: 100%; border-collapse: collapse; font-size: 10px; }
            .table-admit th, .table-admit td { border: 1px solid #000; padding: 3px 6px; text-align: left; }
            .table-admit th { background: #eee !important; text-transform: uppercase; }

            .signature-row { margin-top: auto; padding-bottom: 5px; }
            .sig-box { width: 150px; border-top: 1px solid #000; text-align: center; font-size: 11px; }
            
            .photo-placeholder {
                width: 80px; height: 95px; border: 1px solid #000;
                text-align: center; font-size: 9px; display: flex;
                align-items: center; justify-content: center;
            }
        }
    </style>
</head>
<body>

<div class="no-print">
    <div class="workspace-header d-flex justify-content-between align-items-center">
        <div>
            <button onclick="window.history.back()" class="btn btn-success px-5 fw-bold">Back</button>
        </div>
        <div>
            <h4 class="mb-0"><i class="fa fa-id-card me-2"></i> Admit Card Studio</h4>
            <small class="text-white-50"><?= $school_name; ?> • <?= $established; ?></small>
        </div>
        <div>
            <button onclick="generate()" class="btn btn-success px-5 fw-bold"><i class="fa fa-print me-2"></i>GENERATE PRINT</button>
        </div>
    </div>

    <div class="container-fluid px-4">
        <div class="row g-4">
            <div class="col-md-3">
                <div class="config-card">
                    <h6 class="fw-bold mb-3"><i class="fa fa-cog me-2"></i>Configuration</h6>
                    <div class="mb-2">
                        <label class="small fw-bold">Exam Term</label>
                        <input type="text" id="term" class="form-control form-control-sm" placeholder="Annual Exam 2025">
                    </div>
                    <div class="mb-3">
                        <label class="small fw-bold">Class</label>
                        <input type="text" id="class_name" class="form-control form-control-sm" placeholder="Class Ten">
                    </div>

                    <div class="bg-light p-2 rounded mb-3">
                        <label class="small fw-bold">Roll Range</label>
                        <div class="input-group input-group-sm">
                            <input type="number" id="rollS" class="form-control" placeholder="From">
                            <input type="number" id="rollE" class="form-control" placeholder="To">
                            <button class="btn btn-dark" onclick="setupStudents()">Set</button>
                        </div>
                    </div>
                    
                    <div id="studentInputs" style="max-height: 400px; overflow-y: auto;"></div>
                </div>
            </div>

            <div class="col-md-9">
                <div class="config-card">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h6 class="fw-bold mb-0"><i class="fa fa-calendar-alt me-2"></i>Routine Details</h6>
                        <button class="btn btn-primary btn-sm" onclick="addRow()">+ Add Subject</button>
                    </div>
                    <table class="table table-sm table-bordered">
                        <thead class="table-light small">
                            <tr>
                                <th>Subject</th>
                                <th width="180">Date</th>
                                <th width="140">Time</th>
                                <th width="40"></th>
                            </tr>
                        </thead>
                        <tbody id="subjectRows"></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<div id="printArea"></div>

<script>
function setupStudents() {
    const s = parseInt(document.getElementById('rollS').value);
    const e = parseInt(document.getElementById('rollE').value);
    const container = document.getElementById('studentInputs');
    if(!s || !e) return;
    container.innerHTML = '<p class="small fw-bold text-muted mb-2">Student Names:</p>';
    for(let i=s; i<=e; i++) {
        container.innerHTML += `
        <div class="input-group mb-1">
            <span class="input-group-text small" style="width:70px">Roll ${i}</span>
            <input type="text" class="form-control form-control-sm st-input" data-roll="${i}" placeholder="Student Name">
        </div>`;
    }
}

function addRow() {
    const tr = document.createElement('tr');
    tr.innerHTML = `
        <td><input type="text" class="table-input sub-name" placeholder="Subject Name"></td>
        <td><input type="date" class="table-input sub-date" onchange="syncD(this)"></td>
        <td><input type="text" class="table-input sub-time" placeholder="10:00 AM" onchange="syncT(this)"></td>
        <td class="text-center"><button class="btn btn-link btn-sm text-danger" onclick="this.closest('tr').remove()"><i class="fa fa-times"></i></button></td>
    `;
    document.getElementById('subjectRows').appendChild(tr);
}

function syncD(el) {
    let rows = Array.from(document.querySelectorAll('#subjectRows tr'));
    let startIdx = rows.indexOf(el.closest('tr'));
    let date = new Date(el.value);

    for (let i = startIdx + 1; i < rows.length; i++) {
        // Move to the next day
        date.setDate(date.getDate() + 1);

        // If the day is Friday (getDay() returns 5), skip to Saturday
        if (date.getDay() === 5) {
            date.setDate(date.getDate() + 1);
        }

        let target = rows[i].querySelector('.sub-date');
        if (!target.value) {
            // Set YYYY-MM-DD for the input value
            target.value = date.toISOString().split('T')[0];
            
            // Optional: If you want to show the Day Name (e.g., Monday) next to the input
            // You can update a label or title attribute
            const dayName = date.toLocaleDateString('en-US', { weekday: 'long' });
            target.title = dayName; 
        } else {
            // If a date already exists, sync the 'date' variable to it for the next iteration
            date = new Date(target.value);
        }
    }
}

function syncT(el) {
    let rows = Array.from(document.querySelectorAll('#subjectRows tr'));
    let startIdx = rows.indexOf(el.closest('tr'));
    for(let i=startIdx+1; i<rows.length; i++) {
        let target = rows[i].querySelector('.sub-time');
        if(!target.value) target.value = el.value;
    }
}

function generate() {
    const subjects = [];
    document.querySelectorAll('#subjectRows tr').forEach(row => {
        const name = row.querySelector('.sub-name').value;
        if(name) {
            subjects.push({n: name, d: row.querySelector('.sub-date').value, t: row.querySelector('.sub-time').value});
        }
    });

    if(subjects.length === 0) { alert("Add subjects first!"); return; }

    const mid = Math.ceil(subjects.length / 2);
    const colA = subjects.slice(0, mid);
    const colB = subjects.slice(mid);

    let html = '';
    const students = document.querySelectorAll('.st-input');
    
    if(students.length === 0) { alert("Set roll range and student names!"); return; }

    students.forEach(st => {
        html += `
        <div class="admit-wrapper">
            <div class="admit-inner">
                <div class="d-flex justify-content-between align-items-start">
                    <img src="<?= $school_logo ?>" style="height:100px;object-fit:contain" ">
                    <div class="text-center flex-grow-1">
                        <h1 class="school-title"><?= $school_name; ?></h1>
                        <p class="mb-0 small"><?= $school_addr ?></p>

                        <p style="font-size:12px"><?= "EMIS:" . $emis . " School Code:" . $school_code . " Established:" . $established .
                         "<br> Phone:" . $school_phone . " Email:" . $school_email . " Website:" . $website ?></p>
                        <div class="exam-title">${document.getElementById('term').value.toUpperCase() || 'EXAMINATION'}</div>
                    </div>
                    <div class="photo-placeholder">Student<br>PHOTO</div>
                </div>

                <div class="student-meta d-flex justify-content-between fw-bold">
                    <span>NAME: ${st.value.toUpperCase() || '_______________________'}</span>
                    <span>CLASS: ${document.getElementById('class_name').value.toUpperCase() || '_________'}</span>
                    <span>ROLL: ${st.dataset.roll}</span>
                </div>

                <div class="schedule-grid">
                    <div class="schedule-col">
                        <table class="table-admit">
                            <thead><tr><th>Subject</th><th>Date</th><th>Time</th></tr></thead>
                            <tbody>${colA.map(s=>`<tr><td>${s.n}</td><td>${s.d}</td><td>${s.t}</td></tr>`).join('')}</tbody>
                        </table>
                    </div>
                    <div class="schedule-col">
                        <table class="table-admit">
                            <thead><tr><th>Subject</th><th>Date</th><th>Time</th></tr></thead>
                            <tbody>
                                ${colB.map(s=>`<tr><td>${s.n}</td><td>${s.d}</td><td>${s.t}</td></tr>`).join('')}
                                ${colB.length < colA.length ? '<tr><td>&nbsp;</td><td></td><td></td></tr>' : ''}
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="signature-row d-flex justify-content-between">
                    <div class="sig-box">Class Teacher</div>
                    <div class="sig-box">Principal</div>
                </div>
            </div>
        </div>`;
    });

    document.getElementById('printArea').innerHTML = html;
    setTimeout(() => { window.print(); }, 500);
}

// Init
for(let i=0; i<10; i++) addRow();
</script>
</body>
</html>