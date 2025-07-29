<?php
// learning_report.php
require 'learning_utils.php';

// Adjust thresholds as needed
$lowPerformers = getLowPerformingIntents($pdo, 5, 0.4);
$suggestions   = getAllSuggestions($pdo);

echo "=== Low-Performing Intents ===\n";
foreach ($lowPerformers as $row) {
    echo "Intent #{$row['intent_id']} ({$row['name']}): "
       . round($row['ratio'] * 100, 1) . "% negative ("
       . "{$row['negative_count']}/{$row['total_feedback']})\n";
}

echo "\n=== User Suggestions ===\n";
foreach ($suggestions as $sug) {
    echo "[{$sug['created_at']}] ConvID {$sug['conversation_id']}, "
       . "Intent {$sug['intent_id']}: \"{$sug['suggestion']}\"\n";
}