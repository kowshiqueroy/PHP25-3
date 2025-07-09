<?php
session_start();
include('../includes/db.php');
if (!isset($_SESSION['user_id'])) header("Location: ../login.php");
 $user_id = $_SESSION['user_id'];
$msg = "";
$outcome = "";
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $bet = intval($_POST['bet']);
    $choice = $_POST['choice'];
   
    $res = $conn->query("SELECT coins FROM players WHERE id = $user_id");
    $coins = $res->fetch_assoc()['coins'];

    if ($bet > 0 && $bet <= $coins) {
      $chance=2;
       if (rand(0, 10) < $chance) {
           $flip = $choice === "tails" ? "tails" : "heads";


       } else {
          $flip = $choice === "heads" ? "tails" : "heads";
       }
        $win = ($choice === $flip);
        $conn->query("UPDATE players SET coins = coins + " . ($win ? $bet : -$bet) . " WHERE id = $user_id");
        $stmt = $conn->prepare("INSERT INTO bets (game, player_id, amount, outcome, result) VALUES ('coin_flip', ?, ?, ?, ?)");
        $stmt->bind_param("iisi", $user_id, $bet, $flip, $win);
        $stmt->execute();
// Keep only the last 5 bets of this game
$conn->query("DELETE FROM bets WHERE game='coin_flip' AND player_id=$user_id AND result IS NOT NULL AND created_at < DATE_SUB(NOW(), INTERVAL 1 DAY)");

        $msg = $win ? "🎉 You won!" : "❌ You lost.";
        $outcome = $flip;
        if ($win) {
        $prize=$bet*4;
    
}
    } else {
        $msg = "Invalid bet or insufficient funds.";
    }
}
$coins_res = $conn->query("SELECT coins FROM players WHERE id = $user_id");
$coins_row = $coins_res->fetch_assoc();
$coins = $coins_row['coins'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />  
  <title>Coin Flip | <?php echo $site_name; ?></title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
  <style>
    body {
      background: linear-gradient(135deg, #0d0d0d, #1c1c1c);
      color: #f8f9fa;
      font-family: 'Segoe UI', sans-serif;
    }

    h2, p {
      text-shadow: 0 0 8px #0dcaf0;
    }

    .coin-container {
      width: 120px;
      height: 120px;
      margin: 30px auto;
      perspective: 1000px;
    }

    .coin {
      width: 100%;
      height: 100%;
      background: url('https://dummyimage.com/120x120/2ecc71/fff&text=HEADS') no-repeat center/cover;
      border-radius: 50%;
      transition: transform 1s;
      transform-style: preserve-3d;
      box-shadow: 0 0 15px rgba(255,255,255,0.2);
    }

    .flip {
      animation: flipCoin 1s forwards;
    }

    @keyframes flipCoin {
      0%   { transform: rotateY(0deg); }
      100% { transform: rotateY(180deg); }
    }

    .coin.tails {
      background-image: url('https://dummyimage.com/120x120/ff3737/fff&text=TAILS');
    }

    .form-label {
      font-weight: 600;
    }

    .game-card {
      background-color: #1a1a1a;
      border: 1px solid #343a40;
      box-shadow: 0 0 10px rgba(13, 202, 240, 0.2);
    }

    .btn-success {
      background-color: #28a745;
      border: none;
      font-weight: bold;
    }

    .btn-success:hover {
      background-color: #218838;
    }

    a {
      color: #0dcaf0;
      font-weight: bold;
      text-decoration: none;
    }

    a:hover {
      text-decoration: underline;
    }
  </style>
</head>
<body>
<div class="container py-5 text-center">
  <h2 class="mb-1">🪙 Coin Flip</h2>
  <!-- <p class="mb-4 text-secondary">Test your luck. Pick heads or tails to double your bet!</p> -->

  <div class=" game-info  d-flex justify-content-between align-items-center">
    <div class="">
      💰 <strong></strong> <?= $coins ?> coins
    </div>
     <?php if ($msg): ?>
    <div >
      <span style="animation: fadeIn 1s ease-in-out forwards, hide 4s forwards;"><?= $msg ?></span>
      <span style="animation: fadeIn 1s ease-in-out forwards, hide 10s forwards;"><?= isset($prize) ? '+'.$prize : '' ?></span>
    </div>

    <style>
      @keyframes hide {
        0% { opacity: 1; }
        100% { opacity: 0; }
      }
    </style>
    <?php endif; ?>
  </div>

  

  <div class="coin-container">
    <div id="coin" class="coin <?= $outcome ?>"></div>
  </div>

  <form method="post" onsubmit="animateCoin()" class="game-card p-4 mx-auto text-light" style="max-width: 420px;">
    <?php $selectedChoice = isset($_POST['choice']) ? $_POST['choice'] : ''; ?>
    <div class="btn-group mb-3 w-100 " data-bs-toggle="buttons">
      <input type="radio" class="btn-check mx-1" name="choice" value="heads" id="choiceHeads" required <?= $selectedChoice === 'heads' ? 'checked' : '' ?>>
      <label class="btn btn-outline-success mx-1" style="border-color: green;" for="choiceHeads">Heads</label>

      <input type="radio" class="btn-check mx-1" name="choice" value="tails" id="choiceTails" required <?= $selectedChoice === 'tails' ? 'checked' : '' ?>>
      <label class="btn btn-outline-danger mx-1" style="border-color: red;" for="choiceTails">Tails</label>
    </div>

    <div class="btn-group mb-3 w-100" data-bs-toggle="buttons">
      <input type="radio" class="btn-check" name="bet" value="10" id="bet10" required <?= isset($_POST['bet']) && $_POST['bet'] === '10' ? 'checked' : '' ?>>
      <label class="btn btn-outline-secondary" for="bet10">10</label>

      <input type="radio" class="btn-check" name="bet" value="100" id="bet100" required <?= isset($_POST['bet']) && $_POST['bet'] === '100' ? 'checked' : '' ?>>
      <label class="btn btn-outline-secondary" for="bet100">100</label>

      <input type="radio" class="btn-check" name="bet" value="1000" id="bet1000" required <?= isset($_POST['bet']) && $_POST['bet'] === '1000' ? 'checked' : '' ?>>
      <label class="btn btn-outline-secondary" for="bet1000">1000</label>
    </div>

    <button type="submit" class="btn btn-success w-100">🎲 Flip Coin +2X</button>
    <a href="../dashboard.php" class="d-block mt-3">← Back to Dashboard</a>
  </form>
</div>

<script>
function animateCoin() {
  const coin = document.getElementById('coin');
  coin.classList.remove('flip');
  void coin.offsetWidth;
  coin.classList.add('flip');
}
</script>
</body>
</html>
