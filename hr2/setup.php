<?php
session_start();
include 'config.php';

// Create user table if not exists
$conn->query("CREATE TABLE IF NOT EXISTS user (
    id INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(20) NOT NULL UNIQUE,
    password VARCHAR(80) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

// Insert default user if table is empty
$res = $conn->query("SELECT COUNT(*) as count FROM user");
if ($res) {
    $row = $res->fetch_assoc();
    if ($row && isset($row['count']) && $row['count'] == 0) {
        $default_user = 'kowshiqueroy';
        $default_pass = password_hash('5877', PASSWORD_DEFAULT);
        $stmt = $conn->prepare("INSERT INTO user (username, password) VALUES (?, ?)");
        if ($stmt) {
            $stmt->bind_param("ss", $default_user, $default_pass);
            $stmt->execute();
            $stmt->close();
        }
    }
}

// Handle logout
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

// Handle login
if (!isset($_SESSION['logged_in']) && isset($_POST['login'])) {
    $user = $_POST['login_user'];
    $pass = $_POST['login_pass'];
    $stmt = $conn->prepare("SELECT id, password FROM user WHERE username=?");
    $stmt->bind_param("s", $user);
    $stmt->execute();
    $stmt->store_result();
    $stmt->bind_result($uid, $hashed_pass);
    if ($stmt->num_rows === 1 && $stmt->fetch() && password_verify($pass, $hashed_pass)) {
        $_SESSION['logged_in'] = true;
        $_SESSION['username'] = $user;
        $stmt->close();
        header("Location: " . $_SERVER['PHP_SELF']);
        exit;
    } else {
        $stmt->close();
        $error = "Invalid username or password.";
    }
}

// Handle user creation
$new_user_error = '';
$new_user_success = '';
if (isset($_POST['create_user'])) {
    $new_user = trim($_POST['new_user']);
    $new_pass = $_POST['new_pass'];
    if (strlen($new_user) < 3 || strlen($new_user) > 10) {
        $new_user_error = "Username must be 3-10 characters.";
    } elseif (strlen($new_pass) < 3) {
        $new_user_error = "Password must be at least 3 characters.";
    } else {
        $hashed = password_hash($new_pass, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("INSERT INTO user (username, password) VALUES (?, ?)");
        if ($stmt) {
            $stmt->bind_param("ss", $new_user, $hashed);
            if ($stmt->execute()) {
                $new_user_success = "User '$new_user' created.";
            } else {
                $new_user_error = "Error: " . $conn->error;
            }
            $stmt->close();
        } else {
            $new_user_error = "Error: " . $conn->error;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Panel - User Management</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- Google Fonts & Simple CSS Framework (like Bootstrap) -->
    <link href="https://fonts.googleapis.com/css?family=Roboto:400,500,700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Roboto', Arial, sans-serif;
            background: #f4f6f9;
        }
        .admin-panel {
            max-width: 700px;
            margin: 40px auto;
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 4px 24px rgba(0,0,0,0.07);
            padding: 32px 40px 40px 40px;
        }
        .admin-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 32px;
        }
        .admin-header h2 {
            margin: 0;
            font-weight: 700;
            color: #2c3e50;
        }
        .logout-link {
            color: #fff;
            background: #e74c3c;
            padding: 6px 18px;
            border-radius: 6px;
            text-decoration: none;
            font-weight: 500;
            transition: background 0.2s;
        }
        .logout-link:hover {
            background: #c0392b;
        }
        .form-label {
            font-weight: 500;
        }
        .table-users th, .table-users td {
            vertical-align: middle;
        }
        .alert {
            margin-bottom: 18px;
        }
        @media (max-width: 600px) {
            .admin-panel {
                padding: 16px 6px;
            }
        }
    </style>
</head>
<body>
<div class="admin-panel">
<?php
if (!isset($_SESSION['logged_in'])) {
    echo '<div class="text-center mb-4"><h2 class="mb-3">Admin Login</h2></div>';
    if (!empty($error)) echo "<div class='alert alert-danger'>$error</div>";
    echo '<form method="post" class="mb-2">
        <div class="mb-3">
            <label class="form-label">Username</label>
            <input type="text" name="login_user" class="form-control" required autofocus>
        </div>
        <div class="mb-3">
            <label class="form-label">Password</label>
            <input type="password" name="login_pass" class="form-control" required>
        </div>
        <button type="submit" name="login" class="btn btn-primary w-100">Login</button>
    </form>';
    echo '</div></body></html>';
    exit;
}
?>

    <div class="admin-header">
        <h2>User Management</h2>
        <span>
            <span class="me-2 text-secondary">Welcome, <b><?=htmlspecialchars($_SESSION['username'])?></b></span>
            <a href="?logout=1" class="logout-link">Logout</a>
        </span>
    </div>

    <?php if ($new_user_error): ?>
        <div class="alert alert-danger"><?=htmlspecialchars($new_user_error)?></div>
    <?php endif; ?>
    <?php if ($new_user_success): ?>
        <div class="alert alert-success"><?=htmlspecialchars($new_user_success)?></div>
    <?php endif; ?>

    <div class="card mb-4 shadow-sm">
        <div class="card-body">
            <h5 class="card-title mb-3">Create New User</h5>
            <form method="post" class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Username</label>
                    <input type="text" name="new_user" maxlength="10" class="form-control" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Password</label>
                    <input type="password" name="new_pass" class="form-control" required>
                </div>
                <div class="col-12">
                    <button type="submit" name="create_user" class="btn btn-success w-100">Create User</button>
                </div>
            </form>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-body">
            <h5 class="card-title mb-3">Existing Users</h5>
            <?php
            $res = $conn->query("SELECT id, username FROM user ORDER BY id ASC");
            if ($res && $res->num_rows > 0): ?>
                <div class="table-responsive">
                    <table class="table table-striped table-users align-middle">
                        <thead>
                            <tr>
                                <th style="width:60px;">ID</th>
                                <th>Username</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php while ($row = $res->fetch_assoc()): ?>
                            <tr>
                                <td><?=htmlspecialchars($row['id'])?></td>
                                <td><?=htmlspecialchars($row['username'])?></td>
                            </tr>
                        <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <p class="text-muted">No users found.</p>
            <?php endif; ?>
        </div>
    </div>
</div>
</body>
</html>
<?php $conn->close(); ?>