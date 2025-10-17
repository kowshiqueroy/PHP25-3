<?php
require_once 'head.php';
?>
<section>
      <h2 style="text-align: center;">🪙 Current Balance: 120 🪙</h2>
</section> 
    <section>
      <h2>📋 Complete Everyday tasks</h2>
      <ul>
        <li>Daily Tasks - Reward: 0 Coins</li>
        <li>Update Profile - Increase Rewards</li>
        <li>Invite Friends - Reward: 50 Coins</li>
      </ul>
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
 <section>
  <h2>🔥 Last 5 Rewards</h2>
  <table>
    <tr><th>User</th><th>Coins</th></tr>
    <?php
    // Generate 2 Indian numbers
    $indianNumbers = [
        'IN-' . substr(rand(7000000000, 9999999999), 0, 4) . 'XXX' . substr(rand(7000000000, 9999999999), -3),
        'IN-' . substr(rand(7000000000, 9999999999), 0, 4) . 'XXX' . substr(rand(7000000000, 9999999999), -3)
    ];

    // Generate 1 Bangladeshi number
    $bangladeshiNumbers = [
        'BD-' . substr(rand(1300000000, 1999999999), 0, 4) . 'XXX' . substr(rand(1300000000, 1999999999), -3)
    ];

    // Generate 1 USA number
    $usaNumbers = [
        'US-' . substr(rand(2000000000, 9999999999), 0, 3) . '-XXX-' . substr(rand(2000000000, 9999999999), -4)
    ];

    // Generate 1 random international number
    $countries = ['UK', 'CA', 'AU', 'DE', 'FR'];
    $randomCountry = $countries[array_rand($countries)];
    $randomNumber = $randomCountry . '-' . substr(rand(1000000000, 9999999999), 0, 3) . 'XXX' . substr(rand(1000000000, 9999999999), -3);

    // Merge and shuffle
    $allNumbers = array_merge($indianNumbers, $bangladeshiNumbers, $usaNumbers, [$randomNumber]);
    shuffle($allNumbers);

    // Output table rows
    foreach ($allNumbers as $number) {
        echo "<tr><td>" . $number . "</td><td>" . number_format(rand(1, 100) / 1) . "</td></tr>";
    }
    ?>
  </table>
</section>

<section>
  <h2>🏆 Top 5 Researchers Today</h2>
  <table>
    <tr><th>User</th><th>Country</th><th>Coins</th></tr>
    <?php
    $hour = date('H.i', strtotime(date('H.i')));
      // current hour in 24h format
    $topCoins = 300+($hour * 2); // base coins + 2 coins per hour
    $hashBase = md5(date('Ymd'));
    $countries = ['IN', 'BD', 'US', 'UK', 'CA'];
    $researchers = [];

    for ($i = 0; $i < 5; $i++) {
        $chunk = substr($hashBase, $i * 6, 6);
        $digits = base_convert($chunk, 16, 10);
        $country = $countries[hexdec(substr($chunk, 0, 2)) % count($countries)];
        $masked = substr($digits, 0, 3) . 'XXX' . substr($digits, -3);

        $coins = $topCoins - ($i * 2);

        $coins = number_format($coins);

        $researchers[] = [
            'username' => "{$country}-{$masked}",
            'country' => $country,
            'coins' => $coins
        ];
    }

    foreach ($researchers as $r) {
        echo "<tr><td>{$r['username']}</td><td>{$r['country']}</td><td>{$r['coins']}</td></tr>";
    }
    ?>
  </table>
</section>




    
<?php
require_once 'foot.php';
?>