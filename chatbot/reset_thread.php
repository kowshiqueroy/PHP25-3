<?php
session_start();
session_regenerate_id(true);
$_SESSION['thread_id'] = bin2hex(random_bytes(16));
echo json_encode(['status'=>'reset']);