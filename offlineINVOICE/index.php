<?php // BanglaScript — Modular, Offline-Ready Invoice System ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Modern Invoice Builder</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <style>
    *, *::before, *::after {
      box-sizing: border-box;
    }
    body {
      margin: 0;
      padding: 20px;
      font-family: 'Inter', sans-serif;
      background: #eef2f5;
      color: #2c3e50;
    }
    .wrapper {
      max-width: 960px;
      margin: auto;
      background: #ffffff;
      border-radius: 16px;
      box-shadow: 0 12px 30px rgba(0, 0, 0, 0.05);
      padding: 30px;
    }
    h2 {
      margin-top: 0;
      font-size: 2.2rem;
      color: #34495e;
      text-align: center;
      border-bottom: 2px solid #ecf0f1;
      padding-bottom: 16px;
    }
    .grid {
      display: grid;
      grid-template-columns: 1fr;
      gap: 20px;
    }
    .row {
      display: flex;
      flex-direction: column;
    }
    @media (min-width: 600px) {
      .grid-cols-2 {
        grid-template-columns: 1fr 1fr;
      }
    }
    label {
      font-weight: 600;
      margin-bottom: 5px;
    }
    input, textarea, select {
      width: 100%;
      padding: 12px;
      margin-bottom: 15px;
      border: 1px solid #ccc;
      border-radius: 8px;
      font-size: 1rem;
      background-color: #fdfdfd;
    }
    button {
      padding: 12px 20px;
      border: none;
      border-radius: 8px;
      background-color: #007bff;
      color: white;
      font-weight: bold;
      cursor: pointer;
      transition: background 0.3s ease;
      margin-right: 10px;
    }
    button:hover {
      background-color: #0056b3;
    }
    table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 20px;
    }
    th, td {
      padding: 12px;
      border-bottom: 1px solid #ecf0f1;
      text-align: left;
    }
    tfoot td {
      font-weight: bold;
      background: #f8f9fb;
    }
    .actions {
      margin-top: 20px;
      display: flex;
      flex-wrap: wrap;
    }
    #status {
      background: #ffeeba;
      color: #856404;
      padding: 12px;
      border-radius: 8px;
      margin-top: 15px;
      display: none;
    }
    .meta, .signature {
      margin-top: 20px;
      font-size: 0.95rem;
    }
    .signature {
      font-style: italic;
      margin-top: 40px;
    }
    @media print {
      body {
        background: white;
        padding: 0;
      }
      .wrapper {
        box-shadow: none;
        padding: 0;
      }
      .actions, #status, .row:nth-child(1) { display: none; }
    }
  </style>
</head>
<body>
      <div id="status"></div>
<div class="wrapper">
  <h2><?php echo (filter_var(gethostbyname('google.com'), FILTER_VALIDATE_IP)) ? 'Online Invoice Creator' : 'Offline Invoice Creator'; ?></h2>
  <div class="grid">
    <div style="display: flex; gap: 20px;">
      <div style="flex: 1;">
        <label>Company Name</label>
        <input type="text" id="company" value="BanglaScript Innovations">
      </div>
      <div style="flex: 1;">
        <label>Company Address</label>
        <input type="text" id="companyAddr" value="Nilphamari Sadar, Bangladesh">
      </div>
      <div style="flex: 1;">
        <label>Client Contact</label>
        <input type="text" id="client" value="John Doe">
      </div>
      <div style="flex: 1;">
        <label>Client Email</label>
        <input type="email" id="clientEmail" value="john@example.com">
      </div>
      <div style="flex: 1;">
        <label>Invoice #</label>
        <input type="text" id="invoiceId" value="INV-<?= substr(md5(uniqid()), 0, 8) ?>" onfocus="if(this.value===''){this.value='INV-' + Date.now().toString(36).substring(2, 10)}">
      </div>
    </div>
  </div>

  <div style="display: flex; flex-wrap: wrap; gap: 20px;">
    <div style="flex: 1;">


      <div style="display: flex; gap: 10px;">
        <div style="flex: 1;">
          <label for="discount">Discount</label>
          <input type="number" id="discount" step="0.01" style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 5px; margin-bottom: 10px;">
        </div>
        <div style="flex: 1;">
          <label for="tax">Tax (%)</label>
          <input type="number" id="tax" step="0.01" style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 5px; margin-bottom: 10px;">
        </div>
        <div style="flex: 1;">
          <label for="delivery">Delivery</label>
          <input type="number" id="delivery" step="0.01" style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 5px; margin-bottom: 10px;">
        </div>
      </div>
   
    </div>
    <div style="flex: 1;">
      <label for="note">Notes</label>
      <input type="text" id="note" style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 5px; margin-bottom: 10px;">
  
    </div>
     <div class="actions">
    <button onclick="generateInvoice()">📄 Generate Invoice</button>
    <button onclick="printInvoice()">🖨️ Print</button>

 
  </div>
  </div>

 
 <script>
    function printInvoice() {
      const invoice = document.querySelector('.invoice');
      const win = window.open('', '');
      win.document.write(invoice.outerHTML);
      win.document.close();
      win.print();
    }
  </script>
<hr>
  <div style="display: flex; gap: 10px;">
    <div style="flex: 1;">
      <label for="item">Item</label>
      <input type="text" id="item" value="Item 1" style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 5px; margin-bottom: 10px;">
    </div>
    <div style="flex: 1;">
      <label for="qty">Quantity</label>
      <input type="number" id="qty" min="1" value="1" style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 5px; margin-bottom: 10px;">
    </div>
    <div style="flex: 1;">
      <label for="rate">Rate</label>
      <input type="number" id="rate" step="0.01" value="5.00" style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 5px; margin-bottom: 10px;">
    </div>
    <button onclick="addItem()" style="flex: 0.5; padding: 10px; border: 1px solid #ccc; border-radius: 5px; margin-bottom: 10px;">➕ Add Item</button>
  </div>

  <div class="invoice" style="margin-top: 30px; padding: 50px;  border-radius: 12px;">
    <h2 style="text-align: center;">Invoice</h2>

  <div class="meta">
    <div style="display: flex; justify-content: space-between;">
      <div>
        <strong>Date:</strong> <span id="dateText"><?= date('Y-m-d') ?></span>
        <script>
          document.getElementById('dateText').textContent = new Date().toISOString().slice(0, 10);
        </script>
      </div>
      <div>
        <strong>Invoice #:</strong> <span id="invIdText"></span>
        <script>
          const invoiceId = document.getElementById('invoiceId').value || '';
          document.getElementById('invIdText').textContent = invoiceId ? ' ' + invoiceId : '';
        </script>



      </div>
    </div> 
    <br>
    <div style="text-align: center; font-size: 1.5em;">
        <strong></strong> <span id="compName"><br><br></span>
    </div>
    <div style="text-align: center;"><strong></strong> <span id="compAddr"><br><br></span></div>
    <div style="display: flex; justify-content: space-between;">
        <div>
            <strong>Client:</strong> <span id="clientName">................................................</span>
        </div>
        <div>
            <strong>Contact:</strong> <span id="clientEmailText">................................................</span>
        </div>
    </div>
  </div>

  <table style="margin-top: 20px; width: 100%; border-collapse: collapse; border: 1px solid #ddd; margin: 20px 20px; ">
    <thead><tr><th>Item</th><th>Qty</th><th>Rate</th><th>Amount</th></tr></thead>
    <tbody id="items" style="border: 1px solid #ddd; margin: 20px;">
        
    
    
    <tr><td colspan="4"><br><hr><br><br><hr><br><br><hr><br><br><hr><br><br><hr><br></td></tr></tbody>
    <tfoot>
      <tr><td colspan="4" style="text-align: right;">Subtotal <span id="subtotal">$0.00</span></td></tr>
      <tr>
        <td colspan="4">
            
        <div style="display: flex; justify-content: space-between;">
          <div>Discount: <span id="discountVal">........</span></div>
          <div>Tax: <span id="taxVal">........</span></div>
          <div>Delivery: <span id="deliveryVal">............</span></div>
          <div style="font-weight: bold; font-size: 20px;"> Total: <span id="total">$0.00</span></div>
        </div>


      </td>
      </tr>
     
    </tfoot>
  </table>

  <div class="meta">
    <strong>Note:</strong> <span id="noteText">—</span>
  </div>
  <br><br> <br><br>
  <div style="display: flex; justify-content: space-between;">
    <div class="signature">Authorized by: ___________________</div>
    <div class="signature">Received by: ___________________</div>
  </div>

  </div>
</div>
<br><br>



<div style="text-align: center; margin-top: 30px;">
  <footer>Developed by kowshiqueroy@gmail.com</footer>
</div>

<br><br><br><br><br>

<script>
let itemList = [];
function addItem() {
  const name = document.getElementById('item').value.trim();
  const qty = parseInt(document.getElementById('qty').value);
  const rate = parseFloat(document.getElementById('rate').value);
  if (name && qty && rate >= 0) {
    itemList.push({ name, qty, rate });
    document.getElementById('item').value = '';
    document.getElementById('qty').value = '';
    document.getElementById('rate').value = '';
    generateInvoice();
  }
}
function generateInvoice() {
  document.getElementById('compName').textContent = document.getElementById('company').value || '—';
  document.getElementById('compAddr').textContent = document.getElementById('companyAddr').value || '—';
  document.getElementById('clientName').textContent = document.getElementById('client').value || '—';
  document.getElementById('clientEmailText').textContent = document.getElementById('clientEmail').value || '—';
  document.getElementById('invIdText').textContent = document.getElementById('invoiceId').value || 'INV-001';
  document.getElementById('noteText').textContent = document.getElementById('note').value || '—';
  const tbody = document.getElementById('items');
  let subtotal = 0;
  let rows = '';
  itemList.forEach(it => {
    const amt = it.qty * it.rate;
    subtotal += amt;
    rows += `<tr><td>${it.name}</td><td>${it.qty}</td><td>$${it.rate.toFixed(2)}</td><td>$${amt.toFixed(2)}</td></tr>`;
  });
  tbody.innerHTML = rows || '<tr><td colspan="4"><br><hr><br><br><hr><br><br><hr><br><br><hr><br><br><hr><br></td></tr>';
  document.getElementById('subtotal').textContent = `$${subtotal.toFixed(2)}`;
  const discount = parseFloat(document.getElementById('discount').value) || 0;
  const taxRate = parseFloat(document.getElementById('tax').value) || 0;
  const delivery = parseFloat(document.getElementById('delivery').value) || 0;
  const tax = (subtotal - discount) * (taxRate / 100);
  const total = subtotal - discount + tax + delivery;
  document.getElementById('discountVal').textContent = `-$${discount.toFixed(2)}`;
  document.getElementById('taxVal').textContent = `$${tax.toFixed(2)}`;
  document.getElementById('deliveryVal').textContent = `$${delivery.toFixed(2)}`;
  document.getElementById('total').textContent = `$${total.toFixed(2)}`;
}
['company', 'companyAddr', 'client', 'clientEmail', 'invoiceId', 'note'].forEach(id => {
  document.getElementById(id).addEventListener('input', () => {
    document.getElementById(
      id === 'company' ? 'compName' :
      id === 'companyAddr' ? 'compAddr' :
      id === 'client' ? 'clientName' :
      id === 'clientEmail' ? 'clientEmailText' :
      id === 'invoiceId' ? 'invIdText' : 'noteText'
    ).textContent = document.getElementById(id).value || '—';
  });
});
if (!navigator.onLine) {
  const status = document.getElementById('status');
  status.style.display = 'block';
  status.style.textAlign = 'center';
  status.textContent = '📴 Offline: You can still create and print invoices.';
}


  



</script>
</body>
</html>
