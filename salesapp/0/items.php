<?php include 'header.php'; ?>
<?php
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['id']) && !empty($_POST['id'])) {
        $id = $_POST['id'];
        $item_name = $_POST['name'];
        // Update item
        $stmt = $conn->prepare("UPDATE item SET name = ? WHERE id = ?");
        $stmt->bind_param("si", $item_name, $id);
        if ($stmt->execute()) {
            echo "<script>alert('item $item_name updated successfully.');</script>";
        } else {
            echo "<script>alert('Error updating item.');</script>";
        }
    } else {
        $item_name = $_POST['name'];
        // Check if item already exists
        $stmt = $conn->prepare("SELECT id FROM item WHERE name = ?");
        $stmt->bind_param("s", $item_name);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows > 0) {
            echo "<script>alert('item $item_name already exists.');</script>";
        } else {
            // Insert new item
            $stmt = $conn->prepare("INSERT INTO item (name) VALUES (?)");
            $stmt->bind_param("s", $item_name);
            if ($stmt->execute()) {
                echo "<script>alert('item $item_name added successfully.');</script>";
            } else {
                echo "<script>alert('Error adding item.');</script>";
            }
        }
    }
}
?>
<main>
<?php if (isset($_GET['id'])) { 
  $id = $_GET['id'];
   $stmt = $conn->prepare("SELECT name FROM item WHERE id = ?"); 
   $stmt->bind_param("i", $id); $stmt->execute();
    $stmt->bind_result($name); 
    $stmt->fetch(); $item_name = $name; $stmt->fetch(); } ?>
    <section class="form-section no-print">
      <form action="" method="post">
        <input type="hidden" name="id" value="<?php echo isset($id) ? $id : ''; ?>" />
         <h2><?php echo isset($id) ? 'Edit Item' : 'New Item'; ?></h2>
        <div class="form-group group-1">
          <input type="text" name="name" placeholder="Item Name" value="<?php echo isset($item_name) ? $item_name : ''; ?>" />
        </div>
        
        <button type="submit" name="submit">Submit</button>
      </form>
    </section>
 
    <section class="table-section">
      <h2>All items</h2>
   
  
       <div class="table-scroll">

      <table>
        <thead>
          <tr>
            <th>ID</th><th>Name</th><th class="no-print">Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php
          $sql = "SELECT id, name FROM item";
          $result = $conn->query($sql);
          if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
          ?>
          <tr>
            <td><?php echo $row['id']; ?></td>
            <td><?php echo $row['name']; ?></td>
           
            <td class="actions no-print">
              <button onClick="window.location.href = 'items.php?id=<?php echo $row['id']; ?>' ">✏️</button>
             
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