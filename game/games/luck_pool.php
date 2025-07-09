<?php
session_start();
include('../includes/db.php');
if (!isset($_SESSION['user_id'])) header("Location: ../login.php");
    $user_id = $_SESSION['user_id'];
$msg = "";

        // Create table if not exists
        $conn->query("CREATE TABLE IF NOT EXISTS luck (
            id INT AUTO_INCREMENT PRIMARY KEY,
            `1` INT,
            `2` INT,
            `3` INT,
            `4` INT,
            `5` INT,
            `6` INT,
            `7` INT,
            `8` INT,
            `9` INT,
            `10` INT,
            `11` INT,
            `12` INT,
            winner INT,
            finished BOOLEAN DEFAULT 0,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )");

        $res = $conn->query("SELECT * FROM luck ORDER BY id DESC LIMIT 1");
        if ($res && $res->num_rows > 0) {
            $row = $res->fetch_assoc();
            if (!in_array(null, $row, true)) {
                $stmt = $conn->prepare("INSERT INTO luck (winner) VALUES (NULL)");
                $stmt->execute();
                $id = $conn->insert_id;
            }
            else{
                $id = $row['id'];
            }
            
        } else {
            $stmt = $conn->prepare("INSERT INTO luck (winner) VALUES (NULL)");
            $stmt->execute();
            $id = $conn->insert_id;
        }
      
$coins_res = $conn->query("SELECT coins FROM players WHERE id = $user_id");
$coins_row = $coins_res->fetch_assoc();
$coins = $coins_row['coins'];

        if (isset($_POST['enter'])) {
            if ($coins >= 100) {
                $res = $conn->query("SELECT * FROM luck WHERE id = $id");
                if ($res && $res->num_rows > 0) {
                    $row = $res->fetch_assoc();
                    $count = 0;
                    foreach ($row as $key => $value) {
                        $count++;
                        if (empty($value) && $key != 'id' && $key != 'winner') {
                            $stmt = $conn->prepare("UPDATE luck SET `$key` = $user_id WHERE id = $id");

                            break;
                        }
                    }
                    
                }
                $stmt->execute();
                //echo $count;
                $conn->query("UPDATE players SET coins = coins - 100 WHERE id = $user_id");

                $msg = "1 Slot -100";

                if ($count == 13) {


                    $winner_column = rand(1, 12);
                    //echo $id.' '.$winner_column;

                    $stmt = $conn->prepare("UPDATE luck SET finished = 1, winner = `$winner_column` WHERE id = $id");
                    $stmt->execute();

                    $winner_id = $conn->query("SELECT `$winner_column` FROM luck WHERE id = $id")->fetch_assoc()["$winner_column"];
                   // echo '<br>'.$winner_id;



                    

                    $win = $winner_id == $user_id;
                    $msg = $win ? "🎉 You win!" : "Winner: " . $conn->query("SELECT username FROM players WHERE id = $winner_id")->fetch_assoc()['username'];
                    $prize = $win ? '+1000' : '-100';

                    $winner_coins = $conn->query("SELECT coins FROM players WHERE id = $winner_id")->fetch_assoc()['coins'];
                   //echo $winner_coins;
                    $conn->query("UPDATE players SET coins = $winner_coins + 1000 WHERE id = $winner_id");
              
                      
                   
                    
                }

            } else {
                $msg = "Insufficient balance.";
            }

               
 


        }





if(isset($_SESSION['winner']) && $_SESSION['winner'] == $user_id){
    $msg = "You won the game!";
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
  <h2 class="mb-2"> <span class="luck-icon">✨</span> Luck Pool</h2>
   <div class=" game-info  d-flex justify-content-between align-items-center">
    <div class="">
      💰 <strong></strong> <?= $coins ?> coins
    </div>
    <?php if ($msg): ?>
    <div >
      <span style="animation: fadeIn 1s ease-in-out forwards, hide 4s forwards;"><?= $msg ?></span>
      <span style="animation: fadeIn 1s ease-in-out forwards, hide 10s forwards;"><?= isset($prize) ? ''.$prize : '' ?></span>
      <span class="winner" ></span>

      <script>
        function updateWinner() {
          fetch('winner.php?id=<?= $id ?>')
          .then(response => response.text())
          .then(data => {
            document.querySelector('.winner').innerText = data;
          });
        }
        setTimeout(() => {
          updateWinner();
          setInterval(updateWinner, 3000);
        }, 3000);
      </script>
      
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
  </div>
  <script>
    function updateDice() {
      fetch('pool.php?id=<?= $id ?>')
      .then(response => response.text())
      .then(data => {
        document.querySelector('.dice').innerHTML = data;
        console.log(data);
      });
    }
    updateDice();
    setInterval(updateDice, 5000);
  </script>
 

 
  <a href="../dashboard.php" class="d-block mt-3">← Back to Dashboard</a>
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