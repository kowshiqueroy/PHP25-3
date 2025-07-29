<?php
/**
 * classifier.php
 *
 * Implements a simple Naive Bayes classifier for intent matching.
 * Relies on:
 *   – nlp_utils.php for tokenize()
 *   – intent_utils.php for loadIntents()
 *
 * Returns an array with:
 *   ['id' => int|null, 'name' => string|null]
 */

require_once __DIR__ . '/nlp_utils.php';
require_once __DIR__ . '/intent_utils.php';

function classifyIntent(PDO $pdo, string $message): array
{
    // 1) Tokenize incoming message
    $words = tokenize($message);

    // 2) Load all intents
    // loadIntents() should return an array of:
    //   [ ['id'=>1,'name'=>'greeting',…], ['id'=>2,'name'=>'farewell',…], … ]
    $intents = loadIntents($pdo);

    // 3) Compute total global word count
    $totalGlobal = (int)$pdo
        ->query("SELECT SUM(global_count) FROM word_stats")
        ->fetchColumn();

    $numIntents = count($intents);
    $best = ['id' => null, 'name' => null, 'score' => -INF];

    // 4) Prepare statements for speed
    $stmtTotal = $pdo->prepare("
        SELECT SUM(count)
        FROM intent_word_stats
        WHERE intent_id = ?
    ");
    $stmtCount = $pdo->prepare("
        SELECT count
        FROM intent_word_stats
        WHERE intent_id = ? AND word = ?
    ");

    // 5) Evaluate each intent
    foreach ($intents as $intent) {
        $intentId   = (int)$intent['id'];
        $intentName = $intent['name'];

        // Compute total words observed for this intent (+1 for Laplace smoothing)
        $stmtTotal->execute([$intentId]);
        $intentWords = (int)$stmtTotal->fetchColumn() + 1;

        // Prior probability: P(intent) ≈ totalWords_in_intent / (totalGlobal + N_intents)
        $score = log($intentWords / ($totalGlobal + $numIntents));

        // Likelihood: multiply P(word|intent) for each word (in log space)
        foreach ($words as $w) {
            $stmtCount->execute([$intentId, $w]);
            $count = (int)$stmtCount->fetchColumn();
            // Laplace: (count + 1) / (intentWords + 1)
            $score += log(($count + 1) / ($intentWords + 1));
        }

        // Track the best-scoring intent
        if ($score > $best['score']) {
            $best = [
                'id'    => $intentId,
                'name'  => $intentName,
                'score' => $score
            ];
        }
    }

    // 6) Threshold check: if even the best score is very low, treat as unknown
    if ($best['score'] < log(1e-4)) {
        return ['id' => null, 'name' => null];
    }

    // 7) Return the winning intent
    return ['id' => $best['id'], 'name' => $best['name']];
}