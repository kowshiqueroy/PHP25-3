<?php
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/classifier.php';

// 1. Test setup
echo "Testing classifier... \n";
$pdo = getPDO();
$res = classifyIntent($pdo, "Hello, how are you?");
var_dump($res);

// 2. Simulate chat
$_SESSION['user_id'] = 1;
$_SESSION['thread_id'] = bin2hex(random_bytes(16));
echo "Simulating chat_handler...\n";
ob_start();
$_POST = [];
$json = json_encode(['message'=>'Test message','tone'=>'default']);
file_put_contents('php://input',$json);
include 'chat_handler.php';
$content = ob_get_clean();
echo "Response: $content\n";

// 3. Feedback
echo "Simulating feedback...\n";
ob_start();
$json2 = json_encode(['conversationId'=>1,'helpful'=>1,'suggestion'=>'']);
file_put_contents('php://input',$json2);
include 'feedback_handler.php';
echo ob_get_clean();

echo "All tests executed.\n";