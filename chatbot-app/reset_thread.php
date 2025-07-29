<?php
require_once __DIR__ . '/config.php';
session_regenerate_id(true);
$_SESSION['thread_id'] = bin2hex(random_bytes(16));
header('Location: chat.php');
exit;