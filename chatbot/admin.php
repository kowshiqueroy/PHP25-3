<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Fetch current user’s is_admin flag
$stmt = $pdo->prepare('SELECT is_admin FROM users WHERE id = ?');
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();

if (!$user || !$user['is_admin']) {
    exit('Access denied.');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Chatbot Admin Panel</title>
  <style>
    body { font-family: sans-serif; margin:2rem; }
    table { width:100%; border-collapse: collapse; margin-bottom:2rem; }
    th, td { border:1px solid #ccc; padding:.5rem; text-align:left; }
    input, select, button { margin: .2rem; }
  </style>
</head>
<body>
  <h1>Admin Panel</h1>

  <!-- Sections will be injected here -->
  <div id="low-performers"></div>
  <div id="suggestions"></div>
  <div id="intents-management"></div>

  <script src="admin.js"></script>
</body>
</html>