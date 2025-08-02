<?php
require_once 'config/config.php';
require_once 'includes/functions.php';

require_login();

$user_id = $_SESSION['user_id'];
$user = get_user($user_id);

include 'templates/header.php';
?>

<div class="d-flex">
    <?php include 'templates/sidebar.php'; ?>

    <div class="content flex-grow-1">
        <?php include 'templates/topbar.php'; ?>

        <main class="main-content container-fluid">
            <h1 class="mb-4">My Profile</h1>

            <div class="card">
                <div class="card-header"><i class="fas fa-user-circle me-2"></i>My Information</div>
                <div class="card-body">
                    <p class="mb-2"><strong>Username:</strong> <?php echo sanitize($user['username']); ?></p>
                    <p class="mb-2"><strong>Department:</strong> <?php echo sanitize(get_department_name($user['department_id'])); ?></p>
                    <p class="mb-2"><strong>Role:</strong> <?php echo sanitize(get_role_name($user['role_id'])); ?></p>
                </div>
            </div>
        </main>
    </div>
</div>

<?php include 'templates/footer.php'; ?>