<?php
// config db
    
$db_host = 'localhost';
$db_name = 'pkrsc';
$db_user = 'root';
$db_pass = '';

// database connection using mysqli
$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);

// check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
 session_start();
?>