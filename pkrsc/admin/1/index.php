<?php include 'header.php';
//require_once '../../config.php';
if(isset($_GET['blockid'])){
  $blockid = $_GET['blockid'];
  $query = "DELETE FROM student WHERE id = $blockid";
  mysqli_query($conn, $query);
  echo '<script>window.location.href = "index.php";</script>';
}



$name = $father_name = $mother_name = $dob = $blood = $phone = $reg_id = $address = $photo = "";
$message = '';
if(isset($_POST['add'])){
  $name = $_POST['name'];
  $father_name = $_POST['father_name'];
  $mother_name = $_POST['mother_name'];
  $dob = $_POST['dob'];
  $blood = $_POST['blood'];
  $phone = $_POST['phone'];
  $reg_id = $_POST['reg_id'];
  $address = $_POST['address'];
  $photo = $_POST['photo'];
  $editid = $_POST['editid'];
  $ts = time();
  $photo_name = $ts . '.' . pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION);
  $photo_path = 'photo/' . $photo_name;
  $uploadfile = $photo_path;
  if (move_uploaded_file($_FILES['photo']['tmp_name'], $uploadfile)) {
    $photo = $photo_name;
  } else {
    echo "Sorry, there was an error uploading your file.";
  }
  
  $check_query = "SELECT * FROM student WHERE id='$editid' ";
  $check_result = mysqli_query($conn, $check_query);
  if (mysqli_num_rows($check_result) > 0) {
    $query = "UPDATE student SET name='$name', father_name='$father_name', mother_name='$mother_name', dob='$dob', blood='$blood', phone='$phone', reg_id='$reg_id', address='$address', photo='$photo' WHERE id = $editid";
    mysqli_query($conn, $query);
    echo '<script>window.location.href = "index.php";</script>';
  } else {
    $query = "INSERT INTO student (name, father_name, mother_name, dob, blood, phone, reg_id, address, photo) VALUES ('$name', '$father_name', '$mother_name', '$dob', '$blood', '$phone', '$reg_id', '$address', '$photo')";
    mysqli_query($conn, $query);
    echo '<script>window.location.href = "index.php";</script>';
  }
 
 
}

?>

<main>
    <section class="search-section no-print">
      <h2>Students</h2>

      <?php if(isset($_GET['editid'])){ 
        
          $editid = $_GET['editid'];
  $query = "SELECT * FROM student WHERE id = $editid";
  $result = mysqli_query($conn, $query);
  $user_data = mysqli_fetch_assoc($result);
  $name = $user_data['name'];
  $father_name = $user_data['father_name'];
  $mother_name = $user_data['mother_name'];
  $dob = $user_data['dob'];
  $blood = $user_data['blood'];
  $phone = $user_data['phone'];
  $reg_id = $user_data['reg_id'];
  $address = $user_data['address'];
  $photo = $user_data['photo'];
  echo "<script>alert('Student data fetched successfully' + $editid + ' ' + $name);</script>";
        
        
        ?>
      <form action="" method="post" enctype="multipart/form-data">
      <div class="search-grid">
        <input type="text" name="name" placeholder="name" value="<?php echo $name; ?>" oninput="this.value = this.value.toUpperCase()" required />
        <input type="text" name="father_name" placeholder="father name" value="<?php echo $father_name; ?>" required />
        <input type="text" name="mother_name" placeholder="mother name" value="<?php echo $mother_name; ?>" required />
        <input type="date" name="dob" placeholder="date of birth" value="<?php echo $dob; ?>" required />
        <input type="text" name="blood" placeholder="blood" value="<?php echo $blood; ?>" required />
        <input type="text" name="phone" placeholder="phone" value="<?php echo $phone; ?>" required />
        <input type="text" name="reg_id" placeholder="reg id" value="<?php echo $reg_id; ?>" required />
        <input type="text" name="address" placeholder="address" value="<?php echo $address; ?>" required />
        <input type="file" name="photo" placeholder="photo" accept="image/*" capture="camera" required />
        
        <input type="hidden" name="editid" value="<?php echo $_GET['editid']; ?>" />
      </div>
      <button type="submit" name="add">Update</button>
      </form>
      <?php } else { ?>
      <form action="" method="post" enctype="multipart/form-data">
      <div class="search-grid">
        <input type="text" name="name" placeholder="name" value="" oninput="this.value = this.value.toUpperCase()" required />
        <input type="text" name="father_name" placeholder="father name" value="" required />
        <input type="text" name="mother_name" placeholder="mother name" value="" required />
        <input type="date" name="dob" placeholder="date of birth" value="" required />
        <input type="text" name="blood" placeholder="blood" value="" required />
        <input type="text" name="phone" placeholder="phone" value="" required />
        <input type="text" name="reg_id" placeholder="reg id" value="" required />
        <input type="text" name="address" placeholder="address" value="" required />
        <input type="file" name="photo" placeholder="photo" accept="image/*" capture="camera" required />

      </div>
      <button type="submit" name="add">Save</button>
      </form>
      <?php } ?>
    </section>

    
 
    <section class="table-section">
      <h2>Students' List</h2>

     
       <div class="table-scroll">

      <table>
        <thead>
          <tr>
            <th>ID</th><th>Name</th><th>Father Name</th><th>Mother Name</th><th>DOB</th><th>Blood</th><th>Phone</th><th>Reg ID</th><th>Address</th><th>Photo</th><th class="no-print">🗑️</th><th class="no-print">Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php
          $query = "SELECT * FROM student";
          $result = mysqli_query($conn, $query);

          if (mysqli_num_rows($result) > 0) {
            while($row = mysqli_fetch_assoc($result)) {
              ?>
              <tr>
                <td><?php echo $row['id']; ?></td>
                <td><?php echo $row['name']; ?></td>
                <td><?php echo $row['father_name']; ?></td>
                <td><?php echo $row['mother_name']; ?></td>
                <td><?php echo $row['dob']; ?></td>
                <td><?php echo $row['blood']; ?></td>
                <td><?php echo $row['phone']; ?></td>
                <td><?php echo $row['reg_id']; ?></td>
                <td><?php echo $row['address']; ?></td>
                <td><a href="photo/<?php echo $row['photo']; ?>" target="_blank"><img src="photo/<?php echo $row['photo']; ?>" width="50" height="50" /></a></td>
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