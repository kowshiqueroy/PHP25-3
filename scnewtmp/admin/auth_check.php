<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function require_auth($role = null) {
    if (!isset($_SESSION['user_id'])) {
        header('Location: login.php');
        exit();
    }

    if ($role && (!isset($_SESSION['role']) || $_SESSION['role'] !== $role)) {
        // Optional: Redirect to a 'not authorized' page or back to index
        header('Location: index.php'); 
        exit();
    }
}
?>