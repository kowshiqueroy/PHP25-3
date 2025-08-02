<?php
require_once '../config/config.php';
require_once '../includes/functions.php';

require_login();

if (!has_role(1)) {
    header('Location: ../index.php');
    exit;
}

include '../templates/header.php';
?>

<div class="d-flex">
    <?php include '../templates/sidebar.php'; ?>

    <div class="content flex-grow-1">
        <?php include '../templates/topbar.php'; ?>

        <main class="main-content container-fluid">
            <h1 class="mb-4">Superadmin Dashboard</h1>

            <div class="row">
                <div class="col-md-4 mb-4">
                    <div class="card h-100">
                        <div class="card-header"><i class="fas fa-building me-2"></i>Departments</div>
                        <div class="card-body d-flex flex-column justify-content-between">
                            <p class="card-text">Manage company departments.</p>
                            <a href="manage_departments.php" class="btn btn-primary mt-auto">Go to Departments <i class="fas fa-arrow-right ms-2"></i></a>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="card h-100">
                        <div class="card-header"><i class="fas fa-user-tag me-2"></i>Roles</div>
                        <div class="card-body d-flex flex-column justify-content-between">
                            <p class="card-text">Define and manage user roles.</p>
                            <a href="manage_roles.php" class="btn btn-primary mt-auto">Go to Roles <i class="fas fa-arrow-right ms-2"></i></a>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="card h-100">
                        <div class="card-header"><i class="fas fa-users me-2"></i>Users</div>
                        <div class="card-body d-flex flex-column justify-content-between">
                            <p class="card-text">Manage user accounts and assignments.</p>
                            <a href="manage_users.php" class="btn btn-primary mt-auto">Go to Users <i class="fas fa-arrow-right ms-2"></i></a>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="card h-100">
                        <div class="card-header"><i class="fas fa-cog me-2"></i>Settings</div>
                        <div class="card-body d-flex flex-column justify-content-between">
                            <p class="card-text">Configure global system settings.</p>
                            <a href="manage_settings.php" class="btn btn-primary mt-auto">Go to Settings <i class="fas fa-arrow-right ms-2"></i></a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card mt-4">
                <div class="card-header"><i class="fas fa-list me-2"></i>Current Departments</div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Name</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $sql = "SELECT * FROM departments";
                                $result = $conn->query($sql);

                                if ($result->num_rows > 0) {
                                    while ($row = $result->fetch_assoc()) {
                                        echo "<tr>";
                                        echo "<td>" . sanitize($row['id']) . "</td>";
                                        echo "<td>" . sanitize($row['name']) . "</td>";
                                        echo "</tr>";
                                    }
                                } else {
                                    echo "<tr><td colspan=\"2\">No departments found.</td></tr>";
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