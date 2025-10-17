<?php
require_once 'head.php';
?>
<section>
  <h2 style="text-align: center;">
    <span>
      <a href="https://civicthinkers.com/?register=<?php echo $_SESSION['user_id']*1234; ?>"><?php echo "https://civicthinkers.com/?register=".$_SESSION['user_id']*1234; ?></a>
      <button class="copy-btn" onclick="copyLink()">Copy</button>
      <script>
        function copyLink() {
          var copyText = document.querySelector("a").getAttribute("href");
          var textArea = document.createElement("textarea");
          textArea.value = copyText;
          document.body.appendChild(textArea);
          textArea.select();
          document.execCommand("copy");
          textArea.remove();
        }
      </script>
    </span>
  </h2>


    <section>
      <h2>👤 Your Invites on CivicThinkers</h2>
      <table style="width: 100%; border-collapse: collapse; font-size: 14px;">
        <tr><th>ID</th><th>Username</th><th>Coins</th></tr>
        <tr><td>1123 2023-10-01 12:00</td><td>01712345678</td><td>10 S0</td></tr>
        <tr><td>1124 2023-10-02 14:30</td><td>01787654321</td><td>5 S1</td></tr>
      </table>
    </section>

 

    
<?php
require_once 'foot.php';
?>