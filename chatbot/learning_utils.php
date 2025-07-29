<?php
// learning_utils.php
require 'db.php';

/**
 * Returns an array of intents with poor feedback performance.
 * Each entry: [intent_id, name, default_response, total_feedback, negative_count, ratio]
 */
function getLowPerformingIntents(PDO $pdo, int $minFeedback = 3, float $maxRatio = 0.5) {
    $sql = "
      SELECT
        i.id AS intent_id,
        i.name,
        i.default_response,
        COUNT(f.id) AS total_feedback,
        SUM(f.helpful = 0) AS negative_count,
        SUM(f.helpful = 0) / COUNT(f.id) AS ratio
      FROM intents i
      JOIN conversations c ON c.intent_id = i.id
      JOIN feedbacks f ON f.conversation_id = c.id
      GROUP BY i.id
      HAVING total_feedback >= :minFeedback
         AND ratio >= :maxRatio
      ORDER BY ratio DESC
    ";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':minFeedback' => $minFeedback,
        ':maxRatio'    => $maxRatio
    ]);
    return $stmt->fetchAll();
}

/**
 * Returns an array of all user suggestions (helpful=0 with non-empty suggestion).
 * Each entry: [feedback_id, conversation_id, intent_id, suggestion, created_at]
 */
function getAllSuggestions(PDO $pdo) {
    $sql = "
      SELECT
        f.id AS feedback_id,
        f.conversation_id,
        c.intent_id,
        f.suggestion,
        f.created_at
      FROM feedbacks f
      JOIN conversations c ON c.id = f.conversation_id
      WHERE f.helpful = 0
        AND f.suggestion <> ''
      ORDER BY f.created_at DESC
    ";
    return $pdo->query($sql)->fetchAll();
}