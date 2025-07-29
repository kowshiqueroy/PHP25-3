<?php

require_once 'includes/db_connect.php';

function check_login() {
    session_start();
    if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== TRUE) {
        header("location: index.php");
        exit;
    }
}

function check_role($required_roles) {
    if (!isset($_SESSION['role_id'])) {
        header("location: index.php"); // Not logged in or role not set
        exit;
    }

    $conn = connect_db();
    $stmt = $conn->prepare("SELECT name FROM roles WHERE id = ?");
    $stmt->bind_param("i", $_SESSION['role_id']);
    $stmt->execute();
    $stmt->bind_result($user_role_name);
    $stmt->fetch();
    $stmt->close();
    $conn->close();

    if (!in_array($user_role_name, $required_roles)) {
        header("location: unauthorized.php"); // Create this page later
        exit;
    }
}

function get_user_role_name($role_id) {
    $conn = connect_db();
    $stmt = $conn->prepare("SELECT name FROM roles WHERE id = ?");
    $stmt->bind_param("i", $role_id);
    $stmt->execute();
    $stmt->bind_result($role_name);
    $stmt->fetch();
    $stmt->close();
    $conn->close();
    return $role_name;
}

?>