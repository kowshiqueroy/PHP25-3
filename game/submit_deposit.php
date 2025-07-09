<?php
session_start();
include('includes/db.php');
$msg = "";
if (!isset($_SESSION['user_id'])) exit("Unauthorized");

$user_id = $_SESSION['user_id'];
$txn_id = $_POST['txn_id'];
$amount = intval($_POST['amount']);

if ($amount > 0 && !empty($txn_id)) {
    $stmt = $conn->prepare("INSERT INTO transactions (player_id, txn_id, amount) VALUES (?, ?, ?)");
    $stmt->bind_param("isi", $user_id, $txn_id, $amount);
    if ($stmt->execute()) {
      $msg = "Deposit request submitted. Awaiting approval.";
    } else {
       $msg = "Error: " . $stmt->error;
    }
} else {
   $msg = "Invalid input.";
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
        <div class="card" style="text-align: center;">
            <h2 class="heading mb-3">💸 Deposit</h2>
            <p class="text-secondary mb-4">Wait for approval.</p>
            <div style="animation: fadeIn 2s infinite ease-in-out;" class="alert alert-info"><?= $msg ?></div>
            <br>
            <div style="text-align: center;">
                <button onclick="window.location.href='dashboard.php'" class="btn btn-primary">Back to Dashboard</button>
            </div>
        </div>
    </div>
</body>
</html>