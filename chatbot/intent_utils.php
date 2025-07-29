<?php
// intent_utils.php
require 'db.php';

/**
 * Fetch all intents and their patterns.
 * Returns array[intent_id] = [
 *   'name' => string,
 *   'response' => string,
 *   'tone' => string,
 *   'patterns' => [string, ...]
 * ]
 */
function loadIntents(PDO $pdo) {
    $sql = "
      SELECT i.id, i.name, i.default_response, i.tone_tag, p.pattern
      FROM intents i
      JOIN intent_patterns p ON p.intent_id = i.id
      ORDER BY i.id
    ";
    $stmt = $pdo->query($sql);
    $intents = [];
    while ($row = $stmt->fetch()) {
        $id = $row['id'];
        if (!isset($intents[$id])) {
            $intents[$id] = [
                'name'      => $row['name'],
                'response'  => $row['default_response'],
                'tone'      => $row['tone_tag'],
                'patterns'  => []
            ];
        }
        $intents[$id]['patterns'][] = $row['pattern'];
    }
    return $intents;
}


function pickTemplate(PDO $pdo, int $intentId): string {
  $row = $pdo->prepare("
    SELECT template
    FROM intent_responses
    WHERE intent_id = ?
    ORDER BY success_count/usage_count DESC
    LIMIT 1
  ")->execute([$intentId])->fetch();
  return $row ? $row['template'] : '';
}
/**
 * Simple keyword-based intent matcher.
 * Returns array with keys: id, name, response, tone.
 * Falls back to null if no pattern matches.
 */