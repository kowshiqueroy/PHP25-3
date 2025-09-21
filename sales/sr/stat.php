<?php
require_once '../conn.php'; // Ensure this defines $conn
// if the order status is not 0 (draft) or 4 (edit), then we cannot edit this order
// this is because the order has been finalized and we cannot make any changes
// 0: Draft
// 1: Submit
// 2: Approve
// 3: Reject
// 4: Edit
// 5: Serial
// 6: Processing
// 7: Delivered
// 8: Returned
$where = [];
if (!empty($_GET['from_date'])) $where[] = "order_date >= '" . $conn->real_escape_string($_GET['from_date']) . "'";
if (!empty($_GET['to_date'])) $where[] = "order_date <= '" . $conn->real_escape_string($_GET['to_date']) . "'";
if (isset($_GET['order_status']) && $_GET['order_status'] !== '') {
    if ((int)$_GET['order_status'] === 1) {
        $where[] = "order_status IN (1,2,5,6,7)";
    } elseif ((int)$_GET['order_status'] === 0) {
        $where[] = "order_status IN (0,3,4,8)";
    }
}

$sql = "SELECT * FROM orders";
if ($where) $sql .= " WHERE " . implode(" AND ", $where);
$result = $conn->query($sql);

// Lookup maps
$usernames = [];
$routes = [];
$persons = [];

$res1 = $conn->query("SELECT id, username FROM users");
while ($u = $res1->fetch_assoc()) $usernames[$u['id']] = $u['username'];

$res2 = $conn->query("SELECT id, route_name FROM routes");
while ($r = $res2->fetch_assoc()) $routes[$r['id']] = $r['route_name'];

$res3 = $conn->query("SELECT id, person_name FROM persons");
while ($p = $res3->fetch_assoc()) $persons[$p['id']] = $p['person_name'];

// Group and count
$createdBy = [];
$routeId = [];
$personId = [];
$orderDate = [];

while ($row = $result->fetch_assoc()) {
    $createdBy[$row['created_by']] = ($createdBy[$row['created_by']] ?? 0) + 1;
    $routeId[$row['route_id']] = ($routeId[$row['route_id']] ?? 0) + 1;
    $personId[$row['person_id']] = ($personId[$row['person_id']] ?? 0) + 1;
    $orderDate[$row['order_date']] = ($orderDate[$row['order_date']] ?? 0) + 1;
}

// Sort all by count descending
arsort($createdBy);
arsort($routeId);
arsort($personId);
arsort($orderDate);

// Top 10 for charts
function top10($array) {
    return array_slice($array, 0, 10, true);
}
$topCreatedBy = top10($createdBy);
$topRouteId = top10($routeId);
$topPersonId = top10($personId);
$topOrderDate = top10($orderDate);

// Compact formatter
function compactText($array, $lookup = []) {
    $pairs = [];
    foreach ($array as $key => $val) {
        $label = $lookup[$key] ?? $key;
        $pairs[] = $label . '-' . $val;
    }
    return implode(', ', $pairs);
}
?>

<!DOCTYPE html>
<html>
<head>
  <title>Enriched Order Graphs</title>
  <style>
    body { font-family: Arial; margin: 10px; font-size: 14px; }
    canvas { margin-bottom: 10px; max-width: 100%; }
    form { margin-bottom: 10px; }
    .compact { margin: 4px 0; line-height: 1.4; }
    @media print {
      form, .no-print { display: none; }
      /* canvas, .compact { page-break-after: always; } */
      body { margin: 5px; font-size: 12px; }
    }
  </style>
</head>
<body>

<h3>Order Graphs & Summary</h3>

<form method="GET">
  <?php if (!empty($_GET['from_date']) || !empty($_GET['to_date']) || isset($_GET['order_status'])): ?>
    <div><strong>Filters Applied:</strong>
      <?php if (!empty($_GET['from_date'])) echo "From: " . $_GET['from_date'] . " "; ?>
      <?php if (!empty($_GET['to_date'])) echo "To: " . $_GET['to_date'] . " "; ?>
      <?php if (isset($_GET['order_status'])) echo "Status: " . ($_GET['order_status'] === '1' ? 'Approved' : 'Cancelled'); ?>
    </div>
  <?php endif; ?>
  From: <input type="date" name="from_date" value="<?= $_GET['from_date'] ?? '' ?>">
  To: <input type="date" name="to_date" value="<?= $_GET['to_date'] ?? '' ?>">
  Status:
  <select name="order_status">
    <option value="">All</option>
    <option value="0" <?= ($_GET['order_status'] ?? '') === '0' ? 'selected' : '' ?>>Cancelled</option>
    <option value="1" <?= ($_GET['order_status'] ?? '') === '1' ? 'selected' : '' ?>>Approved</option>
  </select>
  <input type="submit" value="Filter">
  <button onclick="window.print()" class="no-print">🖨️ Print</button>
</form>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
function renderChart(canvasId, label, labels, data, color) {
  new Chart(document.getElementById(canvasId), {
    type: 'bar',
    data: {
      labels: labels,
      datasets: [{
        label: label,
        data: data,
        backgroundColor: color
      }]
    },
    options: {
      responsive: true,
      plugins: {
        legend: { display: false },
        title: { display: true, text: label }
      },
      scales: {
        x: { ticks: { autoSkip: false, maxRotation: 90, minRotation: 45 } }
      }
    }
  });
}
</script>

<!-- Created By -->
<canvas id="createdByChart" height="100"></canvas>
<script>
renderChart('createdByChart', 'Top 10: Created By', <?= json_encode(array_map(fn($id) => $usernames[$id] ?? $id, array_keys($topCreatedBy))) ?>, <?= json_encode(array_values($topCreatedBy)) ?>, 'rgba(255, 99, 132, 0.6)');
</script>
<div class="compact"><strong>Total Created By:</strong> <?= count($createdBy) ?></div>
<div class="compact"><strong>Created By Summary:</strong> <?= compactText($createdBy, $usernames) ?></div>

<!-- Route ID -->
<canvas id="routeChart" height="100"></canvas>
<script>
renderChart('routeChart', 'Top 10: Route ID', <?= json_encode(array_map(fn($id) => $routes[$id] ?? $id, array_keys($topRouteId))) ?>, <?= json_encode(array_values($topRouteId)) ?>, 'rgba(54, 162, 235, 0.6)');
</script>
<div class="compact"><strong>Total Routes:</strong> <?= count($routeId) ?></div>
<div class="compact"><strong>Route Summary:</strong> <?= compactText($routeId, $routes) ?></div>

<!-- Person ID -->
<canvas id="personChart" height="100"></canvas>
<script>
renderChart('personChart', 'Top 10: Person ID', <?= json_encode(array_map(fn($id) => $persons[$id] ?? $id, array_keys($topPersonId))) ?>, <?= json_encode(array_values($topPersonId)) ?>, 'rgba(255, 206, 86, 0.6)');
</script>
<div class="compact"><strong>Total Persons:</strong> <?= count($personId) ?></div>
<div class="compact"><strong>Person Summary:</strong> <?= compactText($personId, $persons) ?></div>

<!-- Order Dates -->
<canvas id="dailyChart" height="100"></canvas>
<script>
renderChart('dailyChart', 'Top 10: Order Dates', <?= json_encode(array_keys($topOrderDate)) ?>, <?= json_encode(array_values($topOrderDate)) ?>, 'rgba(75, 192, 192, 0.6)');
</script>
<div class="compact"><strong>Total Order Dates:</strong> <?= count($orderDate) ?></div>
<div class="compact"><strong>Order Date Summary:</strong> <?= compactText($orderDate) ?></div>

</body>
</html>