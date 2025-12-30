<?php
require 'header.php';

$msg = '';
$error = '';

// --- FOLDER SETUP ---
if (!is_dir('uploads')) {
    mkdir('uploads', 0777, true);
}

// --- LOGIC: BULK CLONE ---
if (isset($_POST['bulk_clone'])) {
    $from_class = $_POST['from_class_id'];
    $to_class = $_POST['to_class_id'];

    if ($from_class == $to_class) {
        $error = "Source and destination classes cannot be the same.";
    } else {
        $stmt = $pdo->prepare("INSERT INTO students (class_id, roll_no, student_name, father_name, address, phone, photo_path) 
                                SELECT ?, roll_no, student_name, father_name, address, phone, photo_path 
                                FROM students WHERE class_id = ?");
        if ($stmt->execute([$to_class, $from_class])) {
            $msg = "Class students cloned successfully!";
        }
    }
}

// --- LOGIC: ADD/EDIT STUDENT ---
if (isset($_POST['save_student'])) {
    $id = $_POST['student_id'] ?? null;
    $class_id = $_POST['class_id'];
    $name = $_POST['student_name'];
    $roll = $_POST['roll_no'];
    $father = $_POST['father_name'];
    $address = $_POST['address'];
    $phone = $_POST['phone'];
    
    // Handle Photo (Upload or Webcam Data)
    $photo_path = $_POST['existing_photo'] ?? '';
    
    if (!empty($_FILES['photo']['name'])) {
        $photo_path = "uploads/" . time() . "_" . $_FILES['photo']['name'];
        move_uploaded_file($_FILES['photo']['tmp_name'], $photo_path);
    } elseif (!empty($_POST['webcam_image'])) {
        $img = $_POST['webcam_image'];
        $img = str_replace('data:image/jpeg;base64,', '', $img);
        $data = base64_decode($img);
        $photo_path = "uploads/" . time() . "_cam.jpg";
        file_put_contents($photo_path, $data);
    }

    if ($id) {
        $stmt = $pdo->prepare("UPDATE students SET class_id=?, roll_no=?, student_name=?, father_name=?, address=?, phone=?, photo_path=? WHERE student_id=?");
        $stmt->execute([$class_id, $roll, $name, $father, $address, $phone, $photo_path, $id]);
        $msg = "Student updated successfully.";
    } else {
        $stmt = $pdo->prepare("INSERT INTO students (class_id, roll_no, student_name, father_name, address, phone, photo_path) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$class_id, $roll, $name, $father, $address, $phone, $photo_path]);
        $msg = "Student added successfully.";
    }
}

// --- LOGIC: DELETE STUDENT ---
if (isset($_POST['delete_student_id'])) {
    $stmt = $pdo->prepare("DELETE FROM students WHERE student_id = ?");
    $stmt->execute([$_POST['delete_student_id']]);
    $msg = "Student removed.";
}

// Fetch Classes and Students
$classes = $pdo->query("SELECT * FROM classes ORDER BY academic_year DESC, class_name ASC")->fetchAll();
$students = $pdo->query("SELECT s.*, c.class_name, c.academic_year FROM students s JOIN classes c ON s.class_id = c.id ORDER BY c.academic_year DESC, s.roll_no ASC")->fetchAll();
?>

<div class="row mb-4">
    <div class="col-md-8">
        <h2 class="fw-bold"><i class="fa-solid fa-users text-primary"></i> Student Management</h2>
    </div>
    <div class="col-md-4 text-end">
        <button class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#cloneModal">
            <i class="fa-solid fa-copy"></i> Bulk Clone Class
        </button>
    </div>
</div>

<?php if($error) echo "<div class='alert alert-danger'>$error</div>"; ?>
<?php if($msg) echo "<div class='alert alert-success'>$msg</div>"; ?>

<div class="row">
    <div class="col-md-4">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white fw-bold">Add/Edit Student</div>
            <div class="card-body">
                <form method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="student_id" id="st_id">
                    <input type="hidden" name="existing_photo" id="st_existing_photo">
                    
                    <div class="mb-2">
                        <label class="small fw-bold">Select Class</label>
                        <select name="class_id" id="st_class_id" class="form-select" required>
                            <?php foreach($classes as $c): ?>
                                <option value="<?= $c['id'] ?>"><?= $c['class_name'] ?> (<?= $c['academic_year'] ?>)</option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="row">
                        <div class="col-8 mb-2">
                            <label class="small fw-bold">Full Name</label>
                            <input type="text" name="student_name" id="st_name" class="form-control" required>
                        </div>
                        <div class="col-4 mb-2">
                            <label class="small fw-bold">Roll</label>
                            <input type="number" name="roll_no" id="st_roll" class="form-control" required>
                        </div>
                    </div>
                    <div class="mb-2">
                        <label class="small fw-bold">Father's Name</label>
                        <input type="text" name="father_name" id="st_father" class="form-control">
                    </div>
                    <div class="mb-2">
                        <label class="small fw-bold">Phone</label>
                        <input type="text" name="phone" id="st_phone" class="form-control">
                    </div>
                    <div class="mb-2">
                        <label class="small fw-bold">Address</label>
                        <textarea name="address" id="st_address" class="form-control" rows="2"></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label class="small fw-bold">Photo</label>
                        <input type="file" name="photo" class="form-control form-control-sm mb-2">
                        <div class="d-flex gap-2">
                            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="startWebcam()">Take Photo</button>
                            <div id="webcam_container" style="display:none;">
                                <video id="video" width="100%" autoplay class="rounded border mb-1"></video>
                                <button type="button" class="btn btn-sm btn-danger" onclick="capture()">Capture</button>
                                <input type="hidden" name="webcam_image" id="webcam_image">
                            </div>
                        </div>
                    </div>
                    
                    <button type="submit" name="save_student" class="btn btn-primary w-100">Save Student Info</button>
                    <button type="button" onclick="location.reload()" class="btn btn-link w-100 mt-1 btn-sm">Reset Form</button>
                </form>
            </div>
        </div>
    </div>

    <div class="col-md-8">
        <div class="card shadow-sm border-0">
            <div class="card-body p-0">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-3">Photo</th>
                            <th>Student / Father</th>
                            <th>Class (Year)</th>
                            <th>Roll</th>
                            <th class="text-end pe-3">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($students as $s): ?>
                        <tr>
                            <td class="ps-3">
                                <img src="<?= $s['photo_path'] ?: 'https://via.placeholder.com/40' ?>" width="40" height="40" class="rounded-circle object-fit-cover border">
                            </td>
                            <td>
                                <strong><?= htmlspecialchars($s['student_name']) ?></strong><br>
                                <small class="text-muted"><?= htmlspecialchars($s['father_name']) ?></small>
                            </td>
                            <td><?= $s['class_name'] ?> (<?= $s['academic_year'] ?>)</td>
                            <td><span class="badge bg-light text-dark border"><?= $s['roll_no'] ?></span></td>
                            <td class="text-end pe-3">
                                <button class="btn btn-sm btn-outline-primary" onclick='editStudent(<?= json_encode($s) ?>)'>
                                    <i class="fa-solid fa-edit"></i>
                                </button>
                                <form method="POST" class="d-inline" onsubmit="return confirm('Delete student?')">
                                    <input type="hidden" name="delete_student_id" value="<?= $s['student_id'] ?>">
                                    <button class="btn btn-sm btn-outline-danger"><i class="fa-solid fa-trash"></i></button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="cloneModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form method="POST" class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Bulk Clone Students</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p class="small text-muted">Use this to copy all student names and details from an old session to a new session.</p>
                <div class="mb-3">
                    <label>Clone FROM (Old Class)</label>
                    <select name="from_class_id" class="form-select" required>
                        <?php foreach($classes as $c): ?>
                            <option value="<?= $c['id'] ?>"><?= $c['class_name'] ?> - <?= $c['academic_year'] ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="mb-3 text-center"><i class="fa-solid fa-arrow-down"></i></div>
                <div class="mb-3">
                    <label>Clone TO (Target Class/Year)</label>
                    <select name="to_class_id" class="form-select" required>
                        <?php foreach($classes as $c): ?>
                            <option value="<?= $c['id'] ?>"><?= $c['class_name'] ?> - <?= $c['academic_year'] ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="submit" name="bulk_clone" class="btn btn-warning w-100">Start Cloning Students</button>
            </div>
        </form>
    </div>
</div>

<script>
function editStudent(data) {
    document.getElementById('st_id').value = data.student_id;
    document.getElementById('st_name').value = data.student_name;
    document.getElementById('st_roll').value = data.roll_no;
    document.getElementById('st_father').value = data.father_name;
    document.getElementById('st_phone').value = data.phone;
    document.getElementById('st_address').value = data.address;
    document.getElementById('st_class_id').value = data.class_id;
    document.getElementById('st_existing_photo').value = data.photo_path;
    window.scrollTo(0, 0);
}

// Simple Webcam Capture
function startWebcam() {
    const container = document.getElementById('webcam_container');
    container.style.display = 'block';
    const video = document.getElementById('video');
    navigator.mediaDevices.getUserMedia({ video: true }).then(stream => { video.srcObject = stream; });
}

function capture() {
    const video = document.getElementById('video');
    const canvas = document.createElement('canvas');
    canvas.width = video.videoWidth;
    canvas.height = video.videoHeight;
    canvas.getContext('2d').drawImage(video, 0, 0);
    document.getElementById('webcam_image').value = canvas.toDataURL('image/jpeg');
    alert('Photo captured!');
    const stream = video.srcObject;
    stream.getTracks().forEach(track => track.stop());
    document.getElementById('webcam_container').style.display = 'none';
}
</script>

<?php require 'footer.php'; ?>