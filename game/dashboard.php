<?php
session_start();
include('includes/db.php');

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
$user_id = $_SESSION['user_id'];
$username = $_SESSION['username'];
// Check if user is blocked
$stmt = $conn->prepare("SELECT is_blocked FROM players WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
if ($row !== null && $row['is_blocked']) {
    header("Location: login.php?msg=Blocked");
    exit();
}
else{
  // echo "no";
}



$result = $conn->query("SELECT coins FROM players WHERE id = $user_id");
$row = $result->fetch_assoc();
$coins = $row['coins'];
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
      <div class="coin-display me-4">  <?= htmlspecialchars($username) ?> 🪙 Coins: <strong><?= $coins ?></strong></div>
      <a href="logout.php" class="btn btn-outline-warning">Logout</a>
    </div>
  <main class="container py-5">
    <?php if ($username === 'admin'): ?>
    <div class="d-flex justify-content-center mb-4">
      <a href="admin.php" class="btn btn-primary">Admin Panel</a>
    </div>
    <?php endif; ?>
    <!-- Games -->
    <h2 class="text-center mb-4 neon-heading ">🎲 Available Games</h2>
    <div class="row g-4">
      <div class="col-md-4">
        <div class="card game-card h-100 text-center border-primary">
          <div class="card-body">
            <h5 class="card-title text-white">🪙 Coin Flip</h5>
            <p class="card-text">Heads or Tails? Win and double your bet!</p>
            <a href="games/coin_flip.php" class="btn btn-primary">Play Now</a>
          </div>
        </div>
      </div>

      <div class="col-md-4">
        <div class="card game-card h-100 text-center border-danger">
          <div class="card-body">
            <h5 class="card-title text-white">🎲 Dice Duel</h5>
            <p class="card-text">High or Low dice rolls—double your coins if you're lucky!</p>
            <a href="games/dice_duel.php" class="btn btn-danger">Play Now</a>
          </div>
        </div>
      </div>

      <div class="col-md-4">
        <div class="card game-card h-100 text-center border-warning">
          <div class="card-body">
            <h5 class="card-title text-white">✨ Luck Pool</h5>
            <p class="card-text">Join a pool of players and win a random prize!</p>
            <a href="games/luck_pool.php" class="btn btn-warning">Join Now</a>
          </div>
        </div>
      </div>

     
    </div>



   

    <!-- Transfer -->
    <section class="card bg-dark border border-info mb-4 mt-4">
      <div class="card-header bg-info text-dark fw-bold">🔁 Send Coins</div>
      <div class="card-body">
        <form method="post" action="send_coins.php" class="row g-2">
          <div class="col-md-6">
            <input type="text" name="to_username" placeholder="Recipient Username" class="form-control" required list="players-list">
            <datalist id="players-list">
              <?php
              $stmt = $conn->prepare("SELECT username FROM players");
              $stmt->execute();
              $players = $stmt->get_result();
              while ($player = $players->fetch_assoc()) {
                echo "<option value=\"{$player['username']}\">";
              }
              ?>
            </datalist>
            <script>
              document.querySelector('[name="to_username"]').addEventListener('input', function() {
                const val = this.value;
                const option = document.querySelector(`#players-list option[value="${val}"]`);
                if (!option) {
                  this.setCustomValidity('Must match an existing username');
                } else {
                  this.setCustomValidity('');
                }
              });
            </script>
          </div>
          <div class="col-md-4">
            <input type="number" name="amount" placeholder="Amount to Send" class="form-control" min="500" required>
          </div>
          <div class="col-md-2">
            <button type="submit" class="btn btn-info w-100 text-dark">Send</button>
          </div>
        </form>
      </div>
    </section>

      <!-- Deposit -->
    <section class="card bg-dark border border-warning mb-4 ">
      <div class="card-header bg-warning text-dark fw-bold">💰 Deposit Coins</div>
      <div class="card-body">
        <form method="post" action="submit_deposit.php" class="row g-2">
          <div class="col-md-6">
            <input type="text" name="txn_id" placeholder="Transaction ID" class="form-control" required>
          </div>
          <div class="col-md-4">
            <input type="number" name="amount" placeholder="Amount" class="form-control" required>
          </div>
          <div class="col-md-2">
            <button type="submit" class="btn btn-warning w-100">Submit</button>
          </div>
        </form>
      </div>
    </section>


<?php
// Fetch transaction data
$user_id = $_SESSION['user_id'];
$query = "SELECT txn_id, amount, approved, created_at FROM transactions WHERE player_id = ? ORDER BY created_at DESC";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

// Display transaction data
if ($result->num_rows > 0): ?>
  <section class="card bg-dark border border-secondary mb-4">
    <div class="card-header bg-secondary text-white fw-bold">📜 Transaction History</div>
    <div class="card-body">
      
      <table class="table table-dark table-striped">
        <thead class="text-center">
          <tr>
            <th>TX ID</th>
            <th>Amount</th>
            <th>Status</th>
            <th>Date</th>
          </tr>
        </thead>
        <tbody>
          <?php while ($row = $result->fetch_assoc()): ?>
            <tr>
              <td style="text-align: center;"><?= htmlspecialchars($row['txn_id']) ?></td>
              <td style="text-align: center;"><?= htmlspecialchars($row['amount']) ?></td>
              <td style="text-align: center;"><?= htmlspecialchars($row['approved'] == 1 ? 'Approved' : 'Waiting') ?></td>
              <td style="text-align: center;"><?= htmlspecialchars($row['created_at']) ?></td>
            </tr>
          <?php endwhile; ?>
        </tbody>
      </table>
    </div>
  </section>
<?php else: ?>
  <div class="alert alert-warning">No transactions found.</div>
<?php endif; ?>

  <section class="card bg-dark border border-secondary mb-4">
    <div class="card-header bg-secondary text-white fw-bold">🔒 Change Password or Block</div>
    <div class="card-body">
      <form method="post" action="">
        <div class="mb-3">
          <label for="old_password" class="form-label text-white">Old Password</label>
          <input type="password" name="old_password" id="old_password" class="form-control" required>
        </div>
        <div class="mb-3">
          <label for="new_password" class="form-label text-white">New Password</label>
          <input type="password" name="new_password" id="new_password" class="form-control" required>
        </div>
        <div class="mb-3">
          <label for="confirm_password" class="form-label text-white">Confirm Password</label>
          <input type="password" name="confirm_password" id="confirm_password" class="form-control" required>
        </div>
        <button type="submit" name="change_password" class="btn btn-primary">Change Password</button>
      </form>
    </div>
  </section>

<?php


// Check if form is submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['change_password'])) {
    $old_password = $_POST['old_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    // Validate new password and confirm password match
    if ($new_password === $confirm_password) {
        // Validate old password
        $stmt = $conn->prepare("SELECT password FROM players WHERE id = ?");
        $stmt->bind_param("i", $_SESSION['user_id']);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();

        if (password_verify($old_password, $user['password'])) {
            // Update password
            $new_password_hashed = password_hash($new_password, PASSWORD_BCRYPT);
            $update_stmt = $conn->prepare("UPDATE players SET password = ? WHERE id = ?");
            $update_stmt->bind_param("si", $new_password_hashed, $_SESSION['user_id']);
            if ($update_stmt->execute()) {
                echo "<div class='alert alert-success'>Password changed successfully.</div>";
            } else {
                echo "<div class='alert alert-danger'>Error updating password.</div>";
            }
        } else {
            echo "<div class='alert alert-danger'>Old password is incorrect.</div>";
        }
    } else {
        echo "<div class='alert alert-danger'>New password and confirm password do not match.</div>";
    }
}
?>






  </main>
</body>
</html>