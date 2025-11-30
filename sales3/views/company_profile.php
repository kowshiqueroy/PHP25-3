<?php
// views/company_profile.php
require_once __DIR__ . '/layout/header.php';

if (!has_role(ROLE_ADMIN)) {
    echo "<p class='error-message'>Access Denied.</p>";
    require_once __DIR__ . '/layout/footer.php';
    exit();
}

// Data is passed from index.php, but re-fetch for safety or if page is directly accessed
$company_data = get_company_by_id($current_user['company_id']);

if (isset($_GET['message']) && $_GET['message'] === 'updated') {
    $success_message = "Company profile updated successfully!";
}

?>

<div class="view" id="company-view">
    <h2>Company Profile</h2>

    <?php if (isset($success_message)): ?>
        <div class="alert success"><?= htmlspecialchars($success_message) ?></div>
    <?php endif; ?>
    <?php if (isset($error_message)): ?>
        <div class="alert error"><?= htmlspecialchars($error_message) ?></div>
    <?php endif; ?>

    <form action="index.php?page=company_profile" method="POST">
        <div class="form-group">
            <label for="company_name">Company Name:</label>
            <input type="text" id="company_name" name="name" value="<?= htmlspecialchars($company_data['name'] ?? '') ?>" required>
        </div>
        <div class="form-group">
            <label for="company_address">Address:</label>
            <textarea id="company_address" name="address"><?= htmlspecialchars($company_data['address'] ?? '') ?></textarea>
        </div>
        <div class="form-group">
            <label for="company_phone">Phone:</label>
            <input type="text" id="company_phone" name="phone" value="<?= htmlspecialchars($company_data['phone'] ?? '') ?>">
        </div>
        <div class="form-group">
            <label for="company_email">Email:</label>
            <input type="email" id="company_email" name="email" value="<?= htmlspecialchars($company_data['email'] ?? '') ?>">
        </div>
        <button type="submit" name="update_company" class="btn btn-primary">Save Changes</button>
    </form>
</div>

<?php
require_once __DIR__ . '/layout/footer.php';
?>
