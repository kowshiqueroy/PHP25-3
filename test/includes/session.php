<?php
// Start the session
session_start();

// Check if the user is logged in
function is_logged_in()
{
    return isset($_SESSION['user_id']);
}

// Redirect to login page if not logged in
function require_login()
{
    if (!is_logged_in()) {
        header('Location: login.php');
        exit;
    }
}
?>