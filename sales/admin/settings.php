<?php
require_once '../conn.php';
require_once 'header.php';
?>

<div class="card p-1 text-center">Settings</div>



            <div class="d-flex two-columns gap-3 mt-3">
                <div class="card p-3 flex-fill">

<?php
$sql = "SELECT * FROM companyinfo WHERE id = 1";
$result = $conn->query($sql);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $companyname = $_POST['companyname'];
    $tagline = $_POST['tagline'];
   
    $logo = $_POST['logo'];
  
    $offertag = $_POST['offertag'];
    $phone = $_POST['phone'];
    $email = $_POST['email'];
    $address = $_POST['address'];
 

    $sql = "SELECT * FROM companyinfo WHERE id = 1";
    $result = $conn->query($sql);
    if ($result->num_rows > 0) {
        $sql = "UPDATE companyinfo SET companyname='$companyname', tagline='$tagline', logo='$logo',  offertag='$offertag', phone='$phone', email='$email', address='$address' WHERE id = 1";
    } else {
        $sql = "INSERT INTO companyinfo (id, companyname, tagline, logo,  offertag, phone, email, address) VALUES (1, '$companyname', '$tagline',  '$logo',  '$offertag', '$phone', '$email', '$address')";
    }
    if ($conn->query($sql) === TRUE) {
        echo "Record updated successfully";
        echo "<meta http-equiv='refresh' content='0'>";
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }
}
?>
<form method="post" action="">
    <?php
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
    ?>
    <div class="mb-3">
        <label for="companyname" class="form-label">Company Name</label>
        <input type="text" class="form-control" id="companyname" name="companyname" value="<?php echo $row['companyname']; ?>" required>
    </div>
    <div class="mb-3">
        <label for="tagline" class="form-label">Tagline</label>
        <input type="text" class="form-control" id="tagline" name="tagline" value="<?php echo $row['tagline']; ?>" required>
    </div>
    
    <div class="mb-3">
        <label for="offertag" class="form-label">Offer Tag</label>
        <input type="text" class="form-control" id="offertag" name="offertag" value="<?php echo $row['offertag']; ?>" required>
    </div>
    <div class="row mb-3">
        <div class="col">
            <label for="phone" class="form-label">Phone</label>
            <input type="text" class="form-control" id="phone" name="phone" value="<?php echo $row['phone']; ?>" required>
        </div>
        <div class="col">
            <label for="email" class="form-label">Email</label>
            <input type="email" class="form-control" id="email" name="email" value="<?php echo $row['email']; ?>" required>
        </div>
    </div>
    <div class="mb-3">
        <label for="address" class="form-label">Address</label>
        <input type="text" class="form-control" id="address" name="address" value="<?php echo $row['address']; ?>" required>
    </div>

    <div class="mb-3">
        <label for="logo" class="form-label">Logo</label>
        <input type="text" class="form-control" id="logo" name="logo" value="<?php echo $row['logo']; ?>" required>
    </div>
    
    
    <?php
    } else {
    ?>
    <div class="mb-3">
        <label for="companyname" class="form-label">Company Name</label>
        <input type="text" class="form-control" id="companyname" name="companyname" placeholder="Company Name" required>
    </div>
    <div class="mb-3">
        <label for="tagline" class="form-label">Tagline</label>
        <input type="text" class="form-control" id="tagline" name="tagline" placeholder="Tagline" required>
    </div>
    
    <div class="mb-3">
        <label for="offertag" class="form-label">Offer Tag</label>
        <input type="text" class="form-control" id="offertag" name="offertag" placeholder="Offer Tag" required>
    </div>
    <div class="row mb-3">
        <div class="col">
            <label for="phone" class="form-label">Phone</label>
            <input type="text" class="form-control" id="phone" name="phone" placeholder="Phone" required>
        </div>
        <div class="col">
            <label for="email" class="form-label">Email</label>
            <input type="email" class="form-control" id="email" name="email" placeholder="Email" required>
        </div>
    </div>
    <div class="mb-3">
        <label for="address" class="form-label">Address</label>
        <input type="text" class="form-control" id="address" name="address" placeholder="Address" required>
    </div>
    

     <div class="mb-3">
        <label for="logo" class="form-label">Logo</label>
        <input type="text" class="form-control" id="logo" name="logo"  required>
    </div>
   
    <?php
    }
    ?>
    <input type="submit" value="Submit" class="btn btn-primary">
</form>


                </div>
                
            </div>



<?php
require_once 'footer.php';
?>