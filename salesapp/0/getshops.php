<?php
require_once '../connection.php'; // Ensure this defines $conn

if (isset($_GET['route_id'])) {
    $route_id = $_GET['route_id'];
    $stmt = $conn->prepare("SELECT id, name FROM shop WHERE route_id = ? order by id desc");
    $stmt->bind_param("i", $route_id);
    $stmt->execute();
    $stmt->bind_result($id, $name);
    $shops = [];
    while ($stmt->fetch()) {
        $shops[] = ["id" => $id, "name" => $name];
    }
    echo json_encode($shops);
}
