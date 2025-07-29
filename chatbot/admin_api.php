<?php
session_start();
require 'db.php';

// Auth check
$stmt = $pdo->prepare('SELECT is_admin FROM users WHERE id = ?');
$stmt->execute([$_SESSION['user_id'] ?? 0]);
if (!$stmt->fetchColumn()) {
    http_response_code(403); exit;
}

// Helpers
require 'learning_utils.php';

$input = json_decode(file_get_contents('php://input'), true);
$action = $_GET['action'] ?? $input['action'] ?? '';

header('Content-Type: application/json');

switch ($action) {
    case 'modelMetrics':
  $last = $pdo->prepare("SELECT value FROM model_config WHERE `key`='last_trained'");
  $last->execute(); $lastTrained = $last->fetchColumn();
  $vocab = $pdo->query("SELECT COUNT(*) FROM word_stats")->fetchColumn();
  echo json_encode(['lastTrained'=>$lastTrained,'vocabSize'=>$vocab]);
  break;
  case 'lowPerformers':
    echo json_encode(getLowPerformingIntents($pdo, 3, 0.3));
    break;

  case 'suggestions':
    echo json_encode(getAllSuggestions($pdo));
    break;

  case 'intents':
    // Fetch all intents + patterns
    $sql = "
      SELECT i.id, i.name, i.default_response, i.tone_tag, i.emotion_tag,
             GROUP_CONCAT(p.pattern SEPARATOR ', ') AS patterns
      FROM intents i
      LEFT JOIN intent_patterns p ON p.intent_id = i.id
      GROUP BY i.id
    ";
    $rows = $pdo->query($sql)->fetchAll();
    // Convert patterns string to array
    foreach ($rows as &$r) {
      $r['patterns'] = array_map('trim', explode(',', $r['patterns']));
    }
    echo json_encode($rows);
    break;

  case 'applySuggestion':
    // Update intent default_response
    $stmt = $pdo->prepare('UPDATE intents SET default_response = ? WHERE id = ?');
    $stmt->execute([$input['suggestion'], $input['intentId']]);
    // Optionally remove the feedback row or mark it processed
    $pdo->prepare('DELETE FROM feedbacks WHERE id = ?')
        ->execute([$input['feedbackId']]);
    echo json_encode(['status'=>'ok']);
    break;

  case 'saveIntent':
    $stmt = $pdo->prepare("
      UPDATE intents
      SET name           = ?,
          default_response = ?,
          tone_tag       = ?,
          emotion_tag    = ?
      WHERE id = ?
    ");
    $stmt->execute([
      $input['name'], $input['resp'], $input['tone'], $input['emo'], $input['id']
    ]);
    echo json_encode(['status'=>'saved']);
    break;

  default:
    echo json_encode(['error'=>'Unknown action']);
}