<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit();
}

$username = $_SESSION['username'];

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>shishuBot Chat</title>
    <link rel="stylesheet" href="../assets/style.css">
</head>
<body>
    <div class="chat-container">
        <div class="chat-header">
            <div class="chatbot-name">shishuBot</div>
            <div class="user-info">
                <span>Welcome, <?php echo htmlspecialchars($username); ?>!</span>
                <a href="logout.php" class="logout-link">Logout</a>
            </div>
        </div>
        <div class="chat-box">
            <!-- Chat messages will appear here -->
        </div>
        <form id="chat-form">
            <div class="input-area">
                <input type="text" id="chat-input" placeholder="Type your message...">
                <button type="submit">Send</button>
            </div>
        </form>
    </div>
    <script src="../assets/script.js"></script>
</body>
</html>
