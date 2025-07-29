<?php

require_once '../conn.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
  
  foreach ($_SESSION['querylist'] as $key => $value) {
    $_SESSION['querylist'][$key] = '';
  }
  
}

?>