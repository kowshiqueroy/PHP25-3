<?php
require_once 'db.php';

set_time_limit(900); // 15 minutes for heavy processing
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

try {
    echo "<h3>🚀 Seeding Heavy Dataset (Class 10 | 2025)</h3>";

    // 1. CLEANUP
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 0;");
    foreach(['marks', 'subject_components', 'subject_parts', 'subjects', 'classes'] as $tbl) {
        $pdo->exec("TRUNCATE TABLE $tbl;");
    }
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 1;");
    echo "✔ Cleaned existing data.<br>";

    // 2. CREATE CLASS
    $year = "2025";
    $className = "Class 10 (Science)";
    $stmt = $pdo->prepare("INSERT INTO classes (class_name, academic_year, start_roll, end_roll) VALUES (?, ?, ?, ?)");
    $stmt->execute([$className, $year, 1, 50]);
    $class_id = $pdo->lastInsertId();

    // 3. DEFINE 14 SUBJECTS
    // Format: 'Subject Name' => ['is_optional', 'parts' => [ 'Part Name' => [[Comp, Max, Pass], ... ] ] ]
    $subjects_config = [
        'Bangla'          => [0, ['1st Paper' => [['CQ', 70, 23], ['MCQ', 30, 10]], '2nd Paper' => [['CQ', 70, 23], ['MCQ', 30, 10]]]],
        'English'         => [0, ['1st Paper' => [['Read', 50, 17], ['Write', 50, 17]], '2nd Paper' => [['Grammar', 60, 20], ['Comp', 40, 13]]]],
        'Mathematics'     => [0, ['General'   => [['CQ', 70, 23], ['MCQ', 30, 10], ['Extra', 0, 0]]]],
        'Physics'         => [0, ['Theory'    => [['CQ', 50, 17], ['MCQ', 25, 8], ['Practical', 25, 8]]]],
        'Chemistry'       => [0, ['Theory'    => [['CQ', 50, 17], ['MCQ', 25, 8], ['Practical', 25, 8]]]],
        'Biology'         => [0, ['Theory'    => [['CQ', 50, 17], ['MCQ', 25, 8], ['Practical', 25, 8]]]],
        'Higher Math'     => [1, ['Advanced'  => [['CQ', 50, 17], ['MCQ', 25, 8], ['Practical', 25, 8]]]],
        'ICT'             => [0, ['Skills'    => [['Written', 25, 8], ['MCQ', 25, 8], ['Practical', 50, 17]]]],
        'Social Science'  => [0, ['General'   => [['CQ', 70, 23], ['MCQ', 30, 10], ['Project', 0, 0]]]],
        'Religion'        => [0, ['Theory'    => [['CQ', 70, 23], ['MCQ', 30, 10], ['Oral', 0, 0]]]],
        'Physical Ed'     => [0, ['Health'    => [['Theory', 30, 10], ['Practical', 70, 23], ['PT', 0, 0]]]],
        'Career Ed'       => [0, ['Work'      => [['Theory', 25, 8], ['Practical', 25, 8], ['Viva', 0, 0]]]],
        'Arts & Crafts'   => [0, ['Creative'  => [['Theory', 20, 7], ['Drawing', 50, 17], ['Portfolio', 30, 10]]],],
        'Agriculture'     => [1, ['Applied'   => [['CQ', 50, 17], ['MCQ', 25, 8], ['Practical', 25, 8]]]]
    ];

    echo "Inserting subjects and marks for 50 students...<br>";

    // 4. INSERT SUBJECTS, PARTS, COMPONENTS, AND MARKS
    foreach ($subjects_config as $s_name => $meta) {
        $stmt = $pdo->prepare("INSERT INTO subjects (class_id, subject_name, is_optional) VALUES (?, ?, ?)");
        $stmt->execute([$class_id, $s_name, $meta[0]]);
        $sub_id = $pdo->lastInsertId();

        foreach ($meta[1] as $p_name => $comps) {
            $stmt = $pdo->prepare("INSERT INTO subject_parts (subject_id, part_name) VALUES (?, ?)");
            $stmt->execute([$sub_id, $p_name]);
            $part_id = $pdo->lastInsertId();

            $comp_list = [];
            foreach ($comps as $c) {
                $stmt = $pdo->prepare("INSERT INTO subject_components (part_id, component_name, max_marks, pass_mark) VALUES (?, ?, ?, ?)");
                $stmt->execute([$part_id, $c[0], $c[1], $c[2]]);
                $comp_list[] = ['id' => $pdo->lastInsertId(), 'max' => $c[1], 'pass' => $c[2]];
            }

            // INSERT MARKS FOR 50 STUDENTS
            for ($roll = 1; $roll <= 50; $roll++) {
                foreach ($comp_list as $cl) {
                    // Logic: Higher rolls (35-50) have a higher chance of failing for testing
                    $fail_chance = ($roll > 35) ? 30 : 5;
                    $is_fail = (rand(1, 100) <= $fail_chance);
                    
                    $mark = ($cl['max'] == 0) ? 0 : ($is_fail ? rand(0, $cl['pass'] - 1) : rand($cl['pass'], $cl['max']));

                    $stmt = $pdo->prepare("INSERT INTO marks (class_id, student_roll, component_id, marks_obtained, exam_term) VALUES (?, ?, ?, ?, ?)");
                    $stmt->execute([$class_id, $roll, $cl['id'], $mark, 'Annual']);
                }
            }
        }
        echo "✔ Finished Subject: $s_name<br>";
    }

    echo "<hr><h2 style='color:green'>SUCCESS! Seeding Completed.</h2>";
    echo "<p>Total Subjects: 14 | Total Students: 50 | Year: 2025 | Term: Annual</p>";
    echo "<a href='transcript.php?year=2025&class_id=$class_id&term=Annual&roll=1'>View Roll 1 Transcript</a>";

} catch (Exception $e) {
    echo "<div style='color:red; border:2px solid red; padding:15px;'><strong>Fatal Error:</strong> " . $e->getMessage() . "</div>";
}