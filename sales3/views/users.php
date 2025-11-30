<?php
// views/users.php
require_once __DIR__ . '/layout/header.php';

// Check if current user is admin, enforced by index.php but good for double checking
if (!has_role(ROLE_ADMIN)) {
    echo "<p class='error-message'>Access Denied.</p>";
    require_once __DIR__ . '/layout/footer.php';
    exit();
}

// Handle Add/Edit/Delete User actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_user'])) {
        $username = trim($_POST['username']);
        $password = $_POST['password'];
        $email = trim($_POST['email']);
        $firstName = trim($_POST['first_name']);
        $lastName = trim($_POST['last_name']);
        $roleId = $_POST['role_id'];
        $isActive = isset($_POST['is_active']) ? 1 : 0;

        try {
            // Check for duplicate username/email
            $existingUser = db_fetch("SELECT user_id FROM users WHERE username = :username AND company_id = :company_id", ['username' => $username, 'company_id' => $current_user['company_id']]);
            if ($existingUser) {
                throw new Exception("Username already exists for this company.");
            }
            $existingEmail = db_fetch("SELECT user_id FROM users WHERE email = :email AND company_id = :company_id", ['email' => $email, 'company_id' => $current_user['company_id']]);
            if ($existingEmail) {
                throw new Exception("Email already exists for this company.");
            }

            $user_data = [
                'company_id' => $current_user['company_id'],
                'role_id' => $roleId,
                'username' => $username,
                'password_hash' => hash_password($password),
                'email' => $email,
                'first_name' => $firstName,
                'last_name' => $lastName,
                'is_active' => $isActive
            ];
            $newUserId = db_insert('users', $user_data);
            if ($newUserId) {
                log_action($current_user['user_id'], $current_user['company_id'], 'USER_CREATED', 'user', $newUserId, null, $user_data, 'New user created by Admin.');
                redirect('index.php?page=users&message=User added successfully.');
            } else {
                throw new Exception("Failed to add user.");
            }
        } catch (Exception $e) {
            log_action($current_user['user_id'], $current_user['company_id'], 'USER_CREATION_FAILED', 'user', null, null, $user_data ?? $_POST, 'Failed to create user: ' . $e->getMessage());
            $error_message = $e->getMessage();
        }
    } elseif (isset($_POST['edit_user'])) {
        $userId = $_POST['user_id'];
        $update_data = [
            'username' => trim($_POST['username']),
            'email' => trim($_POST['email']),
            'first_name' => trim($_POST['first_name']),
            'last_name' => trim($_POST['last_name']),
            'role_id' => $_POST['role_id'],
            'is_active' => isset($_POST['is_active']) ? 1 : 0
        ];
        if (!empty($_POST['password'])) {
            $update_data['password_hash'] = hash_password($_POST['password']);
        }

        try {
            $oldUserData = get_user_by_id($userId);
            if (!$oldUserData || (int)$oldUserData['company_id'] !== (int)$current_user['company_id']) {
                throw new Exception("User not found or unauthorized access.");
            }
             // Check for duplicate username/email excluding current user
            $existingUser = db_fetch("SELECT user_id FROM users WHERE username = :username AND company_id = :company_id AND user_id != :user_id", ['username' => $update_data['username'], 'company_id' => $current_user['company_id'], 'user_id' => $userId]);
            if ($existingUser) {
                throw new Exception("Username already exists for this company.");
            }
            $existingEmail = db_fetch("SELECT user_id FROM users WHERE email = :email AND company_id = :company_id AND user_id != :user_id", ['email' => $update_data['email'], 'company_id' => $current_user['company_id'], 'user_id' => $userId]);
            if ($existingEmail) {
                throw new Exception("Email already exists for this company.");
            }

            if (db_update('users', $update_data, 'user_id', $userId)) {
                log_action($current_user['user_id'], $current_user['company_id'], 'USER_UPDATED', 'user', $userId, $oldUserData, $update_data, 'User data updated by Admin.');
                redirect('index.php?page=users&message=User updated successfully.');
            } else {
                throw new Exception("Failed to update user or no changes made.");
            }
        } catch (Exception $e) {
            log_action($current_user['user_id'], $current_user['company_id'], 'USER_UPDATE_FAILED', 'user', $userId, $oldUserData ?? null, $update_data, 'Failed to update user: ' . $e->getMessage());
            $error_message = $e->getMessage();
        }
    } elseif (isset($_POST['delete_user'])) {
        $userId = $_POST['user_id'];
        try {
            if ((int)$userId === (int)$current_user['user_id']) {
                throw new Exception("Cannot delete your own active user account.");
            }
            $oldUserData = get_user_by_id($userId);
            if (!$oldUserData || (int)$oldUserData['company_id'] !== (int)$current_user['company_id']) {
                throw new Exception("User not found or unauthorized access.");
            }
            if (db_delete('users', 'user_id', $userId)) {
                log_action($current_user['user_id'], $current_user['company_id'], 'USER_DELETED', 'user', $userId, $oldUserData, null, 'User deleted by Admin.');
                redirect('index.php?page=users&message=User deleted successfully.');
            } else {
                throw new Exception("Failed to delete user or user not found.");
            }
        } catch (Exception $e) {
            log_action($current_user['user_id'], $current_user['company_id'], 'USER_DELETION_FAILED', 'user', $userId, null, null, 'Failed to delete user: ' . $e->getMessage());
            $error_message = $e->getMessage();
        }
    }
}

// Fetch all roles for dropdown
$all_roles = db_fetch_all("SELECT * FROM roles");

// Fetch all users for the current admin's company
$users = get_all_users($current_user['company_id']);

?>

<div class="view" id="users-view">
    <h2>User Management</h2>

    <?php if (isset($error_message)): ?>
        <div class="alert error"><?= htmlspecialchars($error_message) ?></div>
    <?php endif; ?>

    <button class="btn btn-primary" onclick="showAddUserForm()">Add New User</button>

    <div id="add-user-form-container" style="display:none; margin-top: 1.5rem;">
        <h3>Add New User</h3>
        <form action="index.php?page=users" method="POST">
            <div class="form-group">
                <label for="new_username">Username:</label>
                <input type="text" id="new_username" name="username" required>
            </div>
            <div class="form-group">
                <label for="new_password">Password:</label>
                <input type="password" id="new_password" name="password" required>
            </div>
            <div class="form-group">
                <label for="new_email">Email:</label>
                <input type="email" id="new_email" name="email" required>
            </div>
            <div class="form-group">
                <label for="new_first_name">First Name:</label>
                <input type="text" id="new_first_name" name="first_name">
            </div>
            <div class="form-group">
                <label for="new_last_name">Last Name:</label>
                <input type="text" id="new_last_name" name="last_name">
            </div>
            <div class="form-group">
                <label for="new_role_id">Role:</label>
                <select id="new_role_id" name="role_id" required>
                    <?php foreach ($all_roles as $role): ?>
                        <option value="<?= $role['role_id'] ?>"><?= htmlspecialchars($role['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <input type="checkbox" id="new_is_active" name="is_active" checked>
                <label for="new_is_active">Is Active</label>
            </div>
            <button type="submit" name="add_user" class="btn btn-success">Add User</button>
            <button type="button" class="btn btn-secondary" onclick="hideAddUserForm()">Cancel</button>
        </form>
    </div>

    <div id="edit-user-form-container" style="display:none; margin-top: 1.5rem;">
        <h3>Edit User</h3>
        <form action="index.php?page=users" method="POST">
            <input type="hidden" id="edit_user_id" name="user_id">
            <div class="form-group">
                <label for="edit_username">Username:</label>
                <input type="text" id="edit_username" name="username" required>
            </div>
            <div class="form-group">
                <label for="edit_password">New Password (leave blank to keep current):</label>
                <input type="password" id="edit_password" name="password">
            </div>
            <div class="form-group">
                <label for="edit_email">Email:</label>
                <input type="email" id="edit_email" name="email" required>
            </div>
            <div class="form-group">
                <label for="edit_first_name">First Name:</label>
                <input type="text" id="edit_first_name" name="first_name">
            </div>
            <div class="form-group">
                <label for="edit_last_name">Last Name:</label>
                <input type="text" id="edit_last_name" name="last_name">
            </div>
            <div class="form-group">
                <label for="edit_role_id">Role:</label>
                <select id="edit_role_id" name="role_id" required>
                    <?php foreach ($all_roles as $role): ?>
                        <option value="<?= $role['role_id'] ?>"><?= htmlspecialchars($role['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <input type="checkbox" id="edit_is_active" name="is_active">
                <label for="edit_is_active">Is Active</label>
            </div>
            <button type="submit" name="edit_user" class="btn btn-primary">Update User</button>
            <button type="button" class="btn btn-secondary" onclick="hideEditUserForm()">Cancel</button>
        </form>
    </div>

    <h3 style="margin-top: 2rem;">All Users</h3>
    <table class="data-table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Username</th>
                <th>Email</th>
                <th>Name</th>
                <th>Role</th>
                <th>Active</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($users)): ?>
                <?php foreach ($users as $user_row): ?>
                    <tr>
                        <td><?= htmlspecialchars($user_row['user_id']) ?></td>
                        <td><?= htmlspecialchars($user_row['username']) ?></td>
                        <td><?= htmlspecialchars($user_row['email']) ?></td>
                        <td><?= htmlspecialchars($user_row['first_name'] . ' ' . $user_row['last_name']) ?></td>
                        <td><?= htmlspecialchars($user_row['role_name']) ?></td>
                        <td><?= $user_row['is_active'] ? 'Yes' : 'No' ?></td>
                        <td>
                            <button class="btn btn-secondary btn-sm" onclick="showEditUserForm(<?= htmlspecialchars(json_encode($user_row)) ?>)">Edit</button>
                            <form action="index.php?page=users" method="POST" style="display:inline-block;">
                                <input type="hidden" name="user_id" value="<?= $user_row['user_id'] ?>">
                                <button type="submit" name="delete_user" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this user?');">Delete</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr><td colspan="7">No users found.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<script>
function showAddUserForm() {
    document.getElementById('add-user-form-container').style.display = 'block';
    document.getElementById('edit-user-form-container').style.display = 'none';
}

function hideAddUserForm() {
    document.getElementById('add-user-form-container').style.display = 'none';
}

function showEditUserForm(user) {
    document.getElementById('edit_user_id').value = user.user_id;
    document.getElementById('edit_username').value = user.username;
    document.getElementById('edit_email').value = user.email;
    document.getElementById('edit_first_name').value = user.first_name;
    document.getElementById('edit_last_name').value = user.last_name;
    document.getElementById('edit_role_id').value = user.role_id;
    document.getElementById('edit_is_active').checked = user.is_active;
    document.getElementById('edit_password').value = ''; // Clear password field for security

    document.getElementById('edit-user-form-container').style.display = 'block';
    document.getElementById('add-user-form-container').style.display = 'none';
}

function hideEditUserForm() {
    document.getElementById('edit-user-form-container').style.display = 'none';
}
</script>

<?php
require_once __DIR__ . '/layout/footer.php';
?>
