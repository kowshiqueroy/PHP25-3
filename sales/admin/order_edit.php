<?php
require_once '../conn.php';
require_once 'header.php';
?>
<?php

if (isset($_POST['product_id'])) {
    $product_id = mysqli_real_escape_string($conn, $_POST['product_id']);
    $quantity = mysqli_real_escape_string($conn, $_POST['quantity']);
    $price = mysqli_real_escape_string($conn, $_POST['price']);
    $order_id = mysqli_real_escape_string($conn, $_POST['id']);
    $order_product_id = mysqli_real_escape_string($conn, $_POST['order_product_id']);

    $update_product_query = "UPDATE order_product SET 
        quantity = '$quantity',
        price = '$price',
        total = '$quantity' * '$price',
        product_id = '$product_id'
        WHERE id = '$order_product_id' AND order_id = '$order_id'";

    if (mysqli_query($conn, $update_product_query)) {
        echo "<script>
        toastr.success('Order product updated successfully!');
        setTimeout(function() {
            window.location.href='order_edit.php?id=" . $order_id . "';
        }, 1000);
        </script>";
    } else {
        echo "<script>
        toastr.error('Error updating order product: " . mysqli_error($conn) . "');
        setTimeout(function() {
            window.location.href='order_edit.php?id=" . $order_id . "';
        }, 1000);
        </script>";
    }
}

if (isset($_POST['update_order'])) {
    $id = mysqli_real_escape_string($conn, $_POST['id']);
    $route_id = mysqli_real_escape_string($conn, $_POST['route_id']);
    $person_id = mysqli_real_escape_string($conn, $_POST['person_id']);

    $delivery_date = mysqli_real_escape_string($conn, $_POST['delivery_date']);
    $order_status = mysqli_real_escape_string($conn, $_POST['order_status']);
    $remarks = mysqli_real_escape_string($conn, $_POST['remarks']);

    $update_query = "UPDATE orders SET 
        route_id = '$route_id',
        person_id = '$person_id',
     
        delivery_date = '$delivery_date',
        order_status = '$order_status',
        remarks = '$remarks'
        WHERE id = '$id'";

    if (mysqli_query($conn, $update_query)) {
        echo "<div class='alert alert-success'>Order updated successfully!</div>";
    } else {
        echo "<div class='alert alert-danger'>Error updating order: " . mysqli_error($conn) . "</div>";
    }
}
?>
<div class="card p-1 text-center">Order Edit <?php if (isset($_REQUEST['id'])) { echo " - " . $_REQUEST['id']; } ?></div>



<div class="container mt-3">
    <form method="GET" action="">
        <div class="row">
            <div class="col-md-6 offset-md-3">
                <div class="input-group mb-3">
                    <input type="number" name="id" class="form-control" placeholder="Enter Order ID" required>
                    <button class="btn btn-primary" type="submit">Search</button>
                </div>
            </div>
        </div>
    </form>

    <?php
    if (isset($_GET['id'])) {
        $order_id = mysqli_real_escape_string($conn, $_GET['id']);
        $query = "SELECT * FROM orders WHERE id = '$order_id'";
        $result = mysqli_query($conn, $query);
        
        if (mysqli_num_rows($result) > 0) {
            $row = mysqli_fetch_assoc($result);
            echo "<div class='alert alert-success'>Order found!</div>";
?>




<div class="card p-1 text-center" id="order-id"> <?php if(isset($_GET['id'])) { echo 'Order ID: ' . htmlspecialchars($_GET['id']); } else { echo 'New Order'; } ?></div>

<?php

if (isset($_POST['update'])) {
    $id = $_POST['id'];
    $route_id = $_POST['route_id'];
    $person_id = $_POST['person_id'];
    $order_date = $_POST['order_date'];
    $delivery_date = $_POST['delivery_date'];
    $remarks = $_POST['remarks'];
    $order_serial = $_POST['order_serial'];
    $order_status = $_POST['order_status'];



    if (!is_numeric($route_id)) {
    $sql = "SELECT id FROM routes WHERE route_name = '$route_id'";
    $result = $conn->query($sql);
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $route_id = $row['id'];
    } else {
        $sql = "INSERT INTO routes (route_name) VALUES ('$route_id')";
        if ($conn->query($sql) === TRUE) {
            $route_id = $conn->insert_id;
        } else {
            echo "Error: " . $sql . "<br>" . $conn->error;
        }
    }
}

if (!is_numeric($person_id)) {
    $sql = "SELECT id FROM persons WHERE person_name = '$person_id'";
    $result = $conn->query($sql);
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $person_id = $row['id'];
    } else {
        $sql = "INSERT INTO persons (person_name) VALUES ('$person_id')";
        if ($conn->query($sql) === TRUE) {
            $person_id = $conn->insert_id;
        } else {
            echo "Error: " . $sql . "<br>" . $conn->error;
        }
    }
}



    $sql = "UPDATE orders SET  route_id='$route_id', person_id='$person_id', order_date='$order_date', delivery_date='$delivery_date', remarks='$remarks', order_serial='$order_serial', order_status='$order_status' WHERE id='$id'";
    if ($conn->query($sql) === TRUE) {
        echo '<div style="text-align: center;">Order updated successfully</div>';
       echo '<script>window.location.href="order_edit.php?id='.$id.'"</script>';
        exit;
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }
}



if (isset($_POST['create'])) {
    $route_id = $_POST['route_id'];
    $person_id = $_POST['person_id'];
    $order_date = $_POST['order_date'];
    $delivery_date = $_POST['delivery_date'];
    $remarks = $_POST['remarks'];
    $order_serial = $_POST['order_serial'];
    $latitude = $_POST['latitude'];
    $longitude = $_POST['longitude'];

if (!is_numeric($route_id)) {
    $sql = "SELECT id FROM routes WHERE route_name = '$route_id'";
    $result = $conn->query($sql);
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $route_id = $row['id'];
    } else {
        $sql = "INSERT INTO routes (route_name) VALUES ('$route_id')";
        if ($conn->query($sql) === TRUE) {
            $route_id = $conn->insert_id;
        } else {
            echo "Error: " . $sql . "<br>" . $conn->error;
        }
    }
}

if (!is_numeric($person_id)) {
    $sql = "SELECT id FROM persons WHERE person_name = '$person_id'";
    $result = $conn->query($sql);
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $person_id = $row['id'];
    } else {
        $sql = "INSERT INTO persons (person_name) VALUES ('$person_id')";
        if ($conn->query($sql) === TRUE) {
            $person_id = $conn->insert_id;
        } else {
            echo "Error: " . $sql . "<br>" . $conn->error;
        }
    }
}


    $created_by = $_SESSION['id'];
    $now = date('Y-m-d H:i:s');
    $sql = "INSERT INTO orders (route_id, person_id, order_date, delivery_date, remarks, order_serial, created_by, created_at, updated_at, latitude, longitude) VALUES ('$route_id', '$person_id', '$order_date', '$delivery_date', '$remarks', '$order_serial', '$created_by', '$now', '$now', '$latitude', '$longitude')";
  
  
  
    if ($conn->query($sql) === TRUE) {
        echo '<div style="text-align: center;">New order created successfully</div>';
        $id = $conn->insert_id;
        echo '<script>window.location.href="order_edit.php?id='.$id.'"</script>';
        exit;
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }
}
        $id='';
        $route_id = '';
        $person_id = '';
        $order_date = '';
        $delivery_date = '';
        $remarks = '';
        $order_serial = '';
        $order_status = 0;

if (isset($_REQUEST['id'])) {
    $id = $_REQUEST['id'];
    $sql = "SELECT * FROM orders WHERE id = '$id'";
    $result = $conn->query($sql);
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $id= $row["id"];
        $route_id = $row['route_id'];
        $person_id = $row['person_id'];
        $order_date = $row['order_date'];
        $delivery_date = $row['delivery_date'];
        $remarks = $row['remarks'];
        $order_serial = $row['order_serial'];
        $order_status = $row['order_status'];
    }
    else {
        $order_status = 0;
        echo '<script>alert("Order not found");</script>';
    }
}else {
        $order_status = 0;
}

// this order has been finalized. We cannot edit this.
// 0: Draft
// 1: Submit
// 2: Approve
// 3: Reject
// 4: Edit
// 5: Serial
// 6: Processing
// 7: Delivered
// 8: Returned

// if the order status is not 0 (draft) or 4 (edit), then we cannot edit this order
// this is because the order has been finalized and we cannot make any changes
// 0: Draft
// 1: Submit
// 2: Approve
// 3: Reject
// 4: Edit
// 5: Serial
// 6: Processing
// 7: Delivered
// 8: Returned
// if($order_status != 0 && $order_status != 4) {
//     echo '<div style="text-align: center;">This order has been finalized. You cannot edit this.</div>';
//     exit;
// }

?>

<form  method="post">
    <div class="row">
        <div class="col-md-6">
            <label for="route_id">Route</label>
            <select class="form-control select2edit" id="route_id" name="route_id">
                <?php
                     if(!empty($route_id)){
                    $sql2 = "SELECT route_name FROM routes WHERE id = '$route_id'";
                    $result2 = $conn->query($sql2);
                    if ($result2->num_rows > 0) {
                        $row2 = $result2->fetch_assoc();
                        echo "<option value='$route_id' selected>".$row2['route_name']."</option>";
                    }
                }
                ?>
               
                
                <?php
                $sql = "SELECT * FROM routes ORDER BY id DESC";
                $result = $conn->query($sql);
                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        echo "<option value='" . $row['id'] . "'>" . $row['route_name'] . "</option>";
                    }
                }
                ?>
            </select>
        </div>
        <div class="col-md-6">
            <label for="person_id">Shop Name, Address, Phone</label>
            <select class="form-control select2edit" id="person_id" name="person_id">


                <?php
                     if(!empty($person_id)){
                    $sql2 = "SELECT person_name FROM persons WHERE id = '$person_id'";
                    $result2 = $conn->query($sql2);
                    if ($result2->num_rows > 0) {
                        $row2 = $result2->fetch_assoc();
                        echo "<option value='$person_id' selected>".$row2['person_name']."</option>";
                    }
                }
                ?>

                <?php
                $sql = "SELECT * FROM persons ORDER BY id DESC";
                $result = $conn->query($sql);
                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        echo "<option value='" . $row['id'] . "'>" . $row['person_name'] . "</option>";
                    }
                }
                ?>
            </select>
        </div>
    </div>

  
    <div class="row mt-3">
        <div class="col-md-6 col-6">
            <label for="order_date">Order Date</label>
            <input type="date" class="form-control" id="order_date" name="order_date" value="<?php echo !empty($order_date) ? $order_date : date('Y-m-d'); ?>">
        </div>
        <div class="col-md-6 col-6">
            <label for="delivery_date">Delivery Date</label>
            <input type="date" class="form-control" id="delivery_date" name="delivery_date" value="<?php echo !empty($delivery_date) ? $delivery_date : date('Y-m-d', strtotime('+1 days')); ?>">
        </div>
        <div class="col-md-6 col-6">
            <label for="remarks">Remarks</label>
            <input  type="text" class="form-control" id="remarks" name="remarks" value="<?php echo !empty($remarks) ? $remarks : ''; ?>" ></input>
        </div>

        <div class="col-md-3 col-3">
            <label for="order_serial">Serial</label>
            <input type="number" class="form-control" id="order_serial" name="order_serial" value="<?php echo !empty($order_serial) ? $order_serial : ''; ?>" >
        </div>
        <div class="col-md-3 col-3">
            <label for="order_status">Status</label>
            <select class="form-control" id="order_status" name="order_status">
                <option value="0" <?php echo $order_status == 0 ? 'selected' : ''; ?>>Draft</option>
                <option value="1" <?php echo $order_status == 1 ? 'selected' : ''; ?>>Submit</option>
                <option value="2" <?php echo $order_status == 2 ? 'selected' : ''; ?>>Approve</option>
                <option value="3" <?php echo $order_status == 3 ? 'selected' : ''; ?>>Reject</option>
                <option value="4" <?php echo $order_status == 4 ? 'selected' : ''; ?>>Edit</option>
                <option value="5" <?php echo $order_status == 5 ? 'selected' : ''; ?>>Serial</option>
                <option value="6" <?php echo $order_status == 6 ? 'selected' : ''; ?>>Processing</option>
                <option value="7" <?php echo $order_status == 7 ? 'selected' : ''; ?>>Delivered</option>
                <option value="8" <?php echo $order_status == 8 ? 'selected' : ''; ?>>Returned</option>
            </select>
        </div>
    </div>
    <div class="row mt-3">
        <div class="col-md-12 text-center">
            <?php if (isset($_REQUEST['id'])) { ?>
                <input type="hidden" name="id" value="<?php echo $id; ?>">
                <button type="submit" name="update" value="<?php echo $_REQUEST['id']; ?>" class="btn btn-success"><i class="fas fa-edit"></i> Update</button>
            <?php } else { ?>
        <input type="hidden" id="latitude" name="latitude">
        <input type="hidden" id="longitude" name="longitude">

        <script>
            function getLocation() {
                if (navigator.geolocation) {
                    navigator.geolocation.getCurrentPosition(function(position) {
                        document.getElementById("latitude").value = position.coords.latitude;
                        document.getElementById("longitude").value = position.coords.longitude;
                    }, function() {
                        if (confirm("Please allow LOCATION permission. Call for help: 01632950179")) {
                            getLocation();
                        }
                    });
                }
            }
            getLocation();
        </script>
                <button type="submit" name="create" value="1" class="btn btn-danger"><i class="fas fa-plus"></i> Create</button>
            <?php } ?>
        </div>
    </div>
</form>

<hr>

    <?php if (isset($_REQUEST['id'])) { ?>


      
            <?php if (isset($_POST['add_product'])) { ?>
                <?php
                $product_id = $_POST['product_id'];
                $quantity = $_POST['quantity'];
                $price = $_POST['price'];
                $total = $quantity * $price;
                                                if (!is_numeric($product_id)) {
                                                    $sql = "SELECT id FROM products WHERE product_name = '$product_id'";
                                                    $result = $conn->query($sql);
                                                    if ($result->num_rows > 0) {
                                                        $row = $result->fetch_assoc();
                                                        $product_id = $row['id'];
                                                    } else {
                                                        $sql = "INSERT INTO products (product_name) VALUES ('$product_id')";
                                                        if ($conn->query($sql) === TRUE) {
                                                            $product_id = $conn->insert_id;
                                                        } else {
                                                            echo "Error: " . $sql . "<br>" . $conn->error;
                                                        }
                                                    }
                                                }

                $sql = "INSERT INTO order_product (order_id, product_id, quantity, price, total) VALUES ('$id', '$product_id', '$quantity', '$price', '$total')";
                if ($conn->query($sql) === TRUE) {
                    echo '<div style="text-align: center; color:green;">Product added successfully</div>';
                    echo "<script>window.location.href='order_edit.php?id=".$_REQUEST['id']."'</script>";
                } else {
                    echo "Error: " . $sql . "<br>" . $conn->error;
                }
                ?>
            <?php } ?>
    <div class="text-center" id="create">Add Products</div>
    <form  action="order_edit.php?id=<?php echo $_REQUEST['id']; ?>" method="post">
        <div class="row">
            <div class="col-md-4">
                <label for="product_id">Product</label>
                <select class="form-control select2edit" id="product_id" name="product_id" data-placeholder="Select product">
                    <?php
                    $sql2 = "SELECT * FROM products ORDER By id DESC";
                    $result2 = $conn->query($sql2);
                    if ($result2->num_rows > 0) {
                        while ($row2 = $result2->fetch_assoc()) {
                            echo "<option value='" . $row2['id'] . "'>" . $row2['product_name'] . "</option>";
                        }
                    }
                    ?>
                </select>
            </div>
            <div class="col-md-2 col-6">
                <label for="quantity">Quantity</label>
                <input type="text" class="form-control" id="quantity" name="quantity" required pattern="^\d+(\.\d+)?$" oninput="this.value = convertBanglaToEnglish(this.value)">
            </div>
       
            <div class="col-md-2 col-6">
                <label for="price">Price</label>
                <input type="text" class="form-control" id="price" name="price" required pattern="^\d+(\.\d+)?$" oninput="this.value = convertBanglaToEnglish(this.value)">
            </div>

                        <script>
                            function convertBanglaToEnglish(str) {
                                var banglaDigits = ['০', '১', '২', '৩', '৪', '৫', '৬', '৭', '৮', '৯'];
                                var englishDigits = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'];
                                return str.replace(/[^\d.০-৯]/g, '').replace(/[০-৯]/g, function (digit) {
                                    return englishDigits[banglaDigits.indexOf(digit)];
                                }).replace(/(\.\d{2})\d+/, '$1');
                            }
                        </script>
            <div class="col-md-2 col-6">
                <label for="total">Total</label>
                <input type="number" step="0.01" class="form-control" id="total" name="total" readonly>
            </div>
       
            <div class="col-md-2 col-6 text-center mt-4">
                <button type="submit" name="add_product" value="<?php echo $_REQUEST['id']; ?>" class="btn btn-primary btn-block">
                    <i class="fas fa-plus"></i> Add
                </button>
            </div>
        </div>
    </form>

    <script>
        $(document).ready(function() {
            $('#product_id, #quantity, #price').on('keyup', function() {
                var product_id = $('#product_id').val();
                var quantity = $('#quantity').val();
                var price = $('#price').val();
                $('#total').val((quantity * price).toFixed(2));
            });
        });
    </script>



<hr>

            <div class="table-responsive">
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Product Name</th>
                <th>Quantity</th>
                <th>Price</th>
                <th>Total</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            <?php
            $sql = "SELECT op.id, op.product_id, op.quantity, op.price, op.total, p.product_name FROM order_product op JOIN products p ON op.product_id = p.id WHERE op.order_id = " . $_REQUEST['id'];
            $result = $conn->query($sql);
            $grandQuantity = 0;
            $grandTotal = 0;
            $productCount = $result->num_rows;
            while ($row = $result->fetch_assoc()) {
                echo '<tr>
                        <td>' . $row['product_name'] . '</td>
                        <td>' . $row['quantity'] . '</td>
                        <td>' . $row['price'] . '</td>
                        <td>' . $row['total'] . '</td>
                        <td><form action="" method="post"><input type="hidden" name="delete_id" value="' . $row['id'] .
                         '"><input type="hidden" name="id" value="' . $_REQUEST['id'] . '">

                         <button type="submit" name="delete_product" value="' . $_REQUEST['id'] . '" class="btn btn-danger"><i class="fas fa-trash-alt"></i></button>
                         
                         
                         </form></td>
                      </tr>';
                $grandQuantity += $row['quantity'];
                $grandTotal += $row['total'];
            }
         echo '<tr>
                    <td colspan="4" class="text-center">' . $productCount . ' Product(s): ' 
                    . $grandQuantity . ' Quantity <strong>Grand Total:</strong> <strong>' . $grandTotal . '</strong>/=</td> <td></td>
                  </tr>';
            ?>
        </tbody>
        <?php
        if (isset($_POST['delete_product'])) {
            $id = $_POST['delete_id'];
            $sql = "DELETE FROM order_product WHERE id = $id";
            if ($conn->query($sql) === TRUE) {
                echo '<div class="alert alert-success">Deleted successfully</div>';
                echo '<script>window.location.href="order_edit.php?id=' . $_REQUEST['id'] . '"</script>';
                exit;
            } else {
                echo '<div class="alert alert-danger">Error: ' . $sql . '<br>' . $conn->error . '</div>';
            }
        }
        ?>
    </table>
</div>

    <div class="row mt-3">
        <div class="col-md-12 text-center">
            <?php if (isset($_REQUEST['id'])) { ?>
                <form action="" method="post">
                    <input type="hidden" name="id" value="<?php echo $_REQUEST['id']; ?>">
                    <input type="hidden" name="grandtotal" value="<?php echo $grandTotal; ?>">
                     <button type="button" onclick="copyTableData()" class="btn btn-info"><i class="fas fa-copy"></i> Copy Data</button>

                    <script>
                    function copyTableData() {
                        // Get order form data
                        var route = document.getElementById('route_id').options[document.getElementById('route_id').selectedIndex].text;
                        var person = document.getElementById('person_id').options[document.getElementById('person_id').selectedIndex].text;
                        var orderDate = document.getElementById('order_date').value;
                        var deliveryDate = document.getElementById('delivery_date').value;
                        var remarks = document.getElementById('remarks').value;
                        var serial = document.getElementById('order_serial').value;
                        var orderId = document.getElementById('order-id').textContent;

                        var formData = ''+orderId + '\n' +
                                     'Route: ' + route + '\n' +
                                     'Shop: ' + person + '\n' + 
                                     'Order Date: ' + orderDate + '\n' +
                                     'Delivery Date: ' + deliveryDate + '\n' +
                                     'Remarks: ' + remarks + '\n' +
                                     'Serial: ' + serial + '\n\n';

                        // Get table data
                        var table = document.querySelector('table');
                        var text = formData;
                        
                        for (var i = 0; i < table.rows.length; i++) {
                            var row = table.rows[i];
                            var rowData = [];
                            for (var j = 0; j < row.cells.length-1; j++) { // -1 to skip last column (delete button)
                                rowData.push(row.cells[j].textContent.trim());
                            }
                            text += rowData.join('\t') + '\n';
                        }
                        
                        navigator.clipboard.writeText(text).then(() => {
                            alert('Order data copied to clipboard');
                        }).catch(err => {
                            console.error('Failed to copy: ', err);
                        });
                    }
                    </script>
                    
             
                </form>
            <?php } ?>
        </div>
    </div>

    <?php
    if (isset($_POST['update_order_status'])) {
        $id = $_POST['id'];
        $order_status = $_POST['update_order_status'];
        $sql = "UPDATE orders SET order_status='$order_status', total='".$_POST['grandtotal']."' WHERE id='$id'";
        if ($conn->query($sql) === TRUE) {
            echo '<div class="alert alert-success">Draft updated successfully</div>';
            echo '<script>window.location.href="orders.php"</script>';
            exit;
        } else {
            echo '<div class="alert alert-danger">Error: ' . $sql . '<br>' . $conn->error . '</div>';
        }
    }
    ?>









    <?php } ?>











<?php
           
                
            } else {
                echo "<div class='alert alert-warning'>No order products found for this order.</div>";
            }


        } else {
            echo "<div class='alert alert-danger'>No order found with this ID.</div>";
        }
    
    ?>
</div>







<?php
require_once 'footer.php';
?>