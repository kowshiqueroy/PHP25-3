<?php
require 'header.php';

$msg = '';

// Handle Form Submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['school_name'];
    $address = $_POST['school_address'];
    $phone = $_POST['school_phone'];
    $email = $_POST['school_email'];
    $principal = $_POST['principal_name'];
    $session = $_POST['current_session'];

    // Handle File Upload
    $logoPath = $settings['school_logo'] ?? ''; // Keep old logo by default
    if (!empty($_FILES['school_logo']['name'])) {
        $targetDir = "uploads/";
        if (!is_dir($targetDir)) mkdir($targetDir, 0777, true);
        
        $fileName = time() . "_" . basename($_FILES['school_logo']['name']);
        $targetFilePath = $targetDir . $fileName;
        $fileType = strtolower(pathinfo($targetFilePath, PATHINFO_EXTENSION));
        
        $allowTypes = array('jpg', 'png', 'jpeg', 'gif');
        if (in_array($fileType, $allowTypes)) {
            if (move_uploaded_file($_FILES['school_logo']['tmp_name'], $targetFilePath)) {
                $logoPath = $targetFilePath;
            } else {
                $msg = '<div class="alert alert-danger">Error uploading file.</div>';
            }
        } else {
            $msg = '<div class="alert alert-danger">Only JPG, JPEG, PNG, & GIF files are allowed.</div>';
        }
    }

    // Update DB
    $sql = "UPDATE settings SET 
            school_name=?, school_address=?, school_phone=?, school_email=?, 
            principal_name=?, current_session=?, school_logo=? 
            WHERE id=1";
    $stmt = $pdo->prepare($sql);
    if ($stmt->execute([$name, $address, $phone, $email, $principal, $session, $logoPath])) {
        $msg = '<div class="alert alert-success">Settings updated successfully!</div>';
        // Refresh settings variable for immediate display
        $stmt = $pdo->query("SELECT * FROM settings WHERE id=1");
        $settings = $stmt->fetch();
    } else {
        $msg = '<div class="alert alert-danger">Database error.</div>';
    }
}
?>

<div class="row">
    <div class="col-md-12 mb-4">
        <h2 class="fw-bold"><i class="fa-solid fa-gear"></i> System Settings</h2>
        <p class="text-muted">Configure school details, logo, and current academic session.</p>
    </div>
</div>

<?php echo $msg; ?>

<form method="POST" enctype="multipart/form-data">
    <div class="row">
        <div class="col-md-8">
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white fw-bold">General Information</div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label">School Name</label>
                        <input type="text" name="school_name" class="form-control" value="<?php echo htmlspecialchars($settings['school_name'] ?? ''); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Address</label>
                        <textarea name="school_address" class="form-control" rows="2"><?php echo htmlspecialchars($settings['school_address'] ?? ''); ?></textarea>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Phone</label>
                            <input type="text" name="school_phone" class="form-control" value="<?php echo htmlspecialchars($settings['school_phone'] ?? ''); ?>">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" name="school_email" class="form-control" value="<?php echo htmlspecialchars($settings['school_email'] ?? ''); ?>">
                        </div>
                    </div>
                </div>
            </div>

            <div class="card shadow-sm">
                <div class="card-header bg-white fw-bold">Academic Config</div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Principal's Name</label>
                            <input type="text" name="principal_name" class="form-control" value="<?php echo htmlspecialchars($settings['principal_name'] ?? ''); ?>">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Current Session</label>
                            <input type="text" name="current_session" class="form-control" value="<?php echo htmlspecialchars($settings['current_session'] ?? ''); ?>" placeholder="e.g. 2024-2025">
                            <small class="text-muted">This appears on all reports.</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card shadow-sm">
                <div class="card-header bg-white fw-bold">School Logo</div>
                <div class="card-body text-center">
                    <?php if (!empty($settings['school_logo'])): ?>
                        <img src="<?php echo htmlspecialchars($settings['school_logo']); ?>" class="img-fluid mb-3" style="max-height: 150px; border: 1px solid #ddd; padding: 5px;">
                    <?php else: ?>
                        <div class="bg-light p-4 mb-3 border text-muted">No Logo Uploaded</div>
                    <?php endif; ?>
                    
                    <input type="file" name="school_logo" class="form-control mb-2">
                    <small class="text-muted">Recommended: PNG or JPG (Transparent background).</small>
                </div>
            </div>
            
            <button type="submit" class="btn btn-primary w-100 mt-4"><i class="fa-solid fa-save"></i> Save Settings</button>
        </div>
    </div>
</form>

<?php require 'footer.php'; ?>