<?php 
require 'includes/header.php'; 

$msg = "";
$error = "";
$current_admin_id = $_SESSION['admin_id'];

// --- 1. AUTO-INITIALIZE SETTINGS TABLE ---
// This ensures the keys exist so the UPDATE command actually finds a row to change
$conn->query("INSERT IGNORE INTO settings (setting_key, setting_value) VALUES ('global_AI_pin', '1234')");
$conn->query("INSERT IGNORE INTO settings (setting_key, setting_value) VALUES ('capture_unknown', '1')");

// --- 2. HANDLE ACTIONS ---

// A. Update Global AI Settings
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_global'])) {
    $pin = $conn->real_escape_string($_POST['global_pin']);
    $capture = isset($_POST['capture_unknown']) ? '1' : '0';
    
    $q1 = $conn->query("UPDATE settings SET setting_value='$pin' WHERE setting_key='global_AI_pin'");
    $q2 = $conn->query("UPDATE settings SET setting_value='$capture' WHERE setting_key='capture_unknown'");
    
    if($q1 && $q2) $msg = "Global settings updated successfully!";
    else $error = "Update failed: " . $conn->error;
}

// B. Add New Admin
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_admin'])) {
    $user = trim($conn->real_escape_string($_POST['username']));
    $pass = password_hash($_POST['password'], PASSWORD_DEFAULT);
    
    if(!empty($user) && !empty($_POST['password'])) {
        $check = $conn->query("SELECT id FROM admins WHERE username = '$user'");
        if($check->num_rows > 0) {
            $error = "Username '$user' already exists!";
        } else {
            if($conn->query("INSERT INTO admins (username, password) VALUES ('$user', '$pass')")) {
                $msg = "New admin account created.";
            } else {
                $error = "DB Error: " . $conn->error;
            }
        }
    }
}

// C. Delete Admin
if (isset($_GET['del'])) {
    $id = intval($_GET['del']);
    if ($id == $current_admin_id) {
        $error = "You cannot delete your own account!";
    } else {
        $conn->query("DELETE FROM admins WHERE id = $id");
        $msg = "Admin removed.";
    }
}

// --- 3. FETCH DATA ---
$admins = $conn->query("SELECT id, username FROM admins ORDER BY id ASC");
$set_res = $conn->query("SELECT * FROM settings");
$config = [];
while($r = $set_res->fetch_assoc()) { 
    $config[$r['setting_key']] = $r['setting_value']; 
}
?>

<div class="container pt-4">
    <?php if($msg): ?> <div class="alert alert-success border-0 bg-success bg-opacity-25 text-white mb-4"><?= $msg ?></div> <?php endif; ?>
    <?php if($error): ?> <div class="alert alert-danger border-0 bg-danger bg-opacity-25 text-white mb-4"><?= $error ?></div> <?php endif; ?>

    <div class="row g-4">
        <div class="col-lg-5">
            <h5 class="text-white mb-3 fw-bold"><i class="fa fa-sliders text-info me-2"></i> Global AI Config</h5>
            <div class="glass-card p-4 shadow-lg">
                <form method="POST" action="settings.php">
                    <div class="mb-4">
                        <label class="small text-white-50">Master AI PIN (iPhone Unlock)</label>
                        <input type="text" name="global_pin" class="form-control bg-dark text-white border-secondary" 
                               value="<?= htmlspecialchars($config['global_AI_pin'] ?? '1234') ?>" maxlength="6" required>
                    </div>

                    <div class="form-check form-switch mb-4">
                        <input class="form-check-input" type="checkbox" name="capture_unknown" id="capSw" 
                               <?= (isset($config['capture_unknown']) && $config['capture_unknown'] == '1') ? 'checked' : '' ?>>
                        <label class="form-check-label text-white" for="capSw">Capture Unknown Faces</label>
                    </div>

                    <button type="submit" name="update_global" class="btn btn-info w-100 fw-bold rounded-pill">
                        Save Global Settings
                    </button>
                </form>
            </div>
        </div>

        <div class="col-lg-7">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="text-white mb-0 fw-bold"><i class="fa fa-user-shield text-primary me-2"></i> Administrators</h5>
                <button class="btn btn-sm btn-primary rounded-pill px-3" data-bs-toggle="modal" data-bs-target="#addModal">
                    <i class="fa fa-plus"></i> Add New
                </button>
            </div>

            <div class="glass-card p-0 overflow-hidden shadow-lg">
                <table class="table table-borderless text-white mb-0">
                    <thead class="bg-white bg-opacity-10 small text-white-50">
                        <tr>
                            <th class="ps-4">Username</th>
                            <th class="text-end pe-4">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($row = $admins->fetch_assoc()): ?>
                        <tr class="border-bottom border-white border-opacity-10">
                            <td class="ps-4 fw-bold">
                                <?= htmlspecialchars($row['username']) ?>
                                <?php if($row['id'] == $current_admin_id): ?>
                                    <span class="badge bg-info bg-opacity-25 text-info ms-2">YOU</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-end pe-4">
                                <?php if($row['id'] != $current_admin_id): ?>
                                    <a href="settings.php?del=<?= $row['id'] ?>" class="btn btn-link text-danger p-0" onclick="return confirm('Delete this admin?')">
                                        <i class="fa fa-trash-alt"></i>
                                    </a>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="addModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content glass-card border-secondary" style="background: #0b1120;">
            <div class="modal-header border-0"><h5 class="text-white m-0">New Admin</h5></div>
            <form method="POST" action="settings.php">
                <div class="modal-body">
                    <input type="text" name="username" class="form-control bg-dark text-white border-secondary mb-3" placeholder="Username" required autocomplete="off">
                    <input type="password" name="password" class="form-control bg-dark text-white border-secondary" placeholder="Password" required>
                </div>
                <div class="modal-footer border-0">
                    <button type="submit" name="add_admin" class="btn btn-primary w-100 rounded-pill">Create Account</button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
    .glass-card { background: rgba(255, 255, 255, 0.05); border: 1px solid rgba(255, 255, 255, 0.1); border-radius: 20px; }
    .table td { padding: 15px; }
    .form-switch .form-check-input:checked { background-color: #0dcaf0; border-color: #0dcaf0; }
</style>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<?php require 'includes/footer.php'; ?>