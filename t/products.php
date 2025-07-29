<?php include_once 'header.php'; ?>




<style>
    body {
        font-family: 'Segoe UI', 'Roboto', 'Helvetica Neue', sans-serif;
        background-color: #f8f9fc;
        padding: 2rem;
    }

    .form-wrapper {
        max-width: 640px;
        margin: auto;
        background: #fff;
        border-radius: 1rem;
        padding: 2.5rem;
        box-shadow: 0 10px 30px rgba(0,0,0,0.08);
        border: 1px solid #e0e0e0;
    }

    .form-wrapper h3 {
        text-align: center;
        font-size: 1.75rem;
        font-weight: 700;
        margin-bottom: 2rem;
        color: #2c3e50;
    }

    .form-group {
        margin-bottom: 1.5rem;
        position: relative;
    }

    .form-label {
        display: block;
        font-weight: 600;
        margin-bottom: 0.5rem;
        color: #34495e;
    }

    .form-control {
        width: 100%;
        padding: 0.75rem 1rem;
        border: 1px solid #ced4da;
        border-radius: 0.5rem;
        font-size: 1rem;
        transition: all 0.25s ease;
        background-color: #fefefe;
    }

    .form-group:focus-within .form-control {
        border-color: #1a73e8;
        box-shadow: 0 0 0 0.25rem rgba(26, 115, 232, 0.2);
    }

    .btn-primary {
        display: block;
        width: 100%;
        background-color: #1a73e8;
        color: white;
        border: none;
        padding: 0.75rem 1.25rem;
        font-size: 1rem;
        font-weight: 600;
        border-radius: 0.5rem;
        transition: background-color 0.3s ease;
        cursor: pointer;
    }

    .btn-primary:hover {
        background-color: #1666cc;
    }
</style>
    <style>
    .table-container {
        max-width: 1000px;
        margin: 3rem auto;
        padding: 1.5rem;
        background-color: #fff;
        border-radius: 1rem;
        box-shadow: 0 8px 30px rgba(0, 0, 0, 0.06);
        overflow-x: auto;
        font-family: 'Segoe UI', 'Roboto', sans-serif;
    }

    table.table {
        width: 100%;
        border-collapse: collapse;
        font-size: 0.95rem;
        color: #2d2d2d;
    }

    thead th {
        padding: 1rem;
        text-align: left;
        background-color: #f5f8fc;
        font-weight: 600;
        color: #34495e;
        border-bottom: 2px solid #e3e3e3;
    }

    tbody td {
        padding: 1rem;
        border-bottom: 1px solid #ebebeb;
    }

    tbody tr:hover {
        background-color: #f0f7ff;
    }

    .btn-sm {
        display: inline-block;
        background-color: #f39c12;
        color: white;
        padding: 0.4rem 0.8rem;
        font-size: 0.85rem;
        border-radius: 0.375rem;
        text-decoration: none;
        font-weight: 500;
        transition: background-color 0.2s ease;
    }

    .btn-sm:hover {
        background-color: #d68910;
    }

    @media screen and (max-width: 768px) {
        thead th,
        tbody td {
            padding: 0.75rem;
            font-size: 0.9rem;
        }

        .btn-sm {
            padding: 0.35rem 0.6rem;
        }
    }
</style>

<div class="form-wrapper">
    <h3>🛒 Add Product</h3>
    <form action="products.php" method="POST">
        <input type="hidden" id="id" name="id">

        <div class="form-group">
            <label for="name" class="form-label">Name</label>
            <input type="text" class="form-control" id="name" name="name" required>
        </div>

        <div class="form-group">
            <label for="tp_rate" class="form-label">TP Rate</label>
            <input type="number" step="0.01" class="form-control" id="tp_rate" name="tp_rate" required>
        </div>

        <div class="form-group">
            <label for="dp_rate" class="form-label">DP Rate</label>
            <input type="number" step="0.01" class="form-control" id="dp_rate" name="dp_rate" required>
        </div>

        <button type="submit" name="add_product" class="btn-primary">Add Product</button>
    </form>
</div>

     
        

<div class="table-container">
    <table class="table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>TP Rate</th>
                <th>DP Rate</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $stmt = $conn->prepare("SELECT id, name, tp_rate, dp_rate FROM products");
            $stmt->execute();
            $result = $stmt->get_result();
            while ($row = $result->fetch_assoc()) {
                echo "<tr>";
                echo "<td>{$row['id']}</td>";
                echo "<td>{$row['name']}</td>";
                echo "<td>{$row['tp_rate']}</td>";
                echo "<td>{$row['dp_rate']}</td>";
                echo "<td><a href='?edit={$row['id']}' class='btn-sm'>Edit</a></td>";
                echo "</tr>";
            }
            $stmt->close();
            ?>
        </tbody>
    </table>
</div>
        

<?php
if (isset($_POST['add_product'])) {
    if (empty($_POST['name']) || empty($_POST['tp_rate']) || empty($_POST['dp_rate'])) {
        echo "<script>alert('All fields are required');</script>";
        exit;
    }
    if (!is_numeric($_POST['tp_rate']) || !is_numeric($_POST['dp_rate'])) {
        echo "<script>alert('TP Rate and DP Rate must be numeric');</script>";
        exit;
    }
    if ($_POST['tp_rate'] < 0 || $_POST['dp_rate'] < 0) {
        echo "<script>alert('TP Rate and DP Rate must be non-negative');</script>";
        exit;
    }
    $stmt = $conn->prepare("SELECT id FROM products WHERE id = ?");
    $stmt->bind_param("i", $_POST['id']);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $stmt = $conn->prepare("UPDATE products SET name = ?, tp_rate = ?, dp_rate = ? WHERE id = ?");
        $stmt->bind_param("sddi", $_POST['name'], $_POST['tp_rate'], $_POST['dp_rate'], $_POST['id']);
        $stmt->execute();
        if ($stmt->affected_rows > 0) {
            echo "<script>alert('Product updated');</script>";
            echo "<script>window.location.href='products.php';</script>";
        } else {
            echo "<script>alert('Error updating product');</script>";
        }
    } else {
        $stmt = $conn->prepare("INSERT INTO products (name, tp_rate, dp_rate) VALUES (?, ?, ?)");
        $stmt->bind_param("sdd", $_POST['name'], $_POST['tp_rate'], $_POST['dp_rate']);
        $stmt->execute();
        if ($stmt->affected_rows > 0) {
            echo "<script>alert('Product added');</script>";
            echo "<script>window.location.href='products.php';</script>";
        } else {
            echo "<script>alert('Error adding product');</script>";
        }
    }
    $stmt->close();



}

if (isset($_GET['edit'])) {

    $stmt = $conn->prepare("SELECT id, name, tp_rate, dp_rate FROM products WHERE id = ?");
    $stmt->bind_param("i", $_GET['edit']);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        echo "<script>document.getElementById('id').value='{$row['id']}'</script>";
        echo "<script>document.getElementById('name').value='{$row['name']}'</script>";
        echo "<script>document.getElementById('tp_rate').value='{$row['tp_rate']}'</script>";
        echo "<script>document.getElementById('dp_rate').value='{$row['dp_rate']}'</script>";
    }
    $stmt->close();
}


?>


<?php include_once 'footer.php'; ?>

      