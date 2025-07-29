<?php
include_once 'header.php';
?>

<main class="printable">
    <h2>Damages</h2>
    <p><button class="edit-btn" onclick="window.location.href='damages_create.php'">Create New Damage Report</button></p>



     <div class="table-container">
          
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        
                        <th>R.Date</th>
                        <th>I.Date</th>
                        <th>Trader</th>
                        <th>Shop</th>
                        <th>Receive</th>
                        <th>Actual</th>
                   
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $stmt = $conn->prepare("SELECT * FROM damage_details ORDER BY id DESC");
                    $stmt->execute();
                    $result = $stmt->get_result();
                    while ($row = $result->fetch_assoc()) {
                        echo "<tr>";
                        echo "<td><a style= 'text-decoration: none' href='damage_edit.php?id=" . $row['id'] .
                         "' class='btn-sm'>" . ($row['status']==1 ? "📝" : "✏️") . "</a> {$row['id']} <a style= 'text-decoration: none' href='damages.php?toggle=" 
                         . $row['id'] . "' class='btn-sm'>" . ($row['status']==1 ? "✖️" : "✔️") . "</a> </td>";
                       
                        echo "<td>{$row['received_date']}</td>";
                        echo "<td>{$row['inspection_date']}</td>";
                        echo "<td>{$row['shop_type']} - {$row['trader_name']}</td>";
                        echo "<td>{$row['shop_total_qty']} ={$row['shop_total_amount']}/-</td>";
                        echo "<td>{$row['received_total_qty']} ={$row['received_total_amount']}/-</td>";
                        echo "<td>{$row['actual_total_qty']} ={$row['actual_total_amount']}/-</td>";
                        echo "</tr>";
                    }
                    $stmt->close();
                    ?>
                </tbody>
            </table>
        </div>








</main>

<?php
include_once 'footer.php';
?>