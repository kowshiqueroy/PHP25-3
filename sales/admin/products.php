<?php
require_once '../conn.php';
require_once 'header.php';
?>
<?php

if (isset($_POST['create'])) {
    $product_name = $_POST['product_name'];

    // Insert new product into the database
    $sql = "INSERT INTO products (product_name) VALUES ('$product_name')";
    if ($conn->query($sql) === TRUE) {
        echo '<div style="text-align: center;">New product created successfully</div>';
        echo '<script>window.location.href="products.php"</script>';
        exit;
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }
}
if (isset($_POST['adjust'])) {
    $product_from = $_POST['product_from'];
    $product_to = $_POST['product_to'];

    if ($product_from == $product_to) {
        echo '<div style="text-align: center;">Same product selected, no adjustment made</div>';
        echo '<script>window.location.href="products.php"</script>';
        exit;
    }

    // Update order_product table
    $sql = "UPDATE order_product SET product_id = '$product_to' WHERE product_id = '$product_from'";
    if ($conn->query($sql) === TRUE) {
        echo '<div style="text-align: center;">Products adjusted successfully</div>';

        // Delete the product from products table
        $sql = "DELETE FROM products WHERE id = '$product_from'";
        if ($conn->query($sql) === TRUE) {
            echo '<div style="text-align: center;">Product deleted successfully</div>';
        } else {
            echo "Error deleting product: " . $conn->error;
        }
        echo '<script>window.location.href="products.php"</script>';
        exit;
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }
}

?>

<div class="card p-1 text-center">products</div><br><br>

<div class="row">
    <div class="col-md-6 offset-md-3">
        <form action="products.php" method="post">
            <div class="form-group d-flex">
                <label for="product_from" class="me-2">From</label>
                <select class="form-select" id="product_from" name="product_from">
                    <?php
                    $sql = "SELECT * FROM products order by product_name asc";
                    $result = $conn->query($sql);
                    if ($result->num_rows > 0) {
                        while($row = $result->fetch_assoc()) {
                            echo '<option value="' . $row['id'] . '">' . $row['product_name'] . '</option>';
                        }
                    }
                    ?>
                </select>
                <label for="product_to" class="me-2">To</label>
                <select class="form-select" id="product_to" name="product_to">
                    <?php
                    $sql = "SELECT * FROM products order by product_name asc";
                    $result = $conn->query($sql);
                    if ($result->num_rows > 0) {
                        while($row = $result->fetch_assoc()) {
                            echo '<option value="' . $row['id'] . '">' . $row['product_name'] . '</option>';
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
        <form action="products.php" method="post">
            <div class="form-group d-flex">
                <label for="product_name" class="me-2">New product Name</label>
                <input type="text" name="product_name" class="form-control" id="product_name" style="flex: 1 0 50%;">
                <button type="submit" name="create" class="btn btn-primary ms-2" style="flex: 1 0 50%;">Create</button>
            </div>
        </form>
    </div>
</div><br>
<div class="row">
    <?php
    if (isset($_POST['edit'])) {
        $id = $_POST['id'];
        $product_name = $_POST['product_name'];
        
        $sql = "UPDATE products SET product_name='$product_name' WHERE id='$id'";
        if ($conn->query($sql) === TRUE) {
            echo '<div style="text-align: center;">product updated successfully</div>';
            echo '<script>window.location.href="products.php"</script>';
            exit;
        } else {
            echo "Error: " . $sql . "<br>" . $conn->error;
        }
    }
    

    // Fetch all products from the database
    $sql = "SELECT * FROM products order by product_name asc";
    $result = $conn->query($sql);
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            echo "<form method='post'><table class='table table-bordered'>";
            echo "<tr>";
            echo "<td><input type='text' name='id' value='" . $row['id'] . "' readonly></td>";
            echo "<td><input type='text' class='form-control' id='product_name' name='product_name' value='" . $row['product_name'] . "'></td>";
           
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