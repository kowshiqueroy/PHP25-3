<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    header('HTTP/1.0 401 Unauthorized');
    echo "Unauthorized";
    exit();
}

$userId = $_SESSION['user_id'];

$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Clear user-specific memory related to conversation state
setMemory('_last_subject', '', $userId, $conn);
setMemory('_waiting_for_data_for_query', '', $userId, $conn);
setMemory('_expecting_data_for_query', '', $userId, $conn);

// Optionally clear conversation history for the user
$stmt = $conn->prepare("DELETE FROM conversation_history WHERE user_id = ?");
$stmt->bind_param("i", $userId);
$stmt->execute();
$stmt->close();

$conn->close();

echo "Conversation cleared.";

// Helper function (copied from chatbot.php to avoid dependency issues)
function setMemory($key, $value, $userId, $conn) {
    $stmt = $conn->prepare("INSERT INTO user_memory (user_id, memory_key, memory_value) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE memory_value = ?");
    $stmt->bind_param("isss", $userId, $key, $value, $value);
    $stmt->execute();
    $stmt->close();
}

?>