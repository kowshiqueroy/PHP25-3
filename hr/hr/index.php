<?php include_once "head2.php"; ?>

<div class="content">
        <!-- Main content goes here -->

       
        



        

        <table class="table table-bordered d-none" id="myTablegraph">
    <thead>
        <tr>
            <th>Dept</th>
            <th>Count</th>
        </tr>
    </thead>
    <tbody>
        <?php
        $sql = "SELECT dept, COUNT(*) AS count FROM person GROUP BY dept";
        $result = mysqli_query($conn, $sql);
        $departments = [];
        $counts = [];
        if (mysqli_num_rows($result) > 0) {
            while($row = mysqli_fetch_assoc($result)) {
                echo "<tr>";
                echo "<td>".$row['dept']."</td>";
                echo "<td>".$row['count']."</td>";
                echo "</tr>";
                $departments[] = $row['dept'];
                $counts[] = $row['count'];
            }
        } else {
            echo "0 results";
        }
        ?>
    </tbody>
</table>

<div style="display: flex; flex-direction: row;">
    <div style="flex: 1;">
        <div id="barChartContainer">
            <canvas id="barChart"></canvas>
        </div>
    </div>
    <div style="flex: 1;">
        <div id="barChartContainer2">
            <canvas id="barChart2"></canvas>
        </div>
    </div>
</div>

<script>
    var ctx = document.getElementById('barChart').getContext('2d');
    var barChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: <?php echo json_encode($departments); ?>,
            datasets: [{
                label: 'Person',
                data: <?php echo json_encode($counts); ?>,
                backgroundColor: <?php
                    $colors = [];
                    foreach ($departments as $department) {
                        $colors[] = '#' . str_pad(dechex(mt_rand(0, 0xFFFFFF)), 6, '0', STR_PAD_LEFT);
                    }
                    echo json_encode($colors);
                ?>,
                borderColor: 'rgba(75, 192, 192, 1)',
                borderWidth: 1
            }]
        },
        options: {
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });
</script>

<script>
    var ctx = document.getElementById('barChart2').getContext('2d');
    var barChart = new Chart(ctx, {
        type: 'pie',
        data: {
            labels: <?php echo json_encode($departments); ?>,
            datasets: [{
                label: 'Person',
                data: <?php echo json_encode($counts); ?>,
                backgroundColor: <?php
                    $colors = [];
                    foreach ($departments as $department) {
                        $colors[] = '#' . str_pad(dechex(mt_rand(0, 0xFFFFFF)), 6, '0', STR_PAD_LEFT);
                    }
                    echo json_encode($colors);
                ?>,
                borderColor: 'rgba(75, 192, 192, 1)',
                borderWidth: 1
            }]
        },
        options: {
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });
</script>





       

    </div>

    <?php include_once "foot.php"; ?>