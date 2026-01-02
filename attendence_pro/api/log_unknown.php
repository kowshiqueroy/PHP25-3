<?php
require '../config/db.php';
$in = json_decode(file_get_contents('php://input'), true);
if (!$in) exit;

$incomingDesc = $in['descriptor'];

// --- 1. SMART CLEANUP ---

$cleanupRes = $conn->query("SELECT id, image_path FROM unknown_logs 
                            WHERE log_time < (NOW() - INTERVAL 60 DAY) 
                            AND id NOT IN (
                                SELECT id FROM (
                                    SELECT id FROM unknown_logs 
                                    ORDER BY log_time DESC LIMIT 1000
                                ) as tmp
                            )");

while($rowDel = $cleanupRes->fetch_assoc()) {
    $fullPath = '../' . str_replace('../', '', $rowDel['image_path']);
    if (file_exists($fullPath)) {
        unlink($fullPath);
    }
    $conn->query("DELETE FROM unknown_logs WHERE id = " . $rowDel['id']);
}

// --- 2. EUCLIDEAN DISTANCE LOGIC ---
function dist($a, $b) {
    $s = 0;
    for($i=0; $i<count($a); $i++) $s += pow($a[$i] - $b[$i], 2);
    return sqrt($s);
}

// --- 3. RECOGNITION LOGIC ---
// Check recent unknowns (last 48h) to see if this "Unknown" is a repeat visitor
$res = $conn->query("SELECT id, face_descriptor FROM unknown_logs WHERE log_time > (NOW() - INTERVAL 2 DAY)");
$matchId = null;

while($row = $res->fetch_assoc()) {
    $storedArray = json_decode($row['face_descriptor']);
    $stored = $storedArray[0]; 
    
    if (dist($incomingDesc, $stored) < 0.5) {
        $matchId = $row['id'];
        break;
    }
}

if ($matchId) {
    // Repeat visitor: Update timestamp so they stay in the "Recent" list
    $conn->query("UPDATE unknown_logs SET log_time = NOW() WHERE id = $matchId");
    echo json_encode(['status' => 'recognized', 'id' => "UNK-$matchId"]);
} else {
    // New visitor: Save image and descriptor
    $img = str_replace(['data:image/jpeg;base64,', ' '], ['', '+'], $in['image']);
    $filename = 'unk_' . time() . '_' . uniqid() . '.jpg';
    $db_path = 'assets/unknown/' . $filename;
    $file_path = '../' . $db_path;

    if(file_put_contents($file_path, base64_decode($img))) {
        $stmt = $conn->prepare("INSERT INTO unknown_logs (face_descriptor, image_path) VALUES (?, ?)");
        $desc = json_encode([$incomingDesc]);
        $stmt->bind_param("ss", $desc, $db_path);
        $stmt->execute();
        echo json_encode(['status' => 'new', 'id' => "NEW"]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Upload directory not writable']);
    }
}