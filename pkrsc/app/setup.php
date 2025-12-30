<?php
require 'db.php';



echo "<h3>Initializing EduResult Pro...</h3>";

// 2. Settings Table
$sql = "CREATE TABLE IF NOT EXISTS settings (
    id INT PRIMARY KEY DEFAULT 1,
    school_name VARCHAR(255) NOT NULL,
    school_address TEXT,
    school_phone VARCHAR(50),
    school_email VARCHAR(100),
    school_logo VARCHAR(255),
    established VARCHAR(50),
    emis VARCHAR(100),
    school_code VARCHAR(100),
    website VARCHAR(100),
    banner_text VARCHAR(255),
    banner_image VARCHAR(255)

    
)";
$pdo->exec($sql);
echo "Table 'settings' created.<br>";

// 3. Users Table (Admin)
$sql = "CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    last_login TIMESTAMP NULL,
    role VARCHAR(20) DEFAULT 'admin'
)";
$pdo->exec($sql);
echo "Table 'users' created.<br>";

// 4. Classes Table (Roll Ranges)
$sql = "CREATE TABLE IF NOT EXISTS classes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    class_name VARCHAR(50) NOT NULL,
    academic_year VARCHAR(20) NOT NULL,
    start_roll INT NOT NULL,
    end_roll INT NOT NULL
)";
$pdo->exec($sql);
echo "Table 'classes' created.<br>";
$sql = "CREATE TABLE IF NOT EXISTS students (
    student_id INT AUTO_INCREMENT PRIMARY KEY,
    class_id INT NOT NULL,
    roll_no INT NOT NULL,
    student_name VARCHAR(100) NOT NULL,
    father_name VARCHAR(100),
    address TEXT,
    phone VARCHAR(20),
    photo_path VARCHAR(255) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (class_id) REFERENCES classes(id) ON DELETE CASCADE
);";
$pdo->exec($sql);
echo "Table 'students' created.<br>";
// 5. Subjects Table
$sql = "CREATE TABLE IF NOT EXISTS subjects (
    id INT AUTO_INCREMENT PRIMARY KEY,
    class_id INT NOT NULL,
    subject_name VARCHAR(100) NOT NULL,
    subject_code VARCHAR(20),
    overall_pass_mark DECIMAL(5,2) DEFAULT 0,
    is_optional TINYINT(1) DEFAULT 0,
    FOREIGN KEY (class_id) REFERENCES classes(id) ON DELETE CASCADE
)";
$pdo->exec($sql);
echo "Table 'subjects' created.<br>";

// 6. Subject Parts (e.g., Paper 1, Paper 2)
$sql = "CREATE TABLE IF NOT EXISTS subject_parts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    subject_id INT NOT NULL,
    part_name VARCHAR(100) NOT NULL,
    part_pass_mark DECIMAL(5,2) DEFAULT 0,
    FOREIGN KEY (subject_id) REFERENCES subjects(id) ON DELETE CASCADE
)";
$pdo->exec($sql);
echo "Table 'subject_parts' created.<br>";

// 7. Subject Components (e.g., MCQ, CQ, Practical)
$sql = "CREATE TABLE IF NOT EXISTS subject_components (
    id INT AUTO_INCREMENT PRIMARY KEY,
    part_id INT NOT NULL,
    component_name VARCHAR(50) NOT NULL,
    max_marks DECIMAL(5,2) NOT NULL,
    pass_mark DECIMAL(5,2) NOT NULL,
    FOREIGN KEY (part_id) REFERENCES subject_parts(id) ON DELETE CASCADE
)";
$pdo->exec($sql);
echo "Table 'subject_components' created.<br>";

// 8. Marks Table
$sql = "CREATE TABLE IF NOT EXISTS marks (
    id INT AUTO_INCREMENT PRIMARY KEY,
    class_id INT NOT NULL,
    student_roll INT NOT NULL,
    component_id INT NOT NULL,
    exam_term VARCHAR(50) NOT NULL,
    marks_obtained DECIMAL(5,2) DEFAULT 0,
    is_confirmed TINYINT(1) DEFAULT 0,
    UNIQUE KEY unique_entry (class_id, student_roll, component_id, exam_term),
    FOREIGN KEY (component_id) REFERENCES subject_components(id) ON DELETE CASCADE
)";
$pdo->exec($sql);



echo "Table 'marks' created.<br>";

// --- SEEDING DATA ---

// Seed Settings
$check = $pdo->query("SELECT count(*) FROM settings")->fetchColumn();
if ($check == 0) {
    $sql = "INSERT INTO settings (id, school_name, school_address, established, emis, school_code, website,school_phone, school_email, school_logo) 
            VALUES (1, 'Parvej Khan Residential School & College', 'Harowa, Debirdanga, Nilphamari', '2018', '00704051910', '487425', 'www.pkrc.com',
             '01724-162121', 'pkrsc.2018@gmail.com', 'uploads/logo.png')";
    $pdo->exec($sql);
    echo "Default settings inserted.<br>";
}

// Seed Admin (pass: admin123)
$check = $pdo->query("SELECT count(*) FROM users WHERE username='admin'")->fetchColumn();
if ($check == 0) {
    $hash = password_hash('admin123', PASSWORD_DEFAULT);
    $sql = "INSERT INTO users (username, password) VALUES ('admin', '$hash')";
    $pdo->exec($sql);
    echo "Admin user created (admin / admin123).<br>";
}

// Seed Sample Class
$check = $pdo->query("SELECT count(*) FROM classes")->fetchColumn();
if ($check == 0) {
    $pdo->exec("INSERT INTO classes (class_name, academic_year, start_roll, end_roll) VALUES ('Class 10', '2024', 1, 50)");
    echo "Sample Class 10 created.<br>";
}

echo "<hr><strong>Setup Complete!</strong> Delete this file or rename it before production use.";
?>

