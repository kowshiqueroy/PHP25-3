</div> 

<div class="bottom-nav">
    <a href="index.php" class="nav-item ">🏠 Home</a>
    
    <?php if($_SESSION['role'] != 'viewer'): ?>
        <a href="transaction.php" class="nav-item ">📝 Entry</a>
        <a href="products.php" class="nav-item ">📦 Products</a>
    <?php endif; ?>
    
    <a href="reports.php" class="nav-item ">📊 Reports</a>
    <a href="full_database.php" class="nav-item " >🗄️ Database</a>
    
    <?php if($_SESSION['role'] == 'admin'): ?>
        <a href="settings.php" class="nav-item ">⚙️ Settings</a>
    <?php endif; ?>
</div>
<br><br><br><br><br><br>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="script.js"></script>
<script>
    // Service Worker Registration
    if ('serviceWorker' in navigator) {
        navigator.serviceWorker.register('sw.js').then(function(reg) {
            console.log('SW Registered');
        });
    }
</script>
</body>
</html>