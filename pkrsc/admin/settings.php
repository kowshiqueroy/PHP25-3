<?php
require '../config/db.php';
require 'includes/header.php';

$error = '';
$success = '';

// Handle Update Settings
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_settings'])) {
    // Loop through all posted data
    foreach ($_POST['settings'] as $key => $value) {
        // Prepare statement to be safe
        $stmt = $pdo->prepare("UPDATE site_settings SET setting_value = ? WHERE setting_key = ?");
        $stmt->execute([trim($value), $key]);
    }
    $success = 'Settings updated successfully!';
}

// Handle Add New Setting
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_setting'])) {
    $key = trim($_POST['new_key']);
    $value = trim($_POST['new_value']);

    if (!empty($key) && !empty($value)) {
        // Check if key already exists
        $stmt = $pdo->prepare("SELECT * FROM site_settings WHERE setting_key = ?");
        $stmt->execute([$key]);
        if ($stmt->rowCount() > 0) {
            $error = "Setting key '{$key}' already exists.";
        } else {
            $stmt = $pdo->prepare("INSERT INTO site_settings (setting_key, setting_value) VALUES (?, ?)");
            $stmt->execute([$key, $value]);
            $success = 'New setting added successfully!';
        }
    } else {
        $error = 'Both key and value are required for a new setting.';
    }
}

// Fetch all settings
$settings = $pdo->query("SELECT * FROM site_settings")->fetchAll(PDO::FETCH_KEY_PAIR);
?>

<h2 class="mb-4">Site Settings</h2>

<?php if ($success) echo "<div class='alert alert-success'>$success</div>"; ?>
<?php if ($error) echo "<div class='alert alert-danger'>$error</div>"; ?>

<div class="card">
    <h3>Update Settings</h3>
    <form method="POST" action="">
        <?php foreach ($settings as $key => $value): ?>
            <div class="form-group">
                <label for="setting-<?php echo htmlspecialchars($key); ?>"><?php echo htmlspecialchars(ucwords(str_replace('_', ' ', $key))); ?></label>
                <input type="text" id="setting-<?php echo htmlspecialchars($key); ?>" name="settings[<?php echo htmlspecialchars($key); ?>]" class="form-control" value="<?php echo htmlspecialchars($value); ?>">
            </div>
        <?php endforeach; ?>
        <button type="submit" name="update_settings" class="btn btn-primary mt-3">Update Settings</button>
    </form>
</div>

<div class="card">
    <h3>Add New Setting</h3>
    <form method="POST" action="">
        <div class="row">
            <div class="col-md-5">
                <div class="form-group">
                    <label>Setting Key</label>
                    <input type="text" name="new_key" class="form-control" placeholder="e.g., school_logo">
                </div>
            </div>
            <div class="col-md-5">
                <div class="form-group">
                    <label>Setting Value</label>
                    <input type="text" name="new_value" class="form-control" placeholder="e.g., logo.png">
                </div>
            </div>
            <div class="col-md-2 d-flex align-items-end">
                <button type="submit" name="add_setting" class="btn btn-secondary">Add Setting</button>
            </div>
        </div>
    </form>
</div>

<?php require 'includes/footer.php'; ?>
