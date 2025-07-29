<?php
ob_start();
header('Content-Type: application/json');
try {
    require_once __DIR__ . '/db.php';
    require_once __DIR__ . '/classifier.php';

    $input = json_decode(file_get_contents('php://input'), true);
    $msg = trim($input['message'] ?? '');
    $tone = $input['tone'] ?? 'default';

    session_start();
    if (empty($_SESSION['user_id'])) throw new Exception('Not logged in');
    $pdo = getPDO();

    // Save placeholder
    $insert = $pdo->prepare("INSERT INTO conversations
      (thread_id,user_id,message) VALUES (?,?,?)");
    $insert->execute([$_SESSION['thread_id'],$_SESSION['user_id'],$msg]);
    $convId = $pdo->lastInsertId();

    // Classify
    $res = classifyIntent($pdo, $msg);
    $intent_id = $res['id'];
    $confidence = $res['confidence'];

    // Choose response
    if ($intent_id) {
      // fetch A/B templates
      $stmt = $pdo->prepare("SELECT * FROM intent_responses WHERE intent_id=?");
      $stmt->execute([$intent_id]);
      $templates = $stmt->fetchAll();
      if ($templates) {
        $t = $templates[array_rand($templates)];
        $botResp = $t['template'];
        // update usage_count
        $upd = $pdo->prepare("UPDATE intent_responses SET usage_count=usage_count+1 WHERE id=?");
        $upd->execute([$t['id']]);
      } else {
        // fallback to default_response
        $stmt = $pdo->prepare("SELECT default_response FROM intents WHERE id=?");
        $stmt->execute([$intent_id]);
        $botResp = $stmt->fetchColumn();
      }
    } else {
      $botResp = "I'm not sure I understand. Can you rephrase?";
    }

    // Simple emotion tag
    $emotion = $confidence > 0.7 ? 'neutral' : 'confused';

    // Apply tone (very simple prefix)
    if ($tone==='friendly') $botResp = "Hey there! " . $botResp;
    elseif ($tone==='formal') $botResp = "Good day. " . $botResp;
    elseif ($tone==='educator') $botResp = "Let me explain: " . $botResp;

    // Update response row
    $upd2 = $pdo->prepare("UPDATE conversations
      SET response=?,intent_id=?,confidence_score=?,emotion_tag=?
      WHERE id=?");
    $upd2->execute([$botResp,$intent_id,$confidence,$emotion,$convId]);

    echo json_encode([
      'response'=>$botResp,
      'conversationId'=>$convId,
      'confidence'=>$confidence,
      'emotion'=>$emotion
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error'=>$e->getMessage()]);
}
ob_end_flush();