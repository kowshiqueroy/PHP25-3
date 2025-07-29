<?php
require_once __DIR__ . '/db.php';
session_start();
if (empty($_SESSION['is_admin'])) {
    http_response_code(403);
    echo "Forbidden"; exit;
}
?>
<!DOCTYPE html>
<html>
<head>
  <title>Admin Panel</title>
</head>
<body>
  <h2>Admin Dashboard</h2>
  <div id="content"></div>
  <script src="admin.js"></script>
</body>
</html>