<?php
require_once 'db.php';

// --- 1. HANDLE ALL LOGIC BEFORE ANY HTML OUTPUT ---
$year = $_GET['year'] ?? date('Y');
$class_id = $_GET['class_id'] ?? null;
$msg = "";

// ACTION: DELETE SUBJECT (Moved from subject_delete.php)
if (isset($_GET['delete_id'])) {
    $did = $_GET['delete_id'];
    // Delete components, then parts, then subject (assuming ON DELETE CASCADE isn't set)
    $pdo->prepare("DELETE FROM subject_components WHERE part_id IN (SELECT id FROM subject_parts WHERE subject_id = ?)")->execute([$did]);
    $pdo->prepare("DELETE FROM subject_parts WHERE subject_id = ?")->execute([$did]);
    $pdo->prepare("DELETE FROM subjects WHERE id = ?")->execute([$did]);
    header("Location: subjects.php?class_id=$class_id&year=$year&msg=Subject Deleted");
    exit;
}

// ACTION: TOGGLE OPTIONAL
if (isset($_GET['toggle_opt'])) {
    $pdo->prepare("UPDATE subjects SET is_optional = NOT is_optional WHERE id = ?")->execute([$_GET['toggle_opt']]);
    header("Location: subjects.php?class_id=$class_id&year=$year");
    exit;
}

// ACTION: DUPLICATE INDIVIDUAL SUBJECT
if (isset($_POST['duplicate_subject'])) {
    $sid = $_POST['subject_id'];
    $new_name = $_POST['new_name'];
    $stmt = $pdo->prepare("SELECT * FROM subjects WHERE id = ?");
    $stmt->execute([$sid]);
    $sub = $stmt->fetch();
    
    $ins = $pdo->prepare("INSERT INTO subjects (class_id, subject_name, overall_pass_mark, is_optional) VALUES (?, ?, ?, ?)");
    $ins->execute([$sub['class_id'], $new_name, $sub['overall_pass_mark'], $sub['is_optional']]);
    $new_sid = $pdo->lastInsertId();
    
    // Duplicate Parts & Components efficiently
    $parts = $pdo->prepare("SELECT * FROM subject_parts WHERE subject_id = ?");
    $parts->execute([$sid]);
    $all_parts = $parts->fetchAll();
    foreach($all_parts as $p) {
        $pdo->prepare("INSERT INTO subject_parts (subject_id, part_name, part_pass_mark) VALUES (?, ?, ?)")->execute([$new_sid, $p['part_name'], $p['part_pass_mark']]);
        $new_pid = $pdo->lastInsertId();
        
        $pdo->prepare("INSERT INTO subject_components (part_id, component_name, max_marks, pass_mark) 
                       SELECT ?, component_name, max_marks, pass_mark FROM subject_components WHERE part_id = ?")
             ->execute([$new_pid, $p['id']]);
    }
    header("Location: subjects.php?class_id=$class_id&year=$year&msg=Subject Duplicated");
    exit;
}

// ACTION: CLONE ALL SUBJECTS TO ANOTHER CLASS
if (isset($_POST['clone_all'])) {
    $from = $_POST['from_class_id'];
    $to = $_POST['to_class_id'];
    
    $subs = $pdo->prepare("SELECT * FROM subjects WHERE class_id = ?");
    $subs->execute([$from]);
    foreach ($subs->fetchAll() as $s) {
        $pdo->prepare("INSERT INTO subjects (class_id, subject_name, overall_pass_mark, is_optional) VALUES (?, ?, ?, ?)")
             ->execute([$to, $s['subject_name'], $s['overall_pass_mark'], $s['is_optional']]);
        $new_sid = $pdo->lastInsertId();
        
        $parts = $pdo->prepare("SELECT * FROM subject_parts WHERE subject_id = ?");
        $parts->execute([$s['id']]);
        foreach($parts->fetchAll() as $p) {
            $pdo->prepare("INSERT INTO subject_parts (subject_id, part_name, part_pass_mark) VALUES (?, ?, ?)")->execute([$new_sid, $p['part_name'], $p['part_pass_mark']]);
            $new_pid = $pdo->lastInsertId();
            $pdo->prepare("INSERT INTO subject_components (part_id, component_name, max_marks, pass_mark) 
                           SELECT ?, component_name, max_marks, pass_mark FROM subject_components WHERE part_id = ?")
                 ->execute([$new_pid, $p['id']]);
        }
    }
    header("Location: subjects.php?class_id=$to&year=$year&msg=Class Cloned");
    exit;
}

// --- 2. START THE PAGE VIEW ---
require 'header.php';
?>

<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold"><i class="fa fa-layer-group text-primary"></i> Subjects</h2>
        <div class="btn-group">
            <button class="btn btn-outline-primary btn-sm" data-bs-toggle="modal" data-bs-target="#cloneModal">Clone Class</button>
            <a href="subject_add.php?class_id=<?= $class_id ?>" class="btn btn-primary btn-sm <?= !$class_id?'disabled':'' ?>">+ New Subject</a>
        </div>
    </div>

    <?php if(isset($_GET['msg'])): ?>
        <div class="alert alert-success py-2"><?= htmlspecialchars($_GET['msg']) ?></div>
    <?php endif; ?>

    <div class="card shadow-sm border-0 mb-4 bg-light">
        <form class="card-body row g-2">
            <div class="col-md-3">
                <label class="small fw-bold">Year</label>
                <select name="year" class="form-select form-select-sm" onchange="this.form.submit()">
                    <?php
                    $years = $pdo->query("SELECT DISTINCT academic_year FROM classes ORDER BY academic_year DESC")->fetchAll(PDO::FETCH_COLUMN);
                    foreach ($years as $y) echo "<option value='$y' ".($year==$y?'selected':'').">$y</option>";
                    ?>
                </select>
            </div>
            <div class="col-md-4">
                <label class="small fw-bold">Class</label>
                <select name="class_id" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="">-- Choose Class --</option>
                    <?php
                    $classes = $pdo->prepare("SELECT id, class_name FROM classes WHERE academic_year = ?");
                    $classes->execute([$year]);
                    while($c = $classes->fetch()) echo "<option value='{$c['id']}' ".($class_id==$c['id']?'selected':'').">{$c['class_name']}</option>";
                    ?>
                </select>
            </div>
        </form>
    </div>

    <?php if ($class_id): ?>
    <div class="table-responsive bg-white rounded shadow-sm">
        <table class="table align-middle mb-0">
            <thead class="table-dark">
                <tr>
                    <th>Subject</th>
                    <th>Status</th>
                    <th>Internal Structure</th>
                    <th class="text-center">Action</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $subjects = $pdo->prepare("
                    SELECT s.*, 
                    (SELECT GROUP_CONCAT(CONCAT(p.part_name, '(', 
                        (SELECT GROUP_CONCAT(CONCAT(c.component_name, ':', c.max_marks) SEPARATOR ', ') 
                         FROM subject_components c WHERE c.part_id = p.id), ')') SEPARATOR ' | ') 
                     FROM subject_parts p WHERE p.subject_id = s.id) as structure
                    FROM subjects s WHERE s.class_id = ?
                ");
                $subjects->execute([$class_id]);
                while ($sub = $subjects->fetch()):
                ?>
                <tr>
                    <td>
                        <div class="fw-bold"><?= $sub['subject_name'] ?></div>
                        <span class="text-muted small">Pass Mark: <?= $sub['overall_pass_mark'] ?></span>
                    </td>
                    <td>
                        <a href="?toggle_opt=<?= $sub['id'] ?>&class_id=<?= $class_id ?>&year=<?= $year ?>" 
                           class="badge text-decoration-none <?= $sub['is_optional'] ? 'bg-warning text-dark' : 'bg-primary' ?>">
                           <?= $sub['is_optional'] ? '4th Subject' : 'Compulsory' ?>
                        </a>
                    </td>
                    <td><small class="text-muted"><?= $sub['structure'] ?: 'No components defined' ?></small></td>
                    <td class="text-center">
                        <div class="btn-group">
                            <button class="btn btn-sm btn-light border" onclick="duplicateSubject(<?= $sub['id'] ?>, '<?= $sub['subject_name'] ?>')"><i class="fa fa-copy"></i></button>
                            <a href="subject_edit.php?id=<?= $sub['id'] ?>" class="btn btn-sm btn-light border text-primary"><i class="fa fa-edit"></i></a>
                            <a href="?delete_id=<?= $sub['id'] ?>&class_id=<?= $class_id ?>&year=<?= $year ?>" 
                               class="btn btn-sm btn-light border text-danger" 
                               onclick="return confirm('Careful! This will delete all marks for this subject.')"><i class="fa fa-trash"></i></a>
                        </div>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
    <?php else: ?>
        <div class="text-center p-5 bg-white border rounded">Select a year and class to manage subjects.</div>
    <?php endif; ?>
</div>

<div class="modal fade" id="dupModal" tabindex="-1">
    <div class="modal-dialog"><form method="POST" class="modal-content">
        <div class="modal-header"><h5>Quick Duplicate</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
        <div class="modal-body">
            <input type="hidden" name="subject_id" id="dup_sid">
            <label class="small fw-bold">New Name</label>
            <input type="text" name="new_name" id="dup_name" class="form-control" required>
        </div>
        <div class="modal-footer"><button type="submit" name="duplicate_subject" class="btn btn-primary">Create</button></div>
    </form></div>
</div>

<div class="modal fade" id="cloneModal" tabindex="-1">
    <div class="modal-dialog"><form method="POST" class="modal-content">
        <div class="modal-header"><h5>Clone From Another Class</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
        <div class="modal-body">
            <label class="small fw-bold">Source Class</label>
            <select name="from_class_id" class="form-select mb-3" required>
                <?php
                $all_c = $pdo->query("SELECT id, class_name, academic_year FROM classes ORDER BY academic_year DESC");
                while($ac = $all_c->fetch()) echo "<option value='{$ac['id']}'>{$ac['class_name']} ({$ac['academic_year']})</option>";
                ?>
            </select>
            <label class="small fw-bold">Destination Class</label>
            <input type="text" class="form-control" value="<?= $class_id ? 'Current Selection' : 'Please select class first' ?>" disabled>
            <input type="hidden" name="to_class_id" value="<?= $class_id ?>">
        </div>
        <div class="modal-footer"><button type="submit" name="clone_all" class="btn btn-primary" <?= !$class_id?'disabled':'' ?>>Clone Now</button></div>
    </form></div>
</div>

<script>
function duplicateSubject(id, name) {
    document.getElementById('dup_sid').value = id;
    document.getElementById('dup_name').value = name + " Copy";
    new bootstrap.Modal(document.getElementById('dupModal')).show();
}
</script>

<?php require 'footer.php'; ?>