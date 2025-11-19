<?php
include 'header.php';
?>
<?php
if (isset($_POST['create'])) {
    $role=$_SESSION['role'];
    $route_id = $_POST['route_id'];

    if (!is_numeric($route_id) || $route_id <= 0) {
        echo "<script>alert('Invalid route id.'); window.location.href = 'shop.php';</script>";
        exit;
    }


    $shop_name = $_POST['shop_name']." ". $_POST['shop_address']." ". $_POST['shop_contact'];
    $check_query = "SELECT id FROM shop WHERE name='$shop_name' AND route_id=$route_id";
    $check_result = mysqli_query($conn, $check_query);
    if (mysqli_num_rows($check_result) > 0) {
        echo "<script>alert('Shop $shop_name already exists for the selected route.'); window.location.href = 'shop.php';</script>";
        exit;
    }
    $insert_query = "INSERT INTO shop (name, route_id) VALUES ('$shop_name', $route_id)";
    if (mysqli_query($conn, $insert_query)) {
        echo "<script>alert('New shop $shop_name added successfully.'); window.location.href = 'shop.php';</script>";
    } else {
        echo "<script>alert('Error creating route: " . mysqli_error($conn) . "');</script>";
    }
}
?>

            <main class="content-area">
                
                <h1 class="page-title">Shop</h1>


              
                 
   <form method="POST">               
<div class="card">
    <h4 style="margin-bottom: 1rem;">New Shop</h4>
    <div class="input-group">

      <div class="custom-select-wrapper">
            <label class="form-label">Route</label>
            <div class="searchable-dropdown">
                <input type="text" name="route" class="form-control" id="comboInput" placeholder="Type & Select" autocomplete="off" required>
                <span class="dropdown-arrow">▼</span>
                <div class="dropdown-options" id="comboOptions">
                    <?php
                    $sql = "SELECT id, name FROM route ORDER BY id DESC";
                    $result = $conn->query($sql);
                    if ($result->num_rows > 0) {
                        while ($row = $result->fetch_assoc()) {
                            echo '<div class="option-item" data-value="' . $row['id'] . '">' . $row['name'] . '</div>';
                        }
                    }
                    ?>
                </div>
            </div>
            <input type="hidden" id="selectedValue" name="route_id" required>
        </div>
        
        <div>
            <label class="form-label">Shop Name</label>
            <input type="text" name= "shop_name" class="form-control" placeholder="e.g. Kowshique Shop" required>
        </div>
         <div>
            <label class="form-label">Address</label>
            <input type="text" name= "shop_address" class="form-control" placeholder="e.g. Babu Para, Nilphamari" required>
        </div>
         <div>
            <label class="form-label">Shop Contact</label>
            <input type="text" name= "shop_contact" class="form-control" placeholder="e.g. 01632950179" required>
        </div>

       
        
      
        

    </div>
    
    <div style="margin-top: 1rem; text-align: right;">
        <button class="btn btn-primary" type="submit" name="create">Create</button>
    </div>

</div>
    </form>
      <div class="card printable-content">
                    
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
                        <div>
                            <h2 style="font-size: 1.25rem; font-weight: 700;">All Shops</h2>
                            <p style="color: var(--text-secondary); font-size: 0.9rem; margin-top: 0.25rem;">Generated on <?php echo date('Y-m-d'); ?></p>
                        </div>
                        <button onclick="window.print()" class="btn btn-ghost no-print" style="border: 1px solid var(--border-color);">
                            🖨 Print Report
                        </button>
                    </div>

                    <div class="table-responsive">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Name</th>
                                    <th>Route<th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $sql = "SELECT shop.id, shop.name, route.name AS route_name FROM shop JOIN route ON shop.route_id = route.id WHERE route.role=".$_SESSION['role']." ORDER BY shop.id DESC";
                                $result = $conn->query($sql);
                                if ($result->num_rows > 0) {
                                    while($row = $result->fetch_assoc()) {
                                ?>
                                <tr>
                                    <td><?php echo $row['id']; ?></td>
                                    <td><?php echo $row['name']; ?></td>
                                    <td><?php echo $row['route_name']; ?></td>
                                </tr>
                                <?php
                                    }
                                }
                                ?>
                            
                            </tbody>
                        </table>
                    </div>
                    
                    <div style="margin-top: 2rem; border-top: 1px solid #eee; padding-top: 1rem; font-size: 0.8rem; color: #666;">
                        Confidential Document - Internal Use Only
                    </div>
                </div>

               
             
                </main>
 <?php
include 'footer.php';
?>     