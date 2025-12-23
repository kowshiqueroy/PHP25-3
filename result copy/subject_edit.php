<?php
require_once 'db.php';

$id = $_GET['id'] ?? null;
if (!$id) { header("Location: subjects.php"); exit; }

// --- 1. HANDLE UPDATE LOGIC ---
if (isset($_POST['update_full_subject'])) {
    try {
        $pdo->beginTransaction();

        // Update Subject Metadata
        $is_opt = isset($_POST['is_optional']) ? 1 : 0;
        $pdo->prepare("UPDATE subjects SET subject_name = ?, overall_pass_mark = ?, is_optional = ? WHERE id = ?")
            ->execute([$_POST['subject_name'], $_POST['overall_pass_mark'], $is_opt, $id]);

        // Process Parts
        if (isset($_POST['parts'])) {
            foreach ($_POST['parts'] as $p_id => $p_data) {
                if (strpos($p_id, 'new_') === 0) {
                    // Insert New Part
                    $ins_p = $pdo->prepare("INSERT INTO subject_parts (subject_id, part_name, part_pass_mark) VALUES (?, ?, ?)");
                    $ins_p->execute([$id, $p_data['name'], $p_data['pass_mark']]);
                    $real_p_id = $pdo->lastInsertId();
                } else {
                    // Update Existing Part
                    $pdo->prepare("UPDATE subject_parts SET part_name = ?, part_pass_mark = ? WHERE id = ?")
                        ->execute([$p_data['name'], $p_data['pass_mark'], $p_id]);
                    $real_p_id = $p_id;
                }

                // Process Components
                if (isset($p_data['comps'])) {
                    foreach ($p_data['comps'] as $c_id => $c_data) {
                        if (strpos($c_id, 'new_') === 0) {
                            $pdo->prepare("INSERT INTO subject_components (part_id, component_name, max_marks, pass_mark) VALUES (?, ?, ?, ?)")
                                ->execute([$real_p_id, $c_data['name'], $c_data['max'], $c_data['pass']]);
                        } else {
                            $pdo->prepare("UPDATE subject_components SET component_name = ?, max_marks = ?, pass_mark = ? WHERE id = ?")
                                ->execute([$c_data['name'], $c_data['max'], $c_data['pass'], $c_id]);
                        }
                    }
                }
            }
        }

        // Handle Deletions (if any IDs were sent to delete)
        if (!empty($_POST['delete_comps'])) {
            $ids = explode(',', $_POST['delete_comps']);
            $pdo->prepare("DELETE FROM subject_components WHERE id IN (" . implode(',', array_map('intval', $ids)) . ")")->execute();
        }
        if (!empty($_POST['delete_parts'])) {
            $ids = explode(',', $_POST['delete_parts']);
            $pdo->prepare("DELETE FROM subject_parts WHERE id IN (" . implode(',', array_map('intval', $ids)) . ")")->execute();
        }

        $pdo->commit();
        header("Location: subjects.php?msg=Subject Structure Updated");
        exit;
    } catch (Exception $e) {
        $pdo->rollBack();
        $error = "Error: " . $e->getMessage();
    }
}

require 'header.php';

// Fetch Existing Data
$sub = $pdo->prepare("SELECT * FROM subjects WHERE id = ?");
$sub->execute([$id]);
$subject = $sub->fetch();
?>

<div class="container py-4">
    <form method="POST" id="editForm">
        <input type="hidden" name="delete_parts" id="delete_parts" value="">
        <input type="hidden" name="delete_comps" id="delete_comps" value="">

        <div class="card shadow-sm mb-4">
            <div class="card-header bg-primary text-white d-flex justify-content-between">
                <h5 class="mb-0">Edit Subject Structure</h5>
                <a href="subjects.php" class="btn btn-sm btn-light">Cancel</a>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-5">
                        <label class="small fw-bold">Subject Name</label>
                        <input type="text" name="subject_name" class="form-control" value="<?= htmlspecialchars($subject['subject_name']) ?>" required>
                    </div>
                    <div class="col-md-3">
                        <label class="small fw-bold">Overall Pass Mark</label>
                        <input type="number" name="overall_pass_mark" class="form-control" value="<?= $subject['overall_pass_mark'] ?>" required>
                    </div>
                    <div class="col-md-4 d-flex align-items-end">
                        <div class="form-check form-switch mb-2">
                            <input class="form-check-input" type="checkbox" name="is_optional" id="opt" <?= $subject['is_optional']?'checked':'' ?>>
                            <label class="form-check-label fw-bold" for="opt">4th Subject</label>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div id="parts-list">
            <?php
            $parts = $pdo->prepare("SELECT * FROM subject_parts WHERE subject_id = ?");
            $parts->execute([$id]);
            while($p = $parts->fetch()):
            ?>
            <div class="card border-primary mb-3 part-item" data-id="<?= $p['id'] ?>">
                <div class="card-header bg-light d-flex justify-content-between align-items-center">
                    <div class="d-flex align-items-center gap-2 w-75">
                        <input type="text" name="parts[<?= $p['id'] ?>][name]" class="form-control form-control-sm" value="<?= htmlspecialchars($p['part_name']) ?>" required>
                        <input type="number" name="parts[<?= $p['id'] ?>][pass_mark]" class="form-control form-control-sm" style="width:80px" value="<?= $p['part_pass_mark'] ?>">
                    </div>
                    <button type="button" class="btn btn-sm btn-outline-danger" onclick="removePart(<?= $p['id'] ?>, this)">Remove Part</button>
                </div>
                <div class="card-body">
                    <table class="table table-sm align-middle">
                        <tbody class="comp-list">
                            <?php
                            $comps = $pdo->prepare("SELECT * FROM subject_components WHERE part_id = ?");
                            $comps->execute([$p['id']]);
                            while($c = $comps->fetch()):
                            ?>
                            <tr data-id="<?= $c['id'] ?>">
                                <td><input type="text" name="parts[<?= $p['id'] ?>][comps][<?= $c['id'] ?>][name]" class="form-control form-control-sm" value="<?= htmlspecialchars($c['component_name']) ?>"></td>
                                <td width="120"><input type="number" name="parts[<?= $p['id'] ?>][comps][<?= $c['id'] ?>][max]" class="form-control form-control-sm" value="<?= $c['max_marks'] ?>"></td>
                                <td width="120"><input type="number" name="parts[<?= $p['id'] ?>][comps][<?= $c['id'] ?>][pass]" class="form-control form-control-sm" value="<?= $c['pass_mark'] ?>"></td>
                                <td width="40"><button type="button" class="btn btn-link text-danger p-0" onclick="removeComp(<?= $c['id'] ?>, this)"><i class="fa fa-times"></i></button></td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                    <button type="button" class="btn btn-sm btn-link text-primary" onclick="addComp(<?= $p['id'] ?>, this)">+ Add Component</button>
                </div>
            </div>
            <?php endwhile; ?>
        </div>

        <div class="d-flex justify-content-between mt-3">
            <button type="button" class="btn btn-outline-dark" onclick="addPart()">+ Add New Part</button>
            <button type="submit" name="update_full_subject" class="btn btn-success px-5">Update Subject Structure</button>
        </div>
    </form>
</div>

<script>
let newPartId = 0;
let newCompId = 0;

function addPart() {
    newPartId++;
    const html = `
    <div class="card border-success mb-3 part-item" data-id="new_${newPartId}">
        <div class="card-header bg-light d-flex justify-content-between align-items-center">
            <input type="text" name="parts[new_${newPartId}][name]" class="form-control form-control-sm w-50" placeholder="New Part Name">
            <button type="button" class="btn btn-sm btn-outline-danger" onclick="this.closest('.part-item').remove()">Cancel</button>
        </div>
        <div class="card-body">
            <table class="table table-sm"><tbody class="comp-list"></tbody></table>
            <button type="button" class="btn btn-sm btn-link" onclick="addComp('new_${newPartId}', this)">+ Add Component</button>
        </div>
    </div>`;
    document.getElementById('parts-list').insertAdjacentHTML('beforeend', html);
}

function addComp(partId, btn) {
    newCompId++;
    const tbody = btn.closest('.card-body').querySelector('.comp-list');
    const html = `
    <tr>
        <td><input type="text" name="parts[${partId}][comps][new_${newCompId}][name]" class="form-control form-control-sm" placeholder="MCQ/CQ"></td>
        <td width="120"><input type="number" name="parts[${partId}][comps][new_${newCompId}][max]" class="form-control form-control-sm" placeholder="Max"></td>
        <td width="120"><input type="number" name="parts[${partId}][comps][new_${newCompId}][pass]" class="form-control form-control-sm" placeholder="Pass"></td>
        <td width="40"><button type="button" class="btn btn-link text-danger p-0" onclick="this.closest('tr').remove()"><i class="fa fa-times"></i></button></td>
    </tr>`;
    tbody.insertAdjacentHTML('beforeend', html);
}

function removePart(id, btn) {
    if(confirm('Delete this part and all its components?')) {
        let current = document.getElementById('delete_parts').value;
        document.getElementById('delete_parts').value = current ? current + ',' + id : id;
        btn.closest('.part-item').remove();
    }
}

function removeComp(id, btn) {
    if(confirm('Remove this component?')) {
        let current = document.getElementById('delete_comps').value;
        document.getElementById('delete_comps').value = current ? current + ',' + id : id;
        btn.closest('tr').remove();
    }
}
</script>

<?php require 'footer.php'; ?>