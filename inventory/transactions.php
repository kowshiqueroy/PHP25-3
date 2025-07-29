<?php
require_once 'includes/auth_check.php';
require_once 'includes/db_connect.php';

check_login();
check_role(['Manager', 'Data Entry', 'QC']);

$conn = connect_db();

// Fetch products for dropdowns (used for initial product search)
$products = [];
$result_products = $conn->query("SELECT id, name, sku FROM products");
if ($result_products->num_rows > 0) {
    while ($row = $result_products->fetch_assoc()) {
        $products[] = $row;
    }
}

$conn->close();

$user_role_name = get_user_role_name($_SESSION['role_id']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Transactions - <?php echo APP_NAME; ?></title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="#"><?php echo APP_NAME; ?></a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                    <li class="nav-item">
                        <a class="nav-link" href="dashboard.php">Dashboard</a>
                    </li>
                    <?php if ($user_role_name == 'Admin' || $user_role_name == 'Manager'): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="user_management.php">User Management</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="store_management.php">Store Management</a>
                    </li>
                    <?php endif; ?>
                    <?php if ($user_role_name == 'Admin' || $user_role_name == 'Manager' || $user_role_name == 'Data Entry'): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="product_management.php">Product Management</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="category_management.php">Category Management</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="product_batch_management.php">Product Batch Management</a>
                    </li>
                    <?php endif; ?>
                    <?php if ($user_role_name == 'Admin' || $user_role_name == 'Manager' || $user_role_name == 'Data Entry' || $user_role_name == 'QC'): ?>
                    <li class="nav-item">
                        <a class="nav-link active" aria-current="page" href="transactions.php">Transactions</a>
                    </li>
                    <?php endif; ?>
                    <?php if ($user_role_name == 'Admin' || $user_role_name == 'Manager' || $user_role_name == 'QC'): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="qc_workflow.php">QC Workflow</a>
                    </li>
                    <?php endif; ?>
                    <?php if ($user_role_name == 'Admin' || $user_role_name == 'Manager' || $user_role_name == 'Viewer'): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="reports.php">Reports</a>
                    </li>
                    <?php endif; ?>
                </ul>
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <span class="navbar-text me-3">
                            Logged in as: <?php echo htmlspecialchars($_SESSION['username']); ?> (<?php echo htmlspecialchars($user_role_name); ?>)
                        </span>
                    </li>
                    <li class="nav-item">
                        <a class="btn btn-outline-light" href="logout.php">Logout</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <h2>POS-Style Transactions</h2>

        <?php if (isset($_SESSION['message'])): ?>
            <div class="alert alert-<?php echo $_SESSION['message_type']; ?> alert-dismissible fade show" role="alert">
                <?php echo $_SESSION['message']; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            <?php 
            unset($_SESSION['message']);
            unset($_SESSION['message_type']);
            ?>
        <?php endif; ?>

        <ul class="nav nav-tabs" id="transactionTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="in-tab" data-bs-toggle="tab" data-bs-target="#in" type="button" role="tab" aria-controls="in" aria-selected="true">IN (New Stock)</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="out-tab" data-bs-toggle="tab" data-bs-target="#out" type="button" role="tab" aria-controls="out" aria-selected="false">OUT (Dispatch)</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="return-store-tab" data-bs-toggle="tab" data-bs-target="#return-store" type="button" role="tab" aria-controls="return-store" aria-selected="false">Return to Store</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="return-supplier-tab" data-bs-toggle="tab" data-bs-target="#return-supplier" type="button" role="tab" aria-controls="return-supplier" aria-selected="false">Return to Supplier</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="damaged-tab" data-bs-toggle="tab" data-bs-target="#damaged" type="button" role="tab" aria-controls="damaged" aria-selected="false">Mark as Damaged</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="expiry-isolation-tab" data-bs-toggle="tab" data-bs-target="#expiry-isolation" type="button" role="tab" aria-controls="expiry-isolation" aria-selected="false">Expiry Isolation</button>
            </li>
        </ul>
        <div class="tab-content" id="transactionTabsContent">
            <!-- IN (New Stock Entry) Tab -->
            <div class="tab-pane fade show active" id="in" role="tabpanel" aria-labelledby="in-tab">
                <div class="card mt-3">
                    <div class="card-header">New Stock Entry (IN)</div>
                    <div class="card-body">
                        <form action="process_transaction.php" method="POST">
                            <input type="hidden" name="transaction_type" value="IN">
                            
                            <div id="in_product_items_container">
                                <!-- Product items will be added here dynamically -->
                            </div>
                            <button type="button" class="btn btn-info btn-sm mt-3" id="add_in_product_item">Add Product Item</button>
                            <hr>
                            <div class="mb-3">
                                <label for="in_comments" class="form-label">Comments</label>
                                <textarea class="form-control" id="in_comments" name="comments" rows="3"></textarea>
                            </div>
                            <div class="mb-3">
                                <label for="in_person_name" class="form-label">Person Name / To Whom</label>
                                <input type="text" class="form-control" id="in_person_name" name="person_name">
                            </div>
                            <div class="mb-3">
                                <label for="in_contact_text" class="form-label">Contact Text</label>
                                <input type="text" class="form-control" id="in_contact_text" name="contact_text">
                            </div>
                            <div class="mb-3">
                                <label for="in_slip_number" class="form-label">Slip Number</label>
                                <input type="text" class="form-control" id="in_slip_number" name="slip_number">
                            </div>
                            <button type="submit" class="btn btn-success">Record IN Transaction</button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- OUT (Dispatch to Usage) Tab -->
            <div class="tab-pane fade" id="out" role="tabpanel" aria-labelledby="out-tab">
                <div class="card mt-3">
                    <div class="card-header">Dispatch to Usage (OUT)</div>
                    <div class="card-body">
                        <form action="process_transaction.php" method="POST">
                            <input type="hidden" name="transaction_type" value="OUT">
                            
                            <div id="out_product_items_container">
                                <!-- Product items will be added here dynamically -->
                            </div>
                            <button type="button" class="btn btn-info btn-sm mt-3" id="add_out_product_item">Add Product Item</button>
                            <hr>
                            <button type="submit" class="btn btn-danger">Record OUT Transaction</button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Return to Store Tab -->
            <div class="tab-pane fade" id="return-store" role="tabpanel" aria-labelledby="return-store-tab">
                <div class="card mt-3">
                    <div class="card-header">Return to Store</div>
                    <div class="card-body">
                        <form action="process_transaction.php" method="POST">
                            <input type="hidden" name="transaction_type" value="Return to Store">
                            <div class="mb-3">
                                <label for="return_store_product_search" class="form-label">Product Search (Name or SKU)</label>
                                <input type="text" class="form-control" id="return_store_product_search" placeholder="Start typing product name or SKU...">
                                <input type="hidden" id="return_store_product_id" name="product_id" required>
                                <div id="return_store_product_suggestions" class="list-group position-absolute w-50" style="z-index: 1000;"></div>
                            </div>
                            <div class="mb-3">
                                <label for="return_store_batch_search" class="form-label">Select Batch</label>
                                <input type="text" class="form-control" id="return_store_batch_search" placeholder="Search for batch (e.g., by expiry date, location)...">
                                <input type="hidden" id="return_store_batch_id" name="batch_id" required>
                                <div id="return_store_batch_suggestions" class="list-group position-absolute w-50" style="z-index: 1000;"></div>
                            </div>
                            <div class="mb-3">
                                <label for="return_store_quantity" class="form-label">Quantity</label>
                                <input type="number" class="form-control" id="return_store_quantity" name="quantity" required min="1">
                            </div>
                            <div class="mb-3">
                                <label for="return_store_comments" class="form-label">Comments</label>
                                <textarea class="form-control" id="return_store_comments" name="comments" rows="3"></textarea>
                            </div>
                            <div class="mb-3">
                                <label for="return_store_person_name" class="form-label">Person Name / To Whom</label>
                                <input type="text" class="form-control" id="return_store_person_name" name="person_name">
                            </div>
                            <div class="mb-3">
                                <label for="return_store_contact_text" class="form-label">Contact Text</label>
                                <input type="text" class="form-control" id="return_store_contact_text" name="contact_text">
                            </div>
                            <div class="mb-3">
                                <label for="return_store_slip_number" class="form-label">Slip Number</label>
                                <input type="text" class="form-control" id="return_store_slip_number" name="slip_number">
                            </div>
                            <button type="submit" class="btn btn-info">Record Return to Store Transaction</button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Return to Supplier Tab -->
            <div class="tab-pane fade" id="return-supplier" role="tabpanel" aria-labelledby="return-supplier-tab">
                <div class="card mt-3">
                    <div class="card-header">Return to Supplier</div>
                    <div class="card-body">
                        <form action="process_transaction.php" method="POST">
                            <input type="hidden" name="transaction_type" value="Return to Supplier">
                            <div class="mb-3">
                                <label for="return_supplier_product_search" class="form-label">Product Search (Name or SKU)</label>
                                <input type="text" class="form-control" id="return_supplier_product_search" placeholder="Start typing product name or SKU...">
                                <input type="hidden" id="return_supplier_product_id" name="product_id" required>
                                <div id="return_supplier_product_suggestions" class="list-group position-absolute w-50" style="z-index: 1000;"></div>
                            </div>
                            <div class="mb-3">
                                <label for="return_supplier_batch_search" class="form-label">Select Batch</label>
                                <input type="text" class="form-control" id="return_supplier_batch_search" placeholder="Search for batch (e.g., by expiry date, location)...">
                                <input type="hidden" id="return_supplier_batch_id" name="batch_id" required>
                                <div id="return_supplier_batch_suggestions" class="list-group position-absolute w-50" style="z-index: 1000;"></div>
                            </div>
                            <div class="mb-3">
                                <label for="return_supplier_quantity" class="form-label">Quantity</label>
                                <input type="number" class="form-control" id="return_supplier_quantity" name="quantity" required min="1">
                            </div>
                            <div class="mb-3">
                                <label for="return_supplier_comments" class="form-label">Comments</label>
                                <textarea class="form-control" id="return_supplier_comments" name="comments" rows="3"></textarea>
                            </div>
                            <div class="mb-3">
                                <label for="return_supplier_person_name" class="form-label">Person Name / To Whom</label>
                                <input type="text" class="form-control" id="return_supplier_person_name" name="person_name">
                            </div>
                            <div class="mb-3">
                                <label for="return_supplier_contact_text" class="form-label">Contact Text</label>
                                <input type="text" class="form-control" id="return_supplier_contact_text" name="contact_text">
                            </div>
                            <div class="mb-3">
                                <label for="return_supplier_slip_number" class="form-label">Slip Number</label>
                                <input type="text" class="form-control" id="return_supplier_slip_number" name="slip_number">
                            </div>
                            <button type="submit" class="btn btn-secondary">Record Return to Supplier Transaction</button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Mark as Damaged Tab -->
            <div class="tab-pane fade" id="damaged" role="tabpanel" aria-labelledby="damaged-tab">
                <div class="card mt-3">
                    <div class="card-header">Mark as Damaged</div>
                    <div class="card-body">
                        <form action="process_transaction.php" method="POST">
                            <input type="hidden" name="transaction_type" value="Mark as Damaged">
                            <div class="mb-3">
                                <label for="damaged_product_search" class="form-label">Product Search (Name or SKU)</label>
                                <input type="text" class="form-control" id="damaged_product_search" placeholder="Start typing product name or SKU...">
                                <input type="hidden" id="damaged_product_id" name="product_id" required>
                                <div id="damaged_product_suggestions" class="list-group position-absolute w-50" style="z-index: 1000;"></div>
                            </div>
                            <div class="mb-3">
                                <label for="damaged_batch_search" class="form-label">Select Batch</label>
                                <input type="text" class="form-control" id="damaged_batch_search" placeholder="Search for batch (e.g., by expiry date, location)...">
                                <input type="hidden" id="damaged_batch_id" name="batch_id" required>
                                <div id="damaged_batch_suggestions" class="list-group position-absolute w-50" style="z-index: 1000;"></div>
                            </div>
                            <div class="mb-3">
                                <label for="damaged_quantity" class="form-label">Quantity</label>
                                <input type="number" class="form-control" id="damaged_quantity" name="quantity" required min="1">
                            </div>
                            <div class="mb-3">
                                <label for="damaged_comments" class="form-label">Comments</label>
                                <textarea class="form-control" id="damaged_comments" name="comments" rows="3"></textarea>
                            </div>
                            <div class="mb-3">
                                <label for="damaged_person_name" class="form-label">Person Name / To Whom</label>
                                <input type="text" class="form-control" id="damaged_person_name" name="person_name">
                            </div>
                            <div class="mb-3">
                                <label for="damaged_contact_text" class="form-label">Contact Text</label>
                                <input type="text" class="form-control" id="damaged_contact_text" name="contact_text">
                            </div>
                            <div class="mb-3">
                                <label for="damaged_slip_number" class="form-label">Slip Number</label>
                                <input type="text" class="form-control" id="damaged_slip_number" name="slip_number">
                            </div>
                            <button type="submit" class="btn btn-warning">Record Damaged Transaction</button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Expiry Isolation Tab -->
            <div class="tab-pane fade" id="expiry-isolation" role="tabpanel" aria-labelledby="expiry-isolation-tab">
                <div class="card mt-3">
                    <div class="card-header">Expiry Isolation</div>
                    <div class="card-body">
                        <form action="process_transaction.php" method="POST">
                            <input type="hidden" name="transaction_type" value="Expiry Isolation">
                            <div class="mb-3">
                                <label for="expiry_isolation_product_search" class="form-label">Product Search (Name or SKU)</label>
                                <input type="text" class="form-control" id="expiry_isolation_product_search" placeholder="Start typing product name or SKU...">
                                <input type="hidden" id="expiry_isolation_product_id" name="product_id" required>
                                <div id="expiry_isolation_product_suggestions" class="list-group position-absolute w-50" style="z-index: 1000;"></div>
                            </div>
                            <div class="mb-3">
                                <label for="expiry_isolation_batch_search" class="form-label">Select Batch</label>
                                <input type="text" class="form-control" id="expiry_isolation_batch_search" placeholder="Search for batch (e.g., by expiry date, location)...">
                                <input type="hidden" id="expiry_isolation_batch_id" name="batch_id" required>
                                <div id="expiry_isolation_batch_suggestions" class="list-group position-absolute w-50" style="z-index: 1000;"></div>
                            </div>
                            <div class="mb-3">
                                <label for="expiry_isolation_quantity" class="form-label">Quantity</label>
                                <input type="number" class="form-control" id="expiry_isolation_quantity" name="quantity" required min="1">
                            </div>
                            <div class="mb-3">
                                <label for="expiry_isolation_comments" class="form-label">Comments</label>
                                <textarea class="form-control" id="expiry_isolation_comments" name="comments" rows="3"></textarea>
                            </div>
                            <div class="mb-3">
                                <label for="expiry_isolation_person_name" class="form-label">Person Name / To Whom</label>
                                <input type="text" class="form-control" id="expiry_isolation_person_name" name="person_name">
                            </div>
                            <div class="mb-3">
                                <label for="expiry_isolation_contact_text" class="form-label">Contact Text</label>
                                <input type="text" class="form-control" id="expiry_isolation_contact_text" name="contact_text">
                            </div>
                            <div class="mb-3">
                                <label for="expiry_isolation_slip_number" class="form-label">Slip Number</label>
                                <input type="text" class="form-control" id="expiry_isolation_slip_number" name="slip_number">
                            </div>
                            <button type="submit" class="btn btn-dark">Record Expiry Isolation Transaction</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Generic function for product search
            function setupProductSearch(searchInputId, productIdInputId, suggestionsDivId) {
                const searchInput = document.getElementById(searchInputId);
                const productIdInput = document.getElementById(productIdInputId);
                const suggestionsDiv = document.getElementById(suggestionsDivId);

                searchInput.addEventListener('input', function() {
                    const query = this.value;
                    if (query.length > 2) {
                        fetch('api/product_search.php?q=' + query)
                            .then(response => response.json())
                            .then(data => {
                                suggestionsDiv.innerHTML = '';
                                if (data.length > 0) {
                                    data.forEach(product => {
                                        const item = document.createElement('a');
                                        item.href = '#';
                                        item.classList.add('list-group-item', 'list-group-item-action');
                                        item.textContent = product.name + ' (SKU: ' + product.sku + ')';
                                        item.addEventListener('click', function(e) {
                                            e.preventDefault();
                                            searchInput.value = product.name + ' (SKU: ' + product.sku + ')';
                                            productIdInput.value = product.id;
                                            suggestionsDiv.innerHTML = '';
                                        });
                                        suggestionsDiv.appendChild(item);
                                    });
                                } else {
                                    suggestionsDiv.innerHTML = '<div class="list-group-item">No products found.</div>';
                                }
                            })
                            .catch(error => console.error('Error fetching products:', error));
                    } else {
                        suggestionsDiv.innerHTML = '';
                    }
                });

                // Clear suggestions when clicking outside
                document.addEventListener('click', function(event) {
                    if (!searchInput.contains(event.target) && !suggestionsDiv.contains(event.target)) {
                        suggestionsDiv.innerHTML = '';
                    }
                });
            }

            // Generic function for product batch search
            function setupBatchSearch(batchSearchInputId, batchIdInputId, batchSuggestionsDivId, productIdInputId, isLowExpiry = false) {
                const batchSearchInput = document.getElementById(batchSearchInputId);
                const batchIdInput = document.getElementById(batchIdInputId);
                const batchSuggestionsDiv = document.getElementById(batchSuggestionsDivId);
                const productIdInput = document.getElementById(productIdInputId);

                batchSearchInput.addEventListener('input', function() {
                    const query = this.value;
                    const productId = productIdInput.value;
                    const storeId = <?php echo $_SESSION['store_id']; ?>;

                    if (query.length > 2 || productId || isLowExpiry) { // Search by query, product, or if low expiry
                        let url = 'api/product_batch_search.php?q=' + query + '&store_id=' + storeId;
                        if (productId) {
                            url += '&product_id=' + productId;
                        }
                        if (isLowExpiry) {
                            url += '&low_expiry_alert=true';
                        }
                        
                        fetch(url)
                            .then(response => response.json())
                            .then(data => {
                                batchSuggestionsDiv.innerHTML = '';
                                if (data.length > 0) {
                                    data.forEach(batch => {
                                        const item = document.createElement('a');
                                        item.href = '#';
                                        item.classList.add('list-group-item', 'list-group-item-action');
                                        item.textContent = `Batch ID: ${batch.id} | Product: ${batch.product_name} | Qty: ${batch.quantity} | Exp: ${batch.expiry_date || 'N/A'} | Loc: ${batch.storage_location}`;
                                        item.addEventListener('click', function(e) {
                                            e.preventDefault();
                                            batchSearchInput.value = `Batch ID: ${batch.id} | Product: ${batch.product_name} | Qty: ${batch.quantity} | Exp: ${batch.expiry_date || 'N/A'} | Loc: ${batch.storage_location}`;
                                            batchIdInput.value = batch.id;
                                            // Optionally update product_id if not already set
                                            if (!productIdInput.value) {
                                                productIdInput.value = batch.product_id;
                                                document.getElementById(searchInputId).value = batch.product_name + ' (SKU: ' + batch.sku + ')';
                                            }
                                            batchSuggestionsDiv.innerHTML = '';
                                        });
                                        batchSuggestionsDiv.appendChild(item);
                                    });
                                } else {
                                    batchSuggestionsDiv.innerHTML = '<div class="list-group-item">No batches found.</div>';
                                }
                            })
                            .catch(error => console.error('Error fetching product batches:', error));
                    } else {
                        batchSuggestionsDiv.innerHTML = '';
                    }
                });

                // Clear suggestions when clicking outside
                document.addEventListener('click', function(event) {
                    if (!batchSearchInput.contains(event.target) && !batchSuggestionsDiv.contains(event.target)) {
                        batchSuggestionsDiv.innerHTML = '';
                    }
                });
            }

            // Setup for IN tab (product search only)
            setupProductSearch('in_product_search', 'in_product_id', 'in_product_suggestions');

            // Setup for OUT tab
            setupProductSearch('out_product_search', 'out_product_id', 'out_product_suggestions');
            setupBatchSearch('out_batch_search', 'out_batch_id', 'out_batch_suggestions', 'out_product_id');
            setupBatchSearch('out_low_expiry_batch_search', 'out_low_expiry_batch_id', 'out_low_expiry_batch_suggestions', 'out_product_id', true);

            const outProductIdInput = document.getElementById('out_product_id');
            const outSubstituteSuggestionsDiv = document.getElementById('out_substitute_suggestions');
            const outSubstituteList = document.getElementById('out_substitute_list');

            outProductIdInput.addEventListener('change', function() {
                const productId = this.value;
                const storeId = <?php echo $_SESSION['store_id']; ?>;

                if (productId) {
                    fetch(`api/product_substitute_search.php?product_id=${productId}&store_id=${storeId}`)
                        .then(response => response.json())
                        .then(data => {
                            outSubstituteList.innerHTML = '';
                            if (data.length > 0) {
                                outSubstituteSuggestionsDiv.classList.remove('d-none');
                                data.forEach(product => {
                                    const listItem = document.createElement('li');
                                    listItem.textContent = `${product.name} (SKU: ${product.sku}) - Available: ${product.total_quantity || 0}`;
                                    outSubstituteList.appendChild(listItem);
                                });
                            } else {
                                outSubstituteSuggestionsDiv.classList.add('d-none');
                            }
                        })
                        .catch(error => console.error('Error fetching substitute products:', error));
                } else {
                    outSubstituteSuggestionsDiv.classList.add('d-none');
                }
            });

            // Setup for Return to Store tab
            setupProductSearch('return_store_product_search', 'return_store_product_id', 'return_store_product_suggestions');
            setupBatchSearch('return_store_batch_search', 'return_store_batch_id', 'return_store_batch_suggestions', 'return_store_product_id');

            // Setup for Return to Supplier tab
            setupProductSearch('return_supplier_product_search', 'return_supplier_product_id', 'return_supplier_product_suggestions');
            setupBatchSearch('return_supplier_batch_search', 'return_supplier_batch_id', 'return_supplier_batch_suggestions', 'return_supplier_product_id');

            // Setup for Mark as Damaged tab
            setupProductSearch('damaged_product_search', 'damaged_product_id', 'damaged_product_suggestions');
            setupBatchSearch('damaged_batch_search', 'damaged_batch_id', 'damaged_batch_suggestions', 'damaged_product_id');

            // Setup for Expiry Isolation tab
            setupProductSearch('expiry_isolation_product_search', 'expiry_isolation_product_id', 'expiry_isolation_product_suggestions');
            setupBatchSearch('expiry_isolation_batch_search', 'expiry_isolation_batch_id', 'expiry_isolation_batch_suggestions', 'expiry_isolation_product_id');
        });
    </script>
</body>
</html>