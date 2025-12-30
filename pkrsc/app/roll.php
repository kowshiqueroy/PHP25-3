<?php
require 'header.php';

$msg = ''; $error = '';

// 1. Fetch all available sessions for the dropdown
$sessions = $pdo->query("SELECT DISTINCT academic_year FROM classes ORDER BY academic_year DESC")->fetchAll(PDO::FETCH_COLUMN);

// 2. Handle the Roll Update Logic
if (isset($_POST['update_rolls'])) {
    $class_id = $_POST['class_id'];
    $new_rolls = $_POST['rolls']; // Array: [student_id => new_roll_number]

    try {
        $pdo->beginTransaction();

        // STEP A: Move students to a temporary "safe" roll range (Roll + 10000)
        // This prevents "Duplicate entry" errors when swapping e.g., 1 with 2
        $temp_stmt = $pdo->prepare("UPDATE students SET roll_no = roll_no + 10000 WHERE class_id = ?");
        $temp_stmt->execute([$class_id]);

        // STEP B: Apply the actual new roll numbers
        $update_stmt = $pdo->prepare("UPDATE students SET roll_no = ? WHERE student_id = ?");
        foreach ($new_rolls as $st_id => $val) {
            $update_stmt->execute([(int)$val, $st_id]);
        }

        // STEP C: Sync the Class Table roll range
        $sync = $pdo->prepare("UPDATE classes SET 
            start_roll = (SELECT MIN(roll_no) FROM students WHERE class_id = ?),
            end_roll = (SELECT MAX(roll_no) FROM students WHERE class_id = ?)
            WHERE id = ?");
        $sync->execute([$class_id, $class_id, $class_id]);

        $pdo->commit();
        $msg = "Roll numbers successfully rearranged!";
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

<div class="container mt-4">
    <div class="card shadow-sm border-0">
        <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0"><i class="fa-solid fa-arrow-rotate-left me-2"></i>Advanced Roll Switcher</h5>
            <a href="classes_students.php" class="btn btn-sm btn-outline-light">Back to Classes</a>
        </div>
        <div class="card-body bg-light">
            <form method="GET" class="row g-3 mb-4">
                <div class="col-md-4">
                    <label class="small fw-bold">1. Select Session</label>
                    <select name="session" class="form-select" onchange="this.form.submit()">
                        <option value="">-- Choose Session --</option>
                        <?php foreach($sessions as $s): ?>
                            <option value="<?= $s ?>" <?= ($_GET['session'] ?? '') == $s ? 'selected' : '' ?>><?= $s ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-4">
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
                    <table class="table table-white table-hover align-middle mb-0">
                        <thead class="table-primary">
                            <tr>
                                <th width="100">Current Roll</th>
                                <th>Student Name</th>
                                <th>Father's Name</th>
                                <th width="150" class="text-center">New Roll Number</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($students as $st): ?>
                            <tr>
                                <td class="text-center fw-bold text-muted"><?= $st['roll_no'] ?></td>
                                <td class="fw-bold"><?= htmlspecialchars($st['student_name']) ?></td>
                                <td class="small"><?= htmlspecialchars($st['father_name']) ?></td>
                                <td>
                                    <input type="number" name="rolls[<?= $st['student_id'] ?>]" 
                                           class="form-control form-control-sm text-center fw-bold border-primary" 
                                           value="<?= $st['roll_no'] ?>" required>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <div class="mt-4 text-center">
                    <button type="submit" name="update_rolls" class="btn btn-primary btn-lg px-5 shadow">
                        Update & Switch All Rolls
                    </button>
                    <p class="text-muted small mt-2">Note: You can swap numbers (e.g., change 1 to 5 and 5 to 1) freely.</p>
                </div>
            </form>
            <?php elseif($selected_class): ?>
                <div class="alert alert-info">No students found in this class.</div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require 'footer.php'; ?>