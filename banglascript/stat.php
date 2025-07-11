<?php
include('header.php');
?>
<html>
<head>
    <title>banglascript | Statistics</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .card {
            margin-bottom: 20px;
            background-color: #1e1e1e;
            border: 1px solid #333;
            color: #76ff03;
        }
        .card-header {
            background-color: #333;
        }
        .table {
            background-color: #1e1e1e;
        }
        .table-bordered {
            border: 1px solid #333;
        }
        .table td, .table th {
            border: 1px solid #333;
        }
        .table-responsive {
            overflow-x: scroll;
        }
    </style>
</head>
<div class="container">
    <h2 class="text-center">Statistics</h2>
    <div class="row">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h4>Hits</h4>
                </div>
                <div class="card-body">
                    <table class="table table-bordered table-responsive">
                        <thead>
                            <tr>
                                <th>IP</th>
                                <th>Location</th>
                                <th>Hits</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $result = $conn->query("SELECT ip, hits FROM hits ORDER BY hits DESC");
                            if ($result->num_rows > 0) {
                                while($row = $result->fetch_assoc()) {
                                    echo "<tr>";
                                    echo "<td>" . $row['ip'] . "</td>";


                                    $ip = $row['ip'];
                                    if (filter_var($ip, FILTER_VALIDATE_IP)) {
                                        $location = json_decode(file_get_contents("http://ip-api.com/json/".$ip), true);
                                        echo "<td>" . (isset($location['city']) ? $location['city'] : '-') . ", " . (isset($location['regionName']) ? $location['regionName'] : '-') . ", " . (isset($location['country']) ? $location['country'] : '-') . "</td>";
                                    } else {
                                        echo "<td>Invalid IP</td>";
                                    }
                                    
                                    echo "<td>" . $row['hits'] . "</td>";
                                    echo "</tr>";
                                }
                            }
                            ?>
                        </tbody>
                    </table>
                    <hr>
                    <p>Total IP: <?= $total_ip ?></p>
                    <p>Total Hits: <?= $total_hits ?></p>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h4>Debug</h4>
                </div>
                <div class="card-body">
                    <table class="table table-bordered table-responsive">
                        <thead>
                            <tr>
                                <th>Details</th>
                         
                                <th>Code & Scan</th>
                                <th>Result</th>
                              
                                
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $result = $conn->query("SELECT * FROM debug");
                            if ($result->num_rows > 0) {
                                while($row = $result->fetch_assoc()) {
                                    echo "<tr>";
                                    echo "<td>" . $row['id'] . " ". $row['ip'] . " " . $row['execution_time'] . " ". $row['timedate'] . "</td>";
                                    echo "<td>" . $row['code'] . "..." . "<br><br> Scan: " . $row['scan'] . "</td>";
                                    echo "<td>" . $row['result'] . " " . $row['error'] . "</td>";
                                   
                                    echo "</tr>";
                                }
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

