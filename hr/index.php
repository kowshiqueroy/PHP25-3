<?php
include_once "dbconnect.php";

if(isset($_POST['loginbtn'])){
$e= $_POST['email'];
$p= $_POST['pass'];
if ($e === $p) {
    $_SESSION['cp'] = true;
}
$p=md5($p);
$_SESSION['email']="";
$_SESSION['role']="";
$_SESSION['company']="";

//check

$sql = "SELECT role, company FROM user WHERE email='$e' AND password='$p' AND status='0'";
$result = mysqli_query($conn, $sql);

if (mysqli_num_rows($result) > 0) {
  // output data of each row
  while($row = mysqli_fetch_assoc($result)) {

    $_SESSION['email']=$e;
    $_SESSION['role']=$row['role'];
    $_SESSION['company']=$row['company'];

    //redirect
echo "  <script>location.replace('".$row['role']."')</script>";
die();


  }
} else {
  echo "
  
  <script>alert('Login failed!');</script>
  
  
  
  ";
}


}


?>



<!DOCTYPE html>

<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="description" content=" Today in this blog you will learn how to create a responsive Login & Registration Form in HTML CSS & JavaScript. The blog will cover everything from the basics of creating a Login & Registration in HTML, to styling it with CSS and adding with JavaScript." />
    <meta
      name="keywords"
      content=" 
 Animated Login & Registration Form,Form Design,HTML and CSS,HTML CSS JavaScript,login & registration form,login & signup form,Login Form Design,registration form,Signup Form,HTML,CSS,JavaScript,
"
    />

    <meta name="viewport" content="width=device-width, initial-scale=1.0" />

    <title>Ovijat App</title>
    <link rel="stylesheet" href="style.css" />
  </head>
  <body>
    <section class="wrapper">
      <div class="form signup">
        <header>Search Person</header>
        <form action="person">

       
          <input type="number" name="id" placeholder="ID: XXXX" required />



          <input type="submit" value="Search" />
        </form>
        <br>
<header>EOvijat.com</header>

      </div>

      <div class="form login">
        <header>Login</header>
        <form method="POST">
          <input type="text" placeholder="Username" name="email" required />
          <input type="password" placeholder="Password" name="pass" required />
   
          <input type="submit" name="loginbtn" value="Enter" />
        </form>
      </div>

      <script>
        const wrapper = document.querySelector(".wrapper"),
          signupHeader = document.querySelector(".signup header"),
          loginHeader = document.querySelector(".login header");

        loginHeader.addEventListener("click", () => {
          wrapper.classList.add("active");
        });
        signupHeader.addEventListener("click", () => {
          wrapper.classList.remove("active");
        });
        // open the login default
        wrapper.classList.add("active");
      </script>
    </section>
  </body>
</html>
