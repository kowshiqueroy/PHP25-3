<?php
require 'header.php';

$msg = ''; $error = '';

// --- FOLDER SETUP ---
if (!is_dir('uploads')) { mkdir('uploads', 0777, true); }

// --- 1. CLASS OPERATIONS (Add/Edit/Delete) ---
if (isset($_POST['save_class'])) {
    $c_id = !empty($_POST['class_id']) ? $_POST['class_id'] : null;
    //check allready existing class name in the session
    $cl_check = $pdo->prepare("SELECT id FROM classes WHERE class_name = ? AND academic_year = ? AND id != ?");
    $cl_check->execute([$_POST['class_name'], $_POST['academic_year'], $_POST['class_id']]);
    if ($cl_check->fetch()) {
        $error = "Error: Class name already exists in the selected academic year.";
    } else if ($cl_check->rowCount() > 0) {
        $error = "Error: Class name already exists in the selected academic year.";
    } 
    else {
    $data = [$_POST['class_name'], $_POST['academic_year'], $_POST['start_roll'], $_POST['end_roll']];

    if ($c_id) {
        $stmt = $pdo->prepare("UPDATE classes SET class_name=?, academic_year=?, start_roll=?, end_roll=? WHERE id=?");
        $data[] = $c_id;
        $stmt->execute($data);
        $msg = "Class updated.";
    } else {
        $stmt = $pdo->prepare("INSERT INTO classes (class_name, academic_year, start_roll, end_roll) VALUES (?, ?, ?, ?)");
        $stmt->execute($data);
        $msg = "Class created.";
        //create student records
        $class_id = $pdo->lastInsertId();
        $stmt = $pdo->prepare("INSERT INTO students (class_id, roll_no, student_name) VALUES (?, ?, ?)");
        for ($i = $data[2]; $i <= $data[3]; $i++) {
            $stmt->execute([$class_id, $i, "StudentName$i"]);
        }
    }
}
}

if (isset($_POST['delete_class'])) {
    $pdo->prepare("DELETE FROM classes WHERE id = ?")->execute([$_POST['class_id']]);
    $msg = "Class and all associated records deleted.";
}

// --- 2. BULK STUDENT GENERATION ---
if (isset($_POST['bulk_add_students'])) {
    $class_id = $_POST['target_class_id'];
    $s_roll = (int)$_POST['range_start'];
    $e_roll = (int)$_POST['range_end'];
  

    //update class roll range
    $stmt = $pdo->prepare("UPDATE classes SET start_roll=:start_roll, end_roll=:end_roll WHERE id=:class_id");
    $stmt->execute([':start_roll' => $s_roll, ':end_roll' => $e_roll, ':class_id' => $class_id]);

    try {
        $pdo->beginTransaction();
        $stmt = $pdo->prepare("INSERT INTO students (class_id, roll_no, student_name) VALUES (?, ?, ?)");
        $count = 0;
        for ($i = $s_roll; $i <= $e_roll; $i++) {
            $check = $pdo->prepare("SELECT student_id FROM students WHERE class_id = ? AND roll_no = ?");
            $check->execute([$class_id, $i]);
            if (!$check->fetch()) {
                $stmt->execute([$class_id, $i, "StudentName$i"]);
                $count++;
            }
        }
        $pdo->commit();
        $msg = "Generated $count student records.";
    } catch (Exception $e) { $pdo->rollBack(); $error = $e->getMessage(); }
}

// --- 3. STUDENT OPERATIONS (Save/Delete) ---
if (isset($_POST['save_student'])) {
    //check already existing roll in the class
    $st_check = $pdo->prepare("SELECT student_id FROM students WHERE class_id = ? AND roll_no = ? AND student_id != ?");
    $st_check->execute([$_POST['class_id'], $_POST['roll_no'], $_POST['student_id'] ?? 0]);
    if ($st_check->fetch()) {
        $error = "Error: Roll number already exists in the selected class.";
     
    } else {
    $st_id = !empty($_POST['student_id']) ? $_POST['student_id'] : null;
    $photo = $_POST['existing_photo'] ?? '';
    if (!empty($_FILES['photo']['name'])) {
        $photo = "uploads/" . time() . "_" . $_FILES['photo']['name'];
        move_uploaded_file($_FILES['photo']['tmp_name'], $photo);
    }
 
    if ($st_id) {
        $stmt = $pdo->prepare("UPDATE students SET roll_no=?, student_name=?, father_name=?, phone=?, address=?, photo_path=? WHERE student_id=?");
        $stmt->execute([$_POST['roll_no'], $_POST['student_name'], $_POST['father_name'], $_POST['phone'], $_POST['address'], $photo, $st_id]);
    } else {
        $stmt = $pdo->prepare("INSERT INTO students (class_id, roll_no, student_name, father_name, address, phone, photo_path) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$_POST['class_id'], $_POST['roll_no'], $_POST['student_name'], $_POST['father_name'], $_POST['phone'], $_POST['address'], $photo]);
        // update class roll range if needed
        $class_id = $_POST['class_id'];
        $stmt = $pdo->prepare("SELECT start_roll, end_roll FROM classes WHERE id=?");
        $stmt->execute([$class_id]);
        $data = $stmt->fetch();
        if ($data['start_roll'] > $_POST['roll_no']) {
            $stmt = $pdo->prepare("UPDATE classes SET start_roll=? WHERE id=?");
            $stmt->execute([$_POST['roll_no'], $class_id]);
        } elseif ($data['end_roll'] < $_POST['roll_no']) {
            $stmt = $pdo->prepare("UPDATE classes SET end_roll=? WHERE id=?");
            $stmt->execute([$_POST['roll_no'], $class_id]);
        }
    
    
    }
    $msg = "Student details saved."; }
}

if (isset($_POST['delete_student'])) {
    // 
    //update class roll range if needed
    $stmt = $pdo->prepare("SELECT class_id, roll_no FROM students WHERE student_id=?");
    $stmt->execute([$_POST['student_id']]);
    $data = $stmt->fetch();
    $class_id = $data['class_id'];
    $roll_no = $data['roll_no'];
    $stmt = $pdo->prepare("SELECT start_roll, end_roll FROM classes WHERE id=?");
    $stmt->execute([$class_id]);
    $data = $stmt->fetch();
    if ($data['start_roll'] == $roll_no) {
        $stmt = $pdo->prepare("UPDATE classes SET start_roll=? WHERE id=?");
        $stmt->execute([$roll_no + 1, $class_id]);
    } elseif ($data['end_roll'] == $roll_no) {
        $stmt = $pdo->prepare("UPDATE classes SET end_roll=? WHERE id=?");
        $stmt->execute([$roll_no - 1, $class_id]);
    }
    $pdo->prepare("DELETE FROM students WHERE student_id = ?")->execute([$_POST['student_id']]);
    $msg = "Student deleted.";
}

// --- 4. SESSION PROMOTION ENGINE ---
if (isset($_POST['execute_promotion'])) {
    $new_year = $_POST['target_year'];
    $mappings = $_POST['class_mapping']; // Array of [name, start, end]

    try {
        $pdo->beginTransaction();
        foreach ($mappings as $old_id => $data) {
            if (empty($data['name'])) continue;
            // 1. Create New Class
            $stmt = $pdo->prepare("INSERT INTO classes (class_name, academic_year, start_roll, end_roll) VALUES (?, ?, ?, ?)");
            $stmt->execute([$data['name'], $new_year, $data['start'], $data['end']]);
            $new_class_id = $pdo->lastInsertId();
            // 2. Clone Students
            $stmtS = $pdo->prepare("INSERT INTO students (class_id, roll_no, student_name, father_name, address, phone, photo_path) 
                                    SELECT ?, roll_no, student_name, father_name, address, phone, photo_path 
                                    FROM students WHERE class_id = ?");
            $stmtS->execute([$new_class_id, $old_id]);
        }
        $pdo->commit();
        $msg = "Session promoted to $new_year successfully.";
    } catch (Exception $e) { $pdo->rollBack(); $error = $e->getMessage(); }
}

// --- DATA FETCHING ---
$sessions = $pdo->query("SELECT DISTINCT academic_year FROM classes ORDER BY academic_year DESC")->fetchAll(PDO::FETCH_COLUMN);
$search_year = $_GET['search_year'] ?? ($sessions[0] ?? date('Y'));
$classes_query = $pdo->prepare("SELECT * FROM classes WHERE academic_year = ? ORDER BY class_name ASC");
$classes_query->execute([$search_year]);
$classes = $classes_query->fetchAll();
?>

<div class="container-fluid mt-4">
    <div class="row">
        <div class="col-md-3">
            <div class="card shadow-sm border-0 mb-3">
                <div class="card-header bg-dark text-white fw-bold">Academic Sessions</div>
                <div class="card-body">
                    <form method="GET" class="mb-3">
                        <div class="input-group input-group-sm">
                            <input type="text" name="search_year" class="form-control" value="<?= $search_year ?>">
                            <button class="btn btn-primary">Go</button>
                        </div>
                    </form>
                    <div class="list-group mb-3 scrollable-list" style="max-height: 300px; overflow-y:auto;">
                        <?php foreach($sessions as $s): ?>
                            <a href="?search_year=<?= $s ?>" class="list-group-item list-group-item-action <?= $search_year == $s ? 'active' : '' ?> small">
                                <i class="fa-solid fa-folder me-2"></i> Session <?= $s ?>
                            </a>
                        <?php endforeach; ?>
                    </div>
                    <button class="btn btn-outline-primary btn-sm w-100 mb-2" onclick="openAddClassModal()">+ Add New Class</button>
                    <button class="btn btn-outline-info btn-sm w-100 mb-2" data-bs-toggle="modal" data-bs-target="#bulkAddModal">Bulk Add Students</button>
                    <button class="btn btn-warning btn-sm w-100 mb-2" data-bs-toggle="modal" data-bs-target="#promoteModal">Promote Session</button>
                    <button class="btn btn-outline-primary btn-sm w-100 mb-2" onclick="window.location.href='roll.php'">Alter Details</button>

                </div>
            </div>
        </div>

        <div class="col-md-9">
            <?php if($msg) echo "<div class='alert alert-success alert-dismissible fade show'>$msg<button type='button' class='btn-close' data-bs-dismiss='alert'></button></div>"; ?>
            <?php if($error) echo "<div class='alert alert-danger'>$error</div>"; ?>

            <div class="d-flex justify-content-between align-items-center mb-4">
                <h3 class="fw-bold">Active Session: <?= $search_year ?></h3>
                <button class="btn btn-success btn-sm" onclick="openAddStudentModal()"><i class="fa-solid fa-plus"></i> Manual Add</button>
            </div>

            <?php foreach($classes as $c): ?>
            <div class="card shadow-sm border-0 mb-4 overflow-hidden">
                <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center py-3">
                    <div>
                        <span class="h5 fw-bold mb-0"><?= $c['class_name'] ?></span>
                        <span class="badge bg-light text-dark ms-2 border">Roll: <?= $c['start_roll'] ?>-<?= $c['end_roll'] ?></span>
                    </div>
                    <div>
                        <button class="btn btn-sm btn-outline-secondary me-2" onclick='editClass(<?= json_encode($c) ?>)'>Edit Class</button>
                        <form method="POST" class="d-inline" onsubmit="return confirm('WARNING: This deletes the class AND all students inside it. Continue?')">
                            <input type="hidden" name="class_id" value="<?= $c['id'] ?>">
                            <button name="delete_class" class="btn btn-sm btn-link text-danger p-0"><i class="fa-solid fa-trash"></i></button>
                        </form>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0 small">
                        <thead class="table-light">
                            <tr><th class="ps-3">Roll</th><th>Student Name</th><th>Father Contact Address Photo</th><th class="text-end pe-3">Actions</th></tr>
                        </thead>
                        <tbody>
                            <?php
                            $st = $pdo->prepare("SELECT * FROM students WHERE class_id = ? ORDER BY roll_no ASC");
                            $st->execute([$c['id']]);
                            while($s = $st->fetch()):
                            ?>
                            <tr>
                                <td class="ps-3 fw-bold"><?= $s['roll_no'] ?></td>
                                <td><?= htmlspecialchars($s['student_name']) ?></td>
                                <td><?= htmlspecialchars($s['father_name']) ?> <?= $s['phone'] ?> <?= $s['address'] ?> <?php if($s['photo_path']) echo "<a href='$s[photo_path]' target='_blank'>View</a>"; ?></td>
                                <td class="text-end pe-3">
                                    <button class="btn btn-sm btn-light border" onclick='editStudent(<?= json_encode($s) ?>)'><i class="fa-solid fa-pen"></i></button>
                                    <form method="POST" class="d-inline" onsubmit="return confirm('Remove student?')">
                                        <input type="hidden" name="student_id" value="<?= $s['student_id'] ?>">
                                        <button name="delete_student" class="btn btn-sm btn-link text-danger"><i class="fa-solid fa-times"></i></button>
                                    </form>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<div class="modal fade" id="classModal" tabindex="-1">
    <div class="modal-dialog">
        <form method="POST" class="modal-content">
            <div class="modal-header"><h5>Class Configuration</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <input type="hidden" name="class_id" id="c_id">
                <div class="mb-3"><label class="small">Class Name</label><input type="text" name="class_name" id="c_name" class="form-control" placeholder="e.g. Class One" required></div>
                <div class="mb-3"><label class="small">Academic Session</label><input type="text" name="academic_year" id="c_year" class="form-control" value="<?= $search_year ?>" required></div>
                <div class="row">
                    <div class="col"><label class="small">Start Roll</label><input type="number" name="start_roll" id="c_start" class="form-control" value="1"></div>
                    <div class="col"><label class="small">End Roll</label><input type="number" name="end_roll" id="c_end" class="form-control" value="50"></div>
                </div>
            </div>
            <div class="modal-footer"><button type="submit" name="save_class" class="btn btn-primary w-100">Save Class Settings</button></div>
        </form>
    </div>
</div>

<div class="modal fade" id="promoteModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <form method="POST" class="modal-content">
            <div class="modal-header bg-warning"><h5>Promote/Clone Session</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <div class="row g-3 mb-4">
                    <div class="col-md-6"><label class="small">Source Year</label><input type="text" class="form-control" value="<?= $search_year ?>" readonly></div>
                    <div class="col-md-6"><label class="small">Target Year (New Session)</label><input type="text" name="target_year" class="form-control" value="<?= $search_year + 1 ?>" required></div>
                </div>
                <h6 class="border-bottom pb-2">Promotion Mapping</h6>
                <?php foreach($classes as $c): ?>
                <div class="row mb-3">
                    <div class="col-md-4 small fw-bold mt-2 text-primary"><?= $c['class_name'] ?> ➔</div>
                    <div class="col-md-8">
                        <div class="input-group input-group-sm">
                            <input type="text" name="class_mapping[<?= $c['id'] ?>][name]" class="form-control" placeholder="Next Class Name">
                            <input type="number" name="class_mapping[<?= $c['id'] ?>][start]" class="form-control" value="<?= $c['start_roll'] ?>">
                            <input type="number" name="class_mapping[<?= $c['id'] ?>][end]" class="form-control" value="<?= $c['end_roll'] ?>">
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <div class="modal-footer"><button type="submit" name="execute_promotion" class="btn btn-warning w-100">Execute Promotion</button></div>
        </form>
    </div>
</div>

<div class="modal fade" id="bulkAddModal" tabindex="-1">
    <div class="modal-dialog">
        <form method="POST" class="modal-content">
            <div class="modal-header bg-info"><h5>Bulk Roll Generator</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <div class="mb-3"><label class="small">Select Class</label>
                    <select name="target_class_id" class="form-select">
                        <?php foreach($classes as $c): ?><option value="<?= $c['id'] ?>"><?= $c['class_name'] ?></option><?php endforeach; ?>
                    </select>
                </div>
                <div class="row">
                    <div class="col"><input type="number" name="range_start" class="form-control" placeholder="Start Roll"></div>
                    <div class="col"><input type="number" name="range_end" class="form-control" placeholder="End Roll"></div>
                </div>
            </div>
            <div class="modal-footer"><button type="submit" name="bulk_add_students" class="btn btn-info w-100">Generate Slots</button></div>
        </form>
    </div>
</div>

<div class="modal fade" id="studentModal" tabindex="-1">
    <div class="modal-dialog">
        <form method="POST" enctype="multipart/form-data" class="modal-content">
            <div class="modal-header"><h5>Student Profile</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <input type="hidden" name="student_id" id="st_id">
                <input type="hidden" name="existing_photo" id="st_existing_photo">
                <div class="mb-3"><label class="small">Class</label>
                    <select name="class_id" id="st_class_id" class="form-select">
                        <?php foreach($classes as $c): ?><option value="<?= $c['id'] ?>"><?= $c['class_name'] ?></option><?php endforeach; ?>
                    </select>
                </div>
                <div class="row g-2 mb-3">
                    <div class="col-4"><input type="number" name="roll_no" id="st_roll" class="form-control" placeholder="Roll"></div>
                    <div class="col-8"><input type="text" name="student_name" id="st_name" class="form-control" placeholder="Student Name" required></div>
                </div>
                <input type="text" name="father_name" id="st_father" class="form-control mb-2" placeholder="Father's Name">
                <input type="text" name="phone" id="st_phone" class="form-control mb-2" placeholder="Phone">
                <textarea name="address" id="st_address" class="form-control mb-2" placeholder="Address"></textarea>
                <input type="file" name="photo" class="form-control">
            </div>
            <div class="modal-footer"><button type="submit" name="save_student" class="btn btn-primary w-100">Save Student</button></div>
        </form>
    </div>
</div>

<script>
function openAddClassModal() {
    document.getElementById('c_id').value = '';
    document.getElementById('c_name').value = '';
    new bootstrap.Modal(document.getElementById('classModal')).show();
}

function editClass(data) {
    document.getElementById('c_id').value = data.id;
    document.getElementById('c_name').value = data.class_name;
    document.getElementById('c_year').value = data.academic_year;
    document.getElementById('c_start').value = data.start_roll;
    document.getElementById('c_end').value = data.end_roll;
    new bootstrap.Modal(document.getElementById('classModal')).show();
}

function openAddStudentModal() {
    document.getElementById('st_id').value = '';
    document.getElementById('st_name').value = '';
    new bootstrap.Modal(document.getElementById('studentModal')).show();
}

function editStudent(data) {
    document.getElementById('st_id').value = data.student_id;
    document.getElementById('st_name').value = data.student_name;
    document.getElementById('st_roll').value = data.roll_no;
    document.getElementById('st_father').value = data.father_name;
    document.getElementById('st_phone').value = data.phone;
    document.getElementById('st_address').value = data.address;
    document.getElementById('st_class_id').value = data.class_id;
    document.getElementById('st_existing_photo').value = data.photo_path;
    new bootstrap.Modal(document.getElementById('studentModal')).show();
}
</script>

<?php require 'footer.php'; ?>