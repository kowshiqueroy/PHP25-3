<?php  

include_once "head2.php";

?>

<?php  

$insert = false;
$update = false;
$empty = false;
$delete = false;
$already_card = false;


        if(isset($_POST['editbtn'])){

            $id= $_POST['idtoEdit'];
            $name= $_POST['nameEdit'];
            $id_no= $_POST['id_noEdit'];
            $bloodgroup= $_POST['bloodgroupEdit'];
           
            $email= $_POST['emailEdit'];
            $issuedate= $_POST['issuedateEdit'];
            $phone= $_POST['phoneEdit'];
            $post= $_POST['postEdit'];
            $dept= $_POST['deptEdit'];
            $status= $_POST['statusEdit'];

            $sql = "UPDATE `person` SET `name` = '$name', `pid` = '$id_no', `bloodgroup` = '$bloodgroup',`status` = '$status', `email` = '$email', `issuedate` = '$issuedate', 
            `phone` = '$phone', `post` = '$post', `dept` = '$dept' WHERE `id` = '$id' ";
            $result = mysqli_query($conn, $sql);

            if($result){
                $update = true;
            }
            else{
                echo "Error: " . $sql . "<br>" . mysqli_error($conn);
            }
        }

                    

if(isset($_GET['deleteid'])){
  $sno = $_GET['deleteid'];
  $oldphoto=$_GET['oldphoto'];
  $sql = "DELETE FROM `person` WHERE `id` = $sno";
  $result = mysqli_query($conn, $sql);
if($result){
  $delete = true;
  //delete the image from folder
  unlink("../assets/person/".$oldphoto);

}


}

if (isset($_POST['savebtn'])) {
    $name = $_POST["name"];
    $id_no = $_POST["id_no"];
    $bloodgroup = $_POST['bloodgroup'];
    $company = $_POST['company'];
    $email = $_POST['email'];
    $issuedate = $_POST['issuedate'];
    $phone = $_POST['phone'];
    $post = $_POST['post'];
    $dept = $_POST['dept'];

    if($name == '' || $id_no == ''){
        $empty = true;
    }
    else{
        //Check that Card no. is Already Registerd or not.
        $querry = mysqli_query($conn, "SELECT * FROM person WHERE pid= '$id_no' ");
        if(mysqli_num_rows($querry)>0)
        {
             $already_card = true;
        }
        else{


          // image upload 
          $uploaddir = '../assets/person/';
          $uploadfile = $uploaddir . basename($_FILES['image']['name']);

          //get the file extension
          $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);

          //get the current timestamp
          $ts = time();

          //new file name
          $newfilename = $id_no . 'T' . $ts . '.' . $ext;

          //set the new location
          $uploadfile = $uploaddir . $newfilename;

          // if the image size is more than 500 kilo bytes then show warning message js  alert and redirect o same page
          if ($_FILES['image']['size'] > 500000) {

        

            echo "<script>alert('Image size is more than 500 kilo bytes.')</script>";
           
          } else {
            //upload the image
            if (move_uploaded_file($_FILES['image']['tmp_name'], $uploadfile)) {

              
            } else {

              echo "<script>alert('Image upload failed.')</script>";
             


            }
          }
         
         
       



  // Sql query to be executed
  $sql = "INSERT INTO `person`(`name`, `pid`, `email`, `phone`, `company`, `bloodgroup`, `issuedate`, `photo`, `post`, `dept`) 
  VALUES ('$name','$id_no','$email','$phone','$company','$bloodgroup','$issuedate','$newfilename', '$post', '$dept')"; 

  // $sql = "INSERT INTO `cards` (`name`, `id_no`) VALUES ('$name', '$id_no')";
  $result = mysqli_query($conn, $sql);



   
  if($result){ 
      $insert = true;
  }
  else{
      echo "The record was not inserted successfully because of this error ---> ". mysqli_error($conn);
  } 
}
}


 }
?>
 
    <div class="content">
        <!-- Main content goes here -->

        <?php
  if($insert){
    echo "<div class='alert alert-success alert-dismissible fade show' role='alert'>
    <strong>Success!</strong> Your Card has been inserted successfully
    <button type='button' class='close' data-dismiss='alert' aria-label='Close'>
      <span aria-hidden='true'>×</span>
    </button>
  </div>";
  }
  ?>
        <?php
  if($delete){
    echo "<div class='alert alert-success alert-dismissible fade show' role='alert'>
    <strong>Success!</strong> Your Card has been deleted successfully
    <button type='button' class='close' data-dismiss='alert' aria-label='Close'>
      <span aria-hidden='true'>×</span>
    </button>
  </div>";
  }
  ?>
        <?php
  if($update){
    echo "<div class='alert alert-success alert-dismissible fade show' role='alert'>
    <strong>Success!</strong> Your Card has been updated successfully
    <button type='button' class='close' data-dismiss='alert' aria-label='Close'>
      <span aria-hidden='true'>×</span>
    </button>
  </div>";
  }
  ?>
        <?php
  if($empty){
    echo "<div class='alert alert-warning alert-dismissible fade show' role='alert'>
    <strong>Error!</strong> The Fields Cannot Be Empty! Please Give Some Values.
    <button type='button' class='close' data-dismiss='alert' aria-label='Close'>
      <span aria-hidden='true'>×</span>
    </button>
  </div>";
  }
  ?>
        <?php
  if($already_card){
    echo "<div class='alert alert-warning alert-dismissible fade show' role='alert'>
    <strong>Error!</strong> This Card is Already Added.
    <button type='button' class='close' data-dismiss='alert' aria-label='Close'>
      <span aria-hidden='true'>×</span>
    </button>
  </div>";
  }
  ?>
        <button class="btn btn-primary float-right " type="button" data-toggle="collapse" data-target="#collapseExample"
            aria-expanded="false" aria-controls="collapseExample">
            <i class="fa fa-plus"></i> Add New
        </button>
    
      
        <?php
        if(isset($_GET['editid'])){
        echo "<script>
            $(document).ready(function () {
                $('#editModal').modal({
                    backdrop: 'static',
                    keyboard: false
                });
            });
        </script>";
        

        //mysql query to get *from person table where id = $editid
        
            $editid = $_GET['editid'];
            $sql = "SELECT * FROM `person` WHERE `id` = '$editid'";
            $result = mysqli_query($conn, $sql);

            if (mysqli_num_rows($result) > 0) {
                while($row = mysqli_fetch_assoc($result)){
                    $id = $row['id'];
                    $name = $row['name'];
                    $pid = $row['pid'];
                    $email = $row['email'];
                    $phone = $row['phone'];
                    $company = $row['company'];
                    $bloodgroup = $row['bloodgroup'];
                    $issuedate = $row['issuedate'];
                    $photo = $row['photo'];
                    $post = $row['post'];
                    $dept = $row['dept'];
                    $status=$row['status'];
                }
            } else {
                echo "<script>alert('No record found.');</script>";
            }
        }

        ?>

        <div class="modal fade" id="editModal" tabindex="-1" role="dialog" aria-labelledby="editModalLabel"
            aria-hidden="true" style="display: none;">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="editModalLabel">Edit</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">×</span>
                        </button>
                    </div>
               

                  
                     


       

                    <form action="<?php   echo basename($_SERVER['PHP_SELF']);?>"id="newedit" method="POST" encrypt="multipart/form-data">
                        <div class="modal-body">
                            <input type="hidden" name="idtoEdit" id="snoEdit" value="<?php if(isset($_GET['editid'])){echo $id; }?>">

        
                           

                            <div class="form-group">
                                <label for="name">Name</label>
                                <div class="input-group">
                                    <input type="text" class="form-control" id="nameEdit" name="nameEdit" value="<?php if(isset($_GET['editid'])){echo $name; }?>" required>
                                    <div class="input-group-append">
                                        <img src="../assets/person/<?php echo $photo;?>" height="50" width="50" alt="Profile Image">
                                    </div>
                                </div>
                            </div>
                            
                            
                            <div class="row">
                            <div class="form-group col-md-6 col-6">
                                <label for="desc">Post/Position</label>
                                <input class="form-control" id="postEdit" name="postEdit" rows="3" value="<?php if(isset($_GET['editid'])){ echo $post; }?>" required></input>
                            </div>
                            <div class="form-group col-md-6 col-6">
                                <label for="desc">Department</label>
                                <input class="form-control" id="deptEdit" name="deptEdit" rows="3" value="<?php if(isset($_GET['editid'])){ echo $dept; }?>" required></input>
                            </div>
                            </div>

                           
<div class="row">
<div class="form-group col-md-6 col-6">
                                <label for="desc">Phone</label>
                                <input class="form-control" id="phoneEdit" name="phoneEdit" rows="3" value="<?php
        if(isset($_GET['editid'])){
        echo $phone; }?>" required></input>
                            </div>
<div class="form-group col-md-6 col-6">
                                <label for="desc">Email Id</label>
                                <input class="form-control" id="emailEdit" name="emailEdit" rows="3" value="<?php
        if(isset($_GET['editid'])){
        echo $email; }?>" required></input>
                            </div>
        </div>
                           


                            <div class="row">
                            <div class="form-group col-3">
                                <label for="desc">Status</label>
                                <Select class="form-control" id="bloodgroupEdit" name="statusEdit" rows="3"  required>
                                    <option value="0" <?php
        if(isset($_GET['editid']) AND $status==0){
        echo "selected"; }?>>Active<option>
                                    <option value="1" <?php
        if(isset($_GET['editid']) AND $status==1){
        echo "selected"; }?>>Block<option>




                                </Select>
                            </div>

                            <div class="form-group col-2">
                                <label for="desc">Blood</label>
                                <input class="form-control" id="bloodgroupEdit" name="bloodgroupEdit" rows="3" value="<?php
        if(isset($_GET['editid'])){
        echo $bloodgroup; }?>" required></input>
                            </div>
                         

                            <div class="form-group  col-3">
                                <label for="desc">ID</label>
                                <input class="form-control" id="id_noEdit" name="id_noEdit" rows="3" value="<?php
        if(isset($_GET['editid'])){
        echo $pid; }?>" required></input>
                            </div>
                            <div class="form-group  col-4">
                                <label for="desc">Issue</label>
                                <input class="form-control" id="issuedateEdit" name="issuedateEdit" rows="3" value="<?php
        if(isset($_GET['editid'])){
        echo $issuedate; }?>" required></input>
                            </div>
        </div>
                           

                            
                        </div>
                        <div style="width:90%; margin:0 auto;" class="modal-footer d-block text-center">
                       
                        <button class="btn btn-primary"><a style="text-decoration:none;color:white" href="<?php if(isset($_GET['editid'])){
                            echo basename($_SERVER['PHP_SELF']).'?deleteid='.$id.'&oldphoto='.$photo;} ?>">Delete</a></button>
                        
                            <button name="editbtn" type="submit" class="btn btn-primary">Save changes</button>
                        </div>
                    </form>
                   
                     
                         <style>
        .input-container {
          
            padding: 10px;
            border-radius: 5px;
            width: auto;
            margin: 20px auto;
            text-align: center;
        }

        #imageUpload {
            display: none;
        }

        #fileLabel, #newButton {
            display: inline-block;
            padding: 10px 20px;
            background-color: #007bff;
            color: white;
            border-radius: 5px;
            cursor: pointer;
            margin: 5px;
            border: none;
        }

        #fileLabel:hover, #newButton:hover {
            background-color: #0056b3;
        }

        #newButton {
            display: none; /* Initially hide the button */
        }

        #preview {
            margin: 10px;
        }
    </style>

<?php
if(isset($_POST['updatePhotobtn'])){
    $upid=$_POST['upid'];
    $upoid=$_POST['upoid'];
    $image = $_FILES['updatedPhoto'];
    $uploaddir = '../assets/person/';
    $uploadfile = $uploaddir . basename($image['name']);
    $ext = pathinfo($uploadfile, PATHINFO_EXTENSION);
    $ts = time();
    $newfilename = $upid . 'T' . $ts . '.' . $ext;
    $uploadfile = $uploaddir . $newfilename;
//check the image size isnot greater than 500KB
    if ($image['size'] > 500000) {
        echo "Sorry, your file is too large.";
        $already_card= true;

        //alert error
        echo "<script>alert('Image size is too large. It should be less than 500KB.');</script>";
        
    } else{

        if (move_uploaded_file($image['tmp_name'], $uploadfile)) {
            $sql = "UPDATE `person` SET `photo` = '$newfilename' WHERE `id` = '$upid'";
            $result = mysqli_query($conn, $sql);
            if($result){
                $update = true;
                //unlink old image upoid
                unlink($uploaddir . $upoid);
    
            }
            else{
                echo "Error: " . $sql . "<br>" . mysqli_error($conn);
            }
        } else {
            echo "Sorry, there was an error uploading your file.";
        }

    }


}

?>
<form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>" method="post" enctype="multipart/form-data">

<input type="hidden" name="upid" id="snoEdit" value="<?php if(isset($_GET['editid'])){echo $id; }?>">
<input type="hidden" name="upoid" id="snoEdit" value="<?php if(isset($_GET['editid'])){echo $photo; }?>">
<div class="row">

 <div class="mx-auto">
  <div class="input-container">
        <label for="imageUpload" id="fileLabel">Change Photo</label>
        <input type="file" id="imageUpload" name="updatedPhoto" accept="image/*">
        <button type="submit" name="updatePhotobtn" id="newButton">Save Photo</button>
        <div id="preview"></div>
    </div>
    <script>
        document.getElementById('imageUpload').addEventListener('change', function(event) {
            const preview = document.getElementById('preview');
            const fileLabel = document.getElementById('fileLabel');
            const newButton = document.getElementById('newButton');
            preview.innerHTML = ''; // Clear previous content

            const file = event.target.files[0];
            if (file) {
                fileLabel.textContent = file.name;
                newButton.style.display = 'inline-block'; // Show the button
                if (file.type.startsWith('image/')) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        const img = document.createElement('img');
                        img.src = e.target.result;
                        img.style.maxWidth = '100%';
                        img.style.height = 'auto';
                        preview.appendChild(img);
                    };
                    reader.readAsDataURL(file);
                } else {
                    preview.textContent = 'Please select a valid image file.';
                }
            } else {
                fileLabel.textContent = 'Change';
                newButton.style.display = 'none'; // Hide the button
            }
        });

        document.getElementById('newButton').addEventListener('click', function() {
           
        });
    </script>
                              </div>
                      
                         </div>
                         
                        
                        </div>

                        </form>
                     
                
                </div>
            </div>
        </div>


        <div class="collapse" id="collapseExample">
            <div class="card card-body mx-auto" style="width: 80%;">

                <form method="POST" enctype="multipart/form-data">
                    <div class="row">
                        <div class="form-group col-md-4">
                            <label >Name</label>
                            <input maxlength="30" type="text" name="name" class="form-control" id="inputCity" required>
                        </div>
                        <div class="form-group col-md-2">
                            <label >Post/Position</label>
                            <input maxlength="30" class="form-control" id="phone" name="post" required>
                        </div>
                        <div class="form-group col-md-2">
                            <label >Department</label>
                            <input maxlength="30" class="form-control" id="phone" name="dept" required>
                        </div>
                        <div class="form-group col-md-2">
                            <label >Blood</label>
                            <select name="bloodgroup" class="form-control" required>
                                <option value="">Select</option>
                                <option value="A+">A+</option>
                                <option value="A-">A-</option>
                                <option value="B+">B+</option>
                                <option value="B-">B-</option>
                                <option value="AB+">AB+</option>
                                <option value="AB-">AB-</option>
                                <option value="O+">O+</option>
                                <option value="O-">O-</option>
                            </select>
                        </div>
                  
                  
                        <div class="form-group">
                            
                            <input  maxlength="10" type="text" name="company" value="ovijat" hidden class="form-control" >
                        </div>
                        <div class="form-group col-md-2">
                            <label >Phone</label>
                            <input maxlength="11" class="form-control" id="phone" name="phone" required>
                        </div>
                        <div class="form-group col-md-4">
                            <label >Email ID</label>
                            <input maxlength="30" type="text" name="email" class="form-control" required>
                        </div>
                    
                        <div class="form-group col-md-2">
                            <label >Issue Date</label>
                            <input type="date" name="issuedate" class="form-control"  required>
                        </div>
                 

                   
                        <div class="form-group col-md-2">
                            <label >ID Number</label>
                            <input  type="number" class="form-control" id="id_no" name="id_no" required>
                        </div>
                        
                       
                        <div class="form-group col-md-4" style="display: flex; align-items: center;">
    <img id="imagePreview" style="display:none; width:100px; height:auto; margin-right:10px;" />
    <div>
        <label>Photo</label>
        <input class="form-control" type="file" name="image" id="imageInput" required/>
    </div>
</div>

<script>
    document.getElementById('imageInput').addEventListener('change', function(event) {
        const [file] = event.target.files;
        if (file) {
            const preview = document.getElementById('imagePreview');
            preview.src = URL.createObjectURL(file);
            preview.style.display = 'block';
        }
    });
</script>


                    </div>
                    <button type="submit" name="savebtn" class="btn btn-primary float-right"><i class="fa fa-plus"></i> Save</button>
                </form>
            </div>
        </div>


        <div class="container">


            <table class="table" id="myTable">
                <thead>
                    <tr>
                        <th scope="col">SN</th>
                        <th scope="col">Name</th>
                        <th scope="col">ID</th>
                        <th scope="col"></th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
          $sql = "SELECT * FROM `person` order by 1 DESC";
          $result = mysqli_query($conn, $sql);
          $sno = 0;
          $color='';
          while($row = mysqli_fetch_assoc($result)){
            $sno = $sno + 1;

            if($row['status']==1){
                $color='red';
            }
            else{
                $color='black';
            }
            echo "<tr style='color:".$color.";'>
            <th scope='row'>". $sno . "</th>
            <td>". $row['name'] . "</td>
            <td>". $row['pid'] . "</td>
            <td>
                       <a href='id.php?pid=".$row['pid']."' class=' btn btn-sm btn-primary'><i class='fas fa-print'></i></a>
           <a href='".basename($_SERVER['PHP_SELF'])."?editid=".$row['id']."' class=' btn btn-sm btn-primary' id=d".$row['id']."><i class='fas fa-edit'></i></a>  
           
            
   
             
             </td>
          </tr>";
        } 


    
          ?>


                </tbody>
            </table>
        </div>











        


   


    <script>


   
    </script>



<?php include_once "foot.php"; ?>