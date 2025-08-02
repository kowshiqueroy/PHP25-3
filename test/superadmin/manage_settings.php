<?php
require_once '../config/config.php';
require_once '../includes/functions.php';

require_login();

if (!has_role(1)) {
    header('Location: ../index.php');
    exit;
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['update_settings'])) {
        $company_name = $_POST['company_name'];

        $sql = "UPDATE settings SET value = ? WHERE name = 'company_name'";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('s', $company_name);
        $stmt->execute();
    }
}

include '../templates/header.php';
?>

<div class="d-flex">
    <?php include '../templates/sidebar.php'; ?>

    <div class="content flex-grow-1">
        <?php include '../templates/topbar.php'; ?>

        <main class="main-content container-fluid">
            <h1 class="mb-4">Manage Settings</h1>

            <div class="card">
                <div class="card-header"><i class="fas fa-cog me-2"></i>Global Settings</div>
                <div class="card-body">
                    <form method="post">
                        <div class="mb-3">
                            <label for="company_name" class="form-label">Company Name</label>
                            <input type="text" name="company_name" id="company_name" class="form-control" value="<?php echo sanitize(get_setting('company_name')); ?>" required>
                        </div>
                        <button type="submit" name="update_settings" class="btn btn-primary"><i class="fas fa-save me-2"></i>Save Settings</button>
                    </form>
                </div>
            </div>
        </main>
    </div>
</div>

<?php include '../templates/footer.php'; ?>