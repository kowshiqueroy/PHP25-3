<?php
require_once(dirname(__FILE__) . '/../config/config.php');
require_once(dirname(__FILE__) . '/../includes/functions.php');

$user_role = isset($_SESSION['role_id']) ? $_SESSION['role_id'] : null;
?>
<div class="d-flex flex-column flex-shrink-0 p-3 text-white bg-dark sidebar" style="width: 280px;">
    <a href="/test/index.php" class="d-flex align-items-center mb-3 mb-md-0 me-md-auto text-white text-decoration-none">
        <i class="fas fa-cogs fa-fw me-2"></i>
        <span class="fs-4">Company CMS</span>
    </a>
    <hr>
    <ul class="nav nav-pills flex-column mb-auto">
        <li class="nav-item">
            <a href="/test/index.php" class="nav-link text-white">
                <i class="fas fa-tachometer-alt fa-fw me-2"></i>
                Dashboard
            </a>
        </li>
        <li>
            <a href="/test/profile.php" class="nav-link text-white">
                <i class="fas fa-user fa-fw me-2"></i>
                My Profile
            </a>
        </li>
        <?php if ($user_role == 1): // Superadmin ?>
            <li>
                <a href="/test/superadmin/manage_departments.php" class="nav-link text-white">
                    <i class="fas fa-building fa-fw me-2"></i>
                    Departments
                </a>
            </li>
            <li>
                <a href="/test/superadmin/manage_roles.php" class="nav-link text-white">
                    <i class="fas fa-user-tag fa-fw me-2"></i>
                    Roles
                </a>
            </li>
            <li>
                <a href="/test/superadmin/manage_users.php" class="nav-link text-white">
                    <i class="fas fa-users fa-fw me-2"></i>
                    Users
                </a>
            </li>
            <li>
                <a href="/test/superadmin/manage_settings.php" class="nav-link text-white">
                    <i class="fas fa-cog fa-fw me-2"></i>
                    Settings
                </a>
            </li>
        <?php endif; ?>
    </ul>
    <hr>
    <div class="dropdown">
        <a href="#" class="d-flex align-items-center text-white text-decoration-none dropdown-toggle" id="dropdownUser1" data-bs-toggle="dropdown" aria-expanded="false">
            <i class="fas fa-user-circle fa-fw me-2"></i>
            <strong><?php echo sanitize(get_user($_SESSION['user_id'])['username']); ?></strong>
        </a>
        <ul class="dropdown-menu dropdown-menu-dark text-small shadow" aria-labelledby="dropdownUser1">
            <li><a class="dropdown-item" href="/test/profile.php">Profile</a></li>
            <li><hr class="dropdown-divider"></li>
            <li><a class="dropdown-item" href="/test/logout.php">Sign out</a></li>
        </ul>
    </div>
</div>