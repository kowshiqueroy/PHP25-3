<?php
require '../config/db.php';
require 'includes/header.php'; // Loads CSS, Sidebar, Navbar

// Dashboard Stats Logic
$total_students = $pdo->query("SELECT COUNT(*) FROM users WHERE role='student'")->fetchColumn();
$pending_apps = $pdo->query("SELECT COUNT(*) FROM admission_applications WHERE status='pending'")->fetchColumn();
$total_notices = $pdo->query("SELECT COUNT(*) FROM notices")->fetchColumn();
?>

<div style="margin-bottom: 20px;">
    <h2>Dashboard Overview</h2>
</div>

<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 20px; margin-bottom: 30px;">
    
    <div class="card" style="border-left: 4px solid var(--primary);">
        <div style="display: flex; justify-content: space-between; align-items: center;">
            <div>
                <p style="color: #64748b; margin: 0;">Total Students</p>
                <h2 style="margin: 5px 0; font-size: 2rem;"><?php echo $total_students; ?></h2>
            </div>
            <i class="fas fa-users" style="font-size: 2.5rem; color: #e2e8f0;"></i>
        </div>
    </div>

    <div class="card" style="border-left: 4px solid #f59e0b;">
        <div style="display: flex; justify-content: space-between; align-items: center;">
            <div>
                <p style="color: #64748b; margin: 0;">Pending Admissions</p>
                <h2 style="margin: 5px 0; font-size: 2rem;"><?php echo $pending_apps; ?></h2>
            </div>
            <i class="fas fa-user-clock" style="font-size: 2.5rem; color: #e2e8f0;"></i>
        </div>
    </div>

    <div class="card" style="border-left: 4px solid #3b82f6;">
        <div style="display: flex; justify-content: space-between; align-items: center;">
            <div>
                <p style="color: #64748b; margin: 0;">Published Notices</p>
                <h2 style="margin: 5px 0; font-size: 2rem;"><?php echo $total_notices; ?></h2>
            </div>
            <i class="fas fa-bullhorn" style="font-size: 2.5rem; color: #e2e8f0;"></i>
        </div>
    </div>

</div>

<div class="card">
    <h3>Quick Actions</h3>
    <p>Select a module from the sidebar to start managing the content.</p>
</div>

<?php require 'includes/footer.php'; ?>