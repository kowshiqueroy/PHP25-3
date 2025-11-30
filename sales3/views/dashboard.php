<?php
// views/dashboard.php
require_once __DIR__ . '/layout/header.php';

// Quick actions based on role
$quick_actions = [];
if (has_role(ROLE_ADMIN)) {
    $quick_actions[] = ['label' => 'Add New User', 'page' => 'users_add', 'class' => 'btn-primary'];
    $quick_actions[] = ['label' => 'View System Logs', 'page' => 'logs', 'class' => 'btn-secondary'];
}
if (has_role(ROLE_SALES_REP)) {
    $quick_actions[] = ['label' => 'Create New Invoice', 'page' => 'invoices_create', 'class' => 'btn-primary'];
    $quick_actions[] = ['label' => 'Record Cash Collection', 'page' => 'cash_collections_create', 'class' => 'btn-primary'];
}
if (has_role(ROLE_MANAGER)) {
    $quick_actions[] = ['label' => 'Review Approvals', 'page' => 'approvals', 'class' => 'btn-primary'];
    $quick_actions[] = ['label' => 'Print Invoice Summary', 'page' => 'reports', 'class' => 'btn-secondary'];
}

?>

<div class="view" id="dashboard-view">
    <h2>Welcome, <?= htmlspecialchars($current_user['username']) ?>!</h2>
    <p>Your role: <strong><?= htmlspecialchars($current_user['role_name']) ?></strong></p>

    <?php if (!empty($quick_actions)): ?>
        <h3>Quick Actions</h3>
        <div class="quick-actions">
            <?php foreach ($quick_actions as $action): ?>
                <a href="index.php?page=<?= $action['page'] ?>" class="btn <?= $action['class'] ?>"><?= $action['label'] ?></a>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <!-- Add more dashboard content here, e.g., summaries, pending tasks -->
    <h3>Summary</h3>
    <p>This section will contain dynamic summaries based on your role, such as:</p>
    <ul>
        <?php if (has_role(ROLE_SALES_REP)): ?>
            <li>Your recent invoices</li>
            <li>Your pending cash collections</li>
        <?php endif; ?>
        <?php if (has_role(ROLE_MANAGER)): ?>
            <li>Invoices awaiting approval</li>
            <li>Cash collections awaiting approval</li>
        <?php endif; ?>
        <?php if (has_role(ROLE_ADMIN)): ?>
            <li>System overview</li>
        <?php endif; ?>
    </ul>
</div>

<?php
require_once __DIR__ . '/layout/footer.php';
?>
