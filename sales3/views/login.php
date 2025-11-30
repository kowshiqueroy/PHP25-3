<?php
// views/login.php
require_once __DIR__ . '/layout/header.php';
?>

<div class="view" id="login-view">
    <h2>Login to <?= APP_NAME ?></h2>
    <form id="login-form" action="index.php" method="POST">
        <?php if ($login_error): ?>
            <p class="error-message"><?= htmlspecialchars($login_error) ?></p>
        <?php endif; ?>
        <div class="form-group">
            <label for="username">Username:</label>
            <input type="text" id="username" name="username" required>
        </div>
        <div class="form-group">
            <label for="password">Password:</label>
            <input type="password" id="password" name="password" required>
        </div>
        <button type="submit" name="login" class="btn btn-primary">Login</button>
    </form>
</div>

<?php
require_once __DIR__ . '/layout/footer.php';
?>
