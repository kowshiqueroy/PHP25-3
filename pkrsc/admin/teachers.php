<?php
require '../config/db.php';
require 'includes/header.php';

// --- 1. Handle Add Teacher ---
$msg = "";
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_teacher'])) {
    $name = $_POST['name'];
    $desig = $_POST['designation'];
    $subj = $_POST['subject'];
    $phone = $_POST['phone'];
    $order = $_POST['sort_order'];
    $education = $_POST['education'];
    $image_path = "default.png"; // Default image if none uploaded

    // Image Upload Logic
    if (!empty($_FILES['photo']['name'])) {
        $ext = pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION);
        $new_name = "teacher_" . time() . "." . $ext;
        move_uploaded_file($_FILES['photo']['tmp_name'], "../assets/uploads/" . $new_name);
        $image_path = $new_name;
    }

    $stmt = $pdo->prepare("INSERT INTO teachers (name, designation, education, subject, phone, image_path, sort_order) VALUES (?, ?, ?, ?, ?, ?, ?)");
    if($stmt->execute([$name, $desig, $education, $subj, $phone, $image_path, $order])) {
        $msg = "<div class='alert alert-success'>Teacher added successfully!</div>";
    }
}

// --- 2. Handle Delete ---
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    // Delete image file first to save space
    $stmt = $pdo->prepare("SELECT image_path FROM teachers WHERE id=?");
    $stmt->execute([$id]);
    $img = $stmt->fetchColumn();
    if($img && $img != 'default.png' && file_exists("../assets/uploads/$img")) {
        unlink("../assets/uploads/$img");
    }
    
    $pdo->prepare("DELETE FROM teachers WHERE id=?")->execute([$id]);
    echo "<script>window.location.href='teachers.php';</script>";
}

// Fetch Teachers
$teachers = $pdo->query("SELECT * FROM teachers ORDER BY sort_order ASC")->fetchAll();
?>

<h2 style="margin-bottom: 20px;">Manage Teachers</h2>

<div class="card">
    <h3>Add New Teacher</h3>
    <?php echo $msg; ?>
    <form method="POST" enctype="multipart/form-data">
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px;">
            <div>
                <label class="form-label" style="display:block; margin-bottom:5px; font-weight:500;">Name</label>
                <input type="text" name="name" class="form-control" required style="width:100%; padding:8px;">
            </div>
              <div>
                <label class="form-label" style="display:block; margin-bottom:5px; font-weight:500;">Education</label>
                <input type="text" name="education" class="form-control" placeholder="e.g. B.Sc, M.Sc in CSE @ JU" style="width:100%; padding:8px;">
            </div>
            <div>
                <label class="form-label" style="display:block; margin-bottom:5px; font-weight:500;">Designation</label>
                <select name="designation" class="form-control" required style="width:100%; padding:8px;">
                   
              <option value="সহকারী শিক্ষক">সহকারী শিক্ষক</option>
<option value="শিক্ষানবিশ শিক্ষক">শিক্ষানবিশ শিক্ষক</option>
<option value="সহকারী প্রশিক্ষক">সহকারী প্রশিক্ষক</option>
<option value="প্রশিক্ষক">প্রশিক্ষক</option>
<option value="প্রদর্শক">প্রদর্শক</option>
<option value="প্রফেসর">প্রফেসর</option>
<option value="লেকচারার">লেকচারার</option>
<option value="সহযোগী প্রফেসর">সহযোগী প্রফেসর</option>
<option value="সহকারী প্রফেসর">সহকারী প্রফেসর</option>
<option value="প্রফেসর">প্রফেসর</option>
<option value="শিক্ষক">শিক্ষক</option>
<option value="প্রধান শিক্ষক">প্রধান শিক্ষক</option>
<option value="স্টাফ">স্টাফ</option>
<option value="অধ্যক্ষ">অধ্যক্ষ</option>
<option value="সহকারী অধ্যক্ষ">সহকারী অধ্যক্ষ</option>
<option value="অধ্যক্ষ ও সভাপতি">অধ্যক্ষ ও সভাপতি</option>
<option value="সভাপতি">সভাপতি</option>
<option value="কমিটি সদস্য">কমিটি সদস্য</option>


                </select>

            </div>
          
            <div>
                <label class="form-label" style="display:block; margin-bottom:5px; font-weight:500;">Subject</label>
                <input type="text" name="subject" class="form-control" placeholder="e.g. Math" style="width:100%; padding:8px;">
            </div>
            <div>
                <label class="form-label" style="display:block; margin-bottom:5px; font-weight:500;">Phone</label>
                <input type="text" name="phone" class="form-control" style="width:100%; padding:8px;">
            </div>
            <div>
                <label class="form-label" style="display:block; margin-bottom:5px; font-weight:500;">Sort Order</label>
                <input type="number" name="sort_order" class="form-control" value="10" style="width:100%; padding:8px;">
            </div>
            <div>
                <label class="form-label    " style="display:block; margin-bottom:5px; font-weight:500;">Photo</label>
                <div style="display: flex; align-items: center;">
                    <input type="file" name="photo" class="form-control" style="width:100%; padding:8px;" accept="image/*" capture="camera" onclick="openCamera()">
                </div>
                <script>
                    function openCamera() {
                        var input = document.querySelector('input[name="photo"]');
                        if (input.capture) {
                            input.setAttribute('capture', 'environment');
                        } else {
                            input.setAttribute('capture', 'user');
                        }
                    }
                </script>
            </div>
        </div>
        <button type="submit" name="add_teacher" class="btn btn-primary" style="margin-top: 15px;">
            <i class="fas fa-plus-circle"></i> Add Teacher
        </button>
    </form>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="custom-table">
            <thead>
                <tr>
                    <th>Photo</th>
                    <th>Name / Designation</th>
                    <th>Education</th>
                    <th>Phone</th>
                    <th>Order</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($teachers as $t): ?>
                <tr>
                    <td>
                        <img src="../assets/uploads/<?php echo $t['image_path'] ?? 'default.png'; ?>" 
                             style="width: 50px; height: 50px; border-radius: 50%; object-fit: cover; border: 2px solid #eee;">
                    </td>
                    <td>
                        <strong><?php echo htmlspecialchars($t['name']); ?></strong><br>
                        <small style="color: #666;"><?php echo htmlspecialchars($t['designation']).' - '.htmlspecialchars($t['subject']); ?></small>
                    </td>
                    <td><?php echo htmlspecialchars($t['education']); ?></td>
                    <td><?php echo htmlspecialchars($t['phone']); ?></td>
                    <td><?php echo $t['sort_order']; ?></td>
                    <td>
                        <a href="?delete=<?php echo $t['id']; ?>" class="btn btn-danger" onclick="return confirm('Delete this teacher?')">
                            <i class="fas fa-trash"></i>
                        </a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require 'includes/footer.php'; ?>