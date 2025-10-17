<?php
require_once 'head.php';
?>
   <section>
      <h2 style="text-align: center;">🪙 Current Balance: 120 🪙</h2>
</section> 
    <style>
    .card-container {
      display: flex;
      flex-wrap: wrap;
      justify-content: center;
    }

    .card {
      flex: 0 0 250px;
      margin: 10px;
      background-color: #f9fbfd;
      border: 1px solid #333;
      border-radius: 10px;
      box-shadow: 0 4px 8px 0 rgba(0,0,0,0.2);
      transition: 0.3s;
    }

    .card:hover {
      box-shadow: 0 8px 16px 0 rgba(0,0,0,0.2);
    }
  </style>

  <div class="card-container">
    <div class="card">
      <h2>🥉 S1 Tier ✔️</h2>
      <p>🔓 1000 coins to Join</p>
      <p>💸 Earn up to 50/day</p>
    </div>
    <div class="card">
      <h2>🥈 S2 Tier</h2>
      <p>🔒 3000 coins to Join</p>
      <p>💸 Earn up to 100/day</p>
    </div>
    <div class="card">
      <h2>🥇 S3 Tier</h2>
      <p>🔒 5000 coins to Join</p>
      <p>💸 Earn up to 200/day</p>
    </div>
    <div class="card">
      <h2>🏆 S4 Tier</h2>
      <p>🔒 10000 coins to Join</p>
      <p>💸 Earn up to 500/day</p>
    </div>
  </div>
<?php
require_once 'foot.php';
?>