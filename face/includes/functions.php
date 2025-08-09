<?php
require_once __DIR__ . '/../config.php';

function get_user_by_username($username) {
    global $conn;
    $stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_assoc();
}

function is_admin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'Admin';
}

function is_hr() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'HR';
}

function get_all_staff() {
    global $conn;
    $result = $conn->query("SELECT staff.*, departments.name AS department_name FROM staff LEFT JOIN departments ON staff.department_id = departments.id");
    return $result->fetch_all(MYSQLI_ASSOC);
}

function get_all_departments() {
    global $conn;
    $result = $conn->query("SELECT * FROM departments");
    return $result->fetch_all(MYSQLI_ASSOC);
}

function get_all_cameras() {
    global $conn;
    $result = $conn->query("SELECT * FROM cameras");
    return $result->fetch_all(MYSQLI_ASSOC);
}
?>