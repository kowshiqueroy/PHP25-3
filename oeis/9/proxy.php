<?php
// Very simple reverse geocoding proxy
// Usage: proxy.php?lat=25.93475810&lon=88.84918150

header("Content-Type: application/json");

if (isset($_GET['lat']) && isset($_GET['lon'])) {
    $lat = $_GET['lat'];
    $lon = $_GET['lon'];

    // Build API URL
    $url = "https://api.bigdatacloud.net/data/reverse-geocode-client?latitude=$lat&longitude=$lon&localityLanguage=en";

    // Fetch and output directly
    $response = file_get_contents($url);
    if ($response === false) {
        echo json_encode(["error" => "Unable to fetch data"]);
    } else {
        echo $response; // already JSON
    }
} else {
    echo json_encode(["error" => "Missing lat/lon parameters"]);
}
?>