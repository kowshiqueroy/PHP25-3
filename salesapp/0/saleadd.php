<?php include 'header.php'; ?>
<?php
if(isset($_POST['submitinfo'])) {
    $order_date = $_POST['order_date'];
    $delivery_date = $_POST['delivery_date'];
    $route_id = $_POST['route_id'];
    $shop_id = $_POST['shop_id'];
    $remarks = $_POST['remarks'];
    $created_by=$_SESSION['user_id'];
    $latitude = $_POST['latitude'];
    $longitude = $_POST['longitude'];
    // You can process and save this data to the database as needed
    // For example:

    if (is_null($route_id) || is_null($shop_id)) {
        echo "<script>alert('Error: route_id or shop_id cannot be null');window.location.href='saleadd.php';</script>";
        exit;
    }
    if ($latitude == 0.00 && $longitude == 0.00) {
        echo "<script>alert('Error: Location Error 0.00'); window.location.href='saleadd.php';</script>";
        exit;
    }

    if (isset($_POST['id']) && !empty($_POST['id'])) {
        $id = $_POST['id'];
        $sql = "UPDATE orders SET order_date='$order_date', delivery_date='$delivery_date', route_id='$route_id', shop_id='$shop_id', remarks='$remarks' WHERE id='$id'";
        if ($conn->query($sql) === TRUE) {
            echo '<div style="text-align: center;">Order updated successfully</div>';
            echo '<script>window.location.href="saleadd.php?id='.$id.'"</script>';
            exit;
        } else {
            echo "Error: " . $sql . "<br>" . $conn->error;
        }
        exit;
    }
    $sql = "INSERT INTO orders (order_date, delivery_date, route_id, shop_id, remarks, created_by,latitude,longitude) VALUES ('$order_date', '$delivery_date', '$route_id', '$shop_id', '$remarks', '$created_by','$latitude','$longitude')";
    if ($conn->query($sql) === TRUE) {
        echo '<div style="text-align: center;">Order created successfully</div>';

        $id = $conn->insert_id;
        echo '<script>window.location.href="saleadd.php?id='.$id.'"</script>';
        
        exit;
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }
}
?>
<main>
    <section class="search-section no-print">
      <h2>Sale Info</h2>
     
      <form action="" method="post">
<?php
        if (isset($_GET['id'])) {
            $id = $_GET['id'];
            $sql = "SELECT * FROM orders WHERE id = '$id'";
            $result = $conn->query($sql);
            if ($result->num_rows > 0) {
                $row = $result->fetch_assoc();
                $order_date = $row['order_date'];
                $delivery_date = $row['delivery_date'];
                $route_id = $row['route_id'];
                $shop_id = $row['shop_id'];
                $remarks = $row['remarks'];
            }
        }

        ?>
      <div class="search-grid">

      <?php
        if (isset($_GET['id'])) {
            echo '<input type="hidden" name="id" value="' . $id . '">';
        }
      ?>
        <input type="hidden" id="latitude" name="latitude">
        <input type="hidden" id="longitude" name="longitude">

        <script>
            function getLocation() {
                if (navigator.geolocation) {
                    navigator.geolocation.getCurrentPosition(function(position) {
                        document.getElementById("latitude").value = position.coords.latitude;
                        document.getElementById("longitude").value = position.coords.longitude;
                    }, function() {
                        if (confirm("Please allow LOCATION permission. Call for help: 01632950179")) {
                            getLocation();
                        }
                    });
                }
            }
            getLocation();
        </script>
 
        <label>Order Date</label>
          <label>Delivery Date</label>
        <input type="date" id="order_date" name="order_date" value="<?php echo isset($order_date) ? $order_date : date('Y-m-d'); ?>" />

        <input type="date" id="delivery_date" name="delivery_date" value="<?php echo isset($delivery_date) ? $delivery_date : date('Y-m-d'); ?>" />

        <select name="route_id" id="route_id" required >
          <option value="">Route</option>
          <?php
          $sql = "SELECT * FROM route order by id desc";
          $result = $conn->query($sql);
          if ($result->num_rows > 0) {
              while ($row = $result->fetch_assoc()) {
                  $selected = (isset($route_id) && $route_id == $row['id']) ? 'selected' : '';
                  echo "<option value='" . $row['id'] . "' $selected>" . $row['name'] . "</option>";
              }
          }
          ?>
        </select>
        <select name="shop_id" id="shop_id" required>
          <option value="">Shop</option>
          <?php
          $sql = "SELECT id, name FROM shop WHERE id = '$shop_id'";
          $result = $conn->query($sql);
          if ($result->num_rows > 0) {
              $row = $result->fetch_assoc();
              $selected = (isset($shop_id) && $shop_id == $row['id']) ? 'selected' : '';
              echo "<option value='" . $row['id'] . "' $selected>" . $row['name'] . "</option>";
          }?>

          

       
        </select>
      </div>

       <div class="search-grid">

        <input type="text" id="remarks" name="remarks" value="<?php echo isset($remarks) ? $remarks : ''; ?>" placeholder="Remarks"/>
</div>
      <button type="submit" name="submitinfo">Save</button>
      </form>
    </section>

    <script>
      document.getElementById('route_id').addEventListener('change', function(){
        var route_id = this.value;
        var shop_id = document.getElementById('shop_id');
        var xhr = new XMLHttpRequest();
        xhr.onreadystatechange = function(){
          if (xhr.readyState == 4 && xhr.status == 200) {
            var response = JSON.parse(xhr.responseText);
            var options = '';
            response.forEach(function(shop){
              options += '<option value="' + shop.id + '">' + shop.name + '</option>';
            });
            shop_id.innerHTML = options;
          }
        }
        xhr.open('GET', 'getshops.php?route_id=' + route_id, true);
        xhr.timeout = 5000;
        xhr.send();
      });
     
    </script>
 

<?php
    if (isset($_GET['id'])) {
        $id = $_GET['id'];
        // $stmt = $conn->prepare("SELECT route_id, shop_id, remarks FROM sale WHERE id = ?");
        // $stmt->bind_param("i", $id);
        // $stmt->execute();
        // $stmt->bind_result($route_id, $shop_id, $remarks);
        // $stmt->fetch();
?>
hi

 <section class="table-section">
      <h2>Items</h2>
    
       <div class="table-scroll">

      <table>
        <thead>
          <tr>
            <th>ID</th><th>Name</th><th>Email</th><th>Status</th><th class="no-print">🗑️</th><th class="no-print">Actions</th>
          </tr>
        </thead>
        <tbody>
          <tr>
            <td>1</td><td>Alice</td><td>alice@example.com</td><td>Active</td>
            <td class="actions no-print"><button>🗑️</button></td>
            <td class="actions no-print">
              <button>✏️</button>
              <button>👁️</button>
              <button>📤</button>
            </td>
          </tr>

           
        </tbody>
      </table>
        </div>
    </section>

<?php
    }    
?>
   




  </main>

  <?php
  require_once 'footer.php';
  ?>