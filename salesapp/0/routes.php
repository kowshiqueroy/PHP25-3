<?php include 'header.php'; ?>
<?php
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['id']) && !empty($_POST['id'])) {
        $id = $_POST['id'];
        $route_name = $_POST['name'];
        // Update route
        $stmt = $conn->prepare("UPDATE route SET name = ? WHERE id = ?");
        $stmt->bind_param("si", $route_name, $id);
        if ($stmt->execute()) {
            echo "<script>alert('Route $route_name updated successfully.');</script>";
        } else {
            echo "<script>alert('Error updating route.');</script>";
        }
    } else {
        $route_name = $_POST['name'];
        // Check if route already exists
        $stmt = $conn->prepare("SELECT id FROM route WHERE name = ?");
        $stmt->bind_param("s", $route_name);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows > 0) {
            echo "<script>alert('Route $route_name already exists.');</script>";
        } else {
            // Insert new route
            $stmt = $conn->prepare("INSERT INTO route (name) VALUES (?)");
            $stmt->bind_param("s", $route_name);
            if ($stmt->execute()) {
                echo "<script>alert('Route $route_name added successfully.');</script>";
            } else {
                echo "<script>alert('Error adding route.');</script>";
            }
        }
    }
}
?>
<main>
<?php if (isset($_GET['id'])) { 
  $id = $_GET['id'];
   $stmt = $conn->prepare("SELECT name FROM route WHERE id = ?"); 
   $stmt->bind_param("i", $id); $stmt->execute();
    $stmt->bind_result($name); 
    $stmt->fetch(); $route_name = $name; $stmt->fetch(); } ?>
    <section class="form-section no-print">
      <form action="" method="post">
        <input type="hidden" name="id" value="<?php echo isset($id) ? $id : ''; ?>" />
         <h2><?php echo isset($id) ? 'Edit Route' : 'New Route'; ?></h2>
        <div class="form-group group-1">
          <input type="text" name="name" placeholder="Route Name" value="<?php echo isset($route_name) ? $route_name : ''; ?>" />
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
            <th>ID</th><th>Name</th><th class="no-print">Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php
          $sql = "SELECT id, name FROM route";
          $result = $conn->query($sql);
          if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
          ?>
          <tr>
            <td><?php echo $row['id']; ?></td>
            <td><?php echo $row['name']; ?></td>
           
            <td class="actions no-print">
              <button onClick="window.location.href = 'routes.php?id=<?php echo $row['id']; ?>' ">✏️</button>
             
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