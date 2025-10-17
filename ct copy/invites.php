<?php
require_once 'head.php';
?>
    <section>
      <h2>👤 Invite a new Researcher</h2>
      <form>
      <div class="form-group">
        <label>Username</label>
        <input type="text" pattern="^01\d{8}$" maxlength="11" name="username" placeholder="Phone Number as Username" />
      </div>
       <div class="form-group">
        <label>Password</label>
        <input type="password" name="password" placeholder="New Password" />
      </div>
      <div class="form-group" style="text-align: center;">
        <label>It will Cost 10 Coins 🪙 & Get 1 Coin 🪙 Reward</label>
   
      </div>

      <button type="submit" name="invite">Submit</button>
      </form>
    </section>

    <section>
      <h2>👤 Your Invites on CivicThinkers</h2>
      <table style="width: 100%; border-collapse: collapse; font-size: 14px;">
        <tr><th>ID</th><th>Username</th><th>Coins</th><th>Date</th></tr>
        <tr><td>1123</td><td>01712345678</td><td>10</td><td>2023-10-01 12:00</td></tr>
        <tr><td>1124</td><td>01787654321</td><td>5</td><td>2023-10-02 14:30</td></tr>
      </table>
    </section>

 

    
<?php
require_once 'foot.php';
?>