<?php
require '../config/db.php';
require 'includes/header.php'; 

?>



     
            <h2>Create New User</h2>
            <div class="card">
            <form action="users.php?action=create" method="post">
                 <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px;">
                <div class="form-group">
                    <label for="username" style="display:block; margin-bottom:5px; font-weight:500;">Username</label>
                    <input type="text" name="username" placeholder="Username" />
                </div>
                <div class="form-group">
                    <label for="password" style="display:block; margin-bottom:5px; font-weight:500;">Password</label>
                    <input type="password" name="password" placeholder="Password" />
                </div>
                <div class="form-group">
                    <label for="role" style="display:block; margin-bottom:5px; font-weight:500;">Full Name</label>
                 <input type='text' name='full_name' placeholder='Full Name' />
                </div>
                <button type="submit" name="submit" class="btn btn-primary">Create</button>
                </div>
            </form>
            </div>

     
       <div class="card">
    <div class="table-responsive">
        <table class="custom-table">
                <thead>
                    <tr>
                        <th>Username</th>
                        <th>Role</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $stmt = $pdo->prepare("SELECT * FROM users");
                    $stmt->execute();
                    $users = $stmt->fetchAll();
                    foreach ($users as $user) {
                    ?>
                    <tr>
                        <td><?php echo $user['username']; ?></td>
                        <td><?php echo $user['role'] == 0 ? 'Admin' : 'User'; ?></td>
                        <td><a href="users.php?action=change_password&id=<?php echo $user['id']; ?>" class="btn btn-primary">Change Password</a></td>
                    </tr>
                    <?php
                    }
                    ?>
                </tbody>
            </table>
            <?php if (isset($_GET['action']) && $_GET['action'] == 'change_password') { ?>
            <form action="users.php?action=update_password&id=<?php echo $_GET['id']; ?>" method="post">
                <div class="form-group">
                    <label for="password">New Password</label>
                    <input type="password" name="password" class="form-control" id="password">
                </div>
                <button type="submit" class="btn btn-primary">Update</button>
            </form>
            <?php } ?>
        </div>

<?php
if (isset($_GET['action']) && $_GET['action'] == 'update_password') {
    $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
    $stmt->execute([password_hash($_POST['password'], PASSWORD_DEFAULT), $_GET['id']]);
}
// Handle user creation
if (isset($_POST['submit'])) {
    $username = $_POST['username'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $full_name = $_POST['full_name'];
    //check if username exists
    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->execute([$username]);
    if ($stmt->rowCount() > 0) {
        echo "Username already exists.";
        exit;
    }
    $stmt = $pdo->prepare("INSERT INTO users (username, password, full_name) VALUES (?, ?, ?)");
    $stmt->execute([$username, $password, $full_name]);
    echo '<script>window.location.href="users.php"</script>';
}
?>
<?php require 'includes/footer.php'; ?>