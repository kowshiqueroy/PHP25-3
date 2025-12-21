
  <nav>
    <button onClick="window.location.href = 'index.php'">🏠</button>
    <button onClick="window.location.href = 'students.php'">👥</button>
    <button onClick="window.location.href = 'print.php'">📑</button>
    <button onClick="window.location.href = 'logout.php'">🔒</button>
  

 

  <div class="dropdown" id="settingsDropdown">
    <button onClick="window.location.href = 'profile.php'">👨🏻‍💼Profile</button>
    <button onClick="window.location.href = 'shops.php'">🏪Shops</button>
    <button onClick="window.location.href = 'items.php'">📦Items</button>
    <button onClick="window.location.href = 'stocks.php'">📈Stocks</button>
    <button onClick="window.location.href = 'balances.php'">💰Balances</button>
  
    
  </div>
</nav>
<script>
  const settingsBtn = document.getElementById('settingsBtn');
  const dropdown = document.getElementById('settingsDropdown');

  function toggleDropdown(e) {
    e.stopPropagation();

    const isVisible = dropdown.style.display === 'flex';
    if (isVisible) {
      dropdown.style.display = 'none';
      return;
    }

    // Show dropdown in bottom-right corner
    dropdown.style.display = 'flex';
    dropdown.style.position = 'fixed';
    dropdown.style.bottom = '4rem'; // just above nav bar
    dropdown.style.right = '1rem';
  }

  function hideDropdown() {
    dropdown.style.display = 'none';
  }

  // Toggle on click and touch
  settingsBtn.addEventListener('click', toggleDropdown);
  settingsBtn.addEventListener('touchstart', toggleDropdown);

  // Hide on outside click or touch
  document.addEventListener('click', (e) => {
    if (!dropdown.contains(e.target) && e.target !== settingsBtn) {
      hideDropdown();
    }
  });

  document.addEventListener('touchstart', (e) => {
    if (!dropdown.contains(e.target) && e.target !== settingsBtn) {
      hideDropdown();
    }
  });

  // Hide on scroll
  document.addEventListener('scroll', hideDropdown);
</script>
</body>
</html>