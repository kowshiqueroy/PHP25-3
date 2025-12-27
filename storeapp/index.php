<?php session_start(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>POS System</title>
    <style>
        /* Mobile First CSS */
        :root { --primary: #2563eb; --bg: #f3f4f6; }
        body { font-family: system-ui, sans-serif; background: var(--bg); margin: 0; padding: 10px; }
        .container { max-width: 600px; margin: 0 auto; background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
        .form-group { margin-bottom: 15px; }
        label { display: block; font-weight: bold; margin-bottom: 5px; font-size: 0.9rem; }
        input, select, textarea { width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; }
        .row { display: flex; gap: 10px; }
        .col { flex: 1; }
        button { width: 100%; padding: 12px; background: var(--primary); color: white; border: none; border-radius: 4px; font-size: 1rem; cursor: pointer; }
        button:disabled { background: #ccc; }
        .status-bar { position: fixed; top: 0; left: 0; right: 0; padding: 5px; text-align: center; color: white; font-size: 0.8rem; display: none; }
        .offline { background: #dc2626; display: block; }
        .syncing { background: #eab308; display: block; }
        .online { background: #16a34a; display: block; }
        
        /* Auto-suggestions list styling */
        datalist { max-height: 150px; overflow-y: auto; }
    </style>
</head>
<body>

<div id="statusIndicator" class="status-bar online">Online</div>

<div class="container" id="loginScreen">
    <h2>Login</h2>
    <input type="text" id="loginUser" placeholder="Username" class="form-group">
    <input type="password" id="loginPass" placeholder="Password" class="form-group">
    <button onclick="login()">Login</button>
</div>

<div class="container" id="appScreen" style="display:none;">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:15px;">
        <h3>New Transaction</h3>
        <a href="reports.php" style="color:var(--primary);">📊 Reports</a>
    </div>

    <form id="posForm">
        <div class="row form-group">
            <div class="col">
                <label>Shop</label>
                <select id="shop_id" required></select>
            </div>
            <div class="col">
                <label>Date</label>
                <input type="date" id="actual_date" required>
            </div>
        </div>

        <div class="form-group">
            <label>Product Type</label>
            <input type="text" id="prod_type" list="list_types" onchange="filterNames()" placeholder="Type or Select">
            <datalist id="list_types"></datalist>
        </div>

        <div class="form-group">
            <label>Product Name</label>
            <input type="text" id="prod_name" list="list_names" onchange="filterUnits()" placeholder="Type or Select">
            <datalist id="list_names"></datalist>
        </div>

        <div class="row form-group">
            <div class="col">
                <label>Unit</label>
                <input type="text" id="unit" list="list_units">
                <datalist id="list_units"></datalist>
            </div>
            <div class="col">
                <label>Slip No</label>
                <input type="text" id="slip_no">
            </div>
        </div>

        <div class="form-group">
            <label>From/To (Party)</label>
            <input type="text" id="party_name" list="list_parties">
            <datalist id="list_parties"></datalist>
        </div>

        <div class="row form-group">
            <div class="col">
                <label>Action</label>
                <select id="txn_type" onchange="toggleQty()">
                    <option value="in">IN (Purchase/Return)</option>
                    <option value="out">OUT (Sell/Use)</option>
                </select>
            </div>
            <div class="col">
                <label>Quantity</label>
                <input type="number" id="qty" step="0.01" required oninput="calcTotal()">
            </div>
        </div>

        <div class="form-group">
            <label>Reason</label>
            <select id="reason">
                <option value="Purchase">New Purchase</option>
                <option value="Return">Return</option>
                <option value="Sell">Sell</option>
                <option value="Requisition">Requisition</option>
            </select>
        </div>

        <div class="row form-group">
            <div class="col">
                <label>Rate</label>
                <input type="number" id="rate" step="0.01" oninput="calcTotal()">
            </div>
            <div class="col">
                <label>Total Price</label>
                <input type="number" id="total_price" readonly>
            </div>
        </div>

        <div class="form-group">
            <label>Remarks</label>
            <textarea id="remarks" rows="2"></textarea>
        </div>

        <button type="button" onclick="saveTransaction()">Save Transaction</button>
    </form>
    <div id="queueMsg" style="margin-top:10px; color:#666; font-size:0.8rem;"></div>
</div>

<script>
    // --- State Management ---
    let dbData = { types: [], names: [], units: [], parties: [], shops: [] };
    const offlineQueue = JSON.parse(localStorage.getItem('posQueue')) || [];

    // --- Init ---
    window.onload = function() {
        if(<?php echo isset($_SESSION['user_id']) ? 'true' : 'false'; ?>) {
            showApp();
        }
        document.getElementById('actual_date').valueAsDate = new Date();
        updateStatus();
    };

    window.addEventListener('online', syncData);
    window.addEventListener('offline', updateStatus);

    // --- Auth ---
    async function login() {
        const u = document.getElementById('loginUser').value;
        const p = document.getElementById('loginPass').value;
        const res = await fetch('api.php?action=login', {
            method: 'POST', body: JSON.stringify({username:u, password:p})
        });
        const data = await res.json();
        if(data.status === 'success') {
            location.reload(); // Reload to start PHP session context
        } else {
            alert(data.message);
        }
    }

    function showApp() {
        document.getElementById('loginScreen').style.display = 'none';
        document.getElementById('appScreen').style.display = 'block';
        fetchMetadata();
        setInterval(syncData, 10000); // Try sync every 10s
    }

    // --- Data Logic ---
    async function fetchMetadata() {
        try {
            const res = await fetch('api.php?action=fetch_metadata');
            dbData = await res.json();
            populateDatalists();
            
            // Populate Shops
            const shopSel = document.getElementById('shop_id');
            shopSel.innerHTML = '';
            dbData.shops.forEach(s => {
                let opt = document.createElement('option');
                opt.value = s.id;
                opt.text = s.name;
                shopSel.appendChild(opt);
            });
        } catch (e) { console.log("Offline mode: using cached lists if available"); }
    }

    function populateDatalists() {
        fillList('list_types', dbData.types);
        fillList('list_parties', dbData.parties);
    }

    function fillList(id, arr) {
        const dl = document.getElementById(id);
        dl.innerHTML = '';
        arr.forEach(item => {
            let op = document.createElement('option');
            op.value = item;
            dl.appendChild(op);
        });
    }

    function filterNames() {
        const type = document.getElementById('prod_type').value;
        const filtered = dbData.names.filter(x => x.prod_type === type).map(x => x.prod_name);
        fillList('list_names', filtered);
    }
    
    function filterUnits() {
        const name = document.getElementById('prod_name').value;
        const filtered = dbData.units.filter(x => x.prod_name === name).map(x => x.unit);
        fillList('list_units', filtered);
    }

    function calcTotal() {
        const qty = parseFloat(document.getElementById('qty').value) || 0;
        const rate = parseFloat(document.getElementById('rate').value) || 0;
        document.getElementById('total_price').value = (qty * rate).toFixed(2);
    }

    // --- Save & Sync ---
    function saveTransaction() {
        const type = document.getElementById('txn_type').value;
        const qty = document.getElementById('qty').value;
        
        const txn = {
            temp_id: Date.now() + Math.random().toString(), // Unique ID for sync
            shop_id: document.getElementById('shop_id').value,
            actual_date: document.getElementById('actual_date').value,
            prod_type: document.getElementById('prod_type').value,
            prod_name: document.getElementById('prod_name').value,
            unit: document.getElementById('unit').value,
            party_name: document.getElementById('party_name').value,
            qty_in: type === 'in' ? qty : 0,
            qty_out: type === 'out' ? qty : 0,
            reason: document.getElementById('reason').value,
            handled_by: 'Current User', // Simplified
            slip_no: document.getElementById('slip_no').value,
            location: '', 
            rate: document.getElementById('rate').value,
            total_price: document.getElementById('total_price').value,
            extra_charge: 0,
            condition_text: '',
            remarks: document.getElementById('remarks').value
        };

        // Offline First Approach: Always save to Queue first
        offlineQueue.push(txn);
        localStorage.setItem('posQueue', JSON.stringify(offlineQueue));
        
        // Reset Form
        document.getElementById('posForm').reset();
        document.getElementById('actual_date').valueAsDate = new Date();
        alert('Saved locally! Will sync when online.');
        
        updateStatus();
        if(navigator.onLine) syncData();
    }

    async function syncData() {
        updateStatus();
        if (!navigator.onLine || offlineQueue.length === 0) return;

        const indicator = document.getElementById('statusIndicator');
        indicator.innerText = "Syncing...";
        indicator.className = "status-bar syncing";

        try {
            const res = await fetch('api.php?action=sync', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify(offlineQueue)
            });
            const result = await res.json();
            
            if (result.status === 'success') {
                // Clear queue
                offlineQueue.length = 0;
                localStorage.setItem('posQueue', JSON.stringify(offlineQueue));
                updateStatus();
            }
        } catch (e) {
            console.error("Sync failed", e);
        }
    }

    function updateStatus() {
        const el = document.getElementById('statusIndicator');
        const count = offlineQueue.length;
        document.getElementById('queueMsg').innerText = `Pending Uploads: ${count}`;
        
        if (navigator.onLine) {
            el.innerText = count > 0 ? "Online (Unsynced Data)" : "Online - All Synced";
            el.className = count > 0 ? "status-bar syncing" : "status-bar online";
        } else {
            el.innerText = "OFFLINE MODE";
            el.className = "status-bar offline";
        }
    }
</script>
</body>
</html>