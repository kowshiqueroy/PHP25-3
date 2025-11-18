<?php
include 'header.php';
?>

            <main class="content-area">
                
                <h1 class="page-title">Company Overview</h1>
                
                <div class="stats-grid">
                    <div class="card stat-card">
                        <span class="stat-title">Total Revenue</span>
                        <span class="stat-value">$84,250</span>
                        <span class="stat-trend trend-up">↗ 12.5% vs last month</span>
                    </div>
                    <div class="card stat-card">
                        <span class="stat-title">Active Orders</span>
                        <span class="stat-value">142</span>
                        <span class="stat-trend" style="color: var(--text-secondary);">→ Stable</span>
                    </div>
                    <div class="card stat-card">
                        <span class="stat-title">Team Utilization</span>
                        <span class="stat-value">87%</span>
                        <span class="stat-trend trend-up">↗ High Efficiency</span>
                    </div>
                </div>

                <div class="card">
                    <h4 style="margin-bottom: 1rem;">Quick Filters</h4>
                    <div class="input-group">
                        <div>
                            <label style="font-size:0.85rem; font-weight:500; margin-bottom:0.5rem; display:block;">Search</label>
                            <input type="text" class="form-control" placeholder="Filter by name or ID...">
                        </div>
                        <div>
                            <label style="font-size:0.85rem; font-weight:500; margin-bottom:0.5rem; display:block;">Department</label>
                            <select class="form-control">
                                <option>All Departments</option>
                                <option>Engineering</option>
                                <option>Marketing</option>
                            </select>
                        </div>
                        <div>
                            <button class="btn btn-primary" style="width: 100%;">Apply Filter</button>
                        </div>
                    </div>
                </div>

                <div class="card printable-content">
                    
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
                        <div>
                            <h2 style="font-size: 1.25rem; font-weight: 700;">Employee Performance Report</h2>
                            <p style="color: var(--text-secondary); font-size: 0.9rem; margin-top: 0.25rem;">Generated on Nov 2023</p>
                        </div>
                        <button onclick="window.print()" class="btn btn-ghost no-print" style="border: 1px solid var(--border-color);">
                            🖨 Print Report
                        </button>
                    </div>

                    <div class="table-responsive">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Employee</th>
                                    <th>Role</th>
                                    <th>Department</th>
                                    <th>Projects</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td style="font-weight: 500;">Sarah Connor</td>
                                    <td>Lead Developer</td>
                                    <td>Engineering</td>
                                    <td>12</td>
                                    <td><span class="badge badge-success">Active</span></td>
                                </tr>
                                <tr>
                                    <td style="font-weight: 500;">James Bond</td>
                                    <td>Security Analyst</td>
                                    <td>Operations</td>
                                    <td>4</td>
                                    <td><span class="badge badge-warning">On Leave</span></td>
                                </tr>
                                <tr>
                                    <td style="font-weight: 500;">Ellen Ripley</td>
                                    <td>Logistics Manager</td>
                                    <td>Shipping</td>
                                    <td>8</td>
                                    <td><span class="badge badge-success">Active</span></td>
                                </tr>
                                <tr>
                                    <td style="font-weight: 500;">Tony Stark</td>
                                    <td>R&D Director</td>
                                    <td>Innovation</td>
                                    <td>25</td>
                                    <td><span class="badge badge-success">Active</span></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    
                    <div style="margin-top: 2rem; border-top: 1px solid #eee; padding-top: 1rem; font-size: 0.8rem; color: #666;">
                        Confidential Document - Internal Use Only
                    </div>
                </div>
                </main>
 <?php
include 'footer.php';
?>     