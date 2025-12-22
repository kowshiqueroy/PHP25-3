<?php
require_once 'db.php';
if (session_status() === PHP_SESSION_NONE) { session_start(); }

$class_id = $_GET['class_id'] ?? null;
$msg = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        if (isset($_POST['add_subject'])) {
            $stmt = $pdo->prepare("INSERT INTO subjects (class_id, subject_name, subject_code, overall_pass_mark) VALUES (?, ?, ?, ?)");
            $stmt->execute([$class_id, $_POST['subject_name'], $_POST['subject_code'], $_POST['overall_pass']]);
            $sub_id = $pdo->lastInsertId();
            
            // AUTO-CREATE A DEFAULT PART for single-part subjects
            $stmt = $pdo->prepare("INSERT INTO subject_parts (subject_id, part_name, part_pass_mark) VALUES (?, 'General', ?)");
            $stmt->execute([$sub_id, $_POST['overall_pass']]);
        } 
        elseif (isset($_POST['add_part'])) {
            $stmt = $pdo->prepare("INSERT INTO subject_parts (subject_id, part_name, part_pass_mark) VALUES (?, ?, ?)");
            $stmt->execute([$_POST['subject_id'], $_POST['part_name'], $_POST['part_pass']]);
        }
        elseif (isset($_POST['add_component'])) {
            $stmt = $pdo->prepare("INSERT INTO subject_components (part_id, component_name, max_marks, pass_mark) VALUES (?, ?, ?, ?)");
            $stmt->execute([$_POST['part_id'], $_POST['comp_name'], $_POST['max_marks'], $_POST['pass_marks']]);
        }
        elseif (isset($_POST['delete_item'])) {
            $allowed = ['subjects', 'subject_parts', 'subject_components'];
            if (in_array($_POST['type'], $allowed)) {
                $stmt = $pdo->prepare("DELETE FROM {$_POST['type']} WHERE id = ?");
                $stmt->execute([$_POST['id']]);
            }
        }
        header("Location: subjects.php?class_id=$class_id&success=1");
        exit;
    } catch (PDOException $e) { $msg = $e->getMessage(); }
}

require 'header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="fw-bold"><i class="fa-solid fa-book text-primary"></i> Subject Setup</h2>
    <select class="form-select w-25 border-primary shadow-sm" onchange="location.href='subjects.php?class_id='+this.value">
        <option value="">-- Select Class --</option>
        <?php 
        $classes = $pdo->query("SELECT * FROM classes ORDER BY class_name ASC")->fetchAll();
        foreach($classes as $c): ?>
            <option value="<?= $c['id'] ?>" <?= ($class_id == $c['id']) ? 'selected' : '' ?>><?= $c['class_name'] ?></option>
        <?php endforeach; ?>
    </select>
</div>

<?php if($class_id): ?>
    <div class="card shadow-sm border-0 mb-4 bg-light">
        <div class="card-body">
            <form method="POST" class="row g-2 align-items-end">
                <div class="col-md-5"><label class="small fw-bold">Subject Name</label><input type="text" name="subject_name" class="form-control" placeholder="e.g. ICT" required></div>
                <div class="col-md-2"><label class="small fw-bold">Code</label><input type="text" name="subject_code" class="form-control" placeholder="101"></div>
                <div class="col-md-2"><label class="small fw-bold">Pass Mark</label><input type="number" name="overall_pass" class="form-control" value="33"></div>
               
               <div class="col-md-3">
    <label class="small fw-bold">Subject Type</label>
    <div class="form-check mt-2">
        <input class="form-check-input" type="checkbox" name="is_optional" id="isOpt" value="1">
        <label class="form-check-label" for="isOpt">
            Mark as 4th Subject
        </label>
    </div>
</div>

<?php
if (isset($_POST['add_subject'])) {
    $name = $_POST['subject_name'];
    $class_id = $_POST['class_id'];
    $pass_mark = $_POST['overall_pass_mark'];
    $is_optional = isset($_POST['is_optional']) ? 1 : 0; // Capture checkbox

    $stmt = $pdo->prepare("INSERT INTO subjects (subject_name, class_id, overall_pass_mark, is_optional) VALUES (?, ?, ?, ?)");
    $stmt->execute([$name, $class_id, $pass_mark, $is_optional]);
    header("Location: subjects.php?success=1");
}
?> <div class="col-md-3"><button type="submit" name="add_subject" class="btn btn-primary w-100">Create Subject</button></div>
            </form>
        </div>
    </div>

    <?php
    $subjects = $pdo->prepare("SELECT * FROM subjects WHERE class_id = ?");
    $subjects->execute([$class_id]);
    foreach($subjects->fetchAll() as $sub):
    ?>
    <div class="card shadow-sm mb-4 border-0">
        <div class="card-header bg-white d-flex justify-content-between align-items-center border-bottom">
            <h5 class="mb-0 fw-bold"><?= $sub['subject_name'] ?> <span class="text-muted small">(<?= $sub['subject_code'] ?>)</span></h5>
            <div>
                <button class="btn btn-sm btn-outline-success" data-bs-toggle="modal" data-bs-target="#addPart<?= $sub['id'] ?>">Add Part</button>
                <form method="POST" class="d-inline" onsubmit="return confirm('Delete Subject?')">
                    <input type="hidden" name="id" value="<?= $sub['id'] ?>"><input type="hidden" name="type" value="subjects">
                    <button type="submit" name="delete_item" class="btn btn-sm btn-outline-danger"><i class="fa-solid fa-trash"></i></button>
                </form>
            </div>
        </div>
        <div class="card-body p-0">
            <table class="table mb-0">
                <thead class="table-light small">
                    <tr>
                        <th class="ps-3">Part/Paper Name</th>
                        <th>Components (MCQ/CQ/etc)</th>
                        <th class="text-center">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $parts = $pdo->prepare("SELECT * FROM subject_parts WHERE subject_id = ?");
                    $parts->execute([$sub['id']]);
                    foreach($parts->fetchAll() as $part):
                    ?>
                    <tr>
                        <td class="ps-3 align-middle">
                            <span class="fw-bold"><?= $part['part_name'] ?></span><br>
                            <button class="btn btn-link btn-sm p-0 text-decoration-none" data-bs-toggle="modal" data-bs-target="#addComp<?= $part['id'] ?>">+ Component</button>
                        </td>
                        <td class="align-middle">
                            <?php
                            $comps = $pdo->prepare("SELECT * FROM subject_components WHERE part_id = ?");
                            $comps->execute([$part['id']]);
                            foreach($comps->fetchAll() as $comp):
                            ?>
                                <div class="badge bg-light text-dark border p-2 mb-1">
                                    <?= $comp['component_name'] ?>: <?= $comp['max_marks'] ?>
                                    <form method="POST" class="d-inline ms-1">
                                        <input type="hidden" name="id" value="<?= $comp['id'] ?>"><input type="hidden" name="type" value="subject_components">
                                        <button type="submit" name="delete_item" class="btn-close" style="font-size:0.5rem"></button>
                                    </form>
                                </div>
                            <?php endforeach; ?>
                        </td>
                        <td class="text-center align-middle">
                            <?php if($part['part_name'] != 'General'): ?>
                            <form method="POST" onsubmit="return confirm('Delete Part?')">
                                <input type="hidden" name="id" value="<?= $part['id'] ?>"><input type="hidden" name="type" value="subject_parts">
                                <button type="submit" name="delete_item" class="btn btn-sm text-danger"><i class="fa-solid fa-xmark"></i></button>
                            </form>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <?php 
    $parts_for_modals = $pdo->prepare("SELECT * FROM subject_parts WHERE subject_id = ?");
    $parts_for_modals->execute([$sub['id']]);
    foreach($parts_for_modals->fetchAll() as $pm): ?>
        <div class="modal fade" id="addComp<?= $pm['id'] ?>" tabindex="-1">
            <div class="modal-dialog"><form method="POST" class="modal-content">
                <div class="modal-header"><h5>Add Component to <?= $pm['part_name'] ?></h5></div>
                <div class="modal-body">
                    <input type="hidden" name="part_id" value="<?= $pm['id'] ?>">
                    <div class="mb-3"><label>Name</label><input type="text" name="comp_name" class="form-control" placeholder="MCQ" required></div>
                    <div class="row"><div class="col-6"><label>Max</label><input type="number" name="max_marks" class="form-control" required></div><div class="col-6"><label>Pass</label><input type="number" name="pass_marks" class="form-control" required></div></div>
                </div>
                <div class="modal-footer"><button type="submit" name="add_component" class="btn btn-primary">Save</button></div>
            </form></div>
        </div>
    <?php endforeach; ?>

    <div class="modal fade" id="addPart<?= $sub['id'] ?>" tabindex="-1">
        <div class="modal-dialog"><form method="POST" class="modal-content">
            <div class="modal-header"><h5>Add Paper to <?= $sub['subject_name'] ?></h5></div>
            <div class="modal-body">
                <input type="hidden" name="subject_id" value="<?= $sub['id'] ?>">
                <div class="mb-3"><label>Paper Name</label><input type="text" name="part_name" class="form-control" placeholder="Theory / 1st Paper" required></div>
                <div class="mb-3"><label>Pass Mark</label><input type="number" name="part_pass" class="form-control" value="0"></div>
            </div>
            <div class="modal-footer"><button type="submit" name="add_part" class="btn btn-primary">Add</button></div>
        </form></div>
    </div>

    <?php endforeach; ?>
<?php endif; ?>

<?php require 'footer.php'; ?>