<?php
require 'header.php';

// Handle Add/Delete
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['add_class'])) {
        $name = $_POST['class_name'];
        $year = $_POST['academic_year'];
        $start = $_POST['start_roll'];
        $end = $_POST['end_roll'];

        if ($start < $end) {
            $stmt = $pdo->prepare("INSERT INTO classes (class_name, academic_year, start_roll, end_roll) VALUES (?, ?, ?, ?)");
            $stmt->execute([$name, $year, $start, $end]);
            $msg = "Class added successfully.";
        } else {
            $error = "Start Roll must be less than End Roll.";
        }
    } elseif (isset($_POST['delete_id'])) {
        $id = $_POST['delete_id'];
        $stmt = $pdo->prepare("DELETE FROM classes WHERE id = ?");
        $stmt->execute([$id]);
        $msg = "Class deleted. (Marks associated were also removed).";
    }
}

// Fetch Classes
$classes = $pdo->query("SELECT * FROM classes ORDER BY class_name ASC")->fetchAll();
?>

<div class="row">
    <div class="col-md-12 mb-4">
        <h2 class="fw-bold"><i class="fa-solid fa-chalkboard-user"></i> Class Management</h2>
        <p class="text-muted">Define classes and student roll ranges here. No manual student entry required.</p>
    </div>
</div>

<div class="row">
    <div class="col-md-4 mb-4">
        <div class="card shadow-sm">
            <div class="card-header bg-white fw-bold">Add New Class</div>
            <div class="card-body">
                <?php if(isset($error)) echo "<div class='alert alert-danger p-2'>$error</div>"; ?>
                <?php if(isset($msg)) echo "<div class='alert alert-success p-2'>$msg</div>"; ?>

                <form method="POST">
                    <div class="mb-3">
                        <label class="form-label">Class Name</label>
                        <input type="text" name="class_name" class="form-control" placeholder="e.g. Class 10" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Academic Year</label>
                        <input type="text" name="academic_year" class="form-control" value="<?php echo date('Y'); ?>" required>
                    </div>
                    <div class="row">
                        <div class="col-6 mb-3">
                            <label class="form-label">Start Roll</label>
                            <input type="number" name="start_roll" class="form-control" value="1" required>
                        </div>
                        <div class="col-6 mb-3">
                            <label class="form-label">End Roll</label>
                            <input type="number" name="end_roll" class="form-control" placeholder="e.g. 50" required>
                        </div>
                    </div>
                    <div class="alert alert-info py-2 small">
                        <i class="fa-solid fa-info-circle"></i> System will auto-generate students for rolls between Start and End.
                    </div>
                    <button type="submit" name="add_class" class="btn btn-primary w-100">Create Class</button>
                </form>
            </div>
        </div>
    </div>

    <div class="col-md-8">
        <div class="card shadow-sm">
            <div class="card-header bg-white fw-bold">Existing Classes</div>
            <div class="card-body p-0">
                <table class="table table-hover mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="ps-4">Class</th>
                            <th>Year</th>
                            <th>Roll Range</th>
                            <th>Students</th>
                            <th class="text-end pe-4">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($classes as $c): ?>
                            <tr>
                                <td class="ps-4 fw-bold"><?php echo htmlspecialchars($c['class_name']); ?></td>
                                <td><?php echo htmlspecialchars($c['academic_year']); ?></td>
                                <td><?php echo $c['start_roll'] . ' - ' . $c['end_roll']; ?></td>
                                <td><span class="badge bg-secondary"><?php echo ($c['end_roll'] - $c['start_roll'] + 1); ?></span></td>
                                <td class="text-end pe-4">
                                    <form method="POST" onsubmit="return confirm('Deleting this class will DELETE ALL subjects and marks associated with it. Continue?');">
                                        <input type="hidden" name="delete_id" value="<?php echo $c['id']; ?>">
                                        <button type="submit" class="btn btn-sm btn-outline-danger"><i class="fa-solid fa-trash"></i></button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if(count($classes) == 0): ?>
                            <tr><td colspan="5" class="text-center py-4 text-muted">No classes defined yet.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php require 'footer.php'; ?>