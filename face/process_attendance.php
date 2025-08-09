<?php
require_once 'includes/session.php';
require_once 'includes/functions.php';

function find_matching_staff($descriptor) {
    global $conn;
    $result = $conn->query("SELECT * FROM face_images");
    $face_images = $result->fetch_all(MYSQLI_ASSOC);

    foreach ($face_images as $face_image) {
        $stored_descriptor = json_decode($face_image['descriptor']);
        $distance = euclidean_distance($descriptor, $stored_descriptor);
        if ($distance < 0.6) { // Threshold for matching
            $staff_id = $face_image['staff_id'];
            $stmt = $conn->prepare("SELECT * FROM staff WHERE id = ?");
            $stmt->bind_param("i", $staff_id);
            $stmt->execute();
            $staff_result = $stmt->get_result();
            return $staff_result->fetch_assoc();
        }
    }
    return null;
}

function euclidean_distance($a, $b) {
    $sum = 0;
    for ($i = 0; $i < count($a); $i++) {
        $sum += pow($a[$i] - $b[$i], 2);
    }
    return sqrt($sum);
}

function get_next_attendance_type($staff_id) {
    global $conn;
    $stmt = $conn->prepare("SELECT type FROM attendance_logs WHERE staff_id = ? ORDER BY timestamp DESC LIMIT 1");
    $stmt->bind_param("i", $staff_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $last_log = $result->fetch_assoc();

    if (!$last_log) {
        return 'Check-In';
    }

    switch ($last_log['type']) {
        case 'Check-In':
            return 'Break-Out';
        case 'Break-Out':
            return 'Break-In';
        case 'Break-In':
            return 'Check-Out';
        case 'Check-Out':
            return 'Check-In';
        default:
            return 'Check-In';
    }
}

$data = json_decode(file_get_contents('php://input'), true);
$descriptor = $data['descriptor'];

$staff = find_matching_staff($descriptor);

if ($staff) {
    $staff_id = $staff['id'];
    $type = get_next_attendance_type($staff_id);

    $stmt = $conn->prepare("INSERT INTO attendance_logs (staff_id, type, timestamp) VALUES (?, ?, NOW())");
    $stmt->bind_param("is", $staff_id, $type);
    $stmt->execute();

    echo json_encode(['success' => true, 'staff' => $staff, 'type' => $type]);
} else {
    echo json_encode(['success' => false, 'message' => 'No matching staff found.']);
}
?>