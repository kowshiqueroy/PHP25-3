<?php
include 'header.php';
?>
<?php
if (isset($_GET['statusid'])) {
    $statusid = $_GET['statusid'];

    // Fetch current status
    $query = "SELECT status FROM user WHERE id = $statusid";
    $result = mysqli_query($conn, $query);
    if ($row = mysqli_fetch_assoc($result)) {
        $current_status = $row['status'];
    }

    // Update status
    $new_status = $current_status == 1 ? 0 : 1;
    $update_query = "UPDATE user SET status = $new_status WHERE id = $statusid";
    if (mysqli_query($conn, $update_query)) {
        echo "<script>alert('Status updated successfully.');</script>";
    }
}

if (isset($_POST['create'])) {
    $username = $_POST['username'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $role = $_POST['role'];
    $status= $_POST['status'];
    $check_query = "SELECT id FROM user WHERE username='$username'";
    $check_result = mysqli_query($conn, $check_query);
    if (mysqli_num_rows($check_result) > 0) {
        $update_query = "UPDATE user SET password='$password', role=$role, status=$status WHERE username='$username'";
        if (mysqli_query($conn, $update_query)) {
            echo "<script>alert('User $username updated successfully.');</script>";
        } else {
            echo "<script>alert('Error updating user: " . mysqli_error($conn) . "');</script>";
        }
    } else {
        $insert_query = "INSERT INTO user (username, password, role, status) VALUES ('$username', '$password', $role, $status)";
        if (mysqli_query($conn, $insert_query)) {
            echo "<script>alert('New user $username added successfully.');</script>";
        } else {
            echo "<script>alert('Error creating user: " . mysqli_error($conn) . "');</script>";
        }
    }
}

if (isset($_GET['editid'])) {
    $editid = $_GET['editid'];

    // Fetch current user data
    $query = "SELECT * FROM user WHERE id = $editid";
    $result = mysqli_query($conn, $query);
    if ($row = mysqli_fetch_assoc($result)) {
        $username = $row['username'];
        $role = $row['role'];
        $status = $row['status'];
    }
}
?>

            <main class="content-area">
                
                <h1 class="page-title">User</h1>
                
               
                 <form method="POST">
                <div class="card">
                    <h4 style="margin-bottom: 1rem;">New User</h4>
                    <div class="input-group">
                       
                        <div>
                            <label style="font-size:0.85rem; font-weight:500; margin-bottom:0.5rem; display:block;">User Name</label>
                            <input type="text" name="username" class="form-control" placeholder="User Name" value="<?php echo isset($username) ? $username : ''; ?>" required>
                        </div>
                         <div>
                            <label style="font-size:0.85rem; font-weight:500; margin-bottom:0.5rem; display:block;">Password</label>
                            <input type="text" name="password" class="form-control" placeholder="Password" required>
                        </div>
                      
                        <div>
                            <label style="font-size:0.85rem; font-weight:500; margin-bottom:0.5rem; display:block;" >Role</label>
                            <select class="form-control" name="role">
                            
                                <option value="1"<?php echo isset($role) && $role == 1 ? ' selected' : ''; ?>>Rice Sales North</option>
                                <option value="2"<?php echo isset($role) && $role == 2 ? ' selected' : ''; ?>>Rice Sales South</option>
                                <option value="3"<?php echo isset($role) && $role == 3 ? ' selected' : ''; ?>>Ovijat Sales</option>
                                <option value="4"<?php echo isset($role) && $role == 4 ? ' selected' : ''; ?>>Ovijat Distribution</option>
                                <option value="5"<?php echo isset($role) && $role == 5 ? ' selected' : ''; ?>>Ovijat Store</option>
                                <option value="6"<?php echo isset($role) && $role == 6 ? ' selected' : ''; ?>>SHARM Store</option>
                                <option value="7"<?php echo isset($role) && $role == 7 ? ' selected' : ''; ?>>SHARM Production</option>
                                <option value="8"<?php echo isset($role) && $role == 8 ? ' selected' : ''; ?>>-</option>
                                <option value="9"<?php echo isset($role) && $role == 9 ? ' selected' : ''; ?>>-</option>
                                <option value="0"<?php echo isset($role) && $role == 0 ? ' selected' : ''; ?>>Admin</option>
                            </select>
                        </div>
                            <div>
                            <label style="font-size:0.85rem; font-weight:500; margin-bottom:0.5rem; display:block;" >Status</label>
                            <select class="form-control" name="status">
                            
                                <option value="1"<?php echo isset($status) && $status == 1 ? ' selected' : ''; ?>>Active</option>
                                <option value="0"<?php echo isset($status) && $status == 0 ? ' selected' : ''; ?>>Block</option>
                            </select>
                        </div>
                        <div>
                            <!-- // Additional fields can be added here -->
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
                            <h2 style="font-size: 1.25rem; font-weight: 700;">User List</h2>
                            <p style="color: var(--text-secondary); font-size: 0.9rem; margin-top: 0.25rem;">Generated on <?php echo date('F j, Y, g:i a'); ?></p>
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
                                    <th>User Name</th>
                                    <th>Role</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                    $query = "SELECT * FROM user";
                                    $result = mysqli_query($conn, $query);
                                    while($row = mysqli_fetch_assoc($result)) {
                                        ?>
                                        <tr>
                                            <td style="font-weight: 500;"><?php echo $row['id']; ?></td>
                                            <td><?php echo $row['username']; ?></td>
                                            <td><?php echo $row['role']; ?></td>
                                            <td><span class="badge <?php echo $row['status'] == 1 ? 'badge-success' : 'badge-danger'; ?>"><?php echo $row['status'] == 1 ? 'Active' : 'Block'; ?></span></td>
                                            <td><span onClick="window.location.href='?statusid=<?php echo $row['id']; ?>'" style="cursor: pointer;" class="badge <?php echo $row['status'] == 1 ? 'badge-danger' : 'badge-success'; ?>"><?php echo $row['status'] == 1 ? '&#10006;' : '&#10004;'; ?></span> <span class="badge badge-primary" onClick="window.location.href='?editid=<?php echo $row['id']; ?>'" style="cursor: pointer;">✏️</span> </td>
                                        </tr>
                                        <?php
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