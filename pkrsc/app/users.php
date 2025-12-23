<?php
require 'header.php';

// Assuming session holds the current user's ID
$current_user_id = $_SESSION['user_id'] ?? 0;
$msg = '';
$error = '';

// 1. Handle POST Actions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // CREATE USER
    if (isset($_POST['add_user'])) {
        $username = trim($_POST['username']);
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
   

        try {
            $stmt = $pdo->prepare("INSERT INTO users (username, password) VALUES (?, ?)");
            $stmt->execute([$username, $password]);
            $msg = "User created successfully.";
        } catch (PDOException $e) {
            $error = "Username already exists.";
        }
    } 
    // DELETE USER
    elseif (isset($_POST['delete_id'])) {
        $delete_id = $_POST['delete_id'];

        if ($delete_id == $current_user_id) {
            $error = "Safety Error: You cannot delete your own account.";
        } else {
            $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
            $stmt->execute([$delete_id]);
            $msg = "User removed successfully.";
        }
    }
}

// 2. Fetch All Users
$users = $pdo->query("SELECT id, username, last_login, created_at FROM users ORDER BY id DESC")->fetchAll();
?>

<div class="row">
    <div class="col-md-12 mb-4">
        <h2 class="fw-bold"><i class="fa-solid fa-users-gear text-primary"></i> System Users</h2>
        <button class="btn btn-primary" type="button" onclick="window.location.href='id'">Go to ID Card Portal</button>
    </div>
</div>

<?php if($error): ?>
    <div class="alert alert-danger alert-dismissible fade show"><?php echo $error; ?>
        <button type="button" class="btn-close" data-bs-dismiss='alert'></button>
    </div>
<?php endif; ?>

<?php if($msg): ?>
    <div class="alert alert-success alert-dismissible fade show"><?php echo $msg; ?>
        <button type="button" class="btn-close" data-bs-dismiss='alert'></button>
    </div>
<?php endif; ?>

<div class="row">
    <div class="col-md-4">
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-white fw-bold py-3">Add New Administrator</div>
            <div class="card-body">
                <form method="POST">
                    <div class="mb-3">
                        <label class="form-label">Username</label>
                        <input type="text" name="username" class="form-control" required autocomplete="off">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Password</label>
                        <input type="password" name="password" class="form-control" required>
                    </div>
                 
                    <button type="submit" name="add_user" class="btn btn-primary w-100">
                        <i class="fa-solid fa-user-plus me-2"></i>Create Account
                    </button>
                </form>
            </div>
        </div>
    </div>

    <div class="col-md-8">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white fw-bold py-3">Existing Accounts</div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-4">Username</th>
                                <th>Last Login</th>
                                <th>Created</th>
                                <th class="text-end pe-4">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($users as $u): ?>
                            <tr>
                                <td class="ps-4">
                                    <div class="d-flex align-items-center">
                                        <div class="rounded-circle bg-light p-2 me-3 text-primary">
                                            <i class="fa-solid fa-user"></i>
                                        </div>
                                        <span class="fw-bold"><?php echo htmlspecialchars($u['username']); ?></span>
                                        <?php if($u['id'] == $current_user_id): ?>
                                            <span class="badge bg-success ms-2">You</span>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td>
                                  
                                        <?php echo $u['last_login']; ?>
                                 
                                </td>
                                <td class="text-muted small"><?php echo date('M d, Y', strtotime($u['created_at'])); ?></td>
                                <td class="text-end pe-4">
                                    <?php if($u['id'] != $current_user_id): ?>
                                        <form method="POST" onsubmit="return confirm('Are you sure you want to remove this user?');">
                                            <input type="hidden" name="delete_id" value="<?php echo $u['id']; ?>">
                                            <button type="submit" class="btn btn-sm btn-outline-danger">
                                                <i class="fa-solid fa-trash"></i>
                                            </button>
                                        </form>
                                    <?php else: ?>
                                        <button class="btn btn-sm btn-light border text-muted" disabled title="Cannot delete yourself">
                                            <i class="fa-solid fa-lock"></i>
                                        </button>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require 'footer.php'; ?>