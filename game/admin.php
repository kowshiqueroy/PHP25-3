<?php
session_start();
include('includes/db.php');

if (!isset($_SESSION['username']) || $_SESSION['username'] !== 'admin') {
    exit("Access denied.");
}

// Approve deposit
if (isset($_GET['approve_txn'])) {
    $id = intval($_GET['approve_txn']);
    // Get amount and player ID
    $txn = $conn->query("SELECT player_id, amount FROM transactions WHERE id = $id AND approved = 0")->fetch_assoc();
    if ($txn) {
        $conn->query("UPDATE transactions SET approved = 1 WHERE id = $id");
        $conn->query("UPDATE players SET coins = coins + {$txn['amount']} WHERE id = {$txn['player_id']}");
    }
}

// Block player
if (isset($_GET['block'])) {
    $block_id = intval($_GET['block']);
    $conn->query("UPDATE players SET is_blocked = 1 WHERE id = $block_id");
}

// Unblock player
if (isset($_GET['unblock'])) {
    $unblock_id = intval($_GET['unblock']);
    $conn->query("UPDATE players SET is_blocked = 0 WHERE id = $unblock_id");
}

// Fetch pending transactions
$txns = $conn->query("SELECT t.id, p.username, t.amount, t.txn_id FROM transactions t JOIN players p ON t.player_id = p.id WHERE t.approved = 0");

// Fetch all players
$players = $conn->query("SELECT id, username, coins, is_blocked FROM players WHERE username != 'admin'");
?>











<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title><?php echo $site_name; ?> Dashboard</title>

  <!-- Bootstrap & Google Fonts -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@500&display=swap" rel="stylesheet">


  <!-- Scripts -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>


  <style>
    body.dashboard-bg {
  background-color: #121212;
  font-family: 'Orbitron', sans-serif;
  color: #f8f9fa;
}

.dashboard-header {
  background: linear-gradient(90deg, #0d0d0d, #1f1f1f);
  border-bottom: 2px solid #ffc107;
}

.dashboard-title {
  font-size: 1.8rem;
  color: #ffc107;
}

.coin-display {
  font-size: 1.2rem;
}

.neon-heading {
  color: #0dcaf0;
  text-shadow: 0 0 10px #0dcaf0;
}

.game-card {
  transition: transform 0.2s ease-in-out, box-shadow 0.2s;
  background-color: #1a1a1a;
}
.game-card:hover {
  transform: scale(1.03);
  box-shadow: 0 0 12px rgba(255, 255, 255, 0.1);
}
@media (max-width: 768px) {
  .dashboard-header {
    flex-direction: column;
    align-items: flex-start;
    overflow: hidden;
  }

  .dashboard-title {
    font-size: 1.5rem;
  }

  .coin-display {
    font-size: 1rem;
  }

  .game-card {
    margin: 10px 0;
    box-shadow: 0 0 8px rgba(255, 255, 255, 0.1);
  }

  .game-card:hover {
    transform: scale(1.02);
  }

  table {
    overflow-y: auto;
    font-size: 0.8rem;
  }
}

  </style>
</head>
<body class="dashboard-bg text-light">

  <!-- Header -->
  <header class="dashboard-header p-4 d-flex justify-content-center align-items-center shadow">
    <h1 class="dashboard-title">🎮 <?php echo $site_name; ?></h1>  
  </header>


<div class="d-flex justify-content-center align-items-center">
     
      <a href="logout.php" class="btn btn-outline-warning">Logout</a>
    </div>
  <main class="container py-5 d-flex justify-content-center align-items-center">
          <div class="col-md-6 text-center">
            <h2>Admin Dashboard</h2>

            <h3>Pending Deposits</h3>
            <table class="table table-dark table-striped">
              <thead>
                <tr>
                  <th>Username</th>
                  <th>Amount</th>
                  <th>TXN ID</th>
                  <th>Approve</th>
                </tr>
              </thead>
              <tbody>
                <?php while ($txn = $txns->fetch_assoc()): ?>
                  <tr>
                    <td><?= $txn['username'] ?></td>
                    <td><?= $txn['amount'] ?></td>
                    <td><?= $txn['txn_id'] ?></td>
                    <td><a href="?approve_txn=<?= $txn['id'] ?>" class="btn btn-primary">Approve</a></td>
                  </tr>
                <?php endwhile; ?>
              </tbody>
            </table>

            <h3>Players</h3>
            <table class="table table-dark table-striped">
              <thead>
                <tr>
                  <th>Username</th>
                  <th>Balance</th>
                  <th>Status</th>
                  <th>Action</th>
                </tr>
              </thead>
              <tbody>
                <?php while ($player = $players->fetch_assoc()): ?>
                  <tr>
                    <td><?= $player['username'] ?></td>
                    <td><?= $player['coins'] ?></td>
                    <td><?= $player['is_blocked'] ? "Blocked" : "Active" ?></td>
                    <td><?= $player['is_blocked'] 
                        ? "<a href='?unblock={$player['id']}' class='btn btn-success'>Unblock</a>" 
                        : "<a href='?block={$player['id']}' class='btn btn-danger'>Block</a>" ?></td>
                  </tr>
                <?php endwhile; ?>
              </tbody>
            </table>


            <h3>All Transactions</h3>
            <table class="table table-dark table-striped">
              <thead>
                <tr>
                  <th>Player</th>
                  <th>Amount</th>
                  <th>TX ID</th>
                  <th>Status</th>
                  <th>Date</th>
                </tr>
              </thead>
              <tbody>
                <?php
                  $stmt = $conn->prepare("SELECT p.username, t.amount, t.txn_id, t.approved, t.created_at FROM transactions t JOIN players p ON t.player_id = p.id ORDER BY t.created_at DESC");
                  $stmt->execute();
                  $all_txns = $stmt->get_result();
                  while ($txn = $all_txns->fetch_assoc()): ?>
                  <tr>
                    <td><?= $txn['username'] ?></td>
                    <td><?= $txn['amount'] ?></td>
                    <td><?= $txn['txn_id'] ?></td>
                    <td><?= $txn['approved'] ? "Approved" : "Waiting" ?></td>
                    <td><?= $txn['created_at'] ?></td>
                  </tr>
                <?php endwhile; ?>
              </tbody>
            </table>

            <a href="dashboard.php" class="btn btn-secondary d-block mx-auto">&larr; Back to Dashboard</a>
          </div>
        </div>
      </div>
    </div>
  </main>

</body>
</html>
