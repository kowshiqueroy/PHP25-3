<?php
// dashboards/admin.php
// The main dashboard for Admins.
?>

<div class="dashboard-admin">
    <h3>Admin Dashboard</h3>

    <div class="management-tabs card">
        <div class="tab-nav">
            <button class="tab-link active" data-tab="companies">Companies</button>
            <button class="tab-link" data-tab="users">Users</button>
            <button class="tab-link" data-tab="logs">System Logs</button>
        </div>

        <!-- Companies Tab -->
        <div id="companies" class="tab-content active">
            <h5>Add New Company</h5>
            <form id="add-company-form">
                <div class="form-group">
                    <label for="company-name">Company Name</label>
                    <input type="text" id="company-name" name="name" required>
                </div>
                <button type="submit">Add Company</button>
            </form>
            <hr>
            <h5>Existing Companies</h5>
            <div id="company-list"></div>
        </div>

        <!-- Users Tab -->
        <div id="users" class="tab-content">
            <h5>Add New User</h5>
            <form id="add-user-form">
                <div class="form-group">
                    <label for="user-company">Company</label>
                    <select id="user-company" name="company_id"></select>
                </div>
                <div class="form-group">
                    <label for="user-name">Username</label>
                    <input type="text" id="user-name" name="username" required>
                </div>
                <div class="form-group">
                    <label for="user-password">Password</label>
                    <input type="password" id="user-password" name="password" required>
                </div>
                <div class="form-group">
                    <label for="user-role">Role</label>
                    <select id="user-role" name="role" required>
                        <option value="Manager">Manager</option>
                        <option value="SR">SR</option>
                        <option value="Viewer">Viewer</option>
                    </select>
                </div>
                <button type="submit">Add User</button>
            </form>
            <hr>
            <h5>Existing Users</h5>
            <div id="user-list"></div>
        </div>

        <!-- System Logs Tab -->
        <div id="logs" class="tab-content">
            <h5>Filter Logs</h5>
            <!-- Add filter controls later -->
            <hr>
            <div id="log-list"></div>
        </div>
    </div>
</div>
