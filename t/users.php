<?php include_once 'header.php'; ?>


<?php

if (isset($_POST['change_pass'])) {
    $old_pass = $_POST['old_pass'];
    $new_pass = $_POST['new_pass'];

    $stmt = $conn->prepare("SELECT password FROM users WHERE id = ?");
    $stmt->bind_param("i", $_SESSION['user_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    if (password_verify($old_pass, $row['password'])) {
        $hashed = password_hash($new_pass, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
        $stmt->bind_param("si", $hashed, $_SESSION['user_id']);
        $stmt->execute();
        echo "<script>alert('Password changed!');</script>";
    } else {
        echo "<script>alert('Old password is incorrect!');</script>";
    }
}
if (isset($_POST['add_user'])) {
    $username = $_POST['new_user'];
    $password = $_POST['new_pass'];

    if (strlen($username) < 3 || strlen($username) > 10) {
        echo "<script>alert('Username must be 3-10 characters.');</script>";
    } elseif (strlen($password) < 3) {
        echo "<script>alert('Password must be at least 3 characters.');</script>";
    } else {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("INSERT INTO users (username, password) VALUES (?, ?)");
        $stmt->bind_param("ss", $username, $hashed_password);
        try {
            $stmt->execute();
            echo "<script>alert('User added successfully!');</script>";
        } catch (mysqli_sql_exception $e) {
            if ($e->getCode() == 1062) {
                echo "<script>alert('Duplicate entry for username: $username');</script>";
            } else {
                echo "<script>alert('Error adding user: " . $e->getMessage() . "');</script>";
            }
        }
        $stmt->close();
    }
}
if (isset($_GET['toggle'])) {
    $user_id = $_GET['toggle'];

    // Fetch current status
    $stmt = $conn->prepare("SELECT status FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    //echo "<script>alert('Current status: " . $row['status'] . "');</script>";
    
    // Toggle status
    $new_status = ($row['status'] === 'active') ? 'inactive' : 'active';
   // echo "<script>alert('New status: " . $new_status . "');</script>";
    
    // Update status in the database
    $stmt = $conn->prepare("UPDATE users SET status = ? WHERE id = ?");
    $stmt->bind_param("si", $new_status, $user_id);
    if ($stmt->execute()) {
        echo "<script>alert('User status changed to {$new_status}!');</script>";
    } else {
        echo "<script>alert('Error changing user status: " . $conn->error . "');</script>";
    }
    $stmt->close();
}


?>





<style>
    :root {
        --primary-color: #1a73e8;
        --secondary-color: #2c3e50;
        --tertiary-color: #f8f9fc;
        --quaternary-color: #f39c12;
    }


    .flex-container {
        display: flex;
        flex-wrap: wrap;
        gap: 2rem;
        justify-content: center;
        margin-bottom: 3rem;
    }

    .card {
        background: #fff;
        border-radius: 1rem;
        padding: 2.5rem;
        box-shadow: 0 10px 30px rgba(0,0,0,0.08);
        border: 1px solid #e0e0e0;
        width: 100%;
        max-width: 480px;
    }

    .card h2 {
        font-size: 1.75rem;
        font-weight: 700;
        margin-bottom: 2rem;
        text-align: center;
        color: var(--secondary-color);
        border-bottom: 2px solid var(--primary-color);
        padding-bottom: 0.5rem;
    }

    form label {
        display: block;
        font-weight: 600;
        margin-bottom: 0.5rem;
        color: #34495e;
    }

    form input[type="text"],
    form input[type="password"] {
        width: 100%;
        padding: 0.75rem 1rem;
        border: 1px solid #ced4da;
        border-radius: 0.5rem;
        font-size: 1rem;
        background-color: #fefefe;
        margin-bottom: 1.5rem;
        transition: all 0.25s ease;
    }

    form input[type="text"]:focus,
    form input[type="password"]:focus {
        border-color: var(--primary-color);
        box-shadow: 0 0 0 0.25rem rgba(26, 115, 232, 0.2);
    }

    form input[type="submit"] {
        background-color: var(--primary-color);
        color: white;
        border: none;
        padding: 0.75rem 1.25rem;
        font-size: 1rem;
        font-weight: 600;
        border-radius: 0.5rem;
        cursor: pointer;
        transition: background-color 0.3s ease;
        width: 100%;
    }

    form input[type="submit"]:hover {
        background-color: #1666cc;
    }

    h2 + table {
        max-width: 1000px;
        margin: auto;
        margin-top: 2rem;
        border-radius: 1rem;
        overflow-x: auto;
        box-shadow: 0 8px 30px rgba(0, 0, 0, 0.06);
        background-color: #fff;
        font-size: 0.95rem;
        font-family: 'Segoe UI', 'Roboto', sans-serif;
    }

    table {
        width: 100%;
        border-collapse: collapse;
    }

    th, td {
        padding: 1rem;
        text-align: left;
        border-bottom: 1px solid #ebebeb;
    }

    th {
        background-color: #f5f8fc;
        font-weight: 600;
        color: var(--secondary-color);
        border-bottom: 2px solid #e3e3e3;
    }

    tr:hover {
        background-color: #f0f7ff;
    }

    .btn-edit {
        background-color: var(--quaternary-color);
        color: white;
        padding: 0.4rem 0.8rem;
        font-size: 0.85rem;
        border-radius: 0.375rem;
        text-decoration: none;
        font-weight: 500;
        transition: background-color 0.2s ease;
    }

    .btn-edit:hover {
        background-color: #d68910;
    }

    @media screen and (max-width: 768px) {
        th, td {
            padding: 0.75rem;
            font-size: 0.9rem;
        }

        .btn-edit {
            padding: 0.35rem 0.6rem;
        }
    }
</style>

<div class="flex-container">
    <div class="card">
        <h2>Change Password</h2>
        <form action="users.php" method="post">
            <label for="old_pass">Old Password</label>
            <input type="password" name="old_pass" id="old_pass" required>

            <label for="new_pass">New Password</label>
            <input type="password" name="new_pass" id="new_pass" required>

            <input type="submit" name="change_pass" value="Change Password">
        </form>
    </div>
    <div class="card">
        <h2>Add New User</h2>
        <form action="users.php" method="post">
            <label for="new_user">Username</label>
            <input type="text" name="new_user" id="new_user" required>

            <label for="new_user_pass">Password</label>
            <input type="password" name="new_pass" id="new_user_pass" required>

            <input type="submit" name="add_user" value="Add New User">
        </form>
    </div>
</div>

<h2 style="text-align: center;">Users</h2>
<table>
    <thead>
        <tr>
            <th>Username</th>
            <th>Status</th>
            <th>Edit</th>
        </tr>
    </thead>
    <tbody>
        <?php
        $res = $conn->query("SELECT * FROM users");
        if ($res) {
            while ($row = $res->fetch_assoc()) {
                echo '<tr>';
                echo '<td>' . htmlspecialchars($row['username']) . '</td>';
                echo '<td>' . htmlspecialchars($row['status']) . '</td>';
                echo '<td><a href="users.php?toggle=' . $row['id'] . '" class="btn-edit">Edit</a></td>';
                echo '</tr>';
            }
        }
        ?>
    </tbody>
</table>




<?php include_once 'footer.php'; ?>
