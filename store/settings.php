<?php 
require_once 'header.php'; 
checkAuth(['admin']); 
// --- USER MANAGEMENT LOGIC ---

// 1. Create User
if(isset($_POST['create_user'])) {
    $u = trim($_POST['new_username']);
    $p = password_hash($_POST['new_password'], PASSWORD_DEFAULT);
    $r = $_POST['new_role'];
    
    // Check if username exists
    $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
    $stmt->execute([$u]);
    if($stmt->rowCount() > 0) {
        echo "<script>alert('Error: Username already exists!');</script>";
    } else {
        $stmt = $pdo->prepare("INSERT INTO users (username, password, role) VALUES (?, ?, ?)");
        $stmt->execute([$u, $p, $r]);
        echo "<script>alert('User Created Successfully!');</script>";
    }
}

// 2. Update User (Role or Password)
if(isset($_POST['update_user'])) {
    $uid = $_POST['user_id'];
    $role = $_POST['role'];
    $new_pass = $_POST['new_pass']; // Optional

    // Update Role
    $stmt = $pdo->prepare("UPDATE users SET role = ? WHERE id = ?");
    $stmt->execute([$role, $uid]);

    // Update Password (only if typed)
    if(!empty($new_pass)) {
        $hash = password_hash($new_pass, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
        $stmt->execute([$hash, $uid]);
    }
    echo "<script>alert('User Updated!');</script>";
}

// 3. Delete User
if(isset($_POST['delete_user'])) {
    $uid = $_POST['user_id'];
    if($uid == $_SESSION['user_id']) {
        echo "<script>alert('You cannot delete your own account!');</script>";
    } else {
        $pdo->prepare("DELETE FROM users WHERE id = ?")->execute([$uid]);
        echo "<script>alert('User Deleted!');</script>";
    }
}
if(isset($_POST['save_settings'])) {
    foreach($_POST['settings'] as $key => $val) {
        $stmt = $pdo->prepare("REPLACE INTO settings (setting_key, setting_value) VALUES (?, ?)");
        $stmt->execute([$key, $val]);
    }
    echo "<script>alert('Settings Saved!');</script>";
}

// Fetch current settings
$settings = [];
$stmt = $pdo->query("SELECT * FROM settings");
while($row = $stmt->fetch()){ $settings[$row['setting_key']] = $row['setting_value']; }
?>

<h2>Admin Settings & Report Configuration</h2>

<form method="POST">
    <div class="form-grid">
        <div style="background:white; padding:15px; border-radius:8px; grid-column: span 2;">
            <h3>🏢 Company Details (For Reports)</h3>
            <div class="form-group"><label>Company Name</label><input type="text" name="settings[company_name]" value="<?= $settings['company_name'] ?? '' ?>"></div>
            <div class="form-group"><label>Address</label><input type="text" name="settings[company_address]" value="<?= $settings['company_address'] ?? '' ?>"></div>
            <div class="form-group"><label>Phone / Email</label><input type="text" name="settings[company_contact]" value="<?= $settings['company_contact'] ?? '' ?>"></div>
            <div class="form-group"><label>Logo URL (Relative path or Image Link)</label><input type="text" name="settings[company_logo]" value="<?= $settings['company_logo'] ?? '' ?>" placeholder="e.g., logo.png"></div>
        </div>

        <div style="background:white; padding:15px; border-radius:8px;">
            <h3>✍️ Signature Options</h3>
            <p style="font-size:0.8rem; color:#666;">Leave blank to hide.</p>
            <div class="form-group"><label>Sign Line 1 (Left)</label><input type="text" name="settings[sign_1]" value="<?= $settings['sign_1'] ?? 'Prepared By' ?>"></div>
            <div class="form-group"><label>Sign Line 2 (Center)</label><input type="text" name="settings[sign_2]" value="<?= $settings['sign_2'] ?? 'Checked By' ?>"></div>
            <div class="form-group"><label>Sign Line 3 (Right)</label><input type="text" name="settings[sign_3]" value="<?= $settings['sign_3'] ?? 'Authorized By' ?>"></div>
        </div>

        <div style="background:white; padding:15px; border-radius:8px;">
             <h3>System</h3>
             <a href="login.php?logout=1" class="btn btn-danger">Logout</a>
        </div>
    </div>
    <br>
    <button type="submit" name="save_settings" class="btn" style="width:100%; padding:15px;">💾 Save All Settings</button>
</form>

<div style="background:white; padding:15px; border-radius:8px; grid-column: span 2;">
    <h3 style="border-bottom:1px solid #eee; padding-bottom:10px;">👤 User Management</h3>

    <div style="background:#f9fafb; padding:15px; border-radius:6px; margin-bottom:20px;">
        <h4 style="margin-top:0;">+ Create New User</h4>
        <form method="POST" style="display:flex; gap:10px; flex-wrap:wrap; align-items:end;">
            <div style="flex:1; min-width:150px;">
                <label>Username</label>
                <input type="text" name="new_username" required placeholder="Login ID">
            </div>
            <div style="flex:1; min-width:150px;">
                <label>Password</label>
                <input type="text" name="new_password" required placeholder="Secret">
            </div>
            <div style="width:120px;">
                <label>Role</label>
                <select name="new_role">
                    <option value="staff">Staff</option>
                    <option value="viewer">Viewer</option>
                    <option value="admin">Admin</option>
                </select>
            </div>
            <button type="submit" name="create_user" class="btn" style="height:38px;">Create</button>
        </form>
    </div>

    <h4>Existing Users</h4>
    <div style="overflow-x:auto;">
        <table style="width:100%; font-size:0.9rem;">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Username</th>
                    <th>Change Role</th>
                    <th>Reset Password (Optional)</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $stmt = $pdo->query("SELECT * FROM users ORDER BY id ASC");
                while($u = $stmt->fetch()):
                    $is_me = ($u['id'] == $_SESSION['user_id']);
                ?>
                <tr>
                    <form method="POST">
                        <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
                        <td><?= $u['id'] ?></td>
                        <td>
                            <strong><?= htmlspecialchars($u['username']) ?></strong>
                            <?= $is_me ? '<span style="color:green; font-size:0.8em;">(You)</span>' : '' ?>
                        </td>
                        <td>
                            <select name="role" style="padding:5px;">
                                <option value="admin" <?= $u['role']=='admin'?'selected':'' ?>>Admin</option>
                                <option value="staff" <?= $u['role']=='staff'?'selected':'' ?>>Staff</option>
                                <option value="viewer" <?= $u['role']=='viewer'?'selected':'' ?>>Viewer</option>
                            </select>
                        </td>
                        <td>
                            <input type="text" name="new_pass" placeholder="Type to reset" style="width:120px; padding:5px;">
                        </td>
                        <td>
                            <button type="submit" name="update_user" class="btn" style="background:#2563eb; padding:5px 10px; font-size:0.8rem;">Save</button>
                            <?php if(!$is_me): ?>
                                <button type="submit" name="delete_user" class="btn" style="background:#dc2626; padding:5px 10px; font-size:0.8rem;" onclick="return confirm('Delete user <?= $u['username'] ?>?')">Delete</button>
                            <?php endif; ?>
                        </td>
                    </form>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once 'footer.php'; ?>