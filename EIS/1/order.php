<?php
include 'header.php';
?>
<?php
// if (isset($_POST['create'])) {
//     $role=$_SESSION['role'];
//     $route_id = $_POST['route_id'];

//     if (!is_numeric($route_id) || $route_id <= 0) {
//         echo "<script>alert('Invalid route id.'); window.location.href = 'shop.php';</script>";
//         exit;
//     }


//     $shop_name = $_POST['shop_name'];
//     $check_query = "SELECT id FROM shop WHERE name='$shop_name' AND route_id=$route_id";
//     $check_result = mysqli_query($conn, $check_query);
//     if (mysqli_num_rows($check_result) > 0) {
//         echo "<script>alert('Shop $shop_name already exists for the selected route.'); window.location.href = 'shop.php';</script>";
//         exit;
//     }
//     $insert_query = "INSERT INTO shop (name, route_id) VALUES ('$shop_name', $route_id)";
//     if (mysqli_query($conn, $insert_query)) {
//         echo "<script>alert('New shop $shop_name added successfully.'); window.location.href = 'shop.php';</script>";
//     } else {
//         echo "<script>alert('Error creating route: " . mysqli_error($conn) . "');</script>";
//     }
// }
?>

            <main class="content-area">
                
                <h1 class="page-title">Order Route</h1>


              
                 
   <form method="GET" action="order2.php">               
<div class="card">
    <h4 style="margin-bottom: 1rem;">Select Route</h4>
    <h4 style="margin-bottom: 1rem; color: red;"><?php echo $_GET['msg'] ?? ''; ?></h4>
    <div class="input-group">
        
        <!-- <div>
            <label class="form-label">Shop Name</label>
            <input type="text" name= "shop_name" class="form-control" placeholder="e.g. Main Street Shop" required>
        </div> -->

       
        
        <div class="custom-select-wrapper">
            <label class="form-label">Route</label>
            <div class="searchable-dropdown">
                <input type="text" name="route_name" class="form-control" id="comboInput" placeholder="Type & Select" autocomplete="off" required>
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