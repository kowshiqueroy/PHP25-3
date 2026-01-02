<?php
require '../config/db.php'; 
header('Content-Type: application/json');

// Using LEFT JOIN to ensure employees show up even if their department is deleted
$sql = "SELECT 
            e.id, 
            e.name, 
            e.emp_id, 
            e.face_descriptors, 
            e.position, 
            e.photo_path,
            COALESCE(d.name, 'Unassigned') AS dept_name 
        FROM employees e 
        LEFT JOIN departments d ON e.department_id = d.id";

$result = $conn->query($sql);

$employees = [];
if ($result) {
    // while($row = $result->fetch_assoc()) {
    //     if (!empty($row['face_descriptors']) && $row['face_descriptors'] !== '[]') {
    //         $employees[] = $row;
    //     }
    // }

    // ... inside the while loop
while($row = $result->fetch_assoc()) {
    if (!empty($row['face_descriptors']) && $row['face_descriptors'] !== '[]') {
        // Ensure photo_path is a full URL or relative to the root
        $row['photo_path'] = $row['photo_path'] ? $row['photo_path'] : 'assets/img/default-avatar.png';
        $employees[] = $row;
    }
}
}

echo json_encode($employees);
?>