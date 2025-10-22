<?php include 'header.php'; ?>

<main>
    <section class="search-section no-print">
       <button type="button" class="btn btn-primary" onclick="window.location.href='saleadd.php'">Create New Sale</button>
       <br><br>
      <h2>Search Sales</h2>
     
      <form action="" method="get">
      <div class="search-grid">

        <input type="date" id="date_from" name="date_from" value="<?php echo isset($_GET['date_from']) ? $_GET['date_from'] : date('Y-m-d'); ?>" />

        <input type="date" id="date_to" name="date_to" value="<?php echo isset($_GET['date_to']) ? $_GET['date_to'] : date('Y-m-d'); ?>" />

      <input type="text" name="route_name" id="route_name" placeholder="Route Name" value="<?php echo isset($_GET['route_name']) ? $_GET['route_name'] : ''; ?>"/>
       <input type="text" name="shop_name" id="shop_name" placeholder="Shop Name" value="<?php echo isset($_GET['shop_name']) ? $_GET['shop_name'] : ''; ?>"/>
            </div>
      <button type="submit" name="search">Search</button>
      </form>
    </section>


 
    <section class="table-section">
      <h2>Sales Table   <button style="float: right; margin-right: 10px; background-color: #4CAF50; color: white; border-radius: 5px; padding: 5px 10px; border: none; cursor: pointer;" type="button" class="btn btn-primary" onclick="window.location.href='sales.php'">Reset</button></h2>
    

       <div class="table-scroll">

      <table>
        <thead>
          <tr>
            <th>ID</th><th>Route</th><th>Shop</th><th>Order</th><th>Delivery</th> <th>Remarks</th> <th>Approval</th><th class="no-print"></th>
          </tr>
        </thead>
        <tbody>
          <?php
          if (isset($_GET['search'])) {
            $query = "SELECT o.id, r.name AS route, s.name AS shop, o.order_date, o.delivery_date, o.remarks, o.approved_by FROM orders o JOIN route r ON o.route_id = r.id JOIN shop s ON o.shop_id = s.id WHERE created_by = '$_SESSION[user_id]' AND r.name LIKE '%".$_GET['route_name']."%' AND s.name LIKE '%".$_GET['shop_name']."%' AND o.order_date BETWEEN '".$_GET['date_from']."' AND '".$_GET['date_to']."' ORDER BY o.id DESC";
          }
          else {
          $query = "SELECT o.id, r.name AS route, s.name AS shop, o.order_date, o.delivery_date, o.remarks, o.approved_by FROM orders o JOIN route r ON o.route_id = r.id JOIN shop s ON o.shop_id = s.id WHERE created_by = '$_SESSION[user_id]' ORDER BY o.id DESC LIMIT 10";
          }
          
          $result = $conn->query($query);

          if ($result->num_rows > 0) {
            while($row = $result->fetch_assoc()) {
          ?>
          <tr>
            <td><?php echo $row['id']; ?></td>
            <td><?php echo $row['route']; ?></td>
            <td><?php echo $row['shop']; ?></td>
            <td><?php echo $row['order_date']; ?></td>
            <td><?php echo $row['delivery_date']; ?></td>
            <td><?php echo $row['remarks']; ?></td>
            <td><?php echo ($row['approved_by'] == 0) ? 'None' : $row['approved_by']; ?></td>

            <td class="actions no-print">

            <?php
            if ($row['approved_by'] == 0) {
            ?>
            <button onClick="window.location.href = 'saleadd.php?id=<?php echo $row['id']; ?>' ">✏️</button>
            <?php
            }
            else {
            ?>
            <button onClick="window.location.href = 'saleview.php?id=<?php echo $row['id']; ?>' ">👁️</button>
            <?php
            }
            ?>
           

            </td>
          </tr>
          <?php
            }
          }
          ?>
           
        </tbody>
      </table>
        </div>
    </section>
  </main>

  <?php
  require_once 'footer.php';
  ?>