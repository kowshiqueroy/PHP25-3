<?php
require_once 'config.php';

// --- Admin User ---
$username = 'admin';
$password = 'password';
$role = 'Admin';

// Hash the password
$hashed_password = password_hash($password, PASSWORD_DEFAULT);

// Check if user already exists
$stmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
$stmt->bind_param("s", $username);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows > 0) {
    echo "Admin user already exists.\n";
} else {
    // Insert the new user
    $insert_stmt = $conn->prepare("INSERT INTO users (username, password, role) VALUES (?, ?, ?)");
    $insert_stmt->bind_param("sss", $username, $hashed_password, $role);
    if ($insert_stmt->execute()) {
        echo "Admin user created successfully!\n";
        echo "Username: admin\n";
        echo "Password: password\n";
    } else {
        echo "Error creating admin user: " . $insert_stmt->error . "\n";
    }
    $insert_stmt->close();
}
$stmt->close();

// --- HR User ---
$hr_username = 'hr';
$hr_password = 'password';
$hr_role = 'HR';

// Hash the password
$hr_hashed_password = password_hash($hr_password, PASSWORD_DEFAULT);

// Check if user already exists
$hr_stmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
$hr_stmt->bind_param("s", $hr_username);
$hr_stmt->execute();
$hr_stmt->store_result();

if ($hr_stmt->num_rows > 0) {
    echo "HR user already exists.\n";
} else {
    // Insert the new user
    $hr_insert_stmt = $conn->prepare("INSERT INTO users (username, password, role) VALUES (?, ?, ?)");
    $hr_insert_stmt->bind_param("sss", $hr_username, $hr_hashed_password, $hr_role);
    if ($hr_insert_stmt->execute()) {
        echo "HR user created successfully!\n";
        echo "Username: hr\n";
        echo "Password: password\n";
    } else {
        echo "Error creating HR user: " . $hr_insert_stmt->error . "\n";
    }
    $hr_insert_stmt->close();
}
$hr_stmt->close();


$conn->close();
?>