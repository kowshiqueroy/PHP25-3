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
                           


                             $link = $conn->real_escape_string($_POST['link']);
                            $google_drive_url_pattern = '/https:\/\/drive\.google\.com\/file\/d\/([^\/]+)\/view\?usp=sharing/';
                            if (preg_match($google_drive_url_pattern, $link, $matches)) {
                                $link = "https://lh3.googleusercontent.com/d/" . $matches[1];
                            }
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
                        <h5 class="card-title">Comments</h5>
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
                                <select name="star" id="star" class="form-select" required>
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
                            <button type="submit" name="addcomment" class="btn btn-success">Add Comment</button>
                        </form>

                        <?php
                        if (isset($_POST['addcomment'])) {
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
                        <h5 class="card-title">Product</h5>



                     
                        <form action="" method="post">
                            <?php if (isset($_GET['editp'])): ?>
                            <?php
                            $id = $_GET['editp'];
                            $sql = "SELECT * FROM products WHERE id = ?";
                            $stmt = $conn->prepare($sql);
                            $stmt->bind_param("i", $id);
                            $stmt->execute();
                            $result = $stmt->get_result();
                            $row = $result->fetch_assoc();
                            ?>
                            <div class="mb-3">
                                <label for="name" class="form-label">Product Name</label>
                                <input type="text" class="form-control" id="name" name="name" placeholder="Enter product name" value="<?= $row['name'] ?>" required>
                            </div>
                            <div class="mb-3">
                                <label for="image" class="form-label">Image Link</label>
                                <input type="text" class="form-control" id="image" name="image" placeholder="Enter image link" value="<?= $row['image'] ?>" required>
                            </div>
                            <div class="mb-3">
                                <label for="category" class="form-label">Category</label>
                                <input type="text" class="form-control" id="category" name="category" placeholder="Enter category" value="<?= $row['category'] ?>" required>
                            </div>
                            <div class="mb-3">
                                <label for="price" class="form-label">Price</label>
                                <input type="number" step="0.01" class="form-control" id="price" name="price" placeholder="Enter price" value="<?= $row['price'] ?>" required>
                            </div>
                            <div class="mb-3">
                                <label for="saleprice" class="form-label">Sale Price</label>
                                <input type="number" step="0.01" class="form-control" id="saleprice" name="saleprice" placeholder="Enter sale price" value="<?= $row['saleprice'] ?>" required>
                            </div>
                            <div class="mb-3">
                                <label for="star" class="form-label">Star (1-5)</label>
                                <select name="star" id="star" class="form-select">
                                    <option value="5" <?= $row['star'] == 5 ? 'selected' : '' ?>>5</option>
                                    <option value="4" <?= $row['star'] == 4 ? 'selected' : '' ?>>4</option>
                                    <option value="3" <?= $row['star'] == 3 ? 'selected' : '' ?>>3</option>
                                    <option value="2" <?= $row['star'] == 2 ? 'selected' : '' ?>>2</option>
                                    <option value="1" <?= $row['star'] == 1 ? 'selected' : '' ?>>1</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="address" class="form-label">Address</label>
                                <input type="text" class="form-control" id="address" name="address" placeholder="Enter address" value="<?= $row['address'] ?>" required>
                            </div>
                            <div class="mb-3">
                                <label for="pack" class="form-label">Pack</label>
                                <input type="text" class="form-control" id="pack" name="pack" placeholder="Enter pack" value="<?= $row['pack'] ?>" required>
                            </div>
                            <div class="mb-3">
                                <label for="type" class="form-label">Type</label>
                                <input type="text" class="form-control" id="type" name="type" placeholder="Enter type" value="<?= $row['type'] ?>" required>
                            </div>
                            <div class="mb-3">
                                <label for="description" class="form-label">Description</label>
                                <textarea class="form-control" id="description" name="description" placeholder="Enter description" required><?= $row['description'] ?></textarea>
                            </div>
                            <div class="mb-3">
                                <label class="form-label"></label>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="checkbox" name="t1" id="t1" value="1" <?= $row['t1'] == 1 ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="t1">t1</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="checkbox" name="t2" id="t2" value="1" <?= $row['t2'] == 1 ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="t2">t2</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="checkbox" name="t3" id="t3" value="1" <?= $row['t3'] == 1 ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="t3">t3</label>
                                </div>
                            <div class="mb-3">
                                <label for="status" class="form-label">Status</label>
                                <select name="status" id="status" class="form-select">
                                    <option value="1" <?= $row['status'] == 1 ? 'selected' : '' ?>>Active</option>
                                    <option value="0" <?= $row['status'] == 0 ? 'selected' : '' ?>>Inactive</option>
                                </select>
                            </div>
                            </div>
                            <?php else: ?>
                            <div class="mb-3">
                                <label for="name" class="form-label">Product Name</label>
                                <input type="text" class="form-control" id="name" name="name" placeholder="Enter product name" required>
                            </div>
                            <div class="mb-3">
                                <label for="image" class="form-label">Image Link</label>
                                <input type="text" class="form-control" id="image" name="image" placeholder="Enter image link" required>
                            </div>
                            <div class="mb-3">
                                <label for="category" class="form-label">Category</label>
                                <input type="text" class="form-control" id="category" name="category" placeholder="Enter category" required>
                            </div>
                            <div class="mb-3">
                                <label for="price" class="form-label">Price</label>
                                <input type="number" step="0.01" class="form-control" id="price" name="price" placeholder="Enter price" required>
                            </div>
                            <div class="mb-3">
                                <label for="saleprice" class="form-label">Sale Price</label>
                                <input type="number" step="0.01" class="form-control" id="saleprice" name="saleprice" placeholder="Enter sale price" required>
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
                                <label for="address" class="form-label">Address</label>
                                <input type="text" class="form-control" id="address" name="address" placeholder="Enter address" required>
                            </div>
                            <div class="mb-3">
                                <label for="pack" class="form-label">Pack</label>
                                <input type="text" class="form-control" id="pack" name="pack" placeholder="Enter pack" required>
                            </div>
                            <div class="mb-3">
                                <label for="type" class="form-label">Type</label>
                                <input type="text" class="form-control" id="type" name="type" placeholder="Enter type" required>
                            </div>
                            <div class="mb-3">
                                <label for="description" class="form-label">Description</label>
                                <textarea class="form-control" id="description" name="description" placeholder="Enter description" required></textarea>
                            </div>
                            <div class="mb-3">
                                <label class="form-label"></label>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="checkbox" name="t1" id="t1" value="1">
                                    <label class="form-check-label" for="t1">t1</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="checkbox" name="t2" id="t2" value="1">
                                    <label class="form-check-label" for="t2">t2</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="checkbox" name="t3" id="t3" value="1">
                                    <label class="form-check-label" for="t3">t3</label>
                                </div>
                            <div class="mb-3">
                                <label for="status" class="form-label">Status</label>
                                <select name="status" id="status" class="form-select">
                                    <option value="1">Active</option>
                                    <option value="0">Inactive</option>
                                </select>
                            </div>
                            </div>

        <?php endif; ?>
                            <?php if (isset($_GET['editp'])): ?>
                            <button type="submit" name="updateproduct" class="btn btn-warning">Update Product</button>
<?php else: ?>
                            <button type="submit" name="addproduct" class="btn btn-warning">Add Product</button>
<?php endif; ?>
                        </form>
                        <?php
                        if (isset($_POST['updateproduct'])) {
                            $name = $_POST['name'];
                            $image = $conn->real_escape_string($_POST['image']);
                            $google_drive_url_pattern = '/https:\/\/drive\.google\.com\/file\/d\/([^\/]+)\/view\?usp=sharing/';
                            if (preg_match($google_drive_url_pattern, $image, $matches)) {
                                $image = "https://lh3.googleusercontent.com/d/" . $matches[1];
                            }
                            $category = $_POST['category'];
                            $price = $_POST['price'];
                            $saleprice = $_POST['saleprice'];
                            $star = $_POST['star'];
                            $address = $_POST['address'];
                            $pack = $_POST['pack'];
                            $type = $_POST['type'];
                            $description = $_POST['description'];
                            $t1 = isset($_POST['t1']) ? 1 : 0;
                            $t2 = isset($_POST['t2']) ? 1 : 0;
                            $t3 = isset($_POST['t3']) ? 1 : 0;
                            $product_id = $_GET['editp'];
                            $status = $_POST['status'];
                            $sql = "UPDATE products SET name=?, image=?, category=?, price=?, saleprice=?, star=?, address=?, pack=?, type=?, description=?, t1=?, t2=?, t3=?, status=? WHERE id=?";
                            $stmt = $conn->prepare($sql);
                            $stmt->bind_param("sssddsssssiiiii", $name, $image, $category, $price, $saleprice, $star, $address, $pack, $type, $description, $t1, $t2, $t3, $status, $product_id);
                            $stmt->execute();
                            $stmt->close();
                            echo '<script>window.location.href="dashboard.php";</script>';
                            exit();
                        }

                        if (isset($_GET['editp'])) {
                            $product_id = $_GET['editp'];
                            $sql = "SELECT * FROM products WHERE id = ?";
                            $stmt = $conn->prepare($sql);
                            $stmt->bind_param("i", $product_id);
                            $stmt->execute();
                            $result = $stmt->get_result();
                            if ($result->num_rows > 0) {
                                $product = $result->fetch_assoc();
                                // Use $product data to populate form fields if needed
                            }
                            $stmt->close();
                        }
                        ?>

                        <?php
                        if (isset($_POST['addproduct'])) {
                            $name = $_POST['name'];
                            $image = $conn->real_escape_string($_POST['image']);
                            $google_drive_url_pattern = '/https:\/\/drive\.google\.com\/file\/d\/([^\/]+)\/view\?usp=sharing/';
                            if (preg_match($google_drive_url_pattern, $image, $matches)) {
                                $image = "https://lh3.googleusercontent.com/d/" . $matches[1];
                            }
                            $category = $_POST['category'];
                            $price = $_POST['price'];
                            $saleprice = $_POST['saleprice'];
                            $star = $_POST['star'];
                            $address = $_POST['address'];
                            $pack = $_POST['pack'];
                            $type = $_POST['type'];
                            $description = $_POST['description'];
                            $t1 = isset($_POST['t1']) ? 1 : 0;
                            $t2 = isset($_POST['t2']) ? 1 : 0;
                            $t3 = isset($_POST['t3']) ? 1 : 0;
                            $status = $_POST['status'];
                            $sql = "INSERT INTO products (name, image, category, price, saleprice, star, address, pack, type, description, t1, t2, t3, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
                            $stmt = $conn->prepare($sql);
                            $stmt->bind_param("sssddssssssiii", $name, $image, $category, $price, $saleprice, $star, $address, $pack, $type, $description, $t1, $t2, $t3, $status);
                            $stmt->execute();
                            $stmt->close();
                           echo '<script>window.location.href="dashboard.php";</script>';
                            exit();
                        }
                        ?>

                          









                    </div>
                </div>
            </div>
        </div>
        <!-- Add more dashboard content here -->


         <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Name</th>
                                    <th>Image</th>
                                    <th>Category</th>
                                    <th>Price</th>
                                    <th>Sale Price</th>
                                    <th>Star</th>
                                    <th>Address</th>
                                    <th>Pack</th>
                                    <th>Type</th>
                                    <th>Description</th>
                                    <th>t1</th>
                                    <th>t2</th>
                                    <th>t3</th>
                                    <th>Status</th>
                                    <th>Description</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php
                            $sql = "SELECT * FROM products ORDER BY id DESC";
                            $result = $conn->query($sql);
                            if ($result->num_rows > 0) {
                                $serial = 1;
                                while ($row = $result->fetch_assoc()) {
                            ?>
                                <tr>
                                    <td><?= $serial ?></td>
                                    <td><?= $row['name'] ?></td>
                                    <td><img src="<?= $row['image'] ?>" alt="product" width="100" height="100"></td>
                                    <td><?= $row['category'] ?></td>
                                    <td><?= $row['price'] ?></td>
                                    <td><?= $row['saleprice'] ?></td>
                                    <td><?= $row['star'] ?></td>
                                    <td><?= $row['address'] ?></td>
                                    <td><?= $row['pack'] ?></td>
                                    <td><?= $row['type'] ?></td>
                                    <td><?= $row['description'] ?></td>
                                    <td><?= $row['t1'] == 1 ? 'Yes' : 'No' ?></td>
                                    <td><?= $row['t2'] == 1 ? 'Yes' : 'No' ?></td>
                                    <td><?= $row['t3'] == 1 ? 'Yes' : 'No' ?></td>
                                    <td><?= $row['status'] == 1 ? 'Active' : 'Inactive' ?></td>
                                    <td><?= $row['description'] ?></td>
                                    <td>
                                        <a href="dashboard.php?editp=<?= $row['id'] ?>" class="btn btn-primary btn-sm">Edit</a>
                                        <a href="dashboard.php?deletep=<?= $row['id'] ?>" class="btn btn-danger btn-sm">Delete</a>
                                    </td>
                                </tr>
                            <?php
                                    $serial++;
                                }
                            }
                            ?>
                            </tbody>
                        </table>


                        <?php
                        if (isset($_GET['deletep'])) {
                            $id = $_GET['deletep'];
                            $sql = "DELETE FROM products WHERE id = ?";
                            $stmt = $conn->prepare($sql);
                            $stmt->bind_param("i", $id);
                            $stmt->execute();
                            $stmt->close();
                            echo '<script>window.location.href="dashboard.php";</script>';
                            exit();
                        }
                        ?>
    </div>
</body>
</html>
