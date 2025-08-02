<?php
require_once '../conn.php';
require_once 'header.php';
?>
<?php
if (isset($_POST['adjust'])) {
    $route_from = $_POST['route_from'];
    $route_to = $_POST['route_to'];

    if ($route_from == $route_to) {
        echo '<div style="text-align: center;">Same route selected, no adjustment made</div>';
        echo '<script>window.location.href="routes.php"</script>';
        exit;
    }

    // Update routes table
    $sql = "UPDATE orders SET route_id = '$route_to' WHERE route_id = '$route_from'";
    if ($conn->query($sql) === TRUE) {
        echo '<div style="text-align: center;">routes adjusted successfully</div>';

        // Delete the route from routes table
        $sql = "DELETE FROM routes WHERE id = '$route_from'";
        if ($conn->query($sql) === TRUE) {
            echo '<div style="text-align: center;">route deleted successfully</div>';
        } else {
            echo "Error deleting route: " . $conn->error;
        }
        echo '<script>window.location.href="routes.php"</script>';
        exit;
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }
}
if (isset($_POST['create'])) {
    $route_name = $_POST['route_name'];

    // Insert new route into the database
    $sql = "INSERT INTO routes (route_name) VALUES ('$route_name')";
    if ($conn->query($sql) === TRUE) {
        echo '<div style="text-align: center;">New route created successfully</div>';
        echo '<script>window.location.href="routes.php"</script>';
        exit;
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }
}
?>

<div class="card p-1 text-center">Routes</div><br><br>
<div class="row">
    <div class="col-md-6 offset-md-3">
        <form action="routes.php" method="post">
            <div class="form-group d-flex">
                <label for="route_from" class="me-2">From</label>
                <select class="form-select" id="route_from" name="route_from">
                    <?php
                    $sql = "SELECT * FROM routes ORDER BY route_name ASC";
                    $result = $conn->query($sql);
                    if ($result->num_rows > 0) {
                        while($row = $result->fetch_assoc()) {
                            echo '<option value="' . $row['id'] . '">' . $row['route_name'] . '</option>';
                        }
                    }
                    ?>
                </select>
                <label for="route_to" class="me-2">To</label>
                <select class="form-select" id="route_to" name="route_to">
                    <?php
                    $sql = "SELECT * FROM routes ORDER BY route_name ASC";
                    $result = $conn->query($sql);
                    if ($result->num_rows > 0) {
                        while($row = $result->fetch_assoc()) {
                            echo '<option value="' . $row['id'] . '">' . $row['route_name'] . '</option>';
                        }
                    }
                    ?>
                </select>
                <button type="submit" name="adjust" class="btn btn-secondary ms-2">Adjust</button>
            </div>
        </form>
    </div>
</div><br>
<div class="row">
    <div class="col-md-6 offset-md-3">
        <form action="routes.php" method="post">
            <div class="form-group d-flex">
                <label for="route_name" class="me-2">New Route Name</label>
                <input type="text" name="route_name" class="form-control" id="route_name" style="flex: 1 0 50%;">
                <button type="submit" name="create" class="btn btn-primary ms-2" style="flex: 1 0 50%;">Create</button>
            </div>
        </form>
    </div>
</div><br>
<div class="row">
    <?php
    if (isset($_POST['edit'])) {
        $id = $_POST['id'];
        $route_name = $_POST['route_name'];
        
        $sql = "UPDATE routes SET route_name='$route_name' WHERE id='$id'";
        if ($conn->query($sql) === TRUE) {
            echo '<div style="text-align: center;">Route updated successfully</div>';
            echo '<script>window.location.href="routes.php"</script>';
            exit;
        } else {
            echo "Error: " . $sql . "<br>" . $conn->error;
        }
    }
    

    // Fetch all routes from the database
    $sql = "SELECT * FROM routes ORDER BY route_name ASC";
    $result = $conn->query($sql);
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            echo "<form method='post'><table class='table table-bordered'>";
            echo "<tr>";
            echo "<td><input type='text' name='id' value='" . $row['id'] . "' readonly></td>";
            echo "<td><input type='text' class='form-control' id='route_name' name='route_name' value='" . $row['route_name'] . "'></td>";
           
            echo "<td>";
            
            echo "<button type='submit' name='edit' value='" . $row['id'] . "' class='btn btn-primary'>Edit</button>";
            echo "</td>";
            echo "</tr>";
            echo "</table> </form>";
        }
    }
    ?>
</div>

<?php
require_once 'footer.php';
?>