<?php
require '../config/db.php';
require 'includes/header.php';

$action = $_GET['action'] ?? 'list';
$id = $_GET['id'] ?? 0;
$class_filter = $_GET['class_filter'] ?? '';
$error = '';
$success = '';

// Handle Add/Edit Student
if ($_SERVER['REQUEST_METHOD'] == 'POST' && ($action === 'add' || $action === 'edit')) {
    $name = trim($_POST['name']);
    $roll_no = trim($_POST['roll_no']);
    $class_name = trim($_POST['class_name']);
    $section = trim($_POST['section']);
    $father_name = trim($_POST['father_name']);
    $phone = trim($_POST['phone']);
    $blood_group = trim($_POST['blood_group']);
    $image_path = $_POST['existing_image'] ?? 'default.png';

    if (empty($name) || empty($roll_no) || empty($class_name)) {
        $error = 'Name, Roll No, and Class are required.';
    } else {
        // Check for duplicate roll number in the same class
        $stmt = $pdo->prepare("SELECT id FROM students WHERE class_name = ? AND roll_no = ? AND id != ?");
        $stmt->execute([$class_name, $roll_no, $id]);
        if ($stmt->rowCount() > 0) {
            $error = "Roll No {$roll_no} already exists in {$class_name}.";
        } else {
             // Handle image upload
            if (!empty($_FILES['photo']['name'])) {
                $target_dir = "../assets/uploads/";
                $image_name = "student_" . time() . "_" . basename($_FILES["photo"]["name"]);
                $target_file = $target_dir . $image_name;
                $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

                $check = getimagesize($_FILES["photo"]["tmp_name"]);
                if($check === false) {
                    $error = "File is not an image.";
                } elseif ($_FILES["photo"]["size"] > 5000000) {
                    $error = "Sorry, your file is too large.";
                } elseif($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg" && $imageFileType != "gif" ) {
                    $error = "Sorry, only JPG, JPEG, PNG & GIF files are allowed.";
                } else {
                    if (move_uploaded_file($_FILES["photo"]["tmp_name"], $target_file)) {
                        if ($action === 'edit' && $image_path !== 'default.png' && file_exists($target_dir . $image_path)) {
                            unlink($target_dir . $image_path);
                        }
                        $image_path = $image_name;
                    } else {
                        $error = "Sorry, there was an error uploading your file.";
                    }
                }
            }
            if(empty($error)){
                if ($action === 'add') {
                    $stmt = $pdo->prepare("INSERT INTO students (name, roll_no, class_name, section, father_name, phone, blood_group, image_path) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
                    $stmt->execute([$name, $roll_no, $class_name, $section, $father_name, $phone, $blood_group, $image_path]);
                } elseif ($action === 'edit' && $id) {
                    $stmt = $pdo->prepare("UPDATE students SET name=?, roll_no=?, class_name=?, section=?, father_name=?, phone=?, blood_group=?, image_path=? WHERE id=?");
                    $stmt->execute([$name, $roll_no, $class_name, $section, $father_name, $phone, $blood_group, $image_path, $id]);
                }
                header("Location: students.php?class_filter=$class_name");
                exit();
            }
        }
    }
}

// Handle Delete Student
if ($action === 'delete' && $id) {
    $stmt = $pdo->prepare("SELECT image_path FROM students WHERE id = ?");
    $stmt->execute([$id]);
    $image_to_delete = $stmt->fetchColumn();

    if ($image_to_delete && $image_to_delete !== 'default.png' && file_exists("../assets/uploads/" . $image_to_delete)) {
        unlink("../assets/uploads/" . $image_to_delete);
    }

    $stmt = $pdo->prepare("DELETE FROM students WHERE id = ?");
    $stmt->execute([$id]);
    
    header("Location: students.php" . ($class_filter ? "?class_filter=$class_filter" : ""));
    exit();
}

// Fetch student for editing
$student = null;
if ($action === 'edit' && $id) {
    $stmt = $pdo->prepare("SELECT * FROM students WHERE id = ?");
    $stmt->execute([$id]);
    $student = $stmt->fetch();
}

// Fetch students with filter
$sql = "SELECT * FROM students";
$params = [];
if ($class_filter) {
    $sql .= " WHERE class_name = ?";
    $params[] = $class_filter;
}
$sql .= " ORDER BY class_name ASC, roll_no ASC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$students = $stmt->fetchAll();

// Get all unique class names for the filter dropdown
$classes = $pdo->query("SELECT DISTINCT class_name FROM students ORDER BY class_name")->fetchAll(PDO::FETCH_COLUMN);

?>

<h2 style="margin-bottom: 20px;">Manage Students</h2>

<?php if ($action === 'add' || $action === 'edit'): ?>
    <div class="card">
        <h3><?php echo ucfirst($action); ?> Student</h3>
        <?php if ($error) echo "<div class='alert alert-danger'>$error</div>"; ?>
        <form method="POST" action="students.php?action=<?php echo $action; ?><?php if ($id) echo '&id=' . $id; ?>" enctype="multipart/form-data">
            <input type="hidden" name="existing_image" value="<?php echo htmlspecialchars($student['image_path'] ?? ''); ?>">
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px;">
                <!-- Form fields -->
                <div><label>Name</label><input type="text" name="name" class="form-control" value="<?php echo htmlspecialchars($student['name'] ?? ''); ?>" required></div>
                <div><label>Roll No</label><input type="text" name="roll_no" class="form-control" value="<?php echo htmlspecialchars($student['roll_no'] ?? ''); ?>" required></div>
                <div><label>Class</label><input type="text" name="class_name" class="form-control" value="<?php echo htmlspecialchars($student['class_name'] ?? ''); ?>" required></div>
                <div><label>Section</label><input type="text" name="section" class="form-control" value="<?php echo htmlspecialchars($student['section'] ?? ''); ?>"></div>
                <div><label>Father's Name</label><input type="text" name="father_name" class="form-control" value="<?php echo htmlspecialchars($student['father_name'] ?? ''); ?>"></div>
                <div><label>Phone</label><input type="text" name="phone" class="form-control" value="<?php echo htmlspecialchars($student['phone'] ?? ''); ?>"></div>
                <div><label>Blood Group</label><input type="text" name="blood_group" class="form-control" value="<?php echo htmlspecialchars($student['blood_group'] ?? ''); ?>"></div>
                <div>
                    <label>Photo</label>
                    <input type="file" name="photo" class="form-control">
                    <?php if ($action === 'edit' && !empty($student['image_path'])): ?>
                        <img src="../assets/uploads/<?php echo htmlspecialchars($student['image_path']); ?>" width="100" class="mt-2">
                    <?php endif; ?>
                </div>
            </div>
            <button type="submit" class="btn btn-primary mt-3"><?php echo ucfirst($action); ?> Student</button>
            <a href="students.php" class="btn btn-secondary mt-3">Cancel</a>
        </form>
    </div>
<?php endif; ?>

<div class="card">
    <a href="students.php?action=add" class="btn btn-primary mb-3"><i class="fas fa-plus-circle"></i> Add New Student</a>
    
    <form method="GET" class="mb-3">
        <div class="row">
            <div class="col-md-4">
                <select name="class_filter" class="form-control" onchange="this.form.submit()">
                    <option value="">-- Filter by Class --</option>
                    <?php foreach ($classes as $class): ?>
                        <option value="<?php echo htmlspecialchars($class); ?>" <?php if ($class_filter === $class) echo 'selected'; ?>>
                            <?php echo htmlspecialchars($class); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
    </form>

    <div class="table-responsive">
        <table class="custom-table">
            <thead>
                <tr>
                    <th>Photo</th>
                    <th>Name / Father's Name</th>
                    <th>Class / Roll</th>
                    <th>Contact</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($students as $s): ?>
                <tr>
                    <td><img src="../assets/uploads/<?php echo htmlspecialchars($s['image_path'] ?? 'default.png'); ?>" style="width: 50px; height: 50px; border-radius: 50%; object-fit: cover;"></td>
                    <td><strong><?php echo htmlspecialchars($s['name']); ?></strong><br><small><?php echo htmlspecialchars($s['father_name']); ?></small></td>
                    <td>Class <?php echo htmlspecialchars($s['class_name']); ?><br><small>Roll: <?php echo htmlspecialchars($s['roll_no']); ?></small></td>
                    <td><?php echo htmlspecialchars($s['phone']); ?></td>
                    <td>
                        <a href="students.php?action=edit&id=<?php echo $s['id']; ?>" class="btn btn-sm btn-info"><i class="fas fa-edit"></i></a>
                        <a href="students.php?action=delete&id=<?php echo $s['id']; ?>&class_filter=<?php echo $class_filter; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure?')"><i class="fas fa-trash"></i></a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require 'includes/footer.php'; ?>
