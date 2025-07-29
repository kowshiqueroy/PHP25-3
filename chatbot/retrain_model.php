<?php
// retrain_model.php: prune and recompute priors, clean noise

require 'db.php';

// 1) Load pruning thresholds
$stmt = $pdo->prepare("SELECT value FROM model_config WHERE `key` = 'prune_threshold'");
$stmt->execute();
$pruneThreshold = (int)$stmt->fetchColumn() ?: 2;

// 2) Prune rare words globally
$pdo->prepare("DELETE FROM word_stats WHERE global_count < ?")
    ->execute([$pruneThreshold]);

// 3) Prune rare intent-word entries
$pdo->prepare("
  DELETE iws
  FROM intent_word_stats iws
  JOIN word_stats ws ON ws.word = iws.word
  WHERE ws.global_count < ?
")->execute([$pruneThreshold]);

// 4) Recompute and store priors for each intent
$totalGlobal = (int)$pdo->query("SELECT SUM(global_count) FROM word_stats")
                     ->fetchColumn();

$pdo->prepare("INSERT INTO model_config (`key`,`value`)
             VALUES ('total_global_words',?)
             ON DUPLICATE KEY UPDATE value = VALUES(value)")
    ->execute([$totalGlobal]);

$stmt = $pdo->query("SELECT intent_id, SUM(count) AS total_intent_words
                     FROM intent_word_stats
                     GROUP BY intent_id");
while ($row = $stmt->fetch()) {
    $prior = $row['total_intent_words'] / ($totalGlobal + 1);
    $pdo->prepare("INSERT INTO model_config (`key`,`value`)
                  VALUES (?,?)
                  ON DUPLICATE KEY UPDATE value = VALUES(value)")
        ->execute([ "prior_intent_{$row['intent_id']}", $prior ]);
}

// 5) Update last‐trained timestamp
$pdo->prepare("INSERT INTO model_config (`key`,`value`)
              VALUES ('last_trained', NOW())
              ON DUPLICATE KEY UPDATE value = NOW()")
    ->execute([]);

echo "Retraining complete. Total words: $totalGlobal\n";