<?php
require_once '../config/config.php';
require_once '../includes/functions.php';

require_login();

if (has_role(1)) { // Superadmin
    header('Location: ../superadmin/index.php');
    exit;
}

include '../templates/header.php';
?>

<div class="d-flex">
    <?php include '../templates/sidebar.php'; ?>

    <div class="content flex-grow-1">
        <?php include '../templates/topbar.php'; ?>

        <main class="main-content container-fluid">
            <h1 class="mb-4">Department Dashboard</h1>

            <div class="card mb-4">
                <div class="card-header"><i class="fas fa-info-circle me-2"></i>Department Information</div>
                <div class="card-body">
                    <?php
                    $user_id = $_SESSION['user_id'];
                    $user = get_user($user_id);
                    $department_id = $user['department_id'];

                    $sql = "SELECT * FROM departments WHERE id = ?";
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param('i', $department_id);
                    $stmt->execute();
                    $result = $stmt->get_result();
                    $department = $result->fetch_assoc();

                    echo "<p><strong>Department:</strong> " . $department['name'] . "</p>";

                    if ($department['name'] == 'Sales') {
                        include '../modules/sales/index.php';
                    }
                    ?>
                </div>
            </div>

            <div class="card">
                <div class="card-header"><i class="fas fa-users me-2"></i>Department Members</div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Username</th>
                                    <th>Role</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $sql = "SELECT users.id, users.username, roles.name AS role_name FROM users LEFT JOIN roles ON users.role_id = roles.id WHERE users.department_id = ?";
                                $stmt = $conn->prepare($sql);
                                $stmt->bind_param('i', $department_id);
                                $stmt->execute();
                                $result = $stmt->get_result();

                                if ($result->num_rows > 0) {
                                    while ($row = $result->fetch_assoc()) {
                                        echo "<tr>";
                                        echo "<td>" . sanitize($row['id']) . "</td>";
                                        echo "<td>" . sanitize($row['username']) . "</td>";
                                        echo "<td>" . sanitize($row['role_name']) . "</td>";
                                        echo "</tr>";
                                    }
                                } else {
                                    echo "<tr><td colspan=\"3\">No members found in this department.</td></tr>";
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<?php include '../templates/footer.php'; ?>