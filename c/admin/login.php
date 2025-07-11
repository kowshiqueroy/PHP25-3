<?php
session_start();
if ($_POST['pass'] == "admin123") {
  $_SESSION['admin'] = true;
  header("Location: dashboard.php");
  exit;
}
?>
<form method="post">
<input type="password" name="pass" placeholder="Admin Password">
<input type="submit" value="Login">
</form>