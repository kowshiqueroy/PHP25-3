<?php
require '../config/db.php';
require 'includes/header.php'; // Loads Sidebar, Navbar & CSS

// --- 1. Handle Add Student Logic ---
$msg = "";
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_student'])) {
    $name = $_POST['name'];
    $roll = $_POST['roll_no'];
    $class = $_POST['class_name'];
    $section = $_POST['section'];
    $fname = $_POST['father_name'];
    $phone = $_POST['phone'];
    $blood = $_POST['blood_group'];
    
    // Check for Duplicate Roll No in the same class
    $check = $pdo->prepare("SELECT id FROM students WHERE class_name=? AND roll_no=?");
    $check->execute([$class, $roll]);
    
    if($check->rowCount() > 0) {
        $msg = "<div class='alert' style='background:#fee2e2; color:#b91c1c;'>Error: Roll No $roll already exists in Class $class!</div>";
    } else {
        // Handle Photo Upload
        $image_path = "default.png";
        if (!empty($_FILES['student_photo']['name'])) {
            $ext = pathinfo($_FILES['student_photo']['name'], PATHINFO_EXTENSION);
            // Allow only images
            if(in_array(strtolower($ext), ['jpg', 'jpeg', 'png'])) {
                $new_name = "student_" . time() . "_" . rand(100,999) . "." . $ext;
                if(move_uploaded_file($_FILES['student_photo']['tmp_name'], "../assets/uploads/" . $new_name)) {
                    $image_path = $new_name;
                }
            }
        }

        // Insert into DB
        $sql = "INSERT INTO students (name, roll_no, class_name, section, father_name, phone, blood_group, image_path) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        if($stmt->execute([$name, $roll, $class, $section, $fname, $phone, $blood, $image_path])) {
            $msg = "<div class='alert' style='background:#dcfce7; color:#15803d;'>Student Admitted Successfully!</div>";
        }
    }
}

// --- 2. Handle Delete Logic ---
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    
    // Optional: Delete physical photo file to save space
    $stmt = $pdo->prepare("SELECT image_path FROM students WHERE id=?");
    $stmt->execute([$id]);
    $img = $stmt->fetchColumn();
    if($img && $img !== 'default.png' && file_exists("../assets/uploads/$img")) {
        unlink("../assets/uploads/$img");
    }

    $pdo->prepare("DELETE FROM students WHERE id=?")->execute([$id]);
    echo "<script>window.location.href='students.php';</script>";
}

// --- 3. Filtering Logic ---
$selected_class = isset($_GET['class_filter']) ? $_GET['class_filter'] : '';
$where_query = $selected_class ? "WHERE class_name = ?" : "";
$params = $selected_class ? [$selected_class] : [];

// Fetch Students
$stmt = $pdo->prepare("SELECT * FROM students $where_query ORDER BY class_name ASC, roll_no ASC");
$stmt->execute($params);
$students = $stmt->fetchAll();
?>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
    <h2>Manage Students</h2>
</div>

<div class="card">
    <h3 style="margin-top:0; border-bottom: 1px solid #eee; padding-bottom: 10px;">New Admission</h3>
    <?php echo $msg; ?>
    
    <form method="POST" enctype="multipart/form-data">
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px;">
            
            <div>
                <label style="display:block; margin-bottom:5px; font-weight:500;">Student Name</label>
                <input type="text" name="name" class="form-control" style="width:100%; padding:8px;" required>
            </div>
            
            <div>
                <label style="display:block; margin-bottom:5px; font-weight:500;">Father's Name</label>
                <input type="text" name="father_name" class="form-control" style="width:100%; padding:8px;" required>
            </div>

            <div>
                <label style="display:block; margin-bottom:5px; font-weight:500;">Class</label>
                <select name="class_name" class="form-control" style="width:100%; padding:8px;" required>
                    <option value="Nursery">Nursery</option>
                    <option value="KG">KG</option>
                    <option value="1">Class 1</option>
                    <option value="2">Class 2</option>
                    <option value="3">Class 3</option>
                    <option value="4">Class 4</option>
                    <option value="5">Class 5</option>
                    <option value="6">Class 6</option>
                    <option value="7">Class 7</option>
                    <option value="8">Class 8</option>
                    <option value="9 Science">Class 9 Science</option>
                    <option value="9 Arts">Class 9 Arts</option>
                    <option value="9 Commerce">Class 9 Commerce</option>
                    <option value="10 Science">Class 10 Science</option>
                    <option value="10 Arts">Class 10 Arts</option>
                    <option value="10 Commerce">Class 10 Commerce</option>
                    <option value="11 Science">Class 11 Science</option>
                    <option value="11 Arts">Class 11 Arts</option>
                    <option value="11 Commerce">Class 11 Commerce</option>
                    <option value="12 Science">Class 12 Science</option>
                    <option value="12 Arts">Class 12 Arts</option>
                    <option value="12 Commerce">Class 12 Commerce</option>
                </select>
            </div>

            <div>
                <label style="display:block; margin-bottom:5px; font-weight:500;">Section</label>
                <select name="section" class="form-control" style="width:100%; padding:8px;" required>
                    <option value="">-</option>
                    <option value="A">Section A</option>
                    <option value="B">Section B</option>
                  
                </select>
            </div>

            <div>
                <label style="display:block; margin-bottom:5px; font-weight:500;">Roll Number</label>
                <input type="number" name="roll_no" class="form-control" style="width:100%; padding:8px;" required>
            </div>

            <div>
                <label style="display:block; margin-bottom:5px; font-weight:500;">Phone Number</label>
                <input type="text" name="phone" class="form-control" style="width:100%; padding:8px;" placeholder="017...">
            </div>

            <div>
                <label style="display:block; margin-bottom:5px; font-weight:500;">Blood Group</label>
                <select name="blood_group" class="form-control" style="width:100%; padding:8px;">
                    <option value="">-</option>
                    <option value="A+">A+</option>
                    <option value="B+">B+</option>
                    <option value="O+">O+</option>
                    <option value="AB+">AB+</option>
                </select>
            </div>

            <div>
                <label style="display:block; margin-bottom:5px; font-weight:500;">Student Photo</label>
            <div>
                <input type="file" name="student_photo" class="form-control" accept="image/*" style="width:100%; padding:8px;" capture="camera" onclick="openCamera()">
                <script>
                    function openCamera() {
                        var input = document.querySelector('input[name="student_photo"]');
                        if (inputcapture) {
                            input.setAttribute('capture', 'environment');
                        } else {
                            input.setAttribute('capture', 'user');
                        }
                    }
                </script>
            </div>

        </div>
        
        <button type="submit" name="add_student" class="btn btn-primary" style="margin-top: 20px;">
            <i class="fas fa-save"></i> Save Student
        </button>
    </form>
</div>

<div class="card">
    <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #eee; padding-bottom: 15px; margin-bottom: 15px; flex-wrap: wrap; gap:10px;">
        <h3>Student List</h3>
        
        <form method="GET" style="display: flex; gap: 10px; align-items: center;">
            <select name="class_filter" style="padding: 8px; border: 1px solid #ddd; border-radius: 4px;">
                <option value="">Show All Classes</option>
                <option value="Nursery" <?php if($selected_class=='Nursery') echo 'selected'; ?>>Nursery</option>
                <option value="KG" <?php if($selected_class=='KG') echo 'selected'; ?>>KG</option>
                <option value="1" <?php if($selected_class=='1') echo 'selected'; ?>>Class 1</option>
                <option value="2" <?php if($selected_class=='2') echo 'selected'; ?>>Class 2</option>
                <option value="3" <?php if($selected_class=='3') echo 'selected'; ?>>Class 3</option>
                <option value="4" <?php if($selected_class=='4') echo 'selected'; ?>>Class 4</option>
                <option value="5" <?php if($selected_class=='5') echo 'selected'; ?>>Class 5</option>
                <option value="6" <?php if($selected_class=='6') echo 'selected'; ?>>Class 6</option>
                <option value="7" <?php if($selected_class=='7') echo 'selected'; ?>>Class 7</option>
                <option value="8" <?php if($selected_class=='8') echo 'selected'; ?>>Class 8</option>
                <option value="9 Science" <?php if($selected_class=='9 Science') echo 'selected'; ?>>Class 9 Science</option>
                <option value="9 Arts" <?php if($selected_class=='9 Arts') echo 'selected'; ?>>Class 9 Arts</option>
                <option value="9 Commerce" <?php if($selected_class=='9 Commerce') echo 'selected'; ?>>Class 9 Commerce</option>
                <option value="10 Science" <?php if($selected_class=='10 Science') echo 'selected'; ?>>Class 10 Science</option>
                <option value="10 Arts" <?php if($selected_class=='10 Arts') echo 'selected'; ?>>Class 10 Arts</option>
                <option value="10 Commerce" <?php if($selected_class=='10 Commerce') echo 'selected'; ?>>Class 10 Commerce</option>
                <option value="11 Science" <?php if($selected_class=='11 Science') echo 'selected'; ?>>Class 11 Science</option>
                <option value="11 Arts" <?php if($selected_class=='11 Arts') echo 'selected'; ?>>Class 11 Arts</option>
                <option value="11 Commerce" <?php if($selected_class=='11 Commerce') echo 'selected'; ?>>Class 11 Commerce</option>
                <option value="12 Science" <?php if($selected_class=='12 Science') echo 'selected'; ?>>Class 12 Science</option>
                <option value="12 Arts" <?php if($selected_class=='12 Arts') echo 'selected'; ?>>Class 12 Arts</option>
                <option value="12 Commerce" <?php if($selected_class=='12 Commerce') echo 'selected'; ?>>Class 12 Commerce</option>
            </select>
            <button type="submit" class="btn btn-primary" style="padding: 8px 15px;">Filter</button>
        </form>
    </div>

    <div style="overflow-x: auto;">
        <table style="width: 100%; border-collapse: collapse; min-width: 600px;">
            <thead>
                <tr style="background: #f8fafc; border-bottom: 2px solid #e2e8f0;">
                    <th style="padding: 12px; text-align: left;">Details</th>
                    <th style="padding: 12px; text-align: left;">Class Info</th>
                    <th style="padding: 12px; text-align: left;">Contact</th>
                    <th style="padding: 12px; text-align: left;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if(count($students) > 0): ?>
                    <?php foreach($students as $s): ?>
                    <tr style="border-bottom: 1px solid #eee;">
                        <td style="padding: 12px; display: flex; align-items: center; gap: 15px;">
                            <img src="../assets/uploads/<?php echo $s['image_path'] ? $s['image_path'] : 'default.png'; ?>" 
                                 style="width: 45px; height: 45px; border-radius: 50%; object-fit: cover; border: 1px solid #ddd;">
                            <div>
                                <div style="font-weight: bold; color: #333;"><?php echo htmlspecialchars($s['name']); ?></div>
                                <div style="font-size: 0.85rem; color: #666;">F: <?php echo htmlspecialchars($s['father_name']); ?></div>
                            </div>
                        </td>
                        
                        <td style="padding: 12px;">
                            <span style="background: #e0f2fe; color: #0284c7; padding: 3px 8px; border-radius: 4px; font-weight: 600; font-size: 0.9rem;">
                                Class <?php echo $s['class_name']; ?>
                            </span>
                            <div style="font-size: 0.85rem; color: #666; margin-top: 4px;">
                                Roll: <strong><?php echo $s['roll_no']; ?></strong> | Sec: <?php echo $s['section']; ?>
                            </div>
                        </td>
                        
                        <td style="padding: 12px;">
                            <i class="fas fa-phone" style="font-size: 0.8rem; color: #666;"></i> <?php echo $s['phone']; ?>
                            <?php if($s['blood_group']): ?>
                                <br><small style="color: #ef4444; font-weight: bold;"><?php echo $s['blood_group']; ?></small>
                            <?php endif; ?>
                        </td>
                        
                        <td style="padding: 12px;">
                            <a href="?delete=<?php echo $s['id']; ?>" 
                               onclick="return confirm('Delete this student? This cannot be undone.')"
                               style="color: #ef4444; text-decoration: none; padding: 5px; font-size: 1.1rem;">
                                <i class="fas fa-trash-alt"></i>
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="4" style="text-align: center; padding: 30px; color: #888;">No students found in this category.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require 'includes/footer.php'; ?>