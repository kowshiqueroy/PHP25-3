<?php
// setup.php
error_reporting(E_ALL);
ini_set('display_errors', 1);

$host = 'localhost'; 
$user = 'root'; 
$pass = ''; // Adjust as needed

$conn = new mysqli($host, $user, $pass);
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

$msg = "";

// --- HANDLE SETUP ACTIONS ---
if (isset($_POST['setup_db'])) {
    $conn->query("CREATE DATABASE IF NOT EXISTS attendance_pro");
    $msg = "Database 'attendance_pro' created/verified.";
}

if (isset($_POST['setup_tables'])) {
    $conn->select_db("attendance_pro");
    
    $queries = [
        "CREATE TABLE IF NOT EXISTS admins (
            id INT AUTO_INCREMENT PRIMARY KEY,
            username VARCHAR(50) UNIQUE,
            password VARCHAR(255),
            pin_code VARCHAR(10) DEFAULT '1234'
        )",
        "CREATE TABLE IF NOT EXISTS departments (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(100)
        )",
        "CREATE TABLE IF NOT EXISTS employees (
            id INT AUTO_INCREMENT PRIMARY KEY,
            emp_id VARCHAR(20) UNIQUE,
            name VARCHAR(100),
            department_id INT,
            position VARCHAR(100),
            joining_date DATE,
            face_descriptors MEDIUMTEXT,
            photo_path VARCHAR(255),
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )",
        "CREATE TABLE IF NOT EXISTS logs (
            id INT AUTO_INCREMENT PRIMARY KEY,
            emp_id INT,
            log_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (emp_id) REFERENCES employees(id) ON DELETE CASCADE
        )",
        "CREATE TABLE IF NOT EXISTS unknown_logs (
            id INT AUTO_INCREMENT PRIMARY KEY,
            face_descriptor TEXT,
            image_path VARCHAR(255),
            log_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )",
        "CREATE TABLE IF NOT EXISTS settings (
            setting_key VARCHAR(50) PRIMARY KEY,
            setting_value TEXT,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )"
    ];

    foreach ($queries as $q) $conn->query($q);

    // Initial Global Settings
    $conn->query("INSERT IGNORE INTO settings (setting_key, setting_value) VALUES ('global_AI_pin', '1234')");
    $conn->query("INSERT IGNORE INTO settings (setting_key, setting_value) VALUES ('capture_unknown', '1')");

    // Default Admin & Departments
    $admin_pass = password_hash('admin123', PASSWORD_DEFAULT);
    $conn->query("INSERT IGNORE INTO admins (username, password) VALUES ('admin', '$admin_pass')");
    $conn->query("INSERT IGNORE INTO departments (name) VALUES ('General'), ('IT'), ('HR')");

    $msg = "Tables initialized and default settings applied!";
}

?>

<!DOCTYPE html>
<html>
<head>
    <title>Attendance Pro | Database Setup</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: #0f172a; color: white; display: flex; align-items: center; justify-content: center; height: 100vh; }
        .setup-card { background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); border-radius: 20px; padding: 40px; text-align: center; backdrop-filter: blur(10px); }
    </style>
</head>
<body>

<div class="setup-card shadow-lg">
    <h2 class="fw-bold mb-4 text-info">System Installation</h2>
    
    <?php if($msg): ?>
        <div class="alert alert-success bg-success bg-opacity-25 text-white border-0 small mb-4"><?= $msg ?></div>
    <?php endif; ?>

    <p class="text-white-50 mb-4">Select an action to configure your environment:</p>

    <form method="POST" class="d-grid gap-3">
        <button type="submit" name="setup_db" class="btn btn-outline-info py-3 fw-bold">
            1. Create Database
        </button>
        <button type="submit" name="setup_tables" class="btn btn-primary py-3 fw-bold shadow">
            2. Initialize Tables & Default Settings
        </button>
    </form>

    <div class="mt-4 pt-4 border-top border-secondary border-opacity-25">
        <p class="text-danger small mb-0"><i class="fas fa-exclamation-triangle"></i> Important: Delete this file after setup for security.</p>
        <a href="index.php" class="btn btn-link text-white-50 mt-2">Go to Login &rarr;</a>
    </div>
</div>

</body>
</html>