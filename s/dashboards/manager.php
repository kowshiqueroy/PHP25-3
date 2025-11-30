<?php
// dashboards/manager.php
// The main dashboard for Managers.
?>

<div class="dashboard-manager">
    <h3>Manager Dashboard</h3>

    <div class="quick-actions card">
        <h4>Quick Actions</h4>
        <button id="approve-all-btn">Approve All Pending</button>
    </div>

    <div class="invoices-section card">
        <h4>Print Queue</h4>
        <a href="print_queue.php" target="_blank" class="print-btn" style="float: right;">Print All from Queue</a>
        <div id="invoice-print-queue">
            <p>Loading print queue...</p>
        </div>
    </div>

    <!-- Invoices Awaiting Approval -->
    <div class="invoices-section card">
        <h4>Invoices Awaiting Approval</h4>
        <div id="invoice-list-manager">
            <p>Loading pending invoices...</p>
        </div>
    </div>

    <div class="invoices-section card">
        <h4>Manage All Invoices</h4>
        <div id="invoice-list-all">
            <p>Loading all invoices...</p>
        </div>
    </div>

    <!-- Potentially other manager-specific sections later -->

</div>
