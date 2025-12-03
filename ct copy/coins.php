<?php
require_once 'head.php';
?>
    <section>
      <h2>🪙 Coin Transfer </h2>
      <form>
      <div class="form-group">
        <label>Username (Phone)</label>
        <input type="text" placeholder="To Username" value="2493694" />
      </div>
      <div class="form-group">
        <label>Amount</label>
        <input type="number" placeholder="Amount" value="10" />
      </div>
      <div class="form-group">
        <label>Reason</label>
        <div class="radio-group">
          <label><input type="radio" name="reason" value='withdraw' /> Withdraw</label>
          <label><input type="radio" name="reason" value='send' checked /> Send</label>
        </div>
      </div>
      
      <button type="submit" name="transfer">Submit</button>
      </form>
    </section>
<section>
      <h2 style="text-align: center;">🪙 Current Balance: 120 🪙</h2>
</section>
    <section>
      <h2>🪙 Your Coin Transactions</h2>
      <table style="width: 100%; border-collapse: collapse; font-size: 14px;">
        <tr><th>ID</th><th>Amount</th><th>To/From</th><th>Status</th></tr>
        <tr><td>5001 2024-10-01 12:00</td><td>Send -10</td><td>2493694</td><td>Completed</td></tr>
        <tr><td>5002 2024-10-02 14:30</td><td>Receive +20</td><td>1234567</td><td>Pending</td></tr>
           <tr><td>5001 2024-10-01 12:00</td><td>Send -10</td><td>2493694</td><td>Completed</td></tr>
        <tr><td>5002 2024-10-02 14:30</td><td>Receive +20</td><td>1234567</td><td>Pending</td></tr>   
        <tr><td>5001 2024-10-01 12:00</td><td>Send -10</td><td>2493694</td><td>Completed</td></tr>
        <tr><td>5002 2024-10-02 14:30</td><td>Widthdraw +20</td><td>1234567</td><td>Pending</td></tr>
      </table>
    </section>

    
<?php
require_once 'foot.php';
?>