<?php require_once 'header.php'; ?>

<div class="dashboard-header">
    <h2>Dashboard</h2>
    <p>Welcome, <b><?= ucfirst($_SESSION['username']) ?></b></p>
</div>

<div class="form-grid" style="grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); margin-bottom: 30px;">
    <div class="card" style="background: white; padding: 20px; border-radius: 8px; border-left: 5px solid #2563eb; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
        <?php
        $stmt = $pdo->query("SELECT COUNT(DISTINCT id) as cnt FROM products");
        $prodCount = $stmt->fetch()['cnt'];
        ?>
        <h3 style="margin:0; font-size: 2rem;"><?= $prodCount ?></h3>
        <span style="color: #666;">Total Products</span>
    </div>

    <div class="card" style="background: white; padding: 20px; border-radius: 8px; border-left: 5px solid #10b981; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
        <?php
        // Calculate total value of current stock (Approximation: Current Stock * Last Purchase Price)
        // Note: For a precise FIFO/LIFO value, more complex logic is needed. 
        // Here we sum the total "IN" value - total "OUT" value for a simple balance.
        $stmt = $pdo->query("SELECT 
            (SUM(CASE WHEN txn_type='IN' THEN total_value ELSE 0 END) - 
             SUM(CASE WHEN txn_type='OUT' THEN total_value ELSE 0 END)) as net_val 
             FROM transactions");
        $netVal = $stmt->fetch()['net_val'] ?? 0;
        ?>
        <h3 style="margin:0; font-size: 2rem;">$<?= number_format($netVal, 2) ?></h3>
        <span style="color: #666;">Est. Stock Value</span>
    </div>

    <div class="card" style="background: white; padding: 20px; border-radius: 8px; border-left: 5px solid #f59e0b; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
        <?php
        $stmt = $pdo->query("SELECT COUNT(*) as cnt FROM transactions WHERE entry_date = CURDATE()");
        $todayTxn = $stmt->fetch()['cnt'];
        ?>
        <h3 style="margin:0; font-size: 2rem;"><?= $todayTxn ?></h3>
        <span style="color: #666;">Transactions Today</span>
    </div>
</div>

<div style="margin-bottom: 20px;">
    <?php if($_SESSION['role'] !== 'viewer'): ?>
        <a href="transaction.php" class="btn" style="text-decoration:none; display:inline-block; margin-right: 10px;">+ New Entry</a>
    <?php endif; ?>
    <a href="reports.php" class="btn" style="background:#4b5563; text-decoration:none; display:inline-block;">View Reports</a>
</div>

<h3>Recent Activity (All Users)</h3>
<table>
    <thead>
        <tr>
            <th>Date</th>
            <th>Type</th>
            <th>Product</th>
            <th>Qty</th>
            <th>User</th>
        </tr>
    </thead>
    <tbody>
        <?php
        $sql = "SELECT t.*, p.p_name, u.username 
                FROM transactions t 
                LEFT JOIN products p ON t.product_id = p.id 
                LEFT JOIN users u ON t.user_id = u.id 
                ORDER BY t.created_at DESC LIMIT 10";
        $stmt = $pdo->query($sql);
        while($row = $stmt->fetch()):
            $color = $row['txn_type'] == 'IN' ? 'green' : 'red';
        ?>
        <tr>
            <td><?= htmlspecialchars($row['entry_date']) ?></td>
            <td style="color:<?= $color ?>; font-weight:bold;"><?= $row['txn_type'] ?></td>
            <td><?= htmlspecialchars($row['p_name']) ?></td>
            <td><?= $row['quantity'] ?></td>
            <td><?= htmlspecialchars($row['username']) ?></td>
        </tr>
        <?php endwhile; ?>
    </tbody>
</table>

<?php require_once 'footer.php'; ?>