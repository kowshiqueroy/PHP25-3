<?php
$host = "localhost";

    $user = "root";
    $pass = "";
    $db = "bs";

if ($conn = @new mysqli($host, $user, $pass, $db)) {
    // Connection established
} else {
    die("Connection failed: " . $conn->connect_error);
}
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);


// Create hits table if not exists
$conn->query("CREATE TABLE IF NOT EXISTS hits (ip VARCHAR(45) NOT NULL, hits INT NOT NULL DEFAULT '0', PRIMARY KEY (ip)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");



$ip = $_SERVER['REMOTE_ADDR'];
$stmt = $conn->prepare("INSERT INTO hits (ip, hits) VALUES (?, 1) ON DUPLICATE KEY UPDATE hits = hits + 1");
$stmt->bind_param("s", $ip);
$stmt->execute();
$stmt->close();

$total_ip = $conn->query("SELECT COUNT(*) FROM hits")->fetch_assoc()['COUNT(*)'];
$total_hits = $conn->query("SELECT SUM(hits) FROM hits")->fetch_assoc()['SUM(hits)'];





?>

