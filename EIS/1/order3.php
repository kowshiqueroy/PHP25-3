<?php
include 'header.php';
?>
<?php
if($_GET['route_id'] == "" || $_GET['route_name'] == "" || $_GET['shop_id'] == "" || $_GET['shop_name'] == "" ){
    echo '<script> window.location.href = "order2.php?msg=Error Name"; </script>';
}
if (isset($_GET['route_id']) && isset($_GET['route_name']) && isset($_GET['shop_id']) && isset($_GET['shop_name'])) {
    $route_id = (int)$_GET['route_id'];
    $shop_id = (int)$_GET['shop_id'];
    $route_name = htmlspecialchars($_GET['route_name']);
    $shop_name = htmlspecialchars($_GET['shop_name']);
    $user_id = $_SESSION['user_id'];
    $order_date = date('Y-m-d H:i:s');
    // You can now use these variables as needed, for example, to create an order record in the database.
    $sql = "INSERT INTO order_info (route_id, shop_id, user_id, order_date) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iiis", $route_id, $shop_id, $user_id, $order_date);
    $stmt->execute();
    if ($stmt->affected_rows > 0) {
        $order_id = $conn->insert_id;
        echo "<script> window.location.href = 'order4.php?order_id=$order_id';</script>";
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }



}
else {
    echo "<script>alert('Missing route or shop information.'); window.location.href = 'order3.php';</script>";
    exit;
}
?>

            <main class="content-area">
                
                <h1 class="page-title">Order 3</h1>




              
                 
   
      

               
             
                </main>
 <?php
include 'footer.php';
?>     