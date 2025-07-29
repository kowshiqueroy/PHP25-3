<?php
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/nlp_utils.php';
require_once __DIR__ . '/intent_utils.php';

/**
 * Classify input into intent using Naive Bayes with Laplace smoothing.
 * Returns ['id'=>int|null,'name'=>string|null,'confidence'=>float]
 */
function classifyIntent(PDO $pdo, string $message): array {
    $intents = loadIntents($pdo);
    $tokens = tokenize($message);
    $totalIntents = count($intents);

    // Load global word counts
    $globalStmt = $pdo->query("SELECT SUM(global_count) AS total FROM word_stats");
    $totalWords = (int)$globalStmt->fetchColumn();

    $best = ['id'=>null,'name'=>null,'score'=>-INF];
    foreach ($intents as $id => $intent) {
        // Prior: uniform if not in model_config
        $prior = 1 / $totalIntents;
        $logProb = log($prior);

        // For each token, apply P(word|intent)
        foreach ($tokens as $w) {
            // Word count for intent
            $stmt = $pdo->prepare("SELECT count FROM intent_word_stats WHERE intent_id=? AND word=?");
            $stmt->execute([$id, $w]);
            $count = (int)$stmt->fetchColumn();

            // Vocabulary size: number of distinct words
            $vocabStmt = $pdo->query("SELECT COUNT(*) FROM word_stats");
            $V = (int)$vocabStmt->fetchColumn();

            // Laplace smoothing
          // Load intent-specific total words
$intentWordTotalStmt = $pdo->prepare("SELECT SUM(count) FROM intent_word_stats WHERE intent_id=?");
$intentWordTotalStmt->execute([$id]);
$intentWordTotal = (int) $intentWordTotalStmt->fetchColumn();

// Vocabulary size: number of distinct words
$vocabStmt = $pdo->query("SELECT COUNT(*) FROM word_stats");
$V = (int) $vocabStmt->fetchColumn();

// Prevent division by zero
if ($intentWordTotal + $V === 0) {
    $prob = 1e-6; // tiny default prob
} else {
    $prob = ($count + 1) / ($intentWordTotal + $V);
}
        }

        if ($logProb > $best['score']) {
            $best = ['id' => $id, 'name' => $intent['name'], 'score' => $logProb];
        }
    }
    // Confidence: softmax approx
    $confidence = min(1, max(0, exp($best['score']) / ($totalIntents)));
    return ['id'=>$best['id'],'name'=>$best['name'],'confidence'=>$confidence];
}