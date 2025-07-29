<?php

$sitename="Sales App";


  if (strpos($_SERVER['HTTP_HOST'], 'localhost') !== false || strpos($_SERVER['HTTP_HOST'], 'ngrok') !== false) {
    $conn = mysqli_connect("localhost", "root", "", "rice");
  } else {
    $conn = mysqli_connect("localhost", "u312077073_salesITOvijat", "B2o;^QkNf", "u312077073_sales");
  }

  if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
  }
  session_start();

  date_default_timezone_set('Asia/Dhaka');
 
?>