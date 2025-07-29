<?php
require_once '../conn.php';
if (isset($_POST['message'])) {
    $message = $_POST['message'];
    $sql = "INSERT INTO chat (username, message, timestamp) VALUES ('".$_SESSION['username']."', '$message', NOW())";
    if ($conn->query($sql) === TRUE) {
       $sql = "SELECT username, message, timestamp FROM chat ORDER BY id DESC LIMIT 20";
    $result = $conn->query($sql);
    $data = array();
    if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            $data[] = array('username' => $row['username'], 'message' => $row['message'], 'timestamp' => $row['timestamp']);
        }
    }
    foreach ($data as $row) {
        if ($_SESSION['username'] != $row['username']) {
            echo "<div style='background-color:rgb(238, 243, 241); box-shadow: 0 2px 6px rgba(0, 0, 0, 0.15); padding: 15px; margin-bottom: 15px; border-radius: 8px;'>
                    <p style='margin: 0; font-size: 14px; color: #555; float: left;'><i class='fa fa-user' style='color:rgb(190, 15, 15);'></i> <strong style='color: #333;'>".$row['username']."</strong></p>
                    <p style='margin: 0; font-size: 14px; color: #555; float: right;'>".date('d/m/Y H:i:s', strtotime($row['timestamp']))."</p>
                    <div style='clear: both;'></div>
                    <p style='margin: 5px 0 0; font-size: 16px; color: #444;'>".nl2br($row['message'])."</p>
                  </div>";
        } else {
            echo "<div style='background-color:rgb(226, 248, 174); box-shadow: 0 2px 6px rgba(0, 0, 0, 0.15); padding: 15px; margin-bottom: 15px; border-radius: 8px;'>
                    <p style='margin: 0; font-size: 14px; color: #555; float: right;'><i class='fa fa-user' style='color:rgb(73, 183, 51);'></i> <strong style='color: #333;'>You</strong></p>
                    <p style='margin: 0; font-size: 14px; color: #555; float: left;'>".date('d/m/Y H:i:s', strtotime($row['timestamp']))."</p>
                    <div style='clear: both;'></div>
                    <p style='margin: 5px 0 0; font-size: 16px; color: #444;'>".nl2br($row['message'])."</p>
                  </div>";
        }
    
    }
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }
} elseif (isset($_POST['get'])) {
    $sql = "SELECT username, message, timestamp FROM chat ORDER BY id DESC LIMIT 20";
    $result = $conn->query($sql);
    $data = array();
    if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            $data[] = array('username' => $row['username'], 'message' => $row['message'], 'timestamp' => $row['timestamp']);
        }
    }
    foreach ($data as $row) {
        if ($_SESSION['username'] != $row['username']) {
            echo "<div style='background-color:rgb(238, 243, 241); box-shadow: 0 2px 6px rgba(0, 0, 0, 0.15); padding: 15px; margin-bottom: 15px; border-radius: 8px;'>
                    <p style='margin: 0; font-size: 14px; color: #555; float: left;'><i class='fa fa-user' style='color:rgb(190, 15, 15);'></i> <strong style='color: #333;'>".$row['username']."</strong></p>
                    <p style='margin: 0; font-size: 14px; color: #555; float: right;'>".date('d/m/Y H:i:s', strtotime($row['timestamp']))."</p>
                    <div style='clear: both;'></div>
                    <p style='margin: 5px 0 0; font-size: 16px; color: #444;'>".nl2br($row['message'])."</p>
                  </div>";
        } else {
            echo "<div style='background-color:rgb(226, 248, 174); box-shadow: 0 2px 6px rgba(0, 0, 0, 0.15); padding: 15px; margin-bottom: 15px; border-radius: 8px;'>
                    <p style='margin: 0; font-size: 14px; color: #555; float: right;'><i class='fa fa-user' style='color:rgb(73, 183, 51);'></i> <strong style='color: #333;'>You</strong></p>
                    <p style='margin: 0; font-size: 14px; color: #555; float: left;'>".date('d/m/Y H:i:s', strtotime($row['timestamp']))."</p>
                    <div style='clear: both;'></div>
                    <p style='margin: 5px 0 0; font-size: 16px; color: #444;'>".nl2br($row['message'])."</p>
                  </div>";
        }
    }
}
?>