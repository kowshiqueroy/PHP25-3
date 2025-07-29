<?php
ob_start();
header('Content-Type: application/json');
try {
    require_once __DIR__ . '/db.php';
    require_once __DIR__ . '/nlp_utils.php';

    $in = json_decode(file_get_contents('php://input'), true);
    $cid = (int)$in['conversationId'];
    $helpful = (int)$in['helpful'];
    $sug = trim($in['suggestion'] ?? '');

    $pdo = getPDO();
    // Insert feedback
    $stmt = $pdo->prepare("INSERT INTO feedbacks (conversation_id,helpful,suggestion) VALUES (?,?,?)");
    $stmt->execute([$cid,$helpful,$sug]);

    // Load the conversation row
    $cstmt = $pdo->prepare("SELECT message,intent_id FROM conversations WHERE id=?");
    $cstmt->execute([$cid]);
    $conv = $cstmt->fetch();
    $msg = $conv['message'];
    $intent_id = $conv['intent_id'];

    if ($helpful) {
        // update word_stats and intent_word_stats
        $words = tokenize($msg);
        foreach ($words as $w) {
            // global
            $u = $pdo->prepare("INSERT INTO word_stats (word,global_count)
              VALUES (?,1) ON DUPLICATE KEY UPDATE global_count=global_count+1");
            $u->execute([$w]);
            // intent-specific
            $u2 = $pdo->prepare("INSERT INTO intent_word_stats (intent_id,word,count)
              VALUES (?,? ,1) ON DUPLICATE KEY UPDATE count=count+1");
            $u2->execute([$intent_id,$w]);
        }
    } else {
        if ($intent_id && $sug) {
            // add new pattern
            $ip = $pdo->prepare("INSERT INTO intent_patterns (intent_id,pattern) VALUES (?,?)");
            $ip->execute([$intent_id,$msg]);
        } elseif (!$intent_id && $sug) {
            // create new intent
            $name = substr($msg,0,20);
            $ci = $pdo->prepare("INSERT INTO intents(name,default_response) VALUES(?,?)");
            $ci->execute([$name,$sug]);
            $newId = $pdo->lastInsertId();
            $ip2 = $pdo->prepare("INSERT INTO intent_patterns(intent_id,pattern) VALUES(?,?)");
            $ip2->execute([$newId,$msg]);
        }
    }

    echo json_encode(['status'=>'ok']);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error'=>$e->getMessage()]);
}
ob_end_flush();