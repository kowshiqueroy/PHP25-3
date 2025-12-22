<?php
require '../config/db.php';
require 'includes/header.php';

$action = $_GET['action'] ?? 'list';
$id = $_GET['id'] ?? 0;
$error = '';
$success = '';

// Handle Add/Edit Teacher
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = trim($_POST['name']);
    $designation = trim($_POST['designation']);
    $education = trim($_POST['education']);
    $subject = trim($_POST['subject']);
    $phone = trim($_POST['phone']);
    $sort_order = filter_input(INPUT_POST, 'sort_order', FILTER_VALIDATE_INT);
    $image_path = $_POST['existing_image'] ?? 'default.png';

    if (empty($name) || empty($designation)) {
        $error = 'Name and designation are required.';
    } else {
        // Handle image upload
        if (!empty($_FILES['photo']['name'])) {
            $target_dir = "../assets/uploads/";
            $image_name = "teacher_" . time() . "_" . basename($_FILES["photo"]["name"]);
            $target_file = $target_dir . $image_name;
            $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

            // Check if image file is a actual image or fake image
            $check = getimagesize($_FILES["photo"]["tmp_name"]);
            if($check === false) {
                $error = "File is not an image.";
            }
            // Check file size (5MB max)
            elseif ($_FILES["photo"]["size"] > 5000000) {
                $error = "Sorry, your file is too large.";
            }
            // Allow certain file formats
            elseif($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg" && $imageFileType != "gif" ) {
                $error = "Sorry, only JPG, JPEG, PNG & GIF files are allowed.";
            } else {
                if (move_uploaded_file($_FILES["photo"]["tmp_name"], $target_file)) {
                    // Delete old image if it's not the default
                    if ($action === 'edit' && $image_path !== 'default.png' && file_exists($target_dir . $image_path)) {
                        unlink($target_dir . $image_path);
                    }
                    $image_path = $image_name;
                } else {
                    $error = "Sorry, there was an error uploading your file.";
                }
            }
        }

        if (empty($error)) {
            if ($action === 'add') {
                $stmt = $pdo->prepare("INSERT INTO teachers (name, designation, education, subject, phone, image_path, sort_order) VALUES (?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([$name, $designation, $education, $subject, $phone, $image_path, $sort_order]);
                $success = 'Teacher added successfully!';
            } elseif ($action === 'edit' && $id) {
                $stmt = $pdo->prepare("UPDATE teachers SET name=?, designation=?, education=?, subject=?, phone=?, image_path=?, sort_order=? WHERE id=?");
                $stmt->execute([$name, $designation, $education, $subject, $phone, $image_path, $sort_order, $id]);
                $success = 'Teacher updated successfully!';
            }
            // Redirect to prevent form resubmission
            header("Location: teachers.php");
            exit();
        }
    }
}

// Handle Delete Teacher
if ($action === 'delete' && $id) {
    // First, get the image path to delete the file
    $stmt = $pdo->prepare("SELECT image_path FROM teachers WHERE id = ?");
    $stmt->execute([$id]);
    $image_to_delete = $stmt->fetchColumn();

    if ($image_to_delete && $image_to_delete !== 'default.png' && file_exists("../assets/uploads/" . $image_to_delete)) {
        unlink("../assets/uploads/" . $image_to_delete);
    }

    $stmt = $pdo->prepare("DELETE FROM teachers WHERE id = ?");
    $stmt->execute([$id]);
    
    header("Location: teachers.php");
    exit();
}

// Fetch teacher for editing
$teacher = null;
if ($action === 'edit' && $id) {
    $stmt = $pdo->prepare("SELECT * FROM teachers WHERE id = ?");
    $stmt->execute([$id]);
    $teacher = $stmt->fetch();
}

// Fetch all teachers
$teachers = $pdo->query("SELECT * FROM teachers ORDER BY sort_order ASC")->fetchAll();
?>

<h2 style="margin-bottom: 20px;">Manage Teachers</h2>

<?php if ($action === 'add' || $action === 'edit'): ?>
    <div class="card">
        <h3><?php echo ucfirst($action); ?> Teacher</h3>
        <?php if ($error) echo "<div class='alert alert-danger'>$error</div>"; ?>
        <form method="POST" action="teachers.php?action=<?php echo $action; ?><?php if ($id) echo '&id=' . $id; ?>" enctype="multipart/form-data">
             <input type="hidden" name="existing_image" value="<?php echo htmlspecialchars($teacher['image_path'] ?? ''); ?>">
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px;">
                <!-- Form fields -->
                <div>
                    <label>Name</label>
                    <input type="text" name="name" class="form-control" value="<?php echo htmlspecialchars($teacher['name'] ?? ''); ?>" required>
                </div>
                <div>
                    <label>Designation</label>
                    <input type="text" name="designation" class="form-control" value="<?php echo htmlspecialchars($teacher['designation'] ?? ''); ?>" required>
                </div>
                <div>
                    <label>Education</label>
                    <input type="text" name="education" class="form-control" value="<?php echo htmlspecialchars($teacher['education'] ?? ''); ?>">
                </div>
                <div>
                    <label>Subject</label>
                    <input type="text" name="subject" class="form-control" value="<?php echo htmlspecialchars($teacher['subject'] ?? ''); ?>">
                </div>
                <div>
                    <label>Phone</label>
                    <input type="text" name="phone" class="form-control" value="<?php echo htmlspecialchars($teacher['phone'] ?? ''); ?>">
                </div>
                <div>
                    <label>Sort Order</label>
                    <input type="number" name="sort_order" class="form-control" value="<?php echo htmlspecialchars($teacher['sort_order'] ?? '10'); ?>">
                </div>
                <div>
                    <label>Photo</label>
                    <input type="file" name="photo" class="form-control">
                    <?php if ($action === 'edit' && !empty($teacher['image_path'])): ?>
                        <img src="../assets/uploads/<?php echo htmlspecialchars($teacher['image_path']); ?>" width="100" class="mt-2">
                    <?php endif; ?>
                </div>
            </div>
            <button type="submit" class="btn btn-primary mt-3"><?php echo ucfirst($action); ?> Teacher</button>
            <a href="teachers.php" class="btn btn-secondary mt-3">Cancel</a>
        </form>
    </div>
<?php endif; ?>

<div class="card">
    <a href="teachers.php?action=add" class="btn btn-primary mb-3"><i class="fas fa-plus-circle"></i> Add New Teacher</a>
    <div class="table-responsive">
        <table class="custom-table">
            <thead>
                <tr>
                    <th>Photo</th>
                    <th>Name / Designation</th>
                    <th>Contact</th>
                    <th>Order</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($teachers as $t): ?>
                <tr>
                    <td><img src="../assets/uploads/<?php echo htmlspecialchars($t['image_path'] ?? 'default.png'); ?>" style="width: 50px; height: 50px; border-radius: 50%; object-fit: cover;"></td>
                    <td>
                        <strong><?php echo htmlspecialchars($t['name']); ?></strong><br>
                        <small><?php echo htmlspecialchars($t['designation']); ?></small>
                    </td>
                    <td><?php echo htmlspecialchars($t['phone']); ?></td>
                    <td><?php echo htmlspecialchars($t['sort_order']); ?></td>
                    <td>
                        <a href="teachers.php?action=edit&id=<?php echo $t['id']; ?>" class="btn btn-sm btn-info"><i class="fas fa-edit"></i></a>
                        <a href="teachers.php?action=delete&id=<?php echo $t['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this teacher?')"><i class="fas fa-trash"></i></a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require 'includes/footer.php'; ?>
