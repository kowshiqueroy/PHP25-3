<?php

// Create folders
$folders = array(
    'admin',
    'assets/css',
);

foreach ($folders as $folder) {
    if (!file_exists($folder)) {
        mkdir($folder, 0777, true);
    }
}

// Create files
$files = array(
    'index.php',
    'lessons.php',
    'quizzes.php',
    'problems.php',
    'admin/login.php',
    'admin/dashboard.php',
    'admin/manage_lessons.php',
    'admin/manage_quizzes.php',
    'admin/manage_problems.php',
    'assets/css/style.css',
    'db.php',
);

foreach ($files as $file) {
    if (!file_exists($file)) {
        touch($file);
    }
}
