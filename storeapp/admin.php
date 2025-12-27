<?php
// admin.php
require 'config.php';
session_start();

// 1. Security Check: Only Superadmin allowed
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'superadmin') {
    die("<h3>⛔ Access Denied</h3><p>You must be a Superadmin to view this page. <a href='index.php'>Go Back</a></p>");
}

// 2. Handle Form Submissions
$msg = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // -- Create Shop --
    if (isset($_POST['create_shop'])) {
        $name = trim($_POST['shop_name']);
        if($name) {
            $stmt = $pdo->prepare("INSERT INTO shops (name) VALUES (?)");
            $stmt->execute([$name]);
            $msg = "✅ Shop '$name' created!";
        }
    }

    // -- Create User --
    if (isset($_POST['create_user'])) {
        $u = trim($_POST['username']);
        $p = $_POST['password'];
        $role = $_POST['role'];
        $shop_id = $_POST['assigned_shop']; // Simple assignment for now

        // Check if username exists
        $check = $pdo->prepare("SELECT id FROM users WHERE username=?");
        $check->execute([$u]);
        
        if ($check->rowCount() > 0) {
            $msg = "❌ Error: Username '$u' already exists.";
        } else {
            // Create User
            $hash = password_hash($p, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO users (username, password, role) VALUES (?, ?, ?)");
            $stmt->execute([$u, $hash, $role]);
            $new_user_id = $pdo->lastInsertId();

            // Assign to Shop (Basic Permission: Full Access to this shop)
            if ($shop_id && $role == 'user') {
                $perm = json_encode(['view'=>1, 'edit'=>1, 'delete'=>0]); // Default permissions
                $stmt = $pdo->prepare("INSERT INTO user_shop_access (user_id, shop_id, permissions) VALUES (?, ?, ?)");
                $stmt->execute([$new_user_id, $shop_id, $perm]);
            }
            $msg = "✅ User '$u' created successfully!";
        }
    }
}

// 3. Fetch Data for Display
$shops = $pdo->query("SELECT * FROM shops")->fetchAll();
$users = $pdo->query("SELECT u.id, u.username, u.role, u.is_active, s.name as shop_name 
                      FROM users u 
                      LEFT JOIN user_shop_access usa ON u.id = usa.user_id 
                      LEFT JOIN shops s ON usa.shop_id = s.id 
                      ORDER BY u.id DESC")->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <style>
        body { font-family: system-ui, sans-serif; background: #f0f2f5; padding: 20px; }
        .container { max-width: 900px; margin: 0 auto; }
        .card { background: white; padding: 20px; margin-bottom: 20px; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); }
        h2, h3 { margin-top: 0; color: #333; }
        .msg { padding: 10px; background: #d1fae5; color: #065f46; border-radius: 4px; margin-bottom: 15px; }
        .error { background: #fee2e2; color: #991b1b; }
        
        /* Form Styles */
        .row { display: flex; gap: 15px; flex-wrap: wrap; }
        input, select, button { padding: 10px; border: 1px solid #ccc; border-radius: 4px; }
        button { background: #2563eb; color: white; border: none; cursor: pointer; }
        button:hover { background: #1d4ed8; }

        /* Table Styles */
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { padding: 10px; border-bottom: 1px solid #eee; text-align: left; }
        th { background: #f8f9fa; }
        .badge { padding: 2px 6px; border-radius: 4px; font-size: 0.8em; font-weight: bold; }
        .bg-admin { background: #e0e7ff; color: #3730a3; }
        .bg-user { background: #ecfdf5; color: #065f46; }
    </style>
</head>
<body>

<div class="container">
    <div style="display:flex; justify-content:space-between; align-items:center;">
        <h1>🛡️ Superadmin Panel</h1>
        <a href="index.php" style="text-decoration:none; color:#2563eb;">← Back to POS</a>
    </div>

    <?php if ($msg): ?>
        <div class="msg <?= strpos($msg, 'Error') !== false ? 'error' : '' ?>"><?= $msg ?></div>
    <?php endif; ?>

    <div class="card">
        <h3>🏪 Manage Shops</h3>
        <form method="POST" style="display:flex; gap:10px; margin-bottom:15px;">
            <input type="text" name="shop_name" placeholder="Enter Shop Name (e.g., Branch 2)" required style="flex:1;">
            <button type="submit" name="create_shop">Add Shop</button>
        </form>

        <table>
            <thead><tr><th>ID</th><th>Shop Name</th></tr></thead>
            <tbody>
                <?php foreach ($shops as $s): ?>
                <tr>
                    <td><?= $s['id'] ?></td>
                    <td><?= htmlspecialchars($s['name']) ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <div class="card">
        <h3>👤 Create New User</h3>
        <form method="POST" class="row">
            <input type="text" name="username" placeholder="Username" required>
            <input type="password" name="password" placeholder="Password" required>
            <select name="role">
                <option value="user">Shop User</option>
                <option value="superadmin">Superadmin</option>
            </select>
            <select name="assigned_shop">
                <option value="">-- Assign Shop --</option>
                <?php foreach ($shops as $s): ?>
                    <option value="<?= $s['id'] ?>"><?= htmlspecialchars($s['name']) ?></option>
                <?php endforeach; ?>
            </select>
            <button type="submit" name="create_user">Create User</button>
        </form>

        <h3 style="margin-top:20px;">Existing Users</h3>
        <table>
            <thead>
                <tr>
                    <th>Username</th>
                    <th>Role</th>
                    <th>Assigned Shop</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $u): ?>
                <tr>
                    <td><?= htmlspecialchars($u['username']) ?></td>
                    <td><span class="badge <?= $u['role'] == 'superadmin' ? 'bg-admin' : 'bg-user' ?>"><?= strtoupper($u['role']) ?></span></td>
                    <td><?= $u['shop_name'] ? htmlspecialchars($u['shop_name']) : '<em>Global / None</em>' ?></td>
                    <td><?= $u['is_active'] ? 'Active' : 'Blocked' ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

</body>
</html>