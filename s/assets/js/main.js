// assets/js/main.js
document.addEventListener('DOMContentLoaded', function() {
    console.log('Sales Management System Initialized.');

    // --- General Login Form Handler (from previous step) ---
    const loginForm = document.getElementById('login-form');
    if (loginForm) {
        loginForm.addEventListener('submit', handleLogin);
    }

    // --- SR Dashboard Specific Logic ---
    if (document.querySelector('.dashboard-sr')) {
        console.log('SR Dashboard loaded.');
        initializeSrDashboard();
    }

    // --- Manager Dashboard Specific Logic ---
    if (document.querySelector('.dashboard-manager')) {
        console.log('Manager Dashboard loaded.');
        initializeManagerDashboard();
    }

    // --- Admin Dashboard Specific Logic ---
    if (document.querySelector('.dashboard-admin')) {
        console.log('Admin Dashboard loaded.');
        initializeAdminDashboard();
    }

    // --- Viewer Dashboard Specific Logic ---
    if (document.querySelector('.dashboard-viewer')) {
        console.log('Viewer Dashboard loaded.');
        initializeViewerDashboard();
    }
});

function initializeViewerDashboard() {
    populateViewerFilters();

    const filterForm = document.getElementById('viewer-filter-form');
    filterForm.addEventListener('submit', function(e) {
        e.preventDefault();
        fetchViewerInvoices();
    });

    document.getElementById('reset-filters').addEventListener('click', () => {
        filterForm.reset();
        fetchViewerInvoices();
    });
}

function populateViewerFilters() {
    // Fetch SRs
    fetch('api/data.php?type=sales_reps')
        .then(res => res.json()).then(data => {
            if(data.success) {
                const select = document.getElementById('filter-sr');
                select.innerHTML = '<option value="">All SRs</option>';
                data.data.forEach(sr => {
                    select.innerHTML += `<option value="${sr.id}">${sr.username}</option>`;
                });
            }
        });
    // Fetch Routes
    fetch('api/data.php?type=routes')
        .then(res => res.json()).then(data => {
            if(data.success) {
                const select = document.getElementById('filter-route');
                select.innerHTML = '<option value="">All Routes</option>';
                data.data.forEach(route => {
                    select.innerHTML += `<option value="${route.id}">${route.name}</option>`;
                });
            }
        });
    // Fetch Shops
    fetch('api/data.php?type=shops')
        .then(res => res.json()).then(data => {
            if(data.success) {
                const select = document.getElementById('filter-shop');
                select.innerHTML = '<option value="">All Shops</option>';
                data.data.forEach(shop => {
                    select.innerHTML += `<option value="${shop.id}">${shop.name}</option>`;
                });
            }
        });
}

function fetchViewerInvoices() {
    const container = document.getElementById('viewer-invoice-list');
    const form = document.getElementById('viewer-filter-form');
    const params = new URLSearchParams(new FormData(form)).toString();

    container.innerHTML = '<p>Loading report...</p>';

    fetch(`api/invoices.php?${params}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                renderViewerInvoiceList(data.data);
            } else {
                container.innerHTML = `<p>${data.message}</p>`;
            }
        });
}

function renderViewerInvoiceList(invoices) {
    const container = document.getElementById('viewer-invoice-list');
    container.innerHTML = '';

    if (invoices.length === 0) {
        container.innerHTML = '<p>No approved invoices match the criteria.</p>';
        return;
    }

    let totalAmount = 0;
    const table = document.createElement('table');
    table.className = 'invoice-table';
    table.innerHTML = `
        <thead>
            <tr>
                <th>ID</th>
                <th>Shop</th>
                <th>SR</th>
                <th>Delivery Date</th>
                <th>Total</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody></tbody>
    `;
    const tbody = table.querySelector('tbody');
    invoices.forEach(inv => {
        totalAmount += parseFloat(inv.grand_total);
        const row = tbody.insertRow();
        row.innerHTML = `
            <td>${inv.id}</td>
            <td>${inv.shop_name}</td>
            <td>${inv.sr_name}</td>
            <td>${inv.delivery_date}</td>
            <td>${inv.grand_total}</td>
            <td><a href="print_invoice.php?id=${inv.id}" target="_blank" class="print-btn">Print</a></td>
        `;
    });
    container.appendChild(table);
    container.innerHTML += `<div style="text-align: right; margin-top: 10px; font-weight: bold;">Total Amount: ${totalAmount.toFixed(2)}</div>`;
}

function initializeAdminDashboard() {
    // Tab navigation
    const tabLinks = document.querySelectorAll('.tab-link');
    const tabContents = document.querySelectorAll('.tab-content');
    tabLinks.forEach(link => {
        link.addEventListener('click', () => {
            const tab = link.dataset.tab;
            tabLinks.forEach(l => l.classList.remove('active'));
            link.classList.add('active');
            tabContents.forEach(c => c.classList.remove('active'));
            document.getElementById(tab).classList.add('active');
        });
    });

    // Form Handlers
    handleFormSubmission('#add-company-form', 'api/admin.php', { action: 'add_company' }, () => fetchAdminData());
    handleFormSubmission('#add-user-form', 'api/admin.php', { action: 'add_user' }, () => fetchAdminData());

    // Initial data fetch
    fetchAdminData();
    fetchLogs(); // Placeholder for now
}

function fetchAdminData() {
    // Fetch companies
    fetch('api/data.php?type=companies_admin')
        .then(response => response.json())
        .then(data => {
            const companyList = document.getElementById('company-list');
            const userCompanySelect = document.getElementById('user-company');
            companyList.innerHTML = '';
            userCompanySelect.innerHTML = '<option value="">Select Company</option>';
            if (data.success) {
                data.data.forEach(company => {
                    const div = document.createElement('div');
                    div.textContent = company.name;
                    companyList.appendChild(div);

                    const option = document.createElement('option');
                    option.value = company.id;
                    option.textContent = company.name;
                    userCompanySelect.appendChild(option);
                });
            }
        });

    // Fetch users
    fetch('api/data.php?type=users_admin')
        .then(response => response.json())
        .then(data => {
            const userList = document.getElementById('user-list');
            userList.innerHTML = '';
            if (data.success) {
                const table = document.createElement('table');
                table.className = 'invoice-table'; // Reuse style
                table.innerHTML = '<thead><tr><th>Username</th><th>Role</th><th>Company</th></tr></thead><tbody></tbody>';
                const tbody = table.querySelector('tbody');
                data.data.forEach(user => {
                    const row = tbody.insertRow();
                    row.innerHTML = `<td>${user.username}</td><td>${user.role}</td><td>${user.company_name}</td>`;
                });
                userList.appendChild(table);
            }
        });
}

function fetchLogs() {
    const logList = document.getElementById('log-list');
    if (!logList) return;

    fetch('api/logs.php')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                renderLogs(data.data);
            } else {
                logList.innerHTML = `<p>${data.message}</p>`;
            }
        })
        .catch(error => {
            logList.innerHTML = '<p>Error loading logs.</p>';
            console.error('Fetch Logs Error:', error);
        });
}

function renderLogs(logs) {
    const container = document.getElementById('log-list');
    container.innerHTML = '';

    if (logs.length === 0) {
        container.innerHTML = '<p>No system logs found.</p>';
        return;
    }

    const table = document.createElement('table');
    table.className = 'invoice-table'; // Reuse style
    table.innerHTML = `
        <thead>
            <tr>
                <th>Timestamp</th>
                <th>User</th>
                <th>Action</th>
                <th>Details</th>
            </tr>
        </thead>
        <tbody>
        </tbody>
    `;
    const tbody = table.querySelector('tbody');
    logs.forEach(log => {
        const row = tbody.insertRow();
        row.innerHTML = `
            <td>${log.timestamp}</td>
            <td>${log.username || 'System'}</td>
            <td>${log.action_type}</td>
            <td>${log.details}</td>
        `;
    });

    container.appendChild(table);
}


function initializeManagerDashboard() {
    fetchAndRenderManagerInvoices();
    fetchAndRenderAllManagerInvoices();
    fetchAndRenderPrintQueue();

    document.getElementById('approve-all-btn').addEventListener('click', handleApproveAll);
}

function fetchAndRenderPrintQueue() {
    const container = document.getElementById('invoice-print-queue');
    if (!container) return;

    // We can reuse the same API endpoint, as it already scopes by role
    fetch('api/invoices.php?queue=true')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                renderPrintQueue(data.data);
            } else {
                container.innerHTML = `<p>${data.message}</p>`;
            }
        });
}

function renderPrintQueue(invoices) {
    const container = document.getElementById('invoice-print-queue');
    container.innerHTML = '';

    if (invoices.length === 0) {
        container.innerHTML = '<p>The print queue is empty.</p>';
        return;
    }

    const table = document.createElement('table');
    table.className = 'invoice-table';
    table.innerHTML = `
        <thead>
            <tr>
                <th>Queue #</th>
                <th>Invoice ID</th>
                <th>Shop</th>
                <th>SR</th>
                <th>Submitted At</th>
            </tr>
        </thead>
        <tbody>
        </tbody>
    `;
    const tbody = table.querySelector('tbody');
    invoices.forEach(inv => {
        const row = tbody.insertRow();
        row.innerHTML = `
            <td>${inv.print_queue_order}</td>
            <td>${inv.id}</td>
            <td>${inv.shop_name}</td>
            <td>${inv.sr_name}</td>
            <td>${inv.submitted_at}</td>
        `;
    });

    container.appendChild(table);
}


function handleApproveAll() {
    const pendingButtons = document.querySelectorAll('#invoice-list-manager .approve-btn');
    const invoiceIds = Array.from(pendingButtons).map(btn => btn.dataset.id);

    if (invoiceIds.length === 0) {
        alert('No invoices to approve.');
        return;
    }

    if (!confirm(`Are you sure you want to approve all ${invoiceIds.length} pending invoices?`)) {
        return;
    }

    fetch(`api/invoices.php`, {
        method: 'PUT',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ invoice_ids: invoiceIds, status: 'Approved', action: 'bulk_update_status' })
    })
    .then(response => response.json())
    .then(data => {
        alert(data.message);
        if (data.success) {
            fetchAndRenderManagerInvoices();
            fetchAndRenderAllManagerInvoices();
        }
    })
    .catch(error => {
        alert('An error occurred during bulk approval.');
        console.error('Bulk Approve Error:', error);
    });
}


function fetchAndRenderAllManagerInvoices() {
    const container = document.getElementById('invoice-list-all');
    if (!container) return;

    fetch('api/invoices.php')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const manageableInvoices = data.data.filter(inv => inv.status !== 'Draft' && inv.status !== 'Confirmed');
                renderAllManagerInvoices(manageableInvoices);
            } else {
                container.innerHTML = `<p>${data.message}</p>`;
            }
        });
}

function renderAllManagerInvoices(invoices) {
    const container = document.getElementById('invoice-list-all');
    container.innerHTML = '';

    if (invoices.length === 0) {
        container.innerHTML = '<p>No other invoices to manage at this time.</p>';
        return;
    }

    const table = document.createElement('table');
    table.className = 'invoice-table';
    table.innerHTML = `
        <thead>
            <tr>
                <th>ID</th>
                <th>Shop</th>
                <th>Status</th>
                <th>Update Status</th>
            </tr>
        </thead>
        <tbody></tbody>
    `;
    const tbody = table.querySelector('tbody');
    const statusOptions = ['Approved', 'On Process', 'On Delivery', 'Delivered', 'Returned', 'Damaged'];

    invoices.forEach(inv => {
        let optionsHtml = '';
        statusOptions.forEach(opt => {
            const selected = inv.status === opt ? 'selected' : '';
            optionsHtml += `<option value="${opt}" ${selected}>${opt}</option>`;
        });

        const row = tbody.insertRow();
        row.innerHTML = `
            <td>${inv.id}</td>
            <td>${inv.shop_name}</td>
            <td><span class="status status-${inv.status.toLowerCase().replace(' ', '-')}">${inv.status}</span></td>
            <td>
                <select class="status-update-select" data-id="${inv.id}">
                    ${optionsHtml}
                </select>
            </td>
        `;
    });
    container.appendChild(table);

    // Add event listeners for the new dropdowns
    container.querySelectorAll('.status-update-select').forEach(select => {
        select.addEventListener('change', function() {
            handleApprovalAction(this.dataset.id, this.value);
        });
    });
}


function fetchAndRenderManagerInvoices() {
    const container = document.getElementById('invoice-list-manager');
    if (!container) return;

    // We can reuse the same API endpoint, as it already scopes by role
    fetch('api/invoices.php')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                renderManagerInvoiceList(data.data);
            } else {
                container.innerHTML = `<p>${data.message}</p>`;
            }
        });
}

function renderManagerInvoiceList(invoices) {
    const container = document.getElementById('invoice-list-manager');
    container.innerHTML = '';

    const pendingInvoices = invoices.filter(inv => inv.status === 'Confirmed');

    if (pendingInvoices.length === 0) {
        container.innerHTML = '<p>No invoices are currently awaiting approval.</p>';
        return;
    }

    const table = document.createElement('table');
    table.className = 'invoice-table';
    table.innerHTML = `
        <thead>
            <tr>
                <th>ID</th>
                <th>Shop</th>
                <th>SR</th>
                <th>Total</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
        </tbody>
    `;
    // Note: We need to get the SR's name. Let's assume the API will provide it.
    const tbody = table.querySelector('tbody');
    pendingInvoices.forEach(inv => {
        const row = tbody.insertRow();
        row.innerHTML = `
            <td>${inv.id}</td>
            <td>${inv.shop_name}</td>
            <td>${inv.sr_name || 'N/A'}</td>
            <td>${inv.grand_total}</td>
            <td><span class="status status-confirmed">${inv.status}</span></td>
            <td class="actions">
                <button class="approve-btn" data-id="${inv.id}">Approve</button>
                <button class="reject-btn" data-id="${inv.id}">Reject</button>
                <a href="print_invoice.php?id=${inv.id}" target="_blank" class="print-btn">Print</a>
            </td>
        `;
    });

    container.appendChild(table);

    // Add event listeners for the new buttons
    container.querySelectorAll('.approve-btn').forEach(btn => {
        btn.addEventListener('click', () => handleApprovalAction(btn.dataset.id, 'Approved'));
    });
    container.querySelectorAll('.reject-btn').forEach(btn => {
        btn.addEventListener('click', () => handleApprovalAction(btn.dataset.id, 'Rejected'));
    });
}

function handleApprovalAction(invoiceId, newStatus) {
    if (!confirm(`Are you sure you want to ${newStatus.toLowerCase().slice(0, -2)} this invoice?`)) {
        return;
    }

    fetch(`api/invoices.php`, {
        method: 'PUT',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ invoice_id: invoiceId, status: newStatus })
    })
    .then(response => response.json())
    .then(data => {
        alert(data.message);
        if (data.success) {
            fetchAndRenderManagerInvoices(); // Refresh the list
        }
    })
    .catch(error => {
        alert('An error occurred.');
        console.error('Approval Action Error:', error);
    });
}

function handleLogin(e) {
    e.preventDefault();
    const form = e.target;
    const formData = new FormData(form);
    const errorMessage = document.getElementById('error-message');

    fetch(form.action, {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            window.location.href = 'index.php';
        } else {
            errorMessage.textContent = data.message || 'An unknown error occurred.';
        }
    })
    .catch(error => {
        errorMessage.textContent = 'Failed to connect to the server. Please try again later.';
        console.error('Login Error:', error);
    });
}

function initializeSrDashboard() {
    // Tab navigation
    const tabLinks = document.querySelectorAll('.tab-link');
    const tabContents = document.querySelectorAll('.tab-content');

    tabLinks.forEach(link => {
        link.addEventListener('click', () => {
            const tab = link.dataset.tab;

            tabLinks.forEach(l => l.classList.remove('active'));
            link.classList.add('active');

            tabContents.forEach(c => c.classList.remove('active'));
            document.getElementById(tab).classList.add('active');
        });
    });

    // Form submission handlers
    handleFormSubmission('#add-route-form', 'api/data_management.php', { action: 'add_route' }, () => fetchRoutes());
    handleFormSubmission('#add-shop-form', 'api/data_management.php', { action: 'add_shop' }, () => fetchShops());
    handleFormSubmission('#add-item-form', 'api/data_management.php', { action: 'add_item' }, () => fetchItems());

    // Initial data fetch
    fetchRoutes();
    fetchShops();
    fetchItems();
    
    // --- Invoice Modal Logic ---
    const modal = document.getElementById('new-invoice-modal');
    const newInvoiceBtn = document.getElementById('new-invoice-btn');
    const closeBtn = modal.querySelector('.close-btn');

    newInvoiceBtn.onclick = () => {
        resetInvoiceForm();
        modal.style.display = 'block';
    };
    closeBtn.onclick = () => modal.style.display = 'none';
    window.onclick = (event) => {
        if (event.target == modal) {
            modal.style.display = 'none';
        }
    };

    // Dynamic shop dropdown based on route selection
    document.getElementById('inv-route').addEventListener('change', function() {
        const routeId = this.value;
        const shopSelect = document.getElementById('inv-shop');
        shopSelect.innerHTML = '<option value="">Loading...</option>';

        if (!routeId) {
            shopSelect.innerHTML = '<option value="">Select a route first</option>';
            return;
        }

        fetch(`api/data.php?type=shops&route_id=${routeId}`)
            .then(response => response.json())
            .then(data => {
                shopSelect.innerHTML = '<option value="">Select a shop</option>';
                if (data.success) {
                    data.data.forEach(shop => {
                        const option = document.createElement('option');
                        option.value = shop.id;
                        option.textContent = shop.name;
                        shopSelect.appendChild(option);
                    });
                }
            });
    });

    // Add item row to invoice
    document.getElementById('add-item-row-btn').addEventListener('click', addInvoiceItemRow);
    
    // Calculate totals on change
    document.getElementById('invoice-items-container').addEventListener('change', calculateInvoiceTotals);

    // Handle Invoice Form Submission
    document.getElementById('invoice-form').addEventListener('submit', handleInvoiceSubmission);

    // Fetch initial invoice list
    fetchInvoices();

    // Event listener for submitting invoices
    document.getElementById('submit-invoices-btn').addEventListener('click', handleSubmitInvoices);
}

function handleSubmitInvoices() {
    if (!confirm('Are you sure you want to submit all "Confirmed" invoices for printing? This action cannot be undone.')) {
        return;
    }

    fetch('api/invoices.php', {
        method: 'PUT',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ action: 'submit_for_printing' })
    })
    .then(res => res.json())
    .then(data => {
        alert(data.message);
        if (data.success) {
            fetchInvoices(); // Refresh the list
        }
    })
    .catch(error => {
        alert('An error occurred while submitting invoices.');
        console.error('Submit Invoices Error:', error);
    });
}

function fetchInvoices() {
    const invoiceListContainer = document.getElementById('invoice-list');
    if (!invoiceListContainer) return;

    fetch('api/invoices.php')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                renderInvoiceList(data.data);
            } else {
                invoiceListContainer.innerHTML = `<p>${data.message}</p>`;
            }
        })
        .catch(error => {
            invoiceListContainer.innerHTML = '<p>Error loading invoices.</p>';
            console.error('Fetch Invoices Error:', error);
        });
}

function renderInvoiceList(invoices) {
    const container = document.getElementById('invoice-list');
    container.innerHTML = '';

    if (invoices.length === 0) {
        container.innerHTML = '<p>No invoices found.</p>';
        return;
    }

    const table = document.createElement('table');
    table.className = 'invoice-table';
    table.innerHTML = `
        <thead>
            <tr>
                <th>ID</th>
                <th>Shop</th>
                <th>Delivery Date</th>
                <th>Total</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
        </tbody>
    `;
    const tbody = table.querySelector('tbody');
    invoices.forEach(inv => {
        const row = tbody.insertRow();
        row.innerHTML = `
            <td>${inv.id}</td>
            <td>${inv.shop_name}</td>
            <td>${inv.delivery_date}</td>
            <td>${inv.grand_total}</td>
            <td><span class="status status-${inv.status.toLowerCase()}">${inv.status}</span></td>
        `;
    });

    container.appendChild(table);
}

function handleInvoiceSubmission(e) {
    e.preventDefault();
    const form = e.target;
    const formData = new FormData(form);
    const submitter = e.submitter;
    formData.append('action', submitter.value);

    // Manually construct items array because FormData is tricky with complex names
    const items = [];
    const itemRows = document.querySelectorAll('.invoice-item-row');
    itemRows.forEach((row, index) => {
        const itemId = row.querySelector('.item-select').value;
        const quantity = row.querySelector('.item-quantity').value;
        const rate = row.querySelector('.item-rate').value;
        if (itemId && quantity && rate) {
            formData.append(`items[${index}][item_id]`, itemId);
            formData.append(`items[${index}][quantity]`, quantity);
            formData.append(`items[${index}][rate]`, rate);
        }
    });

    fetch('api/invoices.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        alert(data.message);
        if (data.success) {
            document.getElementById('new-invoice-modal').style.display = 'none';
            // Optionally, refresh the invoice list here
            // fetchInvoices(); 
        }
    })
    .catch(error => {
        alert('An error occurred while submitting the invoice.');
        console.error('Invoice Submission Error:', error);
    });
}

function resetInvoiceForm() {
    const form = document.getElementById('invoice-form');
    form.reset();
    document.getElementById('invoice-items-container').innerHTML = '';
    document.getElementById('grand-total').textContent = '0.00';

    // Set default dates
    const today = new Date();
    const tomorrow = new Date(today);
    tomorrow.setDate(tomorrow.getDate() + 1);
    
    document.getElementById('inv-order-date').value = today.toISOString().split('T')[0];
    document.getElementById('inv-delivery-date').value = tomorrow.toISOString().split('T')[0];

    // Add one item row to start
    addInvoiceItemRow();
}

let itemOptionsCache = null;
async function getItemOptions() {
    if (!itemOptionsCache) {
        const response = await fetch('api/data.php?type=items');
        const data = await response.json();
        if (data.success) {
            itemOptionsCache = data.data;
        } else {
            itemOptionsCache = [];
        }
    }
    return itemOptionsCache;
}

async function addInvoiceItemRow() {
    const items = await getItemOptions();
    const container = document.getElementById('invoice-items-container');
    const itemRow = document.createElement('div');
    itemRow.classList.add('invoice-item-row');
    
    let optionsHtml = '<option value="">Select Item</option>';
    items.forEach(item => {
        optionsHtml += `<option value="${item.id}" data-rate="${item.rate}">${item.name}</option>`;
    });

    itemRow.innerHTML = `
        <select name="items[][item_id]" class="item-select" required>${optionsHtml}</select>
        <input type="number" name="items[][quantity]" class="item-quantity" placeholder="Qty" min="1" required>
        <input type="number" name="items[][rate]" class="item-rate" placeholder="Rate" step="0.01" required readonly>
        <input type="text" class="item-total" placeholder="Total" readonly>
        <button type="button" class="remove-item-btn">Remove</button>
    `;
    container.appendChild(itemRow);

    // Add event listener for the new row
    const itemSelect = itemRow.querySelector('.item-select');
    itemSelect.addEventListener('change', function() {
        const selectedOption = this.options[this.selectedIndex];
        const rateInput = this.parentElement.querySelector('.item-rate');
        rateInput.value = selectedOption.dataset.rate || '0.00';
        calculateInvoiceTotals();
    });

    const removeBtn = itemRow.querySelector('.remove-item-btn');
    removeBtn.addEventListener('click', () => {
        itemRow.remove();
        calculateInvoiceTotals();
    });
}

function calculateInvoiceTotals() {
    let grandTotal = 0;
    const itemRows = document.querySelectorAll('.invoice-item-row');
    itemRows.forEach(row => {
        const quantity = parseFloat(row.querySelector('.item-quantity').value) || 0;
        const rate = parseFloat(row.querySelector('.item-rate').value) || 0;
        const total = quantity * rate;
        row.querySelector('.item-total').value = total.toFixed(2);
        grandTotal += total;
    });
    document.getElementById('grand-total').textContent = grandTotal.toFixed(2);
}

function handleFormSubmission(formSelector, apiUrl, hiddenData, callback) {
    const form = document.querySelector(formSelector);
    if (!form) return;

    form.addEventListener('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(form);
        for (const key in hiddenData) {
            formData.append(key, hiddenData[key]);
        }

        fetch(apiUrl, {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            alert(data.message);
            if (data.success) {
                form.reset();
                if (callback) callback();
            }
        })
        .catch(error => {
            alert('An error occurred.');
            console.error('Form Submission Error:', error);
        });
    });
}

// --- Data Fetching Functions ---

function fetchRoutes() {
    fetch('api/data.php?type=routes')
        .then(response => response.json())
        .then(data => {
            const routeList = document.getElementById('route-list');
            const shopRouteSelect = document.getElementById('shop-route');
            routeList.innerHTML = '';
            shopRouteSelect.innerHTML = '<option value="">Select a Route</option>';
            if (data.success) {
                data.data.forEach(route => {
                    const li = document.createElement('li');
                    li.textContent = route.name;
                    routeList.appendChild(li);

                    const option = document.createElement('option');
                    option.value = route.id;
                    option.textContent = route.name;
                    shopRouteSelect.appendChild(option);
                });
            }
        });
}

function fetchShops() {
    fetch('api/data.php?type=shops')
        .then(response => response.json())
        .then(data => {
            const shopList = document.getElementById('shop-list');
            shopList.innerHTML = '';
            if (data.success) {
                data.data.forEach(shop => {
                    const li = document.createElement('li');
                    li.textContent = `${shop.name} (Route: ${shop.route_name})`;
                    shopList.appendChild(li);
                });
            }
        });
}

function fetchItems() {
    fetch('api/data.php?type=items')
        .then(response => response.json())
        .then(data => {
            const itemList = document.getElementById('item-list');
            itemList.innerHTML = '';
            if (data.success) {
                data.data.forEach(item => {
                    const li = document.createElement('li');
                    li.textContent = `${item.name} - Rate: ${item.rate}`;
                    itemList.appendChild(li);
                });
            }
        });
}
