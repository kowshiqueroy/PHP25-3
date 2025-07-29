<?php
// train_handler.php
session_start();
header('Content-Type: application/json');
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    exit(json_encode(['error'=>'Not authenticated']));
}

require 'db.php';
$input = json_decode(file_get_contents('php://input'), true);
$intentId    = (int)($input['intentId'] ?? 0);
$newResponse = trim($input['newResponse'] ?? '');

if (!$intentId || $newResponse === '') {
    http_response_code(400);
    exit(json_encode(['error'=>'Invalid data']));
}

// Update default_response for this intent
$stmt = $pdo->prepare("
  UPDATE intents
  SET default_response = ?
  WHERE id = ?
");
$stmt->execute([$newResponse, $intentId]);

echo json_encode(['status'=>'updated']);