<?php include 'header.php'; ?>
<?php
if (isset($_POST['submit'])) {
    if (isset($_POST['id']) && !empty($_POST['id'])) {
        $id = $_POST['id'];
        $shop_name = $_POST['name'];
        $route_id = $_POST['route_id'];
        // Update shop
        $stmt = $conn->prepare("UPDATE shop SET name = ?, route_id = ? WHERE id = ?");
        $stmt->bind_param("sii", $shop_name, $route_id, $id);
        if ($stmt->execute()) {
            echo "<script>alert('Shop $shop_name updated successfully.');</script>";
        } else {
            echo "<script>alert('Error updating shop.');</script>";
        }
    } else {
        $shop_name = $_POST['name'];
        $route_id = $_POST['route_id'];
        // Check if shop already exists
        $stmt = $conn->prepare("SELECT id FROM shop WHERE name = ? ");
        $stmt->bind_param("s", $shop_name);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows > 0) {
            echo "<script>alert('Shop $shop_name already exists.');</script>";
        } else {
            // Insert new shop
            $stmt = $conn->prepare("INSERT INTO shop (name, route_id) VALUES (?, ?)");
            $stmt->bind_param("si", $shop_name, $route_id);
            if ($stmt->execute()) {
                echo "<script>alert('Shop $shop_name added successfully.');</script>";
            } else {
                echo "<script>alert('Error adding shop.');</script>";
            }
        }
    }
       echo"<script>window.location.href='shops.php'</script>";
}
?>


<main>
<?php if (isset($_GET['id'])) { 
  $id = $_GET['id'];
   $stmt = $conn->prepare("SELECT name, route_id FROM shop WHERE id = ?"); 
   $stmt->bind_param("i", $id); $stmt->execute(); $stmt->bind_result($name, $route_id); $stmt->fetch(); $shop_name = $name; 
   $stmt->fetch(); $route_id = $route_id;
   } ?>



    <section class="form-section no-print">
      <form action="" method="post">
        <input type="hidden" name="id" value="<?php echo isset($id) ? $id : ''; ?>" />
         <h2><?php echo isset($id) ? 'Edit Shop' : 'New Shop'; ?></h2>
        <div class="form-group group-1">
          <input type="text" name="name" placeholder="Shop Name" value="<?php echo isset($shop_name) ? $shop_name : ''; ?>" />
        </div>
        <div class="form-group group-2">
          <select name="route_id">
            <option value="">Select Route</option>
            <?php
            $route_sql = "SELECT id, name FROM route";
            $route_result = $conn->query($route_sql);
            if ($route_result->num_rows > 0) {
              while ($route_row = $route_result->fetch_assoc()) {
                $selected = (isset($route_id) && $route_id == $route_row['id']) ? 'selected' : '';
                echo "<option value='" . $route_row['id'] . "' $selected>" . $route_row['name'] . "</option>";
              }
            }
            ?>
          </select>
        </div>
        
        <button type="submit" name="submit">Submit</button>
      </form>
    </section>
 
    <section class="table-section">
      <h2>All Routes</h2>
   
  
       <div class="table-scroll">

      <table>
        <thead>
          <tr>
            <th>ID</th><th>Name</th><th>Route</th><th class="no-print">Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php
          $sql = "SELECT id, name, route_id FROM shop";
          $result = $conn->query($sql);
          if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
          ?>
          <tr>
            <td><?php echo $row['id']; ?></td>
            <td><?php echo $row['name']; ?></td>
            <?php
            $route_sql = "SELECT name FROM route WHERE id = '{$row['route_id']}'";
            $route_result = $conn->query($route_sql);
            if ($route_result->num_rows > 0) {
              $route_row = $route_result->fetch_assoc();
              $route_name = $route_row['name'];
            } else {
              $route_name = "";
            }
            ?>
            <td><?php echo $route_name; ?></td>
           
            <td class="actions no-print">
              <button onClick="window.location.href = 'shops.php?id=<?php echo $row['id']; ?>' ">✏️</button>
             
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