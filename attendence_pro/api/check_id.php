<?php
require '../config/db.php';
header('Content-Type: application/json');

$emp_id = $_GET['emp_id'] ?? '';
$response = ['exists' => false];

if (!empty($emp_id)) {
    $stmt = $conn->prepare("SELECT id FROM employees WHERE emp_id = ? LIMIT 1");
    $stmt->bind_param("s", $emp_id);
    $stmt->execute();
    if ($stmt->get_result()->num_rows > 0) {
        $response['exists'] = true;
    }
}
echo json_encode($response);