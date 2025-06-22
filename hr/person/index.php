

<?php
if( !isset($_REQUEST['id'])){

    echo "<script> window.history.back(); </script>";
    die();


}
?>






    <style>




        .popup {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            width: 100%;
            height: 100%;
            padding: 20px;
            background-color: #fff;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            border-radius: 10px;
            text-align: center;
        }
        .popup h2 {
            margin: 0 0 10px;
            color: #ff6347; /* Tomato color */
        }
        .popup p {
            margin: 0 0 20px;
            color: #333;
        }
        .popup button {
            padding: 10px 20px;
            background-color: #ff6347; /* Tomato color */
            color: #fff;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        .popup button:hover {
            background-color: #e5533d; /* Darker tomato color */
        }
    </style>




<?php 
      
        include '../dbconnect.php';
        $id = '';
        $name = '';
        $pid = '';
        $email = '';
        $phone = '';
        $company = '';
        $bloodgroup = '';
        $issuedate = '';
        $photo = '';
        $post = '';
        $dept = '';



        if(isset($_REQUEST['id'])){

             $id = $_REQUEST['id'];

            //sql query for select data from person table

             $sql = "Select * from person where pid='$id'";
             $result = mysqli_query($conn, $sql);
             $row = mysqli_fetch_assoc($result);
             if($row){
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

                else{
                    
               echo ' <div class="popup">
        <h2>Not found</h2>
        <p>The person you are looking for does not exist.</p>
        <button onclick="window.location.href=\'../\'">Go to Home</button>
    </div>';
    die();
                }






            }


              

                            ?>


<div style="display: flex; flex-direction: column; align-items: center; margin:20px; position:relative; z-index:10;">
        <a href="../" style="background-color:#4CAF50; color:white; border-radius:5px; padding:10px 20px; text-decoration:none;
         font-size:16px;">Back to Home Page</a>
         <h1>EOvijat Online ID Card</h1>
    </div>
  
  
        <!--make a centered box-->
        <style>
            .centered {
                margin: auto;
                width: 50%;
                border: 1px solid #ddd;
                padding: 20px;
                box-shadow: 0 4px 8px 0 rgba(0,0,0,0.2), 0 6px 20px 0 rgba(0,0,0,0.19);
            }
        </style>




        

   

      <?php if($status==1){?>
        
        <style>
            .clickable{
                cursor: pointer;
            }
        </style>
        <div class="clickable" style="position:fixed; top:0; left:0; width:100vw; height:100vh; background-color:rgba(0,0,0,0.5); display:flex; justify-content:center; align-items:center; color:white; font-size:30px;">
            <p  onclick="window.location.href='../'">Expired ID</p>
        </div> 
        
        <?php } ?>

        <div class="centered">
     
            
        <div style="display: flex; flex-direction: column; align-items: center;">
            <img id="logo" src="../assets/img/logo.png" style="width: 200px; height: auto; margin:20px;">
            <img id="pp" src="../assets/person/<?php echo $photo; ?>" style="width: 200px; height: auto; border-radius: 50%;">
            <h2 style="text-align: center;"><?php echo $name; ?></h2>
            <p style="text-align: center;">ID: <?php echo $id; ?></p>
            <p style="text-align: center;" class="post"><?php echo $post; ?>(<?php echo $dept; ?>)</p>
          
            <p style="text-align: center;"><?php echo $email; ?></p>
            <p style="text-align: center;"><?php echo $phone; ?></p>
           
            <p style="text-align: center;">Blood Group: <?php echo $bloodgroup; ?></p>
           
            <p style="text-align: center;">Issue Date: <?php echo $issuedate; ?></p>
        </div>
        </div>

      
         
      
      




  
