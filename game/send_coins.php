<?php
session_start();
include('includes/db.php');

if (!isset($_SESSION['user_id'])) exit("Unauthorized");
$msg="";
$from_id = $_SESSION['user_id'];
$to_username = $_POST['to_username'];
$amount = intval($_POST['amount']);

if ($amount <= 0) exit("Invalid amount.");

$result = $conn->prepare("SELECT id FROM players WHERE username=? AND is_blocked=0");
$result->bind_param("s", $to_username);
$result->execute();
$res = $result->get_result();

if ($row = $res->fetch_assoc()) {
    $to_id = $row['id'];

    if ($to_id == $from_id) {
        $msg = "You can't send coins to yourself.";
    } else {
        // Deduct from sender
        $conn->query("UPDATE players SET coins = coins - $amount WHERE id = $from_id AND coins >= $amount");



        // Add to recipient
        if ($conn->affected_rows > 0) {
            $conn->query("UPDATE players SET coins = coins + $amount WHERE id = $to_id");
            $msg = "Transfer complete.";

            // Get sender and recipient usernames
            $res = $conn->query("SELECT username FROM players WHERE id = $from_id");
            $from_username = $res->fetch_assoc()['username'];
            $res = $conn->query("SELECT username FROM players WHERE id = $to_id");
            $to_username = $res->fetch_assoc()['username'];

            // Create transaction record for sender
            $txn_id = "Send to $to_username";
            $stmt = $conn->prepare("INSERT INTO transactions (player_id, txn_id, amount, approved) VALUES (?, ?, ?, 1)");
            $stmt->bind_param("isi", $from_id, $txn_id, $amount);
            $stmt->execute();

            // Create transaction record for recipient
            $txn_id = "Received from $from_username";
            $stmt = $conn->prepare("INSERT INTO transactions (player_id, txn_id, amount, approved) VALUES (?, ?, ?, 1)");
            $stmt->bind_param("isi", $to_id, $txn_id, $amount);
            $stmt->execute();
        } else {
            $msg =  "Insufficient balance.";
        }
    }
} else {
    $msg =  "Recipient not found or blocked.";
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Send Coins</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body {
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            background-color: #0d0d0d;
            font-family: 'Orbitron', sans-serif;
            color: #f8f9fa;
            margin: 0;
        }
        h2 {
            text-shadow: 0 0 10px #ffc107;
        }
        .card {
            background-color: #1a1a1a;
            border-radius: 12px;
            transition: transform 0.2s ease-in-out, box-shadow 0.2s;
            box-shadow: 0 0 12px rgba(255, 255, 255, 0.1);
            margin: 20px;
            padding: 20px;
        }
        .card:hover {
            transform: scale(1.03);
            box-shadow: 0 0 12px rgba(255, 255, 255, 0.2);
        }
        .heading {
            font-size: 1.8rem;
            color: #ffc107;
            text-align: center;
            margin-bottom: 20px;
        }
        .coin-display {
            font-size: 1.2rem;
            text-align: center;
        }
        .btn-primary {
            background-color: #28a745;
            border: none;
            font-weight: bold;
        }
        .btn-primary:hover {
            background-color: #218838;
        }
        .btn-primary:active {
            background-color: #1e7e34;
        }
        .text-secondary {
            color: #6c757d;
        }
        @keyframes fadeIn {
            0% { opacity: 0; }
            100% { opacity: 1; }
        }
    </style>
</head>
<body>
    <div class="container py-5 text-center">
        <div class="card">
            <h2 class="heading mb-3">💸 Send Coins</h2>
            <p class="text-secondary mb-4">Transfer coins to another player.</p>
            <div style="animation: fadeIn 2s infinite ease-in-out;" class="alert alert-info"><?= $msg ?></div>
            <br>
            <div style="text-align: center;">
                <button onclick="window.location.href='dashboard.php'" class="btn btn-primary">Back to Dashboard</button>
            </div>
        </div>
    </div>
</body>
</html>
