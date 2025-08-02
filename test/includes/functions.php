<?php
require_once 'session.php';

// Function to check user credentials
function authenticate($username, $password)
{
    global $conn;

    $sql = "SELECT * FROM users WHERE username = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('s', $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 1) {
        $user = $result->fetch_assoc();
        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['role_id'] = $user['role_id'];
            $_SESSION['department_id'] = $user['department_id'];
            return true;
        }
    }

    return false;
}

// Function to check user role
function has_role($role_id)
{
    return isset($_SESSION['role_id']) && $_SESSION['role_id'] == $role_id;
}

// Function to check user department
function has_department($department_id)
{
    return isset($_SESSION['department_id']) && $_SESSION['department_id'] == $department_id;
}

// Function to get user data
function get_user($user_id)
{
    global $conn;

    $sql = "SELECT * FROM users WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    return $result->fetch_assoc();
}

// Function to check user permissions
function has_permission($module, $action)
{
    global $conn;

    if (!is_logged_in()) {
        return false;
    }

    $role_id = $_SESSION['role_id'];

    $sql = "SELECT * FROM role_permissions WHERE role_id = ? AND module = ? AND $action = 1";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('is', $role_id, $module);
    $stmt->execute();
    $result = $stmt->get_result();

    return $result->num_rows == 1;
}

// Function to get a specific setting value
function get_setting($name)
{
    global $conn;

    $sql = "SELECT value FROM settings WHERE name = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('s', $name);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 1) {
        $row = $result->fetch_assoc();
        return $row['value'];
    }

    return null;
}

// Function to get department name from ID
function get_department_name($id)
{
    global $conn;

    if (is_null($id)) {
        return 'N/A';
    }

    $sql = "SELECT name FROM departments WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 1) {
        $row = $result->fetch_assoc();
        return $row['name'];
    }

    return null;
}

// Function to get role name from ID
function get_role_name($id)
{
    global $conn;

    $sql = "SELECT name FROM roles WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 1) {
        $row = $result->fetch_assoc();
        return $row['name'];
    }

    return null;
}

// Function to sanitize output to prevent XSS
function sanitize($string)
{
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}
?>