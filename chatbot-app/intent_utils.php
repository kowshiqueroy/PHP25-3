<?php
require_once __DIR__ . '/db.php';

/**
 * Load all intents with their patterns and default response.
 * Returns:
 * [
 *   intentId => [
 *     'name'=>string,
 *     'tone_tag'=>string,
 *     'default_response'=>string,
 *     'patterns'=>[...]
 *   ],
 *   ...
 * ]
 */
function loadIntents(PDO $pdo): array {
    // Load intents
    $stmt = $pdo->query("SELECT id,name,tone_tag,default_response FROM intents");
    $intents = [];
    while ($row = $stmt->fetch()) {
        $intents[$row['id']] = [
            'name' => $row['name'],
            'tone_tag' => $row['tone_tag'],
            'default_response' => $row['default_response'],
            'patterns' => []
        ];
    }
    // Load patterns
    $stmt = $pdo->query("SELECT intent_id,pattern FROM intent_patterns");
    while ($row = $stmt->fetch()) {
        $intents[$row['intent_id']]['patterns'][] = $row['pattern'];
    }
    return $intents;
}