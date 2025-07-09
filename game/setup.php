<?php

include 'includes/db.php';

$msg = "";

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['password'])) {
    if ($_POST['password'] === '5877') {
        if (isset($_POST['reset'])) {
            $tables = [];
            $result = $conn->query("SHOW TABLES");
            while ($row = $result->fetch_array()) {
                $tables[] = $row[0];
            }
            foreach ($tables as $table) {
                $conn->query("DROP TABLE IF EXISTS $table");
            }
            $msg = "Tables have been reset.";
        }
        if (isset($_POST['setup'])) {
            // Create tables
            $conn->query("CREATE TABLE IF NOT EXISTS players (
                id INT AUTO_INCREMENT PRIMARY KEY,
                username VARCHAR(50) UNIQUE,
                password VARCHAR(255),
                coins INT DEFAULT 0,
                is_blocked BOOLEAN DEFAULT 0
            )");
            $conn->query("CREATE TABLE IF NOT EXISTS transactions (
                id INT AUTO_INCREMENT PRIMARY KEY,
                player_id INT,
                txn_id VARCHAR(100),
                amount INT,
                approved BOOLEAN DEFAULT 0,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )");
            $conn->query("CREATE TABLE IF NOT EXISTS bets (
                id INT AUTO_INCREMENT PRIMARY KEY,
                game VARCHAR(30),
                player_id INT,
                amount INT,
                outcome VARCHAR(50),
                result BOOLEAN,
                round INT DEFAULT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )");
            $msg = "Setup complete.";
        }
    } else {
        $msg = "Wrong password.";
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Setup or Reset</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bulma@0.9.1/css/bulma.min.css">
    <style>
        body {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            background: #f9f9f9;
        }
        .container {
            background: white;
            padding: 40px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            max-width: 400px;
            width: 100%;
        }
        .button {
            background-color: #2ecc71;
            color: white;
        }
        .button:hover {
            background-color: #27ae60;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1 class="title">Setup or Reset</h1>
        <form action="" method="post">
            <div class="notification is-danger is-light"><?php echo htmlspecialchars($msg); ?></div>
            <div class="field">
                <label class="label">Password</label>
                <div class="control">
                    <input class="input" type="password" name="password" required>
                </div>
            </div>
            <div class="field is-grouped">
                <div class="control">
                    <button class="button is-primary" name="setup">Setup</button>
                </div>
                <div class="control">
                    <button class="button is-link" name="reset">Reset</button>
                </div>
            </div>
        </form>
        <p class="has-text-centered mt-3">
            <a href="login.php" class="button is-light">Login</a>
        </p>
    </div>
</body>
</html>




