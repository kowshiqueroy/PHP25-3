<?php
require_once 'db.php';

// 1. LOGIC: SAVE SUBJECT (Before HTML)
if (isset($_POST['save_subject'])) {
    $class_id = $_POST['class_id'];
    $sub_name = $_POST['subject_name'];
    $pass_mark = $_POST['overall_pass_mark'];
    $is_opt = isset($_POST['is_optional']) ? 1 : 0;

    try {
        $pdo->beginTransaction();

        // Insert Subject
        $stmt = $pdo->prepare("INSERT INTO subjects (class_id, subject_name, overall_pass_mark, is_optional) VALUES (?, ?, ?, ?)");
        $stmt->execute([$class_id, $sub_name, $pass_mark, $is_opt]);
        $subject_id = $pdo->lastInsertId();

        // Process Parts and Components
        if (isset($_POST['parts'])) {
            foreach ($_POST['parts'] as $p_index => $p_data) {
                $p_stmt = $pdo->prepare("INSERT INTO subject_parts (subject_id, part_name, part_pass_mark) VALUES (?, ?, ?)");
                $p_stmt->execute([$subject_id, $p_data['name'], $p_data['pass_mark']]);
                $part_id = $pdo->lastInsertId();

                if (isset($p_data['comps'])) {
                    foreach ($p_data['comps'] as $c_data) {
                        $c_stmt = $pdo->prepare("INSERT INTO subject_components (part_id, component_name, max_marks, pass_mark) VALUES (?, ?, ?, ?)");
                        $c_stmt->execute([$part_id, $c_data['name'], $c_data['max'], $c_data['pass']]);
                    }
                }
            }
        }

        $pdo->commit();
        header("Location: subjects.php?class_id=$class_id&msg=Subject Created Successfully");
        exit;
    } catch (Exception $e) {
        $pdo->rollBack();
        $error = "Failed to save: " . $e->getMessage();
    }
}

require 'header.php';
$class_id = $_GET['class_id'] ?? '';
?>

<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <form method="POST" id="subjectForm">
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-header bg-dark text-white">
                        <h5 class="mb-0">Add New Subject</h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="small fw-bold">Target Class</label>
                                <select name="class_id" class="form-select" required>
                                    <?php
                                    $classes = $pdo->query("SELECT id, class_name, academic_year FROM classes ORDER BY academic_year DESC");
                                    while($c = $classes->fetch()) {
                                        $sel = ($class_id == $c['id']) ? 'selected' : '';
                                        echo "<option value='{$c['id']}' $sel>{$c['class_name']} ({$c['academic_year']})</option>";
                                    }
                                    ?>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="small fw-bold">Subject Name</label>
                                <input type="text" name="subject_name" class="form-control" placeholder="e.g. Mathematics" required>
                            </div>
                            <div class="col-md-2">
                                <label class="small fw-bold">Overall Pass Mark</label>
                                <input type="number" name="overall_pass_mark" class="form-control" value="33" required>
                            </div>
                            <div class="col-md-2 d-flex align-items-end">
                                <div class="form-check form-switch mb-2">
                                    <input class="form-check-input" type="checkbox" name="is_optional" id="is_opt">
                                    <label class="form-check-label small fw-bold" for="is_opt">4th Subject</label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div id="partsContainer">
                    <div class="card border-primary mb-3 part-card">
                        <div class="card-header bg-light d-flex justify-content-between align-items-center">
                            <input type="text" name="parts[0][name]" class="form-control form-control-sm w-50" value="Main Theory" required>
                            <div class="d-flex align-items-center gap-2">
                                <label class="small fw-bold">Part Pass:</label>
                                <input type="number" name="parts[0][pass_mark]" class="form-control form-control-sm" style="width:70px" value="33">
                            </div>
                        </div>
                        <div class="card-body">
                            <table class="table table-sm table-borderless align-middle">
                                <thead>
                                    <tr class="small fw-bold text-muted">
                                        <th>Component (MCQ, Written, etc)</th>
                                        <th width="150">Max Marks</th>
                                        <th width="150">Pass Mark</th>
                                        <th width="50"></th>
                                    </tr>
                                </thead>
                                <tbody class="comp-container">
                                    <tr>
                                        <td><input type="text" name="parts[0][comps][0][name]" class="form-control form-control-sm" value="Written" required></td>
                                        <td><input type="number" name="parts[0][comps][0][max]" class="form-control form-control-sm" value="100" required></td>
                                        <td><input type="number" name="parts[0][comps][0][pass]" class="form-control form-control-sm" value="33" required></td>
                                        <td></td>
                                    </tr>
                                </tbody>
                            </table>
                            <button type="button" class="btn btn-sm btn-outline-secondary add-comp" data-part-index="0">+ Add Component</button>
                        </div>
                    </div>
                </div>

                <div class="d-flex justify-content-between mt-4">
                    <button type="button" class="btn btn-outline-dark" id="addPart">+ Add Another Part (e.g. Practical)</button>
                    <div>
                        <a href="subjects.php" class="btn btn-light border me-2">Cancel</a>
                        <button type="submit" name="save_subject" class="btn btn-success px-5">Save Subject</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
let partCount = 1;

// Function to add a component to a specific part
document.addEventListener('click', function(e) {
    if (e.target.classList.contains('add-comp')) {
        const partIndex = e.target.getAttribute('data-part-index');
        const container = e.target.closest('.card-body').querySelector('.comp-container');
        const compIndex = container.children.length;
        
        const row = document.createElement('tr');
        row.innerHTML = `
            <td><input type="text" name="parts[${partIndex}][comps][${compIndex}][name]" class="form-control form-control-sm" placeholder="Component Name" required></td>
            <td><input type="number" name="parts[${partIndex}][comps][${compIndex}][max]" class="form-control form-control-sm" value="0" required></td>
            <td><input type="number" name="parts[${partIndex}][comps][${compIndex}][pass]" class="form-control form-control-sm" value="0" required></td>
            <td><button type="button" class="btn btn-sm btn-link text-danger remove-row"><i class="fa fa-times"></i></button></td>
        `;
        container.appendChild(row);
    }

    if (e.target.closest('.remove-row')) {
        e.target.closest('tr').remove();
    }
});

// Function to add a new part
document.getElementById('addPart').addEventListener('click', function() {
    const container = document.getElementById('partsContainer');
    const partDiv = document.createElement('div');
    partDiv.className = 'card border-primary mb-3 part-card';
    partDiv.innerHTML = `
        <div class="card-header bg-light d-flex justify-content-between align-items-center">
            <input type="text" name="parts[${partCount}][name]" class="form-control form-control-sm w-50" placeholder="Part Name (e.g. Practical)" required>
            <div class="d-flex align-items-center gap-2">
                <label class="small fw-bold">Part Pass:</label>
                <input type="number" name="parts[${partCount}][pass_mark]" class="form-control form-control-sm" style="width:70px" value="0">
                <button type="button" class="btn btn-sm btn-danger remove-part"><i class="fa fa-trash"></i></button>
            </div>
        </div>
        <div class="card-body">
            <table class="table table-sm table-borderless align-middle">
                <tbody class="comp-container">
                    <tr>
                        <td><input type="text" name="parts[${partCount}][comps][0][name]" class="form-control form-control-sm" placeholder="e.g. Lab Test" required></td>
                        <td><input type="number" name="parts[${partCount}][comps][0][max]" class="form-control form-control-sm" value="0" required></td>
                        <td><input type="number" name="parts[${partCount}][comps][0][pass]" class="form-control form-control-sm" value="0" required></td>
                        <td></td>
                    </tr>
                </tbody>
            </table>
            <button type="button" class="btn btn-sm btn-outline-secondary add-comp" data-part-index="${partCount}">+ Add Component</button>
        </div>
    `;
    container.appendChild(partDiv);
    partCount++;
});

// Remove Part
document.addEventListener('click', function(e) {
    if (e.target.closest('.remove-part')) {
        e.target.closest('.part-card').remove();
    }
});
</script>

<?php require 'footer.php'; ?>