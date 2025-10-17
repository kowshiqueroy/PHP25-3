  
  <br>  <br>  <br><br>  <br>
</main>

  <nav>
    <div onclick="window.location.href = 'index.php'">🏠<br>Home</div>
    <div onclick="window.location.href = 'tasks.php'">📋<br>Tasks</div>
    <div onclick="window.location.href = 'coins.php'">🪙<br>Coins</div>
    <div onclick="toggleDropdown()">👤<br>Account
      <div id="accountDropdown" class="dropdown">
        <!-- <a href="settings.php">Settings</a> -->
        <a href="profile.php">Profile</a>
        <a href="support.php">Support</a>
        <a href="invites.php">Invites</a>
        <a href="logout.php">Logout</a>
      </div>
    </div>
  </nav>
  <script>
    function toggleDropdown() {
      const dropdown = document.getElementById('accountDropdown');
      dropdown.style.display = dropdown.style.display === 'flex' ? 'none' : 'flex';
    }
  </script>
</body>
</html>