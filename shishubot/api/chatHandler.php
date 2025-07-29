<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
ob_start();
header('Content-Type: application/json');
session_start();

require_once '../config/db.php';
require_once '../lib/BotEngine.php';

$response = ['success' => false, 'message' => '', 'bot_response' => ''];

if (!isset($_SESSION['user_id'])) {
    $response['message'] = "User not logged in.";
    echo json_encode($response);
    ob_end_flush();
    exit();
}

$userId = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_message = $_POST['message'] ?? '';

    if (empty($user_message)) {
        $response['message'] = "Message cannot be empty.";
    } else {
        try {
            $botEngine = new BotEngine($pdo, $userId);
            $full_bot_response_parts = [];

            // Split the user message by common sentence delimiters
            $statements = preg_split('/[.;,](?=\s*[a-zA-Z])/', $user_message, -1, PREG_SPLIT_NO_EMPTY);
            if (empty($statements)) {
                $statements = [$user_message]; // If no delimiters, treat as a single statement
            }

            foreach ($statements as $statement) {
                $statement = trim($statement);
                if (!empty($statement)) {
                    $bot_result = $botEngine->processMessage($statement);
                    $full_bot_response_parts[] = $bot_result['response'];

                    // Append resolved questions if any
                    if (!empty($bot_result['resolved_questions'])) {
                        $full_bot_response_parts[] = "\nI also resolved some pending questions:";
                        foreach ($bot_result['resolved_questions'] as $resolved_q) {
                            $full_bot_response_parts[] = "- For your question: \"{$resolved_q['question_text']}\", the answer is now: '{$resolved_q['definition']}'.";
                        }
                    }
                }
            }

            $response['success'] = true;
            $response['bot_response'] = implode("\n\n", $full_bot_response_parts);

        } catch (Exception $e) {
            $response['message'] = "Bot error: " . $e->getMessage();
        }
    }
} else {
    $response['message'] = "Invalid request method.";
}

echo json_encode($response);
ob_end_flush();
?>