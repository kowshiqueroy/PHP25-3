<?php
// dashboard.php

session_start();
require_once "connection.php";
// Example: Check if user is logged in (simple check)
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: admin.php');
    exit();
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Dashboard</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <style>
        .container {
            max-width: 100%;
            padding: 0 15px;
        }
        @media (min-width: 576px) {
            .container {
                max-width: 540px;
            }
        }
        @media (min-width: 768px) {
            .container {
                max-width: 720px;
            }
        }
        @media (min-width: 992px) {
            .container {
                max-width: 960px;
            }
        }
        @media (min-width: 1200px) {
            .container {
                max-width: 1140px;
            }
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark mb-4">
        <div class="container-fluid">
            <a class="navbar-brand" href="#">My Dashboard</a>
            <div class="d-flex">
                <span class="navbar-text text-white me-3">
                    Welcome, <?= htmlspecialchars($_SESSION['admin_user_id']) ?>
                </span>
                <a href="logout.php"  class="btn btn-outline-light btn-sm">Logout</a>
            </div>
        </div>
    </nav>
    <div class="container">
        <h1 class="mb-4">Dashboard</h1>
        <div class="row">
            <div class="col-md-4">
                <div class="card text-white bg-primary mb-3">
                    <div class="card-body">
                        <h5 class="card-title">Banner</h5>

                        <form action="" method="post">
                            <div class="mb-3">
                                <label for="link" class="form-label">Banner Link</label>
                                <input type="text" class="form-control" id="link" name="link" placeholder="Enter banner link 800X500" required>
                            </div>
                            <button type="submit" class="btn btn-light">Add Banner</button>
                        </form>

                        <?php
                        if (isset($_POST['link'])) {
                            $link = $_POST['link'];
                            $sql = "INSERT INTO banner (link) VALUES (?)";
                            $stmt = $conn->prepare($sql);
                            $stmt->bind_param("s", $link);
                            $stmt->execute();
                            $stmt->close();
                            header('Location: dashboard.php');
                            exit();
                        }

                        $sql = "SELECT * FROM banner ORDER BY id DESC";
                        $result = $conn->query($sql);
                        if ($result->num_rows > 0) {
                            $serial = 1;
                            while ($row = $result->fetch_assoc()) {
                        ?>
                                <p class="mt-3">
                                    <?= $serial ?>. <img src="<?= $row['link'] ?>" alt="banner" class="img-fluid" width="100" height="100">
                                    <span class="badge bg-secondary ms-2"><?= $row['id'] ?></span>
                                    <a href="dashboard.php?delete=<?= $row['id'] ?>" class="btn btn-danger btn-sm float-end">Delete</a>
                                </p>
                        <?php
                                $serial++;
                            }
                        }

                        if (isset($_GET['delete'])) {
                            $id = $_GET['delete'];
                            $sql = "DELETE FROM banner WHERE id = ?";
                            $stmt = $conn->prepare($sql);
                            $stmt->bind_param("i", $id);
                            $stmt->execute();
                            $stmt->close();
                            header('Location: dashboard.php');
                            exit();
                        }
                        ?>
                 
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card text-white bg-success mb-3">
                    <div class="card-body">
                        <h5 class="card-title">Clients</h5>
                        <form action="" method="post">
                            <div class="mb-3">
                                <label for="name" class="form-label">Client Name</label>
                                <input type="text" class="form-control" id="name" name="name" placeholder="Enter client name" required>
                            </div>
                            <div class="mb-3">
                                <label for="job" class="form-label">Job</label>
                                <input type="text" class="form-control" id="job" name="job" placeholder="Enter job" required>
                            </div>
                            <div class="mb-3">
                                <label for="star" class="form-label">Star (1-5)</label>
                                <select name="star" id="star" class="form-select">
                                    <option value="5">5</option>
                                    <option value="4">4</option>
                                    <option value="3">3</option>
                                    <option value="2">2</option>
                                    <option value="1">1</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="comment" class="form-label">Comment</label>
                                <textarea class="form-control" id="comment" name="comment" placeholder="Enter comment" required></textarea>
                            </div>
                            <button type="submit" class="btn btn-success">Add Comment</button>
                        </form>

                        <?php
                        if (isset($_POST['name'])) {
                            $name = $_POST['name'];
                            $job = $_POST['job'];
                            $star = $_POST['star'];
                            $comment = $_POST['comment'];
                            $sql = "INSERT INTO clients (name, job, star, comment) VALUES (?, ?, ?, ?)";
                            $stmt = $conn->prepare($sql);
                            $stmt->bind_param("ssis", $name, $job, $star, $comment);
                            $stmt->execute();
                            $stmt->close();
                            echo '<script>window.location.href="dashboard.php";</script>';
                            exit();
                        }

                        $sql = "SELECT * FROM clients ORDER BY id DESC";
                        $result = $conn->query($sql);
                        if ($result->num_rows > 0) {
                            $serial = 1;
                            while ($row = $result->fetch_assoc()) {
                        ?>
                                <p class="mt-3">
                                    <?= $serial ?>. <?= $row['name'] ?> (<?= $row['job'] ?>) - <?= $row['star'] ?> star
                                    <br>
                                    <small><?= $row['comment'] ?></small>
                                    <a href="dashboard.php?deletec=<?= $row['id'] ?>" class="btn btn-danger btn-sm float-end">Delete</a>
                                </p>
                        <?php
                                $serial++;
                            }
                        }

                        if (isset($_GET['deletec'])) {
                            $id = $_GET['deletec'];
                            $sql = "DELETE FROM clients WHERE id = ?";
                            $stmt = $conn->prepare($sql);
                            $stmt->bind_param("i", $id);
                            $stmt->execute();
                            $stmt->close();
                              echo '<script>window.location.href="dashboard.php";</script>';
                            exit();
                        }
                        ?>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card text-white bg-warning mb-3">
                    <div class="card-body">
                        <h5 class="card-title">Section 3</h5>
                        <p class="card-text">Notifications or updates.</p>
                    </div>
                </div>
            </div>
        </div>
        <!-- Add more dashboard content here -->
    </div>
</body>
</html>
