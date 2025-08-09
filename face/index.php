<?php
require_once 'includes/session.php';

if (isset($_SESSION['user_id'])) {
    if (is_admin()) {
        header("Location: admin/index.php");
    } else {
        header("Location: hr/index.php");
    }
    exit();
} else {
    header("Location: login.php");
    exit();
}
?>