<?php
require_once '../conn.php';
require_once 'header.php';


if (isset($_SESSION['querylist'])) {
    foreach ($_SESSION['querylist'] as $key => $value) {
        ${$key} = $value;
      //  echo '<br>'. $key .''. $value .'';
        
    }
} else {
    $order_status = '';
    $order_date_from = '';
    $order_date_to = '';
    $delivery_date_from = '';
    $delivery_date_to = '';
    $route_id='';
    $person_id='';

}

// if (isset($_POST['serial_submit'])) {
//     $order_serial = $_POST['order_serial'];
//     $id = $_POST['id'];
//     $sql = "UPDATE orders SET order_serial = '$order_serial' WHERE id = '$id'";
//     if ($conn->query($sql) === TRUE) {
//         echo '<div style="text-align: center;">Order serial updated successfully</div>';
//     } else {
//         echo "Error updating record: " . $conn->error;
//     }
// }
if (isset($_POST['update_id'])) {
    $update_id = $_POST['update_id'];
    $sql = "UPDATE orders SET serial = serial + 1 WHERE id = '$update_id'";
    if ($conn->query($sql) === TRUE) {
        echo '<div style="text-align: center;">Order serial updated successfully</div>';
    } else {
        echo "Error updating record: " . $conn->error;
    }
}


if (isset($_GET['order_serial_update'])) {
    $os_update_id = $_GET['order_serial_update'];

    $os_update_id_list = explode('.', $os_update_id);
    foreach ($os_update_id_list as $os_id) {
        $os_update_id_list2 = explode('s', $os_id);
        $idq = $os_update_id_list2[0];


        $order_serialq = $os_update_id_list2[1];

        $sql = "UPDATE orders SET order_serial = '$order_serialq', order_status = 5 WHERE id = '$idq' AND order_status =2";
        if ($conn->query($sql) === TRUE) {
            echo '<div id="success_msg" style="text-align: center; display: none;">Order serial updated successfully</div>';
            echo '<script>
            setTimeout(function() {
                document.getElementById("success_msg").style.display="block";
            }, 500);
            setTimeout(function() {
                document.getElementById("success_msg").style.display="none";
            }, 1500);
            </script>';
        } else {
            echo "Error updating record: " . $conn->error;
        }

            
    }
    
   
}

?>



<div class="card p-1 text-center">Orders</div>
<hr>
<div class="d-flex justify-content-center">
    <button type="button" class="btn btn-success" onclick="window.location.href='create.php'"><i class="fas fa-plus"></i> Create New Order</button>
</div>

<hr>
<form id="filterForm" class="no-print" method="get" action="">
    <div class="row">

        <div class="col-md-2 col-6">
            <label for="order_status">Order Status</label>
            <select class="form-control select2" id="order_status" name="order_status">
                <option value="">All</option>
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

         <div class="col-md-2 col-6">
            <label for="created_by">Created By</label>
            <select class="form-control select2" id="created_by" name="created_by">
              
                <?php if ($_SESSION['role'] == 2): ?>
                    <option value="<?php echo $_SESSION['id']; ?>"><?php echo $_SESSION['username']; ?></option>
                <?php else: ?>
                    <?php
                    $sql = "SELECT * FROM users WHERE role IN (2,3)";
                    $result = $conn->query($sql);
                    if ($result->num_rows > 0) {
                        while ($row = $result->fetch_assoc()) {
                            echo "<option value='" . $row['id'] . "'>" . $row['username'] . "</option>";
                        }
                    }
                    ?>

                <?php endif; ?>
                

            </select>
        </div>
        
      
        <div class="col-md-3">
            <label for="route_id">Route Name</label>
            <select class="form-control select2" id="route_id" name="route_id">
                <option value="">All</option>
                <?php
                $sql = "SELECT * FROM routes";
                $result = $conn->query($sql);
                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {

                        if ($row["id"] == $route_id) {
                             echo "<option selected value='" . $row['id'] . "'>" . $row['route_name'] . "</option>";
                        }
                        else {
                             echo "<option value='" . $row['id'] . "'>" . $row['route_name'] . "</option>";
                        }
                       
                    }
                }
                ?>
            </select>
        </div>
        <div class="col-md-4">
            <label for="person_id">Shop Name, Address, Phone</label>
            <select class="form-control select2" id="person_id" name="person_id">
                <option value="">All</option>
                <?php
                $sql = "SELECT * FROM persons";
                $result = $conn->query($sql);
                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                         if ($row["id"] == $person_id) {
                             echo "<option selected value='" . $row['id'] . "'>" . $row['person_name'] . "</option>";
                        }
                        else {
                             echo "<option value='" . $row['id'] . "'>" . $row['person_name'] . "</option>";
                        }     
                    
                    }
                }
                ?>
            </select>
        </div>

       
       
        
    </div>
    <div class="row mt-3">
        
    <div class="col-md-2 col-6">
        <label for="order_date_from">Order From</label>
        <input type="date" class="form-control" id="order_date_from" name="order_date_from" value="<?php echo !empty($order_date_from) ? $order_date_from : date('Y-m-d'); ?>">
    </div>
    <div class="col-md-2 col-6">
        <label for="order_date_to">Order To</label>
        <input type="date" class="form-control" id="order_date_to" name="order_date_to" value="<?php echo !empty($order_date_to) ? $order_date_to : date('Y-m-d'); ?>">
    </div>
    <div class="col-md-2 col-6">
        <label for="delivery_date_from">Delivery From</label>
        <input type="date" class="form-control" id="delivery_date_from" name="delivery_date_from" value="<?php echo !empty($delivery_date_from) ? $delivery_date_from : ''; ?>">
    </div>
    <div class="col-md-2 col-6">
        <label for="delivery_date_to">Delivery To</label>
        <input type="date" class="form-control" id="delivery_date_to" name="delivery_date_to" value="<?php echo !empty($delivery_date_to) ? $delivery_date_to : ''; ?>">
    </div>





<div class="col-md-4 d-flex justify-content-center mt-4">
    <button type="button" class="btn btn-primary me-2" name="search" onclick="setSessionQueryData()"><i class="fas fa-search"></i> Search</button>
    <button type="button" class="btn btn-success" onclick="blankSessionQueryData()"><i class="fas fa-sync-alt"></i> Reset</button>
</div>




<script>
    function refreshPage() {
        window.location.href = 'orders.php';
    }

 
  
    
</script>
       
         
       
    </div>

</form>


<script>



    function setSessionQueryData() {

        var formData = new FormData(document.getElementById('filterForm'));
        // for (var pair of formData.entries()) {
        //     console.log(pair[0] + ': ' + pair[1]);
        // }
        
        var xhttp = new XMLHttpRequest();
        xhttp.onreadystatechange = function() {
            if (this.readyState == 4 && this.status == 200) {
                console.log(this.responseText);
                refreshPage();
            }
        };
        xhttp.open("POST", "set_session_data.php", true);
        xhttp.send(formData);
        



    }
function blankSessionQueryData() {
    var xhttp = new XMLHttpRequest();
    xhttp.onreadystatechange = function() {
        if (this.readyState == 4 && this.status == 200) {
            console.log(this.responseText);
            refreshPage();
            
        }
    };
    xhttp.open("POST", "clear_session_data.php", true);
    xhttp.send();
}

    </script>
<hr>


<hr>

<?php if ($order_status==2 ): ?>
    <div class=" mt-3 mb-3" style="display: flex; justify-content: center;">
        <button type="button" class="btn btn-primary" id="array_data">Update Order Serial</button>
    </div>
<?php endif; ?>
 <script>
var idWithSerial = new Map();

function setIdWithSerial(orderSerial, orderId) {
        var os=parseInt(orderSerial);
        if (isNaN(os)) {
            os = 0;
           
        } else if (os < 1) {
            os = 0;
        }
    
        idWithSerial.set(parseInt(orderId),os );
    
        console.log("", Array.from(idWithSerial.entries()).map(entry => entry[0] + "s" + entry[1]).join("."));

        document.getElementById("array_data").addEventListener('click', function() {
            const queryString = Array.from(idWithSerial.entries()).map(entry => entry[0] + "s" + entry[1]).join(".");
            window.location.href = 'orders.php?order_serial_update=' + encodeURIComponent(queryString);
        });
    

}

</script>



<div class="table-responsive">
    <table class="table table-striped table-bordered">

    
        <style>
            /* #table_head th{
                white-space: nowrap;
                transform: rotate(-45deg);
                transform-origin: 20px 20px;
                text-align: left;
              
            } */
        </style>
        <thead>
            <tr id="table_head" style="text-align: center; height: 100px;">
                <th>Action</th>   
                <th>Route & Shop</th>
                <th>Total</th>
                <th>Dates</th>
                <!-- <th>Approved By</th> -->
                <th></th>
                <th></th>

                
            </tr>
        </thead>
        <tbody id="table_data">
            <?php
                $idall = '';       
            ?>

<?php
    


if ($_SESSION['role'] == 2) {
    $sql = "SELECT * FROM orders WHERE 1=1 AND created_by = '".$_SESSION['id']."'";
} else {
    $sql = "SELECT * FROM orders WHERE 1=1";
}

if (isset($order_status) && $order_status !== '') {
    $sql .= " AND order_status = '$order_status'";
}
// echo $order_status;
if (isset($order_date_from) && $order_date_from !== '') {
    $sql .= " AND order_date >= '$order_date_from'";
}
else {

    $sql .= " AND order_date >= CURDATE()";
}

if (isset($order_date_to) && $order_date_to !== '') {
    $sql .= " AND order_date <= '$order_date_to'";
} else {

    $sql .= " AND order_date <= CURDATE()";
}


if (isset($delivery_date_from) && $delivery_date_from !== '') {
    $sql .= " AND delivery_date >= '$delivery_date_from'";
}

if (isset($delivery_date_to) && $delivery_date_to !== '') {
    $sql .= " AND delivery_date <= '$delivery_date_to'";
}

if (isset($route_id) && $route_id !== '') {
    $sql .= " AND route_id = '$route_id'";
}

if (isset($person_id) && $person_id !== '') {
    $sql .= " AND person_id = '$person_id'";
}


if (isset($created_by) && $created_by !== '') {
    $sql .= " AND created_by = '$created_by'";
}

$sql.= " ORDER BY order_serial ";
$result = $conn->query($sql);



$result = $conn->query($sql);

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {

        if (empty($idall)) {
            $idall = $row['id'];
        } else {
            $idall .= ",".$row['id'];
        }
        echo '<tr>';
        echo '<td style="text-align: center;">ID: ' . htmlspecialchars($row['id']) ."<br>";
       
            if ($row['order_status'] == 0) {
                echo '<a href="create.php?id=' . htmlspecialchars($row['id']) . '" class="btn btn-primary">Draft</a>';
            } else if ($row['order_status'] == 1) {
                echo '<span class="btn btn-warning">Submit</span>';
            } else if ($row['order_status'] == 2) {
                echo '<span class="btn btn-success">Approve</span>';
            } else if ($row['order_status'] == 3) {
                echo '<span class="btn btn-danger">Reject</span>';
            } else if ($row['order_status'] == 4) {
                echo '<a href="create.php?id=' . htmlspecialchars($row['id']) . '" class="btn btn-primary">Edit</a>';
            } else if ($row['order_status'] == 5) {
                echo '<span class="btn btn-info">Serial</span>';
            } else if ($row['order_status'] == 6) {
                echo '<span class="btn btn-secondary">Processing</span>';
            } else if ($row['order_status'] == 7) {
                echo '<span class="btn btn-success">Delivered</span>';
            } else if ($row['order_status'] == 8) {
                echo '<span class="btn btn-danger">Returned</span>';
            } else {
                echo '';
            }
if ($_SESSION['role'] == 2 && $row['order_status'] ==2 && $order_status==2) {
        echo '<br><br><div style="display: flex; justify-content: center;">
         
            <input type="number" name="order_serial[]" value="' . htmlspecialchars($row['order_serial']) . '" style="width: 40px;" required
             onkeyup="setIdWithSerial(this.value, ' . htmlspecialchars($row['id']) . ')">
           </div>';
?>
<script>
    setIdWithSerial(<?php echo $row['order_serial']; ?>, <?php echo $row['id']; ?>);
</script>

<?php
    
 
} else {
    // Other role-specific logic can go here
}

        
        echo '</td>';
        $sql2 = "SELECT route_name FROM routes WHERE id = '".$row['route_id']."'";
        $result2 = $conn->query($sql2);
        if ($result2->num_rows > 0) {
            $row2 = $result2->fetch_assoc();
            echo '<td><span style="font-weight: bold;">' . htmlspecialchars($row2['route_name']) . '</span> - ';
        }
        
        $sql3 = "SELECT person_name FROM persons WHERE id = '".$row['person_id']."'";
        $result3 = $conn->query($sql3);
        if ($result3->num_rows > 0) {
            $row3 = $result3->fetch_assoc();
            echo ' ' . htmlspecialchars($row3['person_name']) . '</td>';
        }
        echo '<td style="text-align: center;">' .
         htmlspecialchars($row['total']) . 
         '<br>
         <button type="button" class="btn btn-info text-white" onclick="showOrderProducts(' . htmlspecialchars($row['id']) . ')"><i class="fa fa-list" aria-hidden="true"> Show</i></button>
         </td>';

        


        echo '<td> order:' . htmlspecialchars($row['order_date']) . '<br>delivery:' . htmlspecialchars($row['delivery_date']) . '</td>';
        
       
     
        
        

          if ($_SESSION['role'] != 2) {
        echo '<td>' . htmlspecialchars($row['created_at']);
        
          
      $sql4 = "SELECT username FROM users WHERE id = '".$row['created_by']."'";
        $result4 = $conn->query($sql4);
        if ($result4->num_rows > 0) {
            $row4 = $result4->fetch_assoc();
            echo ' <br>' . htmlspecialchars($row4['username']) . '';
        } else {
            echo '-';
        }
    }
    else {
       $sql5 = "SELECT username FROM users WHERE id = '".$row['approved_by']."'";
        $result5 = $conn->query($sql5);
        if ($result5->num_rows > 0) {
            $row5 = $result5->fetch_assoc();
            echo '<td>' .htmlspecialchars($row['updated_at']). ' '.'<br>' . htmlspecialchars($row5['username']) .''.'</td>';
        } else {
            echo '<td></td>';
        }
    }
        
        echo '</td>';


        echo '<td>';
       
       if ($_SESSION['role'] != 2) {
           echo ' <a href="https://www.google.com/maps/search/?api=1&query=' . htmlspecialchars($row['latitude']) . ',' . htmlspecialchars($row['longitude']) . '" target="_blank"> <i class="fa fa-map-marker" aria-hidden="true"></i></a>';
       }
      
      
       echo ' '. htmlspecialchars($row['remarks']) . ' '. '<a href="printfull.php?idall=' . htmlspecialchars($row['id']) . '" class="btn btn-success"><i class="fa fa-print" aria-hidden="false"></i></a>';
       
       
        echo '</td></tr>';

                    //Show Products Here 
                            
                    //         echo '<tr>';
                    // $sqlProducts = "SELECT op.product_id, op.quantity, op.price, op.total, p.product_name FROM order_product op 
                    //                 JOIN products p ON op.product_id = p.id 
                    //                 WHERE op.order_id = '" . $row['id'] . "'";
                    // $resultProducts = $conn->query($sqlProducts);
                    // if ($resultProducts->num_rows > 0) {
                    //     echo '<td colspan="9">';
                    //     while ($productRow = $resultProducts->fetch_assoc()) {
                    //         echo htmlspecialchars($productRow['product_name']) . ': ' .
                    //              '' . htmlspecialchars($productRow['quantity']) . ' X ' .
                    //              '' . htmlspecialchars($productRow['price']) .
                    //              ' = ' . htmlspecialchars($productRow['total']) . '<br>';
                    //     }
                    //     echo '</td>';
                    // } else {
                    //     echo '<td>No products found</td>';
                    // }
                    // echo '</tr>';




    }
} else {
    echo '<tr><td colspan="12" style="text-align: center;">No orders found</td></tr>';
}

   ?> 

        </tbody>
    </table>
</div>
<hr>
   <script>
    function updateSerial(serial, id) {
        var xhttp = new XMLHttpRequest();
        xhttp.onreadystatechange = function() {
            if (this.readyState == 4 && this.status == 200) {
                console.log(this.responseText);
            }
        };
        xhttp.open("POST", "update_serial.php", true);
        xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
        xhttp.send("id=" + id + "&serial=" + serial);
    }
    </script>
   
    
   

<?php
    echo '
    <div style="text-align: center;">
    <button type="button" class="btn btn-info" style="margin-left: 10px;" onclick="window.location.href=\'printshort.php?idall=' . $idall . '\'"><i class="fa fa-list" aria-hidden="true"></i> Print List</button>
    <button type="button" class="btn btn-success" style="margin-left: 10px;" onclick="window.location.href=\'printfull.php?idall=' . $idall . '\'"><i class="fa fa-print" aria-hidden="true"></i> Print Invoice</button>
    </div>';
?>
<style>
    #order_products {
        display: none;
        position: fixed;
        z-index: 9999;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        overflow: auto;
        background-color: rgba(0, 0, 0, 0.4);
    }

    #order_products .modal-content {
        background-color: #fefefe;
        position: absolute;
        left: 50%;
        top: 50%;
        transform: translate(-50%, -50%);
        padding: 20px;
        border-radius: 10px;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2), 0 6px 20px rgba(0, 0, 0, 0.19);
        max-width: 600px;
        width: 100%;
        border: 1px solid #ccc;
        animation: fadeIn 0.3s ease-in-out;
    }

    #order_products .close {
        position: absolute;
        right: 10px;
        top: 10px;
        background-color: #fff;
        border: none;
        cursor: pointer;
        padding: 15px;
    }

    #order_products .close:hover {
        background-color: #f2f2f2;
    }

    #order_products .content-text {
        text-align: center;
        font-size: 1.2rem;
        font-weight: bold;
        line-height: 1.5;
        margin-bottom: 20px;
        color: #333;
    }

    @media (max-width: 768px) {
        #order_products .content-text {
            font-size: 1rem;
        }
    }

    @keyframes fadeIn {
        0% {
            opacity: 0;
        }
        100% {
            opacity: 1;
        }
    }
</style>

<div id="order_products">
    <button type="button" class="close" aria-label="Close" onclick="document.getElementById('order_products').style.display='none'"></button>
    <div class="modal-content">
        <button type="button" class="close" aria-label="Close" onclick="document.getElementById('order_products').style.display='none'">
            <span aria-hidden="true">&times;</span>
        </button>
        <div style="display: flex; justify-content: center;" class="content-text">
            ProductNameQtyXPrice=Total
        </div>
    </div>
</div>
<script>
    function showOrderProducts(orderId) {
            if (!orderId) {
                console.error('Order ID is not provided');
                return;
            }

            $.ajax({
                url: 'get_order_product.php',
                type: 'GET',
                data: { id: orderId },
                success: function(response) {
                    try {
                        console.log(response);
                        $('#order_products').show();



                        $('#order_products .modal-content .content-text').html(response);



                    } catch (error) {
                        console.error('Error updating order products:', error);
                    }
                },
                error: function(xhr, status, error) {
                    console.error('AJAX request failed:', status, error);
                }
            });
        }
</script>
<?php
require_once 'footer.php';
?>
