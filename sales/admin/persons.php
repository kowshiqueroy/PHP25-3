<?php
require_once '../conn.php';
require_once 'header.php';
?>
<?php
if (isset($_POST['adjust'])) {
    $person_from = $_POST['person_from'];
    $person_to = $_POST['person_to'];

    if ($person_from == $person_to) {
        echo '<div style="text-align: center;">Same person selected, no adjustment made</div>';
        echo '<script>window.location.href="persons.php"</script>';
        exit;
    }

    // Update persons table
    $sql = "UPDATE orders SET person_id = '$person_to' WHERE person_id = '$person_from'";
    if ($conn->query($sql) === TRUE) {
        echo '<div style="text-align: center;">persons adjusted successfully</div>';

        // Delete the person from persons table
        $sql = "DELETE FROM persons WHERE id = '$person_from'";
        if ($conn->query($sql) === TRUE) {
            echo '<div style="text-align: center;">person deleted successfully</div>';
        } else {
            echo "Error deleting person: " . $conn->error;
        }
        echo '<script>window.location.href="persons.php"</script>';
        exit;
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }
}
if (isset($_POST['create'])) {
    $person_name = $_POST['person_name'];

    // Insert new person into the database
    $sql = "INSERT INTO persons (person_name) VALUES ('$person_name')";
    if ($conn->query($sql) === TRUE) {
        echo '<div style="text-align: center;">New person created successfully</div>';
        echo '<script>window.location.href="persons.php"</script>';
        exit;
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }
}
?>

<div class="card p-1 text-center">persons</div><br><br>
<div class="row">
    <div class="col-md-6 offset-md-3">
        <form action="persons.php" method="post">
            <div class="form-group d-flex">
                <label for="person_from" class="me-2">From</label>
                <select class="form-select" id="person_from" name="person_from">
                    <?php
                    $sql = "SELECT * FROM persons ORDER BY person_name ASC";
                    $result = $conn->query($sql);
                    if ($result->num_rows > 0) {
                        while($row = $result->fetch_assoc()) {
                            echo '<option value="' . $row['id'] . '">' . $row['person_name'] . '</option>';
                        }
                    }
                    ?>
                </select>
                <label for="person_to" class="me-2">To</label>
                <select class="form-select" id="person_to" name="person_to">
                    <?php
                    $sql = "SELECT * FROM persons ORDER BY person_name ASC";
                    $result = $conn->query($sql);
                    if ($result->num_rows > 0) {
                        while($row = $result->fetch_assoc()) {
                            echo '<option value="' . $row['id'] . '">' . $row['person_name'] . '</option>';
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
        <form action="persons.php" method="post">
            <div class="form-group d-flex">
                <label for="person_name" class="me-2">New person Name</label>
                <input type="text" name="person_name" class="form-control" id="person_name" style="flex: 1 0 50%;">
                <button type="submit" name="create" class="btn btn-primary ms-2" style="flex: 1 0 50%;">Create</button>
            </div>
        </form>
    </div>
</div><br>
<div class="row">
    <?php
    if (isset($_POST['edit'])) {
        $id = $_POST['id'];
        $person_name = $_POST['person_name'];
        
        $sql = "UPDATE persons SET person_name='$person_name' WHERE id='$id'";
        if ($conn->query($sql) === TRUE) {
            echo '<div style="text-align: center;">person updated successfully</div>';
            echo '<script>window.location.href="persons.php"</script>';
            exit;
        } else {
            echo "Error: " . $sql . "<br>" . $conn->error;
        }
    }
    

    // Fetch all persons from the database
    $sql = "SELECT * FROM persons ORDER BY person_name ASC";
    $result = $conn->query($sql);
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            echo "<form method='post'><table class='table table-bordered'>";
            echo "<tr>";
            echo "<td><input type='text' name='id' value='" . $row['id'] . "' readonly></td>";
            echo "<td><input type='text' class='form-control' id='person_name' name='person_name' value='" . $row['person_name'] . "'></td>";
           
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