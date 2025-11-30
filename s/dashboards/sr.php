<?php
// dashboards/sr.php
// The main dashboard for Sales Representatives.
?>

<div class="dashboard-sr">
    <h3>SR Dashboard</h3>

    <!-- Quick Actions -->
    <div class="quick-actions card">
        <h4>Quick Actions</h4>
        <button id="new-invoice-btn">Create New Invoice</button>
        <button id="submit-invoices-btn">Submit Confirmed Invoices</button>
    </div>

    <!-- Invoice Management Section -->
    <div class="invoices-section card">
        <h4>My Invoices</h4>
        <!-- Invoice list will be loaded here dynamically -->
        <div id="invoice-list">
            <p>Loading invoices...</p>
        </div>
    </div>

    <!-- Data Management Tabs -->
    <div class="management-tabs card">
        <h4>Manage Data</h4>
        <div class="tab-nav">
            <button class="tab-link active" data-tab="routes">Routes</button>
            <button class="tab-link" data-tab="shops">Shops</button>
            <button class="tab-link" data-tab="items">Items</button>
        </div>

        <!-- Routes Tab -->
        <div id="routes" class="tab-content active">
            <h5>Add New Route</h5>
            <form id="add-route-form">
                <div class="form-group">
                    <label for="route-name">Route Name</label>
                    <input type="text" id="route-name" name="name" required>
                </div>
                <button type="submit">Add Route</button>
            </form>
            <hr>
            <h5>Existing Routes</h5>
            <ul id="route-list"></ul>
        </div>

        <!-- Shops Tab -->
        <div id="shops" class="tab-content">
            <h5>Add New Shop</h5>
            <form id="add-shop-form">
                <div class="form-group">
                    <label for="shop-route">Select Route</label>
                    <select id="shop-route" name="route_id" required></select>
                </div>
                <div class="form-group">
                    <label for="shop-name">Shop Name</label>
                    <input type="text" id="shop-name" name="name" required>
                </div>
                <button type="submit">Add Shop</button>
            </form>
            <hr>
            <h5>Existing Shops</h5>
            <ul id="shop-list"></ul>
        </div>

        <!-- Items Tab -->
        <div id="items" class="tab-content">
            <h5>Add New Item</h5>
            <form id="add-item-form">
                <div class="form-group">
                    <label for="item-name">Item Name</label>
                    <input type="text" id="item-name" name="name" required>
                </div>
                <div class="form-group">
                    <label for="item-rate">Rate</label>
                    <input type="number" id="item-rate" name="rate" step="0.01" required>
                </div>
                <button type="submit">Add Item</button>
            </form>
            <hr>
            <h5>Existing Items</h5>
            <ul id="item-list"></ul>
        </div>
    </div>
</div>

<style>
/* Simple card styling */
.card {
    background: #fff;
    padding: 20px;
    border-radius: 8px;
    box-shadow: 0 2px 5px rgba(0,0,0,0.05);
    margin-bottom: 20px;
}
/* Tab styling */
.tab-nav { border-bottom: 1px solid #ddd; margin-bottom: 15px; }
.tab-link { background: none; border: none; padding: 10px 15px; cursor: pointer; }
.tab-link.active { border-bottom: 2px solid #3498db; font-weight: bold; }
.tab-content { display: none; }
.tab-content.active { display: block; }

/* Modal for New Invoice */
.modal {
    display: none; 
    position: fixed; 
    z-index: 1000; 
    left: 0;
    top: 0;
    width: 100%; 
    height: 100%; 
    overflow: auto; 
    background-color: rgba(0,0,0,0.4);
}
.modal-content {
    background-color: #fefefe;
    margin: 5% auto;
    padding: 20px;
    border: 1px solid #888;
    width: 80%;
    max-width: 700px;
    border-radius: 8px;
}
.close-btn {
    color: #aaa;
    float: right;
    font-size: 28px;
    font-weight: bold;
}
.close-btn:hover,
.close-btn:focus {
    color: black;
    text-decoration: none;
    cursor: pointer;
}
</style>

<!-- New Invoice Modal -->
<div id="new-invoice-modal" class="modal">
    <div class="modal-content">
        <span class="close-btn">&times;</span>
        <h3>Create New Invoice</h3>
        <form id="invoice-form">
            <!-- Invoice Header -->
            <div class="form-grid">
                <div class="form-group">
                    <label for="inv-route">Route</label>
                    <select id="inv-route" name="route_id" required></select>
                </div>
                <div class="form-group">
                    <label for="inv-shop">Shop</label>
                    <select id="inv-shop" name="shop_id" required></select>
                </div>
                <div class="form-group">
                    <label for="inv-order-date">Order Date</label>
                    <input type="date" id="inv-order-date" name="order_date" required>
                </div>
                <div class="form-group">
                    <label for="inv-delivery-date">Delivery Date</label>
                    <input type="date" id="inv-delivery-date" name="delivery_date" required>
                </div>
            </div>
            <div class="form-group">
                <label for="inv-remarks">Remarks</label>
                <textarea id="inv-remarks" name="remarks" rows="2"></textarea>
            </div>

            <!-- Invoice Items -->
            <h4>Invoice Items</h4>
            <div id="invoice-items-container">
                <!-- Item rows will be added here -->
            </div>
            <button type="button" id="add-item-row-btn">Add Item</button>
            <hr>

            <!-- Totals -->
            <div class="totals">
                <strong>Grand Total: <span id="grand-total">0.00</span></strong>
            </div>

            <hr>
            <button type="submit" name="action" value="save_draft">Save as Draft</button>
            <button type="submit" name="action" value="confirm_invoice">Confirm Invoice</button>
        </form>
    </div>
</div>

