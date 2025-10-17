<?php
include_once 'config.php';
if (isset($_POST['username']) && isset($_POST['password'])) {
    $username = $conn->real_escape_string($_POST['username']);
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT id, password, status FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->store_result();
    $stmt->bind_result($uid, $hashed_pass, $status);
    if ($stmt->num_rows === 1 && $stmt->fetch() && password_verify($password, $hashed_pass) && $status == 1) {
        session_start();
        $_SESSION['user_id'] = $uid;
        header("Location: dashboard.php");
        exit;
    } else {
        $statusText = '';
        if ($stmt->num_rows === 1 &&  $status == 0) {
            $statusText = ' (Account inactive)';
        }
        else if ($stmt->num_rows === 0) {
            $statusText = ' (User not found)';
        }
        else if ($stmt->num_rows === 1 && !password_verify($password, $hashed_pass)) {
            $statusText = ' (Wrong password)';
        }
        else if ($stmt->num_rows > 1) {
            $statusText = ' (Multiple users found)';
        }
        else if ($stmt->num_rows === 1 && $stmt->fetch() && $status == 2) {
            $statusText = ' (Account suspended)';
        }
        else if ($stmt->num_rows === 1 && $stmt->fetch() && $status == 3) {
            $statusText = ' (Account deleted)';
        }
        else if ($stmt->num_rows === 1 && $stmt->fetch() && $status == 4) {
            $statusText = ' (Account locked)';
        }
        else if ($stmt->num_rows === 1 && $stmt->fetch() && $status == 5) {
            $statusText = ' (Account expired)';
        }
        else if ($stmt->num_rows === 1 && $stmt->fetch() && $status == 6) {
            $statusText = ' (Account disabled)';
        }
        else if ($stmt->num_rows === 1 && $stmt->fetch() && $status == 7) {
            $statusText = ' (Account pending)';
        }
        else if ($stmt->num_rows === 1 && $stmt->fetch() && $status == 8) {
            $statusText = ' (Account blocked)';
        }
        else if ($stmt->num_rows === 1 && $stmt->fetch() && $status == 9) {
            $statusText = ' (Account closed)';
        }
        else {
            $statusText = ' (Unknown error)';
        }
       header("Location: index.php?error=" . $statusText);
    }
    $stmt->close();
}
else {
    header("Location: index.php");
    exit;
}
?>