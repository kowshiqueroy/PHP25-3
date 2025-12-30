<?php
require 'header.php';

$msg = ''; $error = '';

// 1. Fetch all available sessions for the dropdown
$sessions = $pdo->query("SELECT DISTINCT academic_year FROM classes ORDER BY academic_year DESC")->fetchAll(PDO::FETCH_COLUMN);

// 2. Handle the Update Logic
if (isset($_POST['update_students'])) {
    $class_id = $_POST['class_id'];
    $student_data = $_POST['student']; // Array: [student_id => [fields]]

    try {
        $pdo->beginTransaction();

        // STEP A: Temporary Roll Shift (to avoid unique constraint violations during swapping)
        $temp_stmt = $pdo->prepare("UPDATE students SET roll_no = roll_no + 10000 WHERE class_id = ?");
        $temp_stmt->execute([$class_id]);

        // STEP B: Apply actual updates for all fields
        $update_stmt = $pdo->prepare("UPDATE students SET 
            roll_no = ?, 
            student_name = ?, 
            father_name = ?, 
            phone = ?, 
            address = ?, 
            photo_path = ? 
            WHERE student_id = ?");

        foreach ($student_data as $st_id => $fields) {
            $update_stmt->execute([
                (int)$fields['roll_no'],
                $fields['name'],
                $fields['father'],
                $fields['phone'],
                $fields['address'],
                $fields['photo'],
                $st_id
            ]);
        }

        // STEP C: Sync the Class Table roll range
        $sync = $pdo->prepare("UPDATE classes SET 
            start_roll = (SELECT MIN(roll_no) FROM students WHERE class_id = ?),
            end_roll = (SELECT MAX(roll_no) FROM students WHERE class_id = ?)
            WHERE id = ?");
        $sync->execute([$class_id, $class_id, $class_id]);

        $pdo->commit();
        $msg = "Student records and roll numbers updated successfully!";
    } catch (Exception $e) {
        $pdo->rollBack();
        $error = "Update failed: " . $e->getMessage();
    }
}

// 3. Load students if a class is selected
$selected_class = $_GET['class_id'] ?? null;
$students = [];
if ($selected_class) {
    $st_query = $pdo->prepare("SELECT * FROM students WHERE class_id = ? ORDER BY roll_no ASC");
    $st_query->execute([$selected_class]);
    $students = $st_query->fetchAll();
}
?>

<div class="container-fluid mt-4 px-4">
    <div class="card shadow-sm border-0">
        <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0"><i class="fa-solid fa-users-gear me-2"></i>Bulk Student & Roll Editor</h5>
            <a href="classes_students.php" class="btn btn-sm btn-outline-light">Back to Classes</a>
        </div>
        <div class="card-body bg-light">
            <form method="GET" class="row g-3 mb-4">
                <div class="col-md-3">
                    <label class="small fw-bold">1. Select Session</label>
                    <select name="session" class="form-select" onchange="this.form.submit()">
                        <option value="">-- Choose Session --</option>
                        <?php foreach($sessions as $s): ?>
                            <option value="<?= $s ?>" <?= ($_GET['session'] ?? '') == $s ? 'selected' : '' ?>><?= $s ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="small fw-bold">2. Select Class</label>
                    <select name="class_id" class="form-select" onchange="this.form.submit()">
                        <option value="">-- Choose Class --</option>
                        <?php 
                        if(isset($_GET['session'])){
                            $cls = $pdo->prepare("SELECT id, class_name FROM classes WHERE academic_year = ?");
                            $cls->execute([$_GET['session']]);
                            while($c = $cls->fetch()){
                                $sel = ($selected_class == $c['id']) ? 'selected' : '';
                                echo "<option value='{$c['id']}' $sel>{$c['class_name']}</option>";
                            }
                        }
                        ?>
                    </select>
                </div>
            </form>

            <?php if($msg) echo "<div class='alert alert-success'>$msg</div>"; ?>
            <?php if($error) echo "<div class='alert alert-danger'>$error</div>"; ?>

            <?php if($selected_class && $students): ?>
            <form method="POST">
                <input type="hidden" name="class_id" value="<?= $selected_class ?>">
                <div class="table-responsive shadow-sm rounded">
                    <table class="table table-white table-hover align-middle mb-0" style="min-width: 1200px;">
                        <thead class="table-primary">
                            <tr>
                                <th width="80">Roll</th>
                                <th>Student Name</th>
                                <th>Father's Name</th>
                                <th>Phone</th>
                                <th>Address</th>
                                <th>Photo Path</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($students as $st): ?>
                            <tr>
                                <td>
                                    <input type="number" name="student[<?= $st['student_id'] ?>][roll_no]" 
                                           class="form-control form-control-sm text-center fw-bold" 
                                           value="<?= $st['roll_no'] ?>" required>
                                </td>
                                <td>
                                    <input type="text" name="student[<?= $st['student_id'] ?>][name]" 
                                           class="form-control form-control-sm" 
                                           value="<?= htmlspecialchars($st['student_name']) ?>" required>
                                </td>
                                <td>
                                    <input type="text" name="student[<?= $st['student_id'] ?>][father]" 
                                           class="form-control form-control-sm" 
                                           value="<?= htmlspecialchars($st['father_name']) ?>">
                                </td>
                                <td>
                                    <input type="text" name="student[<?= $st['student_id'] ?>][phone]" 
                                           class="form-control form-control-sm" 
                                           value="<?= htmlspecialchars($st['phone']) ?>">
                                </td>
                                <td>
                                    <textarea name="student[<?= $st['student_id'] ?>][address]" 
                                              class="form-control form-control-sm" rows="1"><?= htmlspecialchars($st['address']) ?></textarea>
                                </td>
                                <td>
                                    <input type="text" name="student[<?= $st['student_id'] ?>][photo]" 
                                           class="form-control form-control-sm" 
                                           value="<?= htmlspecialchars($st['photo_path']) ?>" placeholder="uploads/photo.jpg">
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <div class="mt-4 text-center">
                    <button type="submit" name="update_students" class="btn btn-success btn-lg px-5 shadow">
                        <i class="fa-solid fa-save me-2"></i> Save All Changes
                    </button>
                </div>
            </form>
            <?php elseif($selected_class): ?>
                <div class="alert alert-info">No students found in this class.</div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require 'footer.php'; ?>