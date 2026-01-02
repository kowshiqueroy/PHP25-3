<?php
require '../config/db.php';
$in = json_decode(file_get_contents('php://input'), true);

// Check last log time
$check = $conn->query("SELECT id FROM logs WHERE emp_id = {$in['id']} AND log_time > (NOW() - INTERVAL 1 MINUTE)");

if($check->num_rows == 0) {
    $conn->query("INSERT INTO logs (emp_id) VALUES ({$in['id']})");
    $name = $conn->query("SELECT name FROM employees WHERE id = {$in['id']}")->fetch_assoc()['name'];
    echo json_encode(['status'=>'success', 'name'=>$name]);
} else {
    echo json_encode(['status'=>'ignored']);
}
?>