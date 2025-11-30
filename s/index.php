<?php
// index.php
require_once 'templates/header.php';

// --- Authentication Check ---
// If the user is not logged in, redirect to the login page.
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// --- Role-Based Dashboard Router ---
$user_role = $_SESSION['user_role'] ?? 'Viewer';

echo "<h1>Welcome, " . htmlspecialchars($_SESSION['username']) . "!</h1>";
echo "<h2>Your Role: " . htmlspecialchars($user_role) . "</h2>";

// Depending on the user's role, display the appropriate dashboard content.
// For now, we will just show a placeholder.
// In the future, we will include files like 'dashboard_admin.php', 'dashboard_manager.php', etc.

switch ($user_role) {
    case 'Admin':
        echo "<p>This is the Admin dashboard. You can manage companies, users, and system settings.</p>";
        include 'dashboards/admin.php';
        break;
    case 'Manager':
        echo "<p>This is the Manager dashboard. You can approve invoices and manage company data.</p>";
        include 'dashboards/manager.php';
        break;
    case 'SR':
        echo "<p>This is the Sales Representative dashboard. You can create invoices and manage your sales.</p>";
        include 'dashboards/sr.php';
        break;
    case 'Viewer':
        echo "<p>This is the Viewer dashboard. You have read-only access to reports and summaries.</p>";
        include 'dashboards/viewer.php';
        break;
    default:
        echo "<p>Invalid role assigned. Please contact an administrator.</p>";
        break;
}

// A simple logout link for convenience
echo '<p><a href="logout.php">Logout</a></p>';


require_once 'templates/footer.php';
?>
