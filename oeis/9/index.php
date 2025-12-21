<?php
include 'header.php';
?>

<div class="container">
   <div class="glass-panel printable">
  <style>
   
    .dashboard-section { margin-bottom: 50px; }
    h2 { font-weight: 400; font-size: 1.8rem; margin-bottom: 20px; color: #333; text-align: center; }
    table.demo-table { margin: 0 auto 20px; border-collapse: collapse; width: 80%; display:none; }
    table.demo-table th, table.demo-table td { border: 1px solid #ddd; padding: 8px; text-align: center; }
    table.demo-table th { background-color: #f4f4f4; }
    .charts { display: flex; gap: 30px; flex-wrap: wrap; justify-content: center; }
    .chart-card { flex: 1; min-width: 280px; max-width: 500px; background: #fff; border-radius: 16px;
      box-shadow: 0 6px 20px rgba(0,0,0,0.1); padding: 20px; transition: transform 0.3s ease, box-shadow 0.3s ease; }
    .chart-card:hover { transform: translateY(-5px); box-shadow: 0 10px 25px rgba(0,0,0,0.15); }
    .chart-card canvas { width: 100% !important; height: 300px !important; }
  </style>

  <!-- Sales Table -->
  <div class="dashboard-section">
    <h2>📊 Sales</h2>
    <table class="demo-table" id="salesTable">
      <thead><tr><th>Product</th><th>Units Sold</th><th>Revenue ($)</th></tr></thead>
      <tbody>
        <tr><td>Laptops</td><td>120</td><td>90000</td></tr>
        <tr><td>Mobiles</td><td>200</td><td>60000</td></tr>
        <tr><td>Tablets</td><td>80</td><td>32000</td></tr>
      </tbody>
    </table>
    <div class="charts">
      <div class="chart-card"><canvas id="salesBar"></canvas></div>
      <div class="chart-card"><canvas id="salesLine"></canvas></div>
      <div class="chart-card"><canvas id="salesPie"></canvas></div>
      <div class="chart-card"><canvas id="salesDoughnut"></canvas></div>
      <div class="chart-card"><canvas id="salesRadar"></canvas></div>
    </div>
  </div>

  <!-- Orders Table -->
  <div class="dashboard-section">
    <h2>📦 Orders & Sales Performance</h2>
    <table class="demo-table" id="ordersTable">
      <thead>
        <tr>
          <th>User</th><th>Order Count</th><th>Damage Count</th>
          <th>Order Value ($)</th><th>Damage Value ($)</th>
          <th>Return Value ($)</th><th>Net Sale Value ($)</th>
        </tr>
      </thead>
      <tbody>
        <tr><td>John Doe</td><td>120</td><td>5</td><td>90000</td><td>3750</td><td>2000</td><td>86250</td></tr>
        <tr><td>Jane Smith</td><td>150</td><td>8</td><td>110000</td><td>6000</td><td>3000</td><td>104000</td></tr>
        <tr><td>Bob Brown</td><td>100</td><td>3</td><td>70000</td><td>2000</td><td>1500</td><td>68000</td></tr>
      </tbody>
    </table>
    <div class="charts">
      <div class="chart-card"><canvas id="ordersGrouped"></canvas></div>
      <div class="chart-card"><canvas id="ordersStacked"></canvas></div>
      <div class="chart-card"><canvas id="ordersLine"></canvas></div>
      <div class="chart-card"><canvas id="ordersRadar"></canvas></div>
      <div class="chart-card"><canvas id="ordersPie"></canvas></div>
      <div class="chart-card"><canvas id="ordersDoughnut"></canvas></div>
    </div>
  </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Utility: read table data
function getTableData(tableId) {
  const table = document.getElementById(tableId);
  const rows = table.querySelectorAll('tbody tr');
  const labels = [];
  const columns = [];
  rows.forEach(row => {
    const cells = row.querySelectorAll('td');
    labels.push(cells[0].innerText);
    const values = [];
    for (let i=1; i<cells.length; i++) {
      values.push(parseFloat(cells[i].innerText) || 0);
    }
    columns.push(values);
  });
  return { labels, columns };
}

// Sales Charts
const sales = getTableData('salesTable');
const salesUnits = sales.columns.map(r => r[0]);
const salesRevenue = sales.columns.map(r => r[1]);

new Chart(document.getElementById('salesBar'), {
  type: 'bar',
  data: { labels: sales.labels, datasets: [{ label: 'Revenue ($)', data: salesRevenue, backgroundColor: 'rgba(54,162,235,0.6)' }] }
});
new Chart(document.getElementById('salesLine'), {
  type: 'line',
  data: { labels: sales.labels, datasets: [{ label: 'Revenue ($)', data: salesRevenue, borderColor: '#4caf50', fill: false }] }
});
new Chart(document.getElementById('salesPie'), {
  type: 'pie',
  data: { labels: sales.labels, datasets: [{ data: salesRevenue, backgroundColor: ['#ff6384','#36a2eb','#ffcd56'] }] }
});
new Chart(document.getElementById('salesDoughnut'), {
  type: 'doughnut',
  data: { labels: sales.labels, datasets: [{ data: salesRevenue, backgroundColor: ['#4bc0c0','#9966ff','#ff9f40'] }] }
});
new Chart(document.getElementById('salesRadar'), {
  type: 'radar',
  data: { labels: sales.labels, datasets: [{ label: 'Revenue ($)', data: salesRevenue, backgroundColor: 'rgba(75,192,192,0.2)', borderColor: '#4bc0c0' }] }
});

// Orders Charts
const orders = getTableData('ordersTable');
const orderCounts = orders.columns.map(r => r[0]);
const damageCounts = orders.columns.map(r => r[1]);
const orderValues = orders.columns.map(r => r[2]);
const damageValues = orders.columns.map(r => r[3]);
const returnValues = orders.columns.map(r => r[4]);
const netValues = orders.columns.map(r => r[5]);

new Chart(document.getElementById('ordersGrouped'), {
  type: 'bar',
  data: {
    labels: orders.labels,
    datasets: [
      { label: 'Order Value', data: orderValues, backgroundColor: 'rgba(54,162,235,0.6)' },
      { label: 'Damage Value', data: damageValues, backgroundColor: 'rgba(255,99,132,0.6)' },
      { label: 'Return Value', data: returnValues, backgroundColor: 'rgba(255,206,86,0.6)' },
      { label: 'Net Sale Value', data: netValues, backgroundColor: 'rgba(75,192,192,0.6)' }
    ]
  }
});
new Chart(document.getElementById('ordersStacked'), {
  type: 'bar',
  data: {
    labels: orders.labels,
    datasets: [
      { label: 'Order Value', data: orderValues, backgroundColor: 'rgba(54,162,235,0.6)' },
      { label: 'Damage Value', data: damageValues, backgroundColor: 'rgba(255,99,132,0.6)' },
      { label: 'Return Value', data: returnValues, backgroundColor: 'rgba(255,206,86,0.6)' },
      { label: 'Net Sale Value', data: netValues, backgroundColor: 'rgba(75,192,192,0.6)' }
    ]
  },
  options: { scales: { x: { stacked: true }, y: { stacked: true } } }
});
// Orders Line Chart
new Chart(document.getElementById('ordersLine'), {
  type: 'line',
  data: {
    labels: orders.labels,
    datasets: [{
      label: 'Net Sale Value',
      data: netValues,
      borderColor: '#4caf50',
      backgroundColor: 'rgba(76, 175, 80, 0.2)',
      fill: true,
      tension: 0.3
    }]
  },
  options: {
    responsive: true,
    plugins: { title: { display: true, text: 'Net Sale Value Trend' } },
    scales: { y: { beginAtZero: true } }
  }
});

// Orders Radar Chart
new Chart(document.getElementById('ordersRadar'), {
  type: 'radar',
  data: {
    labels: ['Order Count','Damage Count','Order Value','Damage Value','Return Value','Net Sale Value'],
    datasets: orders.labels.map((user, i) => ({
      label: user,
      data: [
        orderCounts[i],
        damageCounts[i],
        orderValues[i],
        damageValues[i],
        returnValues[i],
        netValues[i]
      ],
      backgroundColor: `rgba(${50+i*50}, ${100+i*30}, ${200-i*40}, 0.2)`,
      borderColor: `rgba(${50+i*50}, ${100+i*30}, ${200-i*40}, 1)`
    }))
  },
  options: {
    responsive: true,
    plugins: { title: { display: true, text: 'Orders Radar Comparison' } }
  }
});

// Orders Pie Chart (Order Value distribution)
new Chart(document.getElementById('ordersPie'), {
  type: 'pie',
  data: {
    labels: orders.labels,
    datasets: [{
      data: orderValues,
      backgroundColor: ['#ff6384','#36a2eb','#ffcd56']
    }]
  },
  options: {
    responsive: true,
    plugins: { title: { display: true, text: 'Order Value Distribution' } }
  }
});

// Orders Doughnut Chart (Net Sale distribution)
new Chart(document.getElementById('ordersDoughnut'), {
  type: 'doughnut',
  data: {
    labels: orders.labels,
    datasets: [{
      data: netValues,
      backgroundColor: ['#4bc0c0','#9966ff','#ff9f40']
    }]
  },
  options: {
    responsive: true,
    plugins: { title: { display: true, text: 'Net Sale Value Distribution' } }
  }
});

// ============================
// End of Charts Initialization
// ============================
</script>
</div>
<?php
include 'footer.php';
?>