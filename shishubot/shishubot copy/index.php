<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>shishuBot Login</title>
    <link rel="stylesheet" href="../assets/style.css">
</head>
<body>
    <div class="login-container">
        <h1>shishuBot Login</h1>
        <p id="error-message" class="error"></p>
        <form id="login-form">
            <div class="form-group">
                <label for="username">Username:</label>
                <input type="text" id="username" name="username" required>
            </div>
            <div class="form-group">
                <label for="password">Password:</label>
                <input type="password" id="password" name="password" required>
            </div>
            <button type="submit">Login or Register</button>
        </form>
    </div>
    <script>
        document.getElementById('login-form').addEventListener('submit', async (event) => {
            event.preventDefault();

            const username = document.getElementById('username').value;
            const password = document.getElementById('password').value;
            const errorMessage = document.getElementById('error-message');

            errorMessage.textContent = ''; // Clear previous errors

            try {
                const response = await fetch('../api/loginHandler.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `username=${encodeURIComponent(username)}&password=${encodeURIComponent(password)}`,
                });

                const data = await response.json();

                if (data.success) {
                    window.location.href = data.redirect;
                } else {
                    errorMessage.textContent = data.message;
                }
            } catch (error) {
                console.error('Error:', error);
                errorMessage.textContent = 'An unexpected error occurred. Please try again.';
            }
        });
    </script>
</body>
</html>