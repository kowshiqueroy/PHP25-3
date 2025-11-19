<?php
include 'header.php';
?>
<?php
if (isset($_POST['create'])) {
    $role=$_SESSION['role'];
    $item_name = $_POST['item_name'];
    $check_query = "SELECT id FROM item WHERE name='$item_name' AND role=$role";
    $check_result = mysqli_query($conn, $check_query);
    if (mysqli_num_rows($check_result) > 0) {
        echo "<script>alert('item $item_name already exists for role $role.'); window.location.href = 'item.php';</script>";
        exit;
    }
    $insert_query = "INSERT INTO item (name,role) VALUES ('$item_name', $role)";
    if (mysqli_query($conn, $insert_query)) {
        echo "<script>alert('New item $item_name added successfully.'); window.location.href = 'item.php';</script>";
    } else {
        echo "<script>alert('Error creating item: " . mysqli_error($conn) . "');</script>";
    }
}
?>

            <main class="content-area">
                
                <h1 class="page-title">item</h1>


              
                        <form method="POST">
                <div class="card">
                    <h4 style="margin-bottom: 1rem;">New item</h4>
                    <div class="input-group">
                       
                        <div>
                            <label style="font-size:0.85rem; font-weight:500; margin-bottom:0.5rem; display:block;">item Name</label>
                            <input type="text" name="item_name" class="form-control" placeholder="item Name"  required>
                        </div>
                         
                        <div>
                            <button class="btn btn-primary" style="width: 100%;" type="submit" name="create">Create</button>
                        </div>
                       
                    </div>
                </div>
                 </form>
                  

      <div class="card printable-content">
                    
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
                        <div>
                            <h2 style="font-size: 1.25rem; font-weight: 700;">All items</h2>
                            <p style="color: var(--text-secondary); font-size: 0.9rem; margin-top: 0.25rem;">Generated on <?php echo date('Y-m-d'); ?></p>
                        </div>
                        <button onclick="window.print()" class="btn btn-ghost no-print" style="border: 1px solid var(--border-color);">
                            🖨 Print Report
                        </button>
                    </div>

                    <div class="table-responsive">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Name</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $sql = "SELECT id, name FROM item WHERE role=".$_SESSION['role']." ORDER BY id DESC";
                                $result = $conn->query($sql);
                                if ($result->num_rows > 0) {
                                    while($row = $result->fetch_assoc()) {
                                ?>
                                <tr>
                                    <td><?php echo $row['id']; ?></td>
                                    <td><?php echo $row['name']; ?></td>
                                </tr>
                                <?php
                                    }
                                }
                                ?>
                            
                            </tbody>
                        </table>
                    </div>
                    
                    <div style="margin-top: 2rem; border-top: 1px solid #eee; padding-top: 1rem; font-size: 0.8rem; color: #666;">
                        Confidential Document - Internal Use Only
                    </div>
                </div>

               
             
                </main>
 <?php
include 'footer.php';
?>     