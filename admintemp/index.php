<?php include_once 'header.php'; ?>

<?php
if (!isset($_GET['p']) || $_GET['p'] === 'dashboard' || $_GET['p'] === '') {
    // Default to dashboard if no page is specified
 
   ?>

        <div class="cards-container">
                <div class="card">
                    <h3><?php echo $lang[$language]['total_revenue']; ?></h3>
                    <div class="value">$45,231.89</div>
                    <div class="details">+15% from last month</div>
                </div>
                <div class="card">
                    <h3><?php echo $lang[$language]['subscriptions']; ?></h3>
                    <div class="value">1,234</div>
                    <div class="details">+20% from last month</div>
                </div>
                <div class="card">
                    <h3><?php echo $lang[$language]['sales']; ?></h3>
                    <div class="value">897</div>
                    <div class="details">+10% from last month</div>
                </div>
                <div class="card">
                    <h3><?php echo $lang[$language]['active_now']; ?></h3>
                    <div class="value">256</div>
                    <div class="details">Currently online</div>
                </div>


                     <div class="card">
                    <h3><?php echo $lang[$language]['total_revenue']; ?></h3>
                    <div class="value">$45,231.89</div>
                    <div class="details">+15% from last month</div>
                </div>
                <div class="card">
                    <h3><?php echo $lang[$language]['subscriptions']; ?></h3>
                    <div class="value">1,234</div>
                    <div class="details">+20% from last month</div>
                </div>
                <div class="card">
                    <h3><?php echo $lang[$language]['sales']; ?></h3>
                    <div class="value">897</div>
                    <div class="details">+10% from last month</div>
                </div>
                <div class="card">
                    <h3><?php echo $lang[$language]['active_now']; ?></h3>
                    <div class="value">256</div>
                    <div class="details">Currently online</div>
                </div>
        </div>

<?php
}
else {
    include_once htmlspecialchars($_GET["p"]) . '.php';
    if (!file_exists(htmlspecialchars($_GET["p"]) . '.php')) {
        echo '<h1>Page not found</h1>';
    }
}
?>


<?php include_once 'footer.php'; ?>

      