<?php
require_once 'conn.php';
session_destroy();
header('location:admin');
?>