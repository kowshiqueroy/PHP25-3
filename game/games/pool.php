<?php
session_start();
include('../includes/db.php');
$id = $_GET['id'];
$luck_row = $conn->query("SELECT * FROM luck WHERE id = $id")->fetch_assoc();
if ($luck_row) {
?>


      <p class="text-secondary" style="font-size: 0.7rem; line-height: 1.5;">
       <span class="text-white"><span class="luck-icon"></span> Pool: <span class="luck-icon">✨</span><?php echo $id;?></span>
      
        <?php $players = array_map(function($key) use ($luck_row, $conn) { 
            $val = $luck_row[$key] ?? '';
            if ($val) {
                $stmt = $conn->prepare("SELECT username FROM players WHERE id = ?");
                $stmt->bind_param("i", $val);
                $stmt->execute();
                $result = $stmt->get_result();
                if ($result->num_rows > 0) {
                    $row = $result->fetch_assoc();
                    $val = $row['username'];
                }
            }
            return $key . ': ' . $val;
        }, array_slice(array_keys($luck_row), 1, 12)); ?>
        <div style="font-size: 0.6rem; display: grid; grid-template-columns: repeat(3, 1fr); grid-template-rows: repeat(4, auto); gap: 5px; margin: 0 auto; text-align: left;">
        <?php foreach (array_chunk($players, 3) as $chunk): ?>
            <?php foreach ($chunk as $player): ?>

                
                <div class="card bg-warning" style="border-radius: 10px; padding: 10px; word-break: break-all;"><?= $player ?></div>
            <?php endforeach; ?>
        <?php endforeach; ?>
        </div>



      </p>




<?php



} else {
    echo "Pool not found.";
}

?>
  <form method="post" class="game-card card p-4 mx-auto text-light" style="max-width: 420px;" onsubmit="animateDice()">
   

    <?php 
    $stmt = $conn->prepare("SELECT * FROM luck WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    
    
    
    if ($row['finished'] == 0): ?>
    <button type="submit" name="enter" class="btn btn-primary w-100">Jump In +1000</button>
    <?php else: ?>
    <button onclick="window.location.href='luck_pool.php'" class="btn btn-secondary w-100">New Pool</button>
    <?php endif; ?>
  
  </form>