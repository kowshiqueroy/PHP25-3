<?php

require_once '../conn.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $_SESSION['querylist'] = $_POST;
    foreach ($_SESSION['querylist'] as $key => $value) {
        // if (!empty($value)) {
            echo "$key: $value ";
        //}
    }
}

?>