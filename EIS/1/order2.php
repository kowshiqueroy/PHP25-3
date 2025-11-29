<?php
include 'header.php';
?>
<?php
if($_GET['route_id'] == "" || $_GET['route_name'] == "" ){
    echo '<script> window.location.href = "order.php?msg=Error Name"; </script>';
}
?>

            <main class="content-area">
                
                <h1 class="page-title">Order Shop Info</h1>


              
                 
   <form method="GET" action="order3.php">               
<div class="card">
    <h4 style="margin-bottom: 1rem;">Select Shop</h4>
    <div class="input-group">
        
        <div>
            <label class="form-label">Route Name</label>
            <input type="text" name= "route_name" class="form-control" placeholder="e.g. Main Street Route" value="<?php echo $_GET['route_name'] ?? ''; ?>" readonly required>
            <input type="hidden" name= "route_id" class="form-control" placeholder="e.g. Main Street Route" value="<?php echo $_GET['route_id'] ?? ''; ?>" required>
        </div>

       
        
        <div class="custom-select-wrapper">
            <label class="form-label">Shop</label>
            <div class="searchable-dropdown">
                <input type="text" name="shop_name" class="form-control" id="comboInput" placeholder="Type & Select" autocomplete="off" required>
                <span class="dropdown-arrow">▼</span>
                <div class="dropdown-options" id="comboOptions">
                    <?php
                    $sql = "SELECT id, name FROM shop WHERE route_id = " . intval($_GET['route_id']) . " ORDER BY id DESC";
                    $result = $conn->query($sql);
                    if ($result->num_rows > 0) {
                        while ($row = $result->fetch_assoc()) {
                            echo '<div class="option-item" data-value="' . $row['id'] . '">' . $row['name'] . '</div>';
                        }
                    }
                    ?>
                </div>
            </div>
            <input type="hidden" id="selectedValue" name="shop_id" required>
        </div>
        

    </div>
    
    <div style="margin-top: 1rem; text-align: right;">
        <button class="btn btn-primary" type="submit" name="create">Next</button>
    </div>

</div>
    </form>
      

               
             
                </main>
 <?php
include 'footer.php';
?>     