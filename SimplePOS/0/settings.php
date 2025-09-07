<?php include_once 'header.php'; ?>


<?php

if (isset($_POST['submit'])) {
    $companyname = $_POST['companyname'];
    $address = $_POST['address'];
    $phone = $_POST['phone'];
    $theme = $_POST['theme'];
    $language = $_POST['language'];
    $photopath = $_POST['photopath'];
    $bannerpath = $_POST['bannerpath'];
    $theme = ($theme == 1) ? 1 : 0;
    $language = ($language == 1) ? 1 : 0;


    $sql = "UPDATE settings SET theme='$theme', language='$language', companyname='$companyname', address='$address', phone='$phone', photopath='$photopath', bannerpath='$bannerpath' WHERE id=1";

    if ($conn->query($sql) === TRUE) {

        $_SESSION['theme'] = $theme;
        $_SESSION['language'] = $language;
        


        echo "<script>window.location.href='settings.php?msg=".$lang[$language]['Success']."';</script>";
    } else {
        echo "<script>window.location.href='settings.php?msg=".$lang[$language]['Error']."';</script>";
    }
}








$sql = "SELECT * FROM settings WHERE id = 1";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
?>

    


  <div class="modal-content">
    <div class="modal-header">
        <h2><?php echo $lang[$language]['settings']; ?>  <?php
    if (isset($_GET['msg'])) {
        $msg = $_GET['msg'];
        if (strpos($msg, 'Error') !== false) {
            echo "<span     id = 'msg' style='color: red;'>" . $msg . "</span>";
        } else {
            echo "<span  id = 'msg' style='color: green;'>" . $msg . "</span>";
        }
    }
    ?></h2>
    </div>
    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>" method="post">
        <div class="form-group">
            <label for="companyname">Company Name</label>
            <input type="text" id="companyname" name="companyname" class="modern-input" value="<?php echo $row['companyname']; ?>" required>
        </div>
        <div class="form-group">
            <label for="address">Address</label>
            <input type="text" id="address" name="address" class="modern-input" value="<?php echo $row['address']; ?>" required>
        </div>
        <div class="form-group">
            <label for="photopath">Photo Path</label>
            <input type="text" id="photopath" name="photopath" class="modern-input" value="<?php echo $row['photopath']; ?>" required>
        </div>
        <div class="form-group">
            <label for="bannerpath">Banner Path</label>
            <input type="text" id="bannerpath" name="bannerpath" class="modern-input" value="<?php echo $row['bannerpath']; ?>" required>
        </div>
        <div class="form-group">
            <label for="phone">Phone</label>
            <input type="text" id="phone" name="phone" class="modern-input" value="<?php echo $row['phone']; ?>" required>
        </div>
        <div class="form-group">
            <label for="theme">Theme</label>
            <select id="theme" name="theme" class="modern-select" required>
                <option value="0" <?php echo $row['theme'] == 0 ? 'selected' : ''; ?>>Light</option>
                <option value="1" <?php echo $row['theme'] == 1 ? 'selected' : ''; ?>>Dark</option>
            </select>
        </div>
        <div class="form-group">
            <label for="language">Language</label>
            <select id="language" name="language" class="modern-select" required>
                <option value="0" <?php echo $row['language'] == 0 ? 'selected' : ''; ?>>English</option>
                <option value="1" <?php echo $row['language'] == 1 ? 'selected' : ''; ?>>Bangla</option>
            </select>
        </div>

       
        <div class="modal-footer">
            <button type="submit" name="submit" class="btn-submit">Update</button>
        </div>
    </form>
</div>
 
                
<?php
}
?>
<?php include_once 'footer.php'; ?>

      