<?php
require_once 'config/config.php';
require_once 'includes/functions.php';

require_login();

include 'templates/header.php';
?>

<div class="d-flex">
    <?php include 'templates/sidebar.php'; ?>

    <div class="content flex-grow-1">
        <?php include 'templates/topbar.php'; ?>

        <main class="main-content container-fluid">
            <h1 class="mb-4">Welcome to the Company Management System</h1>
            <p class="lead">This is the main dashboard. Select a module from the sidebar to get started.</p>
        </main>
    </div>
</div>

<?php include 'templates/footer.php'; ?>