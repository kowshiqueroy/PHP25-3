<?php
require_once __DIR__ . '/db.php';
$pdo = getPDO();

// Prune rare words (global_count < 2)
$pdo->exec("DELETE FROM word_stats WHERE global_count < 2");

// Recompute priors in model_config
$totalIntent = $pdo->query("SELECT COUNT(*) FROM intents")->fetchColumn();
$stmt = $pdo->query("SELECT id, COUNT(*) AS uses FROM conversations WHERE intent_id IS NOT NULL GROUP BY intent_id");
while ($row = $stmt->fetch()) {
    $prior = $row['uses'] / $pdo->query("SELECT COUNT(*) FROM conversations WHERE intent_id IS NOT NULL")->fetchColumn();
    $up = $pdo->prepare("INSERT INTO model_config (`key`,`value`) VALUES (?,?) ON DUPLICATE KEY UPDATE `value`=?");
    $up->execute(["prior_{$row['id']}", $prior, $prior]);
}

// Update last_trained
$u2 = $pdo->prepare("INSERT INTO model_config (`key`,`value`) VALUES ('last_trained',?) ON DUPLICATE KEY UPDATE `value`=?");
$now = date('c');
$u2->execute([$now,$now]);

echo "Retraining complete.\n";