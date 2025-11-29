<?php
include 'header.php';
?>
<?php
if (!isset($_GET['order_id'])) {
    

echo "<script>alert('Missing order information.'); window.location.href = 'order3.php';</script>";
    exit;
}
$order_id = intval($_GET['order_id']);
$order = "SELECT * FROM order_info WHERE id = ?";
$stmt = $conn->prepare($order);
$stmt->bind_param("i", $order_id);
$stmt->execute();
$result = $stmt->get_result();
$order_details = $result->fetch_assoc();

$route_id = $order_details['route_id'];
$shop_id = $order_details['shop_id'];
$route_sql = "SELECT name FROM route WHERE id = ?";
$route_stmt = $conn->prepare($route_sql);
$route_stmt->bind_param("i", $route_id);
$route_stmt->execute();
$route_result = $route_stmt->get_result();
$route_row = $route_result->fetch_assoc();
$route_name = $route_row['name'];

$shop_sql = "SELECT name FROM shop WHERE id = ?";
$shop_stmt = $conn->prepare($shop_sql);
$shop_stmt->bind_param("i", $shop_id);
$shop_stmt->execute();
$shop_result = $shop_stmt->get_result();
$shop_row = $shop_result->fetch_assoc();
$shop_name = $shop_row['name'];
?>

            <main class="content-area">
                
                <h1 class="page-title">Order Items</h1>
                <p>ID: <?php echo intval($_GET['order_id']); ?> Date: <?php echo $order_details['order_date']; ?> Status: <?php echo $order_details['status']; ?><br>Route: <?php echo $route_name; ?> Shop: <?php echo $shop_name; ?> </p>




              
                 
   
      

               
             
                </main>
 <?php
include 'footer.php';
?>     