<?php include 'header.php';

?>

<main>
    

    
 
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
          $query = "SELECT * FROM student ORDER BY id DESC";
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
                <td><a href="photo/<?php echo $row['photo']; ?>" target="_blank">photo/<?php echo $row['photo']; ?></a></td>
                <td class="actions no-print"><button onClick="window.location.href='index.php?blockid=<?php echo $row['id']; ?>' ">X</button></td>
                <td class="actions no-print">
                  <button onClick="window.location.href='index.php?editid=<?php echo $row['id']; ?>'">✏️</button>
                
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