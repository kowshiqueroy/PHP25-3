<?php
header('Content-Type: application/json');
require_once __DIR__ . '/db.php';
session_start();
if (empty($_SESSION['is_admin'])) {
    http_response_code(403); echo json_encode(['error'=>'Forbidden']); exit;
}
$pdo = getPDO();
$act = $_GET['action'] ?? '';

if ($act==='dashboard') {
    // Low performing: intents with success_count/usage_count < 0.5
    $low = $pdo->query(
      "SELECT i.name, SUM(ir.success_count)/SUM(ir.usage_count) AS rate
       FROM intent_responses ir
       JOIN intents i ON i.id=ir.intent_id
       GROUP BY intent_id
       HAVING rate<0.5"
    )->fetchAll();
    // Suggestions
    $sug = $pdo->query(
      "SELECT f.id,u.username,c.message,f.suggestion 
       FROM feedbacks f
       JOIN conversations c ON c.id=f.conversation_id
       JOIN users u ON u.id=c.user_id
       WHERE f.helpful=0 AND f.suggestion<>''"
    )->fetchAll();
    echo json_encode(['low'=>$low,'suggestions'=>$sug]);
}