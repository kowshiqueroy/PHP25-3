<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'includes/db_connect.php';
require_once 'config.php';

session_start();

echo "<p>Login script started.</p>";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    echo "<p>Attempting login for Username: " . htmlspecialchars($username) . "</p>";
    echo "<p>Password (raw): " . htmlspecialchars($password) . "</p>";

    $conn = connect_db();

    $stmt = $conn->prepare("SELECT id, password_hash, role_id, store_id FROM users WHERE username = ?");
    if ($stmt === false) {
        echo "<p>Prepare failed: " . htmlspecialchars($conn->error) . "</p>";
        $conn->close();
        exit();
    }
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows == 1) {
        echo "<p>User found in database.</p>";
        $stmt->bind_result($id, $password_hash, $role_id, $store_id);
        $stmt->fetch();
        echo "<p>Stored password hash: " . htmlspecialchars($password_hash) . "</p>";

        if (password_verify($password, $password_hash)) {
            echo "<p>Password verification successful.</p>";
            // Password is correct, start a new session
            $_SESSION['loggedin'] = TRUE;
            $_SESSION['id'] = $id;
            $_SESSION['username'] = $username;
            $_SESSION['role_id'] = $role_id;
            $_SESSION['store_id'] = $store_id;

            // Redirect to dashboard page
            header("location: dashboard.php");
            exit(); // Important to exit after header redirect
        } else {
            echo "<p>Password verification failed.</p>";
            // Display an error message if password is not valid
            $login_err = "Invalid username or password.";
        }
    } else {
        echo "<p>User not found in database (num_rows is not 1).</p>";
        // Display an error message if username doesn't exist
        $login_err = "Invalid username or password.";
    }

    $stmt->close();
    $conn->close();
}

// If there's a login error, display it on the index page
if (isset($login_err)) {
    $_SESSION['login_error'] = $login_err;
}

header("location: index.php");

?>