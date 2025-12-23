<?php include 'header.php';
//require_once '../../config.php';
if(isset($_GET['blockid'])){
  $blockid = $_GET['blockid'];
  $query = "UPDATE user SET status = 0 WHERE id = $blockid";
  mysqli_query($conn, $query);
  header("Location: index.php");
}
$username = $password = $role = $status = $editid = "";
if(isset($_GET['editid'])){
  $editid = $_GET['editid'];
  $query = "SELECT * FROM user WHERE id = $editid";
  $result = mysqli_query($conn, $query);
  $user_data = mysqli_fetch_assoc($result);
  $username = $user_data['username'];
  $role = $user_data['role'];
  $status = $user_data['status'];

}

if(isset($_POST['add'])){
  $username = $_POST['username'];
  $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
  $role = $_POST['role'];
  $status = $_POST['status'];
  $editid = $_POST['editid'];
  
  $check_query = "SELECT * FROM user WHERE id='$editid' OR username='$username'";
  $check_result = mysqli_query($conn, $check_query);
  if (mysqli_num_rows($check_result) > 0) {
    if($editid){
      $query = "UPDATE user SET username='$username', password='$password', role=$role, status=$status WHERE id = $editid";
    }else{
      echo "<script>alert('Username already exists');</script>";
    }
  }else{
    $query = "INSERT INTO user (username, password, role, status) VALUES ('$username', '$password', $role, $status)";
  }
  mysqli_query($conn, $query);
 
 
 
}

?>

<main>
    <section class="search-section no-print">
      <h2>Users</h2>
  
      <form action="" method="post">
      <div class="search-grid">
        <input type="text" name="username" placeholder="username" value="<?php echo $username; ?>" />
        <input type="text" name="password" placeholder="password" />
        <?php if(isset($_GET['editid'])){ ?>
          <input type="hidden" name="editid" value="<?php echo $_GET['editid']; ?>" />
        <?php } ?>
      
       
        <select name="role">
          <option value="1" <?php if($role == 1) echo "selected"; ?>>teacher</option>
          <option value="0" <?php if($role == 0) echo "selected"; ?>>admin</option>
        </select>
         <select name="status">
          <option value="1" <?php if($status == 1) echo "selected"; ?>>active</option>
          <option value="0" <?php if($status == 0) echo "selected"; ?>>block</option>
        </select>
      </div>
      <button type="submit" name="add">Save</button>
      </form>
    </section>

    
 
    <section class="table-section">
      <h2>Users' List</h2>

     
       <div class="table-scroll">

      <table>
        <thead>
          <tr>
            <th>ID</th><th>Username</th><th>Role</th><th>Status</th><th class="no-print">🗑️</th><th class="no-print">Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php
          $query = "SELECT * FROM user";
          $result = mysqli_query($conn, $query);

          if (mysqli_num_rows($result) > 0) {
            while($row = mysqli_fetch_assoc($result)) {
              ?>
              <tr>
                <td><?php echo $row['id']; ?></td>
                <td><?php echo $row['username']; ?></td>
                <td><?php echo $row['role']; ?></td>
                <td><?php echo $row['status']; ?></td>
                <td class="actions no-print"><button onClick="window.location.href='?blockid=<?php echo $row['id']; ?>' ">X</button></td>
                <td class="actions no-print">
                  <button onClick="window.location.href='?editid=<?php echo $row['id']; ?>'">✏️</button>
                
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