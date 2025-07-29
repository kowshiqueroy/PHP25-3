<?php
require_once '../config/db.php';

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $sql = file_get_contents('../sql/schema.sql');
        if ($sql === false) {
            throw new Exception("Could not read schema.sql file.");
        }
        $pdo->exec($sql);
        $message = "Database schema created/updated successfully!";
    } catch (Exception $e) {
        $message = "Error setting up database: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>shishuBot Database Setup</title>
    <link rel="stylesheet" href="../assets/style.css">
</head>
<body>
    <div class="login-container">
        <h1>Database Setup</h1>
        <?php if (!empty($message)): ?>
            <p><?php echo $message; ?></p>
        <?php endif; ?>
        <form method="POST">
            <button type="submit">Setup Database</button>
        </form>
        <p><a href="../shishubot/index.php">Go to Login</a></p>
    </div>
</body>
</html>
