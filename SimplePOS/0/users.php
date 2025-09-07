<?php include_once 'header.php'; ?>



<?php

if($_SESSION['role'] == 0 ) {
        if(isset($_GET['toggle'])) {
            if ($_SESSION['user_id'] == $_GET['toggle']) {
                echo "<script>window.location.href = 'users.php';</script>";
                exit;
            }
            $stmt = $conn->prepare("UPDATE users SET blocked = !blocked WHERE id = ?");
            $stmt->execute([$_GET['toggle']]);
            echo "<script>window.location.href = 'users.php';</script>";
            exit;
        }


        if(isset($_GET['reset'])) {
            $stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
            $hashed_password = password_hash($_GET['username'], PASSWORD_DEFAULT);
            $stmt->execute([$hashed_password, $_GET['reset']]);
            echo "<script>window.location.href = 'users.php';</script>";
            exit;
        }

   if (isset($_POST['add_user'])) {
            $username = $_POST['username'];
            $role = $_POST['role'];

           
                $hashed_password = password_hash($username, PASSWORD_DEFAULT);
                $stmt = $conn->prepare("INSERT INTO users (username, password, role) VALUES (?, ?, ?)");
                $stmt->bind_param("sss", $username, $hashed_password, $role);
                if ($stmt->execute()) {
                    echo "<script>alert('User added successfully');</script>";
                    echo "<script>window.location.href = 'users.php';</script>";
                } else {
                    echo "<script>alert('Error: " . $conn->error . "');</script>";
                }
                $stmt->close();
            
        }
    }
        if (isset($_POST['update_user'])) {
            $old_password = $_POST['old_password'];
            $new_password = $_POST['new_password'];

            if (empty($old_password) || empty($new_password)) {
                echo "<script>alert('Please fill in all fields');</script>";
            } else {
                $stmt = $conn->prepare("SELECT password FROM users WHERE id = ?");
                $stmt->bind_param("i", $_SESSION['user_id']);
                $stmt->execute();
                $result = $stmt->get_result();
                $row = $result->fetch_assoc();

                if (password_verify($old_password, $row['password'])) {
                    $stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
                    $hashed_new_password = password_hash($new_password, PASSWORD_DEFAULT);
                    $stmt->bind_param("si", $hashed_new_password, $_SESSION['user_id']);
                    if ($stmt->execute()) {
                        echo "<script>alert('Password updated successfully');</script>";
                    } else {
                        echo "<script>alert('Error: " . $conn->error . "');</script>";
                    }
                    $stmt->close();
                } else {
                    echo "<script>alert('Current password is incorrect');</script>";
                }
            }
        }



     
?>


    <div class="cards-container">
                <div class="card">

                    <form action="users.php" method="post">
                        <h2>Update Password</h2>
                        <div class="form-group">
                            <label for="old_password">Old Password</label>
                            <input type="password" id="old_password" name="old_password" required>
                        </div>
                        <div class="form-group">
                            <label for="new_password">New Password</label>
                            <input type="password" id="new_password" name="new_password" required>
                        </div>
                        <button type="submit" class='btn' style='text-decoration: none; color: white; background-color: blue' name="update_user">Submit</button>
                    </form>
                    <br>
                    <form action="users.php" method="post">
                        <h2>Create New User</h2>
                        <div class="form-group">
                            <label for="username">Username</label>
                            <input type="text" id="username" name="username" required>
                        </div>
                        <div class="form-group">
                            <label for="role">Role</label>
                            <select id="role" name="role" required>
                                <option value="0">Admin</option>
                                <option value="1">Manager</option>
                            </select>
                        </div>
                     
                        <button type="submit" class='btn' style='text-decoration: none; color: white; background-color: green' name="add_user">Submit</button>
                    </form>
                    
                </div>



                <div class="card">
                    <div class="table-responsive">
                        <table style="width: 100%; border-collapse: collapse; margin-top: 1rem; item-align: center;">
                            <thead style="height: 40px;">
                                <tr>
                                    <th>ID</th>
                                    <th>Username</th>
                                    <th>Password</th>
                                    <th>Role</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody style="text-align: center;">
                                <?php
                                $stmt = $conn->prepare("SELECT id, username,  role,blocked FROM users");
                                $stmt->execute();
                                $result = $stmt->get_result();
                                while ($row = $result->fetch_assoc()) {
                                    echo "<tr style='height: 40px;'>";
                                    echo "<td>" . $row['id'] . "</td>";
                                    echo "<td>" . htmlspecialchars($row['username']) . "</td>";
                                    echo "<td><a href='users.php?username=" . $row['username'] . "&reset=" . $row['id'] . "' class='btn' style='text-decoration: none; color: white; background-color: red'>Reset</a></td>";
                                    echo "<td>" . ($row['role'] == 1 ? "Manager" : "Admin") . "</td>";
                                    if ($row['id'] != $_SESSION['user_id']) {
                                        echo "<td><a href='users.php?toggle=" . $row['id'] . "'  class='btn' style='text-decoration: none; color: white; background-color: " . ($row['blocked'] == 1 ? "green" : "red") . "'>" . ($row['blocked'] == 1 ? "Unblock" : "Block") . "</a></td>";
                                    } else {
                                        echo "<td>You</td>";
                                    }
                                    echo "</tr>";
                                }
                                $stmt->close();
                                ?>
                            </tbody>
                        </table>
                    </div>















                    
                </div>
              
    </div>








<?php include_once 'footer.php'; ?>

      