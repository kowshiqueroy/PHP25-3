<?php
require_once '../conn.php';
require_once 'header.php';
?>
<div class="card p-1 text-center">Map</div>

<form action="" method="get" style="display: flex; flex-wrap: wrap; justify-content: center; gap: 10px;">
    <div style="flex: 1 1 200px;">
        <div class="input-group mb-3">
            <div class="input-group-prepend">
                <span class="input-group-text" id="basic-addon1">Username</span>
            </div>
            <select class="form-control" name="username" required>
                <option value="">Select User</option>
                <?php
                    $sql = "SELECT id, username FROM users WHERE role IN (2, 3)";
                    $result = $conn->query($sql);
                    if ($result->num_rows > 0) {
                        while($row = $result->fetch_assoc()) {
                            echo "<option value='" . $row['id'] . "' " . (isset($_GET['username']) && $_GET['username'] == $row['id'] ? "selected" : "") . ">" . $row['username'] . "</option>";
                        }
                    }
                ?>
            </select>
        </div>
    </div>
    <div style="flex: 1 1 200px;">
        <div class="input-group mb-3">
            <div class="input-group-prepend">
                <span class="input-group-text" id="basic-addon2">Date</span>
            </div>
            <input type="date" class="form-control" name="date" placeholder="Date" aria-label="Date" aria-describedby="basic-addon2" required value="<?php echo isset($_GET['date']) ? $_GET['date'] : date("Y-m-d"); ?>">
        </div>
    </div>
    <button type="submit" class="btn btn-primary" style="flex: 1 1 0">Location</button>
</form>


<?php
if (isset($_GET['username']) && isset($_GET['date'])) {
    $username = $_GET['username'];
    $date = $_GET['date'];
    $sql = "SELECT latitude, longitude, created_at FROM orders";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {

        
        $locations = [];
        while ($row = $result->fetch_assoc()) {
            $locations[] = $row;
        //     $lastLatitude = $row['latitude'];
        //     $lastLongitude = $row['longitude'];
        //     $randomLatitude = $lastLatitude + (rand(-1000, 1000) / 111000);
        //     $randomLongitude = $lastLongitude + (rand(-1000, 1000) / (111320 * cos(deg2rad($lastLatitude))));
        //     $locations[] = ['latitude' => $randomLatitude, 'longitude' => $randomLongitude, 'created_at' => date('Y-m-d H:i:s', strtotime($row['created_at'] . ' +10 minutes'))];
         }
        
        // Assuming you will use the locations array to plot points on a map
        ?>
        
        
        <table id = "myTable" class="table table-bordered d-none">
            <thead>
                <tr>
                    <th>Latitude</th>
                    <th>Longitude</th>
                    <th>Created At</th>
                </tr>
            </thead>
            <tbody>
                <?php
                foreach ($locations as $location) {
                    echo "<tr>";
                    echo "<td>" . $location['latitude'] . "</td>";
                    echo "<td>" . $location['longitude'] . "</td>";
                    echo "<td>" . $location['created_at'] . "</td>";
                    echo "</tr>";
                }
                ?>
            </tbody>
        </table>
        
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/leaflet@1.9.3/dist/leaflet.css" />
<script src="https://cdn.jsdelivr.net/npm/leaflet@1.9.3/dist/leaflet.js"></script>
<script src="https://unpkg.com/leaflet-geodesy@1.3.0/dist/leaflet-geodesy.js"></script>

<div id="map" style="height: 600px; width: 100%;"></div>
<script>
    var map = L.map('map');

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 29,
        attribution: '  OpenStreetMap'
    }).addTo(map);

    <?php if (!empty($locations)): ?>
        <?php
        $last_location = end($locations);
        $first_location = reset($locations);
        ?>
        var currentIndex = 0;
        var markers = [];
        var polyline = L.polyline([], {
            color: 'red',
            weight: 3,
            opacity: 0.5,
            smoothFactor: 1
        }).addTo(map);

        <?php foreach ($locations as $index => $location): ?>
            markers.push(L.marker([<?php echo $location['latitude']; ?>, <?php echo $location['longitude']; ?>]).addTo(map).bindPopup("Serial: <?php echo $index + 1; ?><br>Created At: <?php echo date('F j, Y, g:i a', strtotime($location['created_at'])); ?>"));
            polyline.addLatLng([<?php echo $location['latitude']; ?>, <?php echo $location['longitude']; ?>]);
        <?php endforeach; ?>
        
        function animateWalk() {
            if (currentIndex < markers.length) {
                map.setView(markers[currentIndex].getLatLng(), 15, {
                    animate: true,
                    pan: { duration: 2, easeLinearity: 0.5 }
                });
                markers[currentIndex].openPopup();
                currentIndex++;
                setTimeout(animateWalk, 3000); // Move to the next point every 3 seconds
            }
        }

        animateWalk();
    <?php endif; ?>
</script>
        <?php
            
    
    } else {
        echo "No locations found.";
    }

}
        // if (isset($locations)) {
        //     echo "Locations: ";
        //     echo "<pre>";
        //     print_r($locations);
        //     echo "</pre>";
        // }
?>




<?php
require_once 'footer.php';
?>