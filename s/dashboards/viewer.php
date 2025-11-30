<?php
// dashboards/viewer.php
// The main dashboard for Viewers.
?>

<div class="dashboard-viewer">
    <h3>Invoice Report</h3>

    <div class="report-filters card">
        <h4>Filters</h4>
        <form id="viewer-filter-form">
            <div class="form-grid">
                <div class="form-group">
                    <label for="filter-start-date">Start Date</label>
                    <input type="date" id="filter-start-date" name="start_date">
                </div>
                <div class="form-group">
                    <label for="filter-end-date">End Date</label>
                    <input type="date" id="filter-end-date" name="end_date">
                </div>
                <div class="form-group">
                    <label for="filter-sr">Sales Rep</label>
                    <select id="filter-sr" name="sr_id"></select>
                </div>
                <div class="form-group">
                    <label for="filter-route">Route</label>
                    <select id="filter-route" name="route_id"></select>
                </div>
                 <div class="form-group">
                    <label for="filter-shop">Shop</label>
                    <select id="filter-shop" name="shop_id"></select>
                </div>
            </div>
            <button type="submit">Apply Filters</button>
            <button type="reset" id="reset-filters">Reset</button>
        </form>
    </div>

    <div class="report-summary card">
        <h4>Report Results</h4>
        <div id="viewer-invoice-list">
            <p>Apply filters to see a report.</p>
        </div>
    </div>
</div>

<style>
.form-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
    gap: 15px;
}
</style>
