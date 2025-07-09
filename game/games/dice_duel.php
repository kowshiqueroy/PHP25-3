<?php
session_start();
include('../includes/db.php');
if (!isset($_SESSION['user_id'])) header("Location: ../login.php");
    $user_id = $_SESSION['user_id'];
$msg = "";
$roll = null;
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $choice = $_POST['choice']; // 'high' or 'low'
    $bet = intval($_POST['bet']);


    $coins = $conn->query("SELECT coins FROM players WHERE id = $user_id")->fetch_assoc()['coins'];
    if ($bet > 0 && $bet <= $coins) {


       $chance=2;
       if (rand(0, 10) < $chance) {
           $roll = intval($choice);


       } else {
          $roll = rand(1, 6);
          while ($roll == intval($choice)) {
            $roll = rand(1, 6);
          }
       }




        
        $win = $roll === intval($choice);
        $conn->query("UPDATE players SET coins = coins + " . ($win ? $bet*3 : -$bet) . " WHERE id = $user_id");

        $stmt = $conn->prepare("INSERT INTO bets (game, player_id, amount, outcome, result) VALUES ('dice_duel', ?, ?, ?, ?)");
        $stmt->bind_param("iisi", $user_id, $bet, $roll, $win);
        $stmt->execute();
$conn->query("DELETE FROM bets WHERE game='dice_duel' AND player_id=$user_id AND result IS NOT NULL ORDER BY created_at ASC");
        $msg = $win ? "🎉 You won!" : "❌ You lost.";

if ($win) {
  $prize=$bet*4;
    
}

    } else {
        $msg = "Invalid bet or not enough coins.";
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
  <title>🎲 Dice Duel | <?php echo $site_name; ?></title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
  <style>
    body {
      background: linear-gradient(135deg, #0f0f0f, #1b1b2f);
      color: #f8f9fa;
      font-family: 'Segoe UI', sans-serif;
    }

    h2 {
      color:rgb(245, 251, 252);
      text-shadow: 0 0 10px #0dcaf0;
    }

    .dice {
      font-size: 80px;
    }

    .animated-dice {
      width: 100px;
      height: 100px;
      display: inline-block;
      background-size: cover;
      animation: roll 0.6s ease-in-out;
      box-shadow: 0 0 15px rgba(255,255,255,0.2);
    }

    @keyframes roll {
      0% { transform: rotate(0deg) scale(1.2); }
      50% { transform: rotate(180deg) scale(0.8); }
      100% { transform: rotate(360deg) scale(1); }
    }

    .game-card {
      background-color: #1a1a1a;
      border: 1px solid #343a40;
      box-shadow: 0 0 15px rgba(13, 202, 240, 0.2);
    }

    .btn-primary {
      background-color: #0d6efd;
      border: none;
      font-weight: bold;
    }

    .btn-primary:hover {
      background-color: #0b5ed7;
    }

    a {
      color: #0dcaf0;
      font-weight: 500;
      text-decoration: none;
    }

    a:hover {
      text-decoration: underline;
    }

    .form-label {
      font-weight: 600;
    }
  </style>
</head>
<body>
<div class="container py-5 text-center">
  <h2 class="mb-2" onclick="window.location.href='../dashboard.php'">🎲 Dice Duel</h2>
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

 
    <div class="dice my-3">
      <?php if ($roll): ?>
        <div class="animated-dice" style="background-image: url('https://dummyimage.com/100x100/000/fff&text=<?= $roll ?>');"></div>
      <?php else: ?>
        <div class="animated-dice" style="background-image: url('https://dummyimage.com/100x100/000/fff&text=0');"></div>
      <?php endif; ?>
    </div>
 

 
  <form method="post" class="game-card card p-4 mx-auto text-light" style="max-width: 420px;" onsubmit="animateDice()">
    <?php $selectedChoice = isset($_POST['choice']) ? $_POST['choice'] : ''; ?>
    <div class="btn-group mb-3 w-100" data-bs-toggle="buttons">
      <input type="radio" class="btn-check" name="choice" value="1" id="choice1" required <?= $selectedChoice === '1' ? 'checked' : '' ?>>
      <label class="btn btn-outline-secondary" for="choice1">1</label>

      <input type="radio" class="btn-check" name="choice" value="2" id="choice2" required <?= $selectedChoice === '2' ? 'checked' : '' ?>>
      <label class="btn btn-outline-secondary" for="choice2">2</label>

      <input type="radio" class="btn-check" name="choice" value="3" id="choice3" required <?= $selectedChoice === '3' ? 'checked' : '' ?>>
      <label class="btn btn-outline-secondary" for="choice3">3</label>

      <input type="radio" class="btn-check" name="choice" value="4" id="choice4" required <?= $selectedChoice === '4' ? 'checked' : '' ?>>
      <label class="btn btn-outline-secondary" for="choice4">4</label>

      <input type="radio" class="btn-check" name="choice" value="5" id="choice5" required <?= $selectedChoice === '5' ? 'checked' : '' ?>>
      <label class="btn btn-outline-secondary" for="choice5">5</label>

      <input type="radio" class="btn-check" name="choice" value="6" id="choice6" required <?= $selectedChoice === '6' ? 'checked' : '' ?>>
      <label class="btn btn-outline-secondary" for="choice6">6</label>
    </div>

    <?php $selectedBet = isset($_POST['bet']) ? $_POST['bet'] : ''; ?>
    <div class="btn-group mb-3 w-100" data-bs-toggle="buttons">
      <input type="radio" class="btn-check" name="bet" value="10" id="bet10" required <?= $selectedBet === '10' ? 'checked' : '' ?>>
      <label class="btn btn-outline-secondary" for="bet10">10</label>

      <input type="radio" class="btn-check" name="bet" value="50" id="bet50" required <?= $selectedBet === '50' ? 'checked' : '' ?>>
      <label class="btn btn-outline-secondary" for="bet50">50</label>

      <input type="radio" class="btn-check" name="bet" value="100" id="bet100" required <?= $selectedBet === '100' ? 'checked' : '' ?>>
      <label class="btn btn-outline-secondary" for="bet100">100</label>
    </div>

    <button type="submit" class="btn btn-primary w-100">🎲 Roll the Dice X4</button>
    <a href="../dashboard.php" class="d-block mt-3">← Back to Dashboard</a>
  </form>
</div>

<script>
function animateDice() {
  const dice = document.querySelector('.animated-dice');
  if (dice) {
    dice.style.animation = 'none';
    void dice.offsetWidth;
    dice.style.animation = 'roll 0.6s ease-in-out';
  }
}
</script>
</body>
</html>