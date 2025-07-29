<?php
// feedback_handler.php

session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Not authenticated']);
    exit;
}

require 'db.php';
require 'nlp_utils.php';  // provides tokenize()

/**
 * Reinforce word‐level stats for a successful intent.
 */
function learnFromFeedback(PDO $pdo, int $intentId, string $message): void {
    $words = tokenize($message);
    foreach ($words as $w) {
        // Update global count
        $pdo->prepare("
            INSERT INTO word_stats (word, global_count)
            VALUES (?, 1)
            ON DUPLICATE KEY
            UPDATE global_count = global_count + 1
        ")->execute([$w]);

        // Update intent‐specific count
        $pdo->prepare("
            INSERT INTO intent_word_stats (intent_id, word, count)
            VALUES (?, ?, 1)
            ON DUPLICATE KEY
            UPDATE count = count + 1
        ")->execute([$intentId, $w]);
    }
}

/**
 * Create a brand‐new intent when none was matched.
 * Returns the new intent’s ID.
 */
function createNewIntent(PDO $pdo, string $trigger, string $response): int {
    $autoName = 'topic_' . bin2hex(random_bytes(4));

    // 1) Insert into intents
    $stmt = $pdo->prepare("
        INSERT INTO intents (name, default_response)
        VALUES (?, ?)
    ");
    $stmt->execute([$autoName, $response]);
    $newId = (int)$pdo->lastInsertId();

    // 2) Seed the trigger pattern
    $pdo->prepare("
        INSERT INTO intent_patterns (intent_id, pattern)
        VALUES (?, ?)
    ")->execute([$newId, $trigger]);

    return $newId;
}

// 1) Read JSON payload
$input      = json_decode(file_get_contents('php://input'), true);
$convId     = $input['conversationId'] ?? null;
$helpful    = isset($input['helpful']) && $input['helpful'] ? 1 : 0;
$suggestion = trim($input['suggestion'] ?? '');

if (!$convId) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing conversationId']);
    exit;
}

// 2) Fetch the original conversation turn
$stmt = $pdo->prepare("
    SELECT intent_id, message
    FROM conversations
    WHERE id = ?
");
$stmt->execute([$convId]);
$conv = $stmt->fetch();

if (!$conv) {
    http_response_code(404);
    echo json_encode(['error' => 'Conversation record not found']);
    exit;
}

$intentId    = $conv['intent_id'];    // may be NULL
$userMessage = $conv['message'];

// 3) Insert feedback record
$insert = $pdo->prepare("
    INSERT INTO feedbacks (conversation_id, helpful, suggestion)
    VALUES (?, ?, ?)
");
$insert->execute([$convId, $helpful, $suggestion]);

// 4) Positive feedback: reinforce learning
if ($helpful === 1 && $intentId !== null) {
    learnFromFeedback($pdo, $intentId, $userMessage);
}

// 5) Negative feedback with suggestion: expand knowledge
if ($helpful === 0 && $suggestion !== '') {
    if ($intentId === null) {
        // No intent matched: create a new topic/intent
        createNewIntent($pdo, $userMessage, $suggestion);
    } else {
        // Intent existed: add the suggestion as a new pattern
        $stmt = $pdo->prepare("
            INSERT INTO intent_patterns (intent_id, pattern)
            VALUES (?, ?)
        ");
        $stmt->execute([$intentId, $suggestion]);
    }
}

echo json_encode(['status' => 'ok']);