<?php include 'header.php'; ?>
<?php 
if(isset($_GET['submit'])) {
    // Process form data here
    $username = $_GET['username'];
    $password = $_GET['password'];
    $role = $_GET['role'];
    $status = $_GET['status'];
    $id = $_GET['id'];
    // You can add code to save this data to a database
    // echo "<script>alert('User $username with role $role and status $status submitted.');</script>";
    $stmt = $conn->prepare("SELECT id FROM user WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result && $result->num_rows === 0 && $id == '') {
        // Insert new user
        $stmt = $conn->prepare("INSERT INTO user (username, password, role, status) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("sssi", $username, password_hash($password, PASSWORD_DEFAULT), $role, $status);
        if ($stmt->execute()) {
            echo "<script>alert('New user $username added.');</script>";
        } else {
            echo "<script>alert('Error: " . $conn->error . "');</script>";
        }
        $stmt->close();
    } else {
        // Update existing user
        $stmt = $conn->prepare("UPDATE user SET password = ?, role = ?, status = ? WHERE id = ?");
        $stmt->bind_param("ssii", password_hash($password, PASSWORD_DEFAULT), $role, $status, $id);
        if ($stmt->execute()) {
            echo "<script>alert('User $username updated.');</script>";
        } else {
            echo "<script>alert('Error: " . $conn->error . "');</script>";
        }
        $stmt->close();
}

}


?>
<main>
<?php
$username = '';
$role = 0;
$status = 1;
if(isset($_GET['id'])) {
    $id = $_GET['id'];
    $stmt = $conn->prepare("SELECT username, role, status FROM user WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows === 1) {
        $row = $result->fetch_assoc();
        $username = $row['username'];
        $role = $row['role'];
        $status = $row['status'];
    } else {
        echo "<script>alert('Error: User not found');</script>";
    }
    $stmt->close();
}?>
  <section class="form-section no-print">
        <h2>New User</h2>
      <form action="" method="get">
       
        <div class="form-group group-2">
          <input type="username" name="username" placeholder="Username" value="<?php echo $username; ?>" />
          <input type="password" name="password" value="" placeholder="Password" />
        </div>
        <div class="form-group group-3">
          <input type="number" name="id" value="<?php echo isset($_GET['id']) ? $_GET['id'] : ''; ?>" placeholder="ID" readonly />
          <select name="role">
            <option <?php echo $role == 0 ? 'selected' : ''; ?> value="0">User</option>
            <option <?php echo $role == 1 ? 'selected' : ''; ?> value="1">Admin</option>
          </select>
         <select name="status">
            <option <?php echo $status == 1 ? 'selected' : ''; ?> value="1">Active</option>
            <option <?php echo $status == 0 ? 'selected' : ''; ?> value="0">Inactive</option>
          </select>
        </div>
        <button type="submit" name="submit">Submit</button>
      </form>
    </section>
 
    <section class="table-section">
      <h2>All Users</h2>
     
      <div class="table-actions">
        <button onClick="window.print()">Print</button>
        <button>Export</button>
        <button>Copy</button>
      </div>
       <div class="table-scroll">

      <table>
        <thead>
          <tr>
            <th>ID</th><th>Name</th><th>Role</th><th>Status</th><th></th>
          </tr>
        </thead>
        <tbody>
          <?php
          $stmt = $conn->prepare("SELECT id, username, role, status FROM user");
          $stmt->execute();
          $result = $stmt->get_result();
          while ($row = $result->fetch_assoc()) {
          ?>
          <tr>
            <td><?php echo $row['id']; ?></td>
            <td><?php echo $row['username']; ?></td>
            <td><?php echo $row['role'] == 1 ? 'Admin' : 'User'; ?></td>
       
            <td><?php echo $row['status'] == 1 ? 'Active' : 'Inactive'; ?></td>
            <td class="actions no-print">
              <button onclick="window.location.href='users.php?id=<?php echo $row['id']; ?>'">✏️</button>
            </td>
          </tr>
          <?php
          }

          $stmt->close();
?>
           
        </tbody>
      </table>
        </div>
    </section>
</main>

  <?php
  require_once 'footer.php';
  ?>