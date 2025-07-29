<?php
require_once '../conn.php';
require_once 'header.php';
?>

<div class="card p-1 text-center">Users</div>

        <div class="d-flex two-columns gap-3 mt-3">
                <div class="card p-3 flex-fill">

                    <?php
                    if (isset($_POST['updatePassword'])) {
                        $userId = $_SESSION['id'];
                        $newPassword = md5($_POST['new_password']);

                        $sql = "UPDATE users SET password='$newPassword' WHERE id='$userId'";
                        if ($conn->query($sql) === TRUE) {
                            echo "Password updated successfully.";
                        } else {
                            echo "Error updating password: " . $conn->error;
                        }
                    }
                    ?>

                    <form method="post" class="d-flex flex-column gap-2" style="max-width: 300px; margin: auto;">
                        <div class="input-group">
                            <input type="password" name="new_password" placeholder="New Password" required class="form-control">
                        </div>
                        <button type="submit" name="updatePassword" value="Update Password" class="btn btn-primary">Update Password</button>
                    </form>








                </div>
                <!-- <div class="card p-3 flex-fill">


                        Can't add User
                </div> -->
            </div>


            <table class="table table-striped mt-3 table-responsive">
                <thead class="table-dark">
                    <tr><th>ID</th><th>User</th><th>Last Login</th><th>Created At</th></tr>
                </thead>
                <tbody>
                    <?php
                    $sql = "SELECT * FROM users order by last_login desc";
                    $result = $conn->query($sql);
                    if ($result->num_rows > 0) {
                        while ($row = $result->fetch_assoc()) {
                            $roleName = '';
                            switch ($row['role']) {
                                case 1:
                                    $roleName = 'admin';
                                    break;
                                case 2:
                                    $roleName = 'sr';
                                    break;
                                case 3:
                                    $roleName = 'sradmin';
                                    break;
                                case 4:
                                    $roleName = 'viewer';
                                    break;
                                default:
                                    $roleName = 'Unknown';
                                    break;
                            }
                            ?>
                            <tr>
                                <td><?php echo $row['id']; ?></td>
                                <td><?php echo $row['username']; ?><br><?php echo $roleName; ?><br>
                                    <form method="post">
                                        <input type="hidden" name="user_id" value="<?php echo $row['id']; ?>">
                                        <input type="hidden" name="new_status" value="<?php echo ($row['status'] == 1) ? 0 : 1; ?>">
                                        <input type="submit" name="toggleStatus" value="<?php echo ($row['status'] == 1) ? 'Active' : 'Inactive'; ?>" class="btn <?php echo ($row['status'] == 1) ? 'btn-success' : 'btn-danger'; ?>">
                                    </form>
                                </td>
                                <td><?php echo ($row['last_login'] == null) ? 'Never' : date('M d, Y H:i', strtotime($row['last_login'])); ?></td>
                                <td><?php echo date('M d, Y H:i', strtotime($row['created_at'])); ?></td>
                            </tr>
                            <?php
                        }
                    }

                
                    ?>
                </tbody>
            </table>



<?php
require_once 'footer.php';
?>