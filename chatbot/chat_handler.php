<?php
// chat_handler.php
// Uses classifyIntent() instead of matchIntent()

ob_start();
session_start();
header('Content-Type: application/json; charset=utf-8');
ini_set('display_errors', 0);
error_reporting(0);

try {
    // --- 1) Core includes ---
    require __DIR__ . '/db.php';            // defines $pdo
    require __DIR__ . '/intent_utils.php';  // defines loadIntents()
    require __DIR__ . '/nlp_utils.php';     // defines tokenize()
    require __DIR__ . '/classifier.php';    // defines classifyIntent(PDO, string)

    // --- 2) Confidence detector ---
    function detectConfidence(?int $intentId): float {
        return $intentId !== null ? 0.90 : 0.30;
    }

    // --- 3) Emotion detector ---
    function detectEmotion(string $text): ?string {
        $mapping = [
            'happy'    => ['great','fantastic','wonderful','🙂'],
            'sad'      => ['sorry','unfortunately'],
            'confused' => ['not sure','confused','what'],
        ];
        $low = mb_strtolower($text);
        foreach ($mapping as $emo => $words) {
            foreach ($words as $w) {
                if (mb_strpos($low, mb_strtolower($w)) !== false) {
                    return $emo;
                }
            }
        }
        return null;
    }

    // --- 4) Tone applicator ---
    function applyTone(string $text, ?string $toneTag): string {
        switch ($toneTag) {
            case 'friendly':
                return "🙂 $text How’s that?";
            case 'formal':
                return "Dear user, $text Thank you.";
            case 'educator':
                return "$text\n\nLet me know if you’d like more details!";
            default:
                return $text;
        }
    }

    // --- 5) Response generator using classifyIntent() ---
    function getBotResponse(
        PDO    $pdo,
        string $msg,
        array  $intents,
        ?string $overrideTone,
        array  $context = []
    ): array {
        // classifyIntent returns ['id'=>int|null,'name'=>string|null]
        $classification = classifyIntent($pdo, $msg);
        $intentId       = $classification['id'];

        if ($intentId !== null && isset($intents[$intentId])) {
            $rawResp    = $intents[$intentId]['response'];
            $intentTone = $intents[$intentId]['tone'];
        } else {
            $rawResp    = "I’m not sure how to answer that yet.";
            $intentTone = null;
        }

        // decide tone
        $toneToUse = ($overrideTone !== '') ? $overrideTone : $intentTone;

        // apply tone
        $finalResp = applyTone($rawResp, $toneToUse);

        return [
            'response' => $finalResp,
            'intentId' => $intentId
        ];
    }

    // --- 6) Read & sanitize input ---
    $rawInput = file_get_contents('php://input');
    $data     = json_decode($rawInput, true) ?? [];
    $userMsg  = isset($data['message']) ? (string)$data['message'] : '';
    $userTone = isset($data['tone'])    ? (string)$data['tone']    : '';

    // --- 7) Session validation ---
    if (!isset($_SESSION['user_id'], $_SESSION['thread_id'])) {
        throw new Exception('Session expired. Please log in.');
    }
    $userId   = $_SESSION['user_id'];
    $threadId = $_SESSION['thread_id'];

    if ($userMsg === '') {
        ob_clean();
        echo json_encode(['response' => 'Please send a valid message.']);
        exit;
    }

    // --- 8) Fetch last 5 turns for context (optional) ---
    $ctxStmt = $pdo->prepare("
        SELECT message, response
        FROM conversations
        WHERE thread_id = ? AND user_id = ?
        ORDER BY created_at DESC
        LIMIT 5
    ");
    $ctxStmt->execute([$threadId, $userId]);
    $recentTurns = $ctxStmt->fetchAll();

    // --- 9) Store user message (placeholder) ---
    $ins = $pdo->prepare("
        INSERT INTO conversations (thread_id, user_id, message, response)
        VALUES (?, ?, ?, '')
    ");
    $ins->execute([$threadId, $userId, $userMsg]);
    $convId = (int)$pdo->lastInsertId();

    // --- 10) Load intents & generate response ---
    $intents = loadIntents($pdo);
    $botData = getBotResponse($pdo, $userMsg, $intents, $userTone, $recentTurns);
    $botResp = $botData['response'];
    $intentId= $botData['intentId'];

    // --- 11) Compute confidence & emotion ---
    $confidence = detectConfidence($intentId);
    $emotion    = detectEmotion($botResp);

    // --- 12) Update conversation with full metadata ---
    $upd = $pdo->prepare("
        UPDATE conversations
        SET response = ?, intent_id = ?, confidence_score = ?, emotion_tag = ?
        WHERE id = ?
    ");
    $upd->execute([$botResp, $intentId, $confidence, $emotion, $convId]);

    // --- 13) Return JSON payload ---
    ob_clean();
    echo json_encode([
        'response'       => $botResp,
        'conversationId' => $convId,
        'confidence'     => $confidence,
        'emotion'        => $emotion
    ]);
}
catch (Throwable $e) {
    // log & return error in JSON
    error_log('chat_handler.php error: ' . $e->getMessage());
    ob_clean();
    echo json_encode([
        'response' => 'Sorry, something went wrong.',
        'error'    => $e->getMessage()
    ]);
}