<?php
// login.php
require_once 'templates/header.php';

// If user is already logged in, redirect to dashboard
if (isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}
?>

<form id="login-form" action="api/auth.php" method="POST">
    <h2>Login</h2>
    <div id="error-message" style="color: red; margin-bottom: 15px;"></div>
    <div class="form-group">
        <label for="username">Username</label>
        <input type="text" id="username" name="username" required>
    </div>
    <div class="form-group">
        <label for="password">Password</label>
        <input type="password" id="password" name="password" required>
    </div>
    <button type="submit">Login</button>
</form>

<script>
// Basic AJAX login handler
document.getElementById('login-form').addEventListener('submit', function(e) {
    e.preventDefault();
    const form = e.target;
    const formData = new FormData(form);
    const errorMessage = document.getElementById('error-message');

    fetch(form.action, {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            window.location.href = 'index.php';
        } else {
            errorMessage.textContent = data.message || 'An unknown error occurred.';
        }
    })
    .catch(error => {
        errorMessage.textContent = 'Failed to connect to the server. Please try again later.';
        console.error('Login Error:', error);
    });
});
</script>

<?php
require_once 'templates/footer.php';
?>
