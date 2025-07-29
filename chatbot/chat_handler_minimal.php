<?php
ob_start();
header('Content-Type: application/json');
ob_clean();
echo json_encode(['response'=>'TEST OK','conversationId'=>999]);