<?php
require_once 'includes/auth_check.php';
require_once 'includes/db_connect.php';

check_login();
check_role(['Manager', 'Data Entry']);

$conn = connect_db();

// Handle Add/Edit Product
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['add_product'])) {
        $name = $_POST['name'];
        $category_id = $_POST['category_id'];
        $sku = $_POST['sku'];
        $description = $_POST['description'];
        $image_path = null;

        // Handle image upload
        if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
            $target_dir = "uploads/";
            $image_name = basename($_FILES['image']['name']);
            $target_file = $target_dir . uniqid() . "_" . $image_name;
            $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

            // Allow certain file formats
            $allowed_types = array("jpg", "png", "jpeg", "gif");
            if (in_array($imageFileType, $allowed_types)) {
                if (move_uploaded_file($_FILES['image']['tmp_name'], $target_file)) {
                    $image_path = $target_file;
                } else {
                    $_SESSION['message'] = "Error uploading image.";
                    $_SESSION['message_type'] = "danger";
                }
            } else {
                $_SESSION['message'] = "Sorry, only JPG, JPEG, PNG & GIF files are allowed.";
                $_SESSION['message_type'] = "danger";
            }
        }

        $stmt = $conn->prepare("INSERT INTO products (name, category_id, sku, image_path, description) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("sisss", $name, $category_id, $sku, $image_path, $description);
        if ($stmt->execute()) {
            $_SESSION['message'] = "Product added successfully!";
            $_SESSION['message_type'] = "success";
        } else {
            $_SESSION['message'] = "Error adding product: " . $conn->error;
            $_SESSION['message_type'] = "danger";
        }
        $stmt->close();
    } elseif (isset($_POST['edit_product'])) {
        $id = $_POST['product_id'];
        $name = $_POST['name'];
        $category_id = $_POST['category_id'];
        $sku = $_POST['sku'];
        $description = $_POST['description'];
        $current_image_path = $_POST['current_image_path'];
        $image_path = $current_image_path;

        // Handle new image upload
        if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
            $target_dir = "uploads/";
            $image_name = basename($_FILES['image']['name']);
            $target_file = $target_dir . uniqid() . "_" . $image_name;
            $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

            $allowed_types = array("jpg", "png", "jpeg", "gif");
            if (in_array($imageFileType, $allowed_types)) {
                if (move_uploaded_file($_FILES['image']['tmp_name'], $target_file)) {
                    $image_path = $target_file;
                    // Delete old image if it exists and is not the default
                    if ($current_image_path && file_exists($current_image_path)) {
                        unlink($current_image_path);
                    }
                } else {
                    $_SESSION['message'] = "Error uploading new image.";
                    $_SESSION['message_type'] = "danger";
                }
            } else {
                $_SESSION['message'] = "Sorry, only JPG, JPEG, PNG & GIF files are allowed for new image.";
                $_SESSION['message_type'] = "danger";
            }
        }

        $stmt = $conn->prepare("UPDATE products SET name = ?, category_id = ?, sku = ?, image_path = ?, description = ? WHERE id = ?");
        $stmt->bind_param("sisssi", $name, $category_id, $sku, $image_path, $description, $id);
        if ($stmt->execute()) {
            $_SESSION['message'] = "Product updated successfully!";
            $_SESSION['message_type'] = "success";
        } else {
            $_SESSION['message'] = "Error updating product: " . $conn->error;
            $_SESSION['message_type'] = "danger";
        }
        $stmt->close();
    }
    header("location: product_management.php");
    exit();
}

// Handle Delete Product
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];

    // Get image path before deleting product record
    $image_to_delete = null;
    $stmt = $conn->prepare("SELECT image_path FROM products WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->bind_result($image_to_delete);
    $stmt->fetch();
    $stmt->close();

    $stmt = $conn->prepare("DELETE FROM products WHERE id = ?");
    $stmt->bind_param("i", $id);
    if ($stmt->execute()) {
        // Delete image file from server
        if ($image_to_delete && file_exists($image_to_delete)) {
            unlink($image_to_delete);
        }
        $_SESSION['message'] = "Product deleted successfully!";
        $_SESSION['message_type'] = "success";
    } else {
        $_SESSION['message'] = "Error deleting product: " . $conn->error;
        $_SESSION['message_type'] = "danger";
    }
    $stmt->close();
    header("location: product_management.php");
    exit();
}

// Fetch all products with their category names
$products = [];
$result = $conn->query("SELECT p.id, p.name, c.name as category_name, p.sku, p.image_path, p.description, p.category_id FROM products p JOIN categories c ON p.category_id = c.id");
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $products[] = $row;
    }
}

// Fetch categories for dropdown
$categories = [];
$result_categories = $conn->query("SELECT id, name FROM categories");
if ($result_categories->num_rows > 0) {
    while ($row = $result_categories->fetch_assoc()) {
        $categories[] = $row;
    }
}

$conn->close();

$user_role_name = get_user_role_name($_SESSION['role_id']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Product Management - <?php echo APP_NAME; ?></title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="#"><?php echo APP_NAME; ?></a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                    <li class="nav-item">
                        <a class="nav-link" href="dashboard.php">Dashboard</a>
                    </li>
                    <?php if ($user_role_name == 'Admin' || $user_role_name == 'Manager'): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="user_management.php">User Management</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="store_management.php">Store Management</a>
                    </li>
                    <?php endif; ?>
                    <?php if ($user_role_name == 'Admin' || $user_role_name == 'Manager' || $user_role_name == 'Data Entry'): ?>
                    <li class="nav-item">
                        <a class="nav-link active" aria-current="page" href="product_management.php">Product Management</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="category_management.php">Category Management</a>
                    </li>
                    <?php endif; ?>
                    <?php if ($user_role_name == 'Admin' || $user_role_name == 'Manager' || $user_role_name == 'Data Entry' || $user_role_name == 'QC'): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="transactions.php">Transactions</a>
                    </li>
                    <?php endif; ?>
                    <?php if ($user_role_name == 'Admin' || $user_role_name == 'Manager' || $user_role_name == 'QC'): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="qc_workflow.php">QC Workflow</a>
                    </li>
                    <?php endif; ?>
                    <?php if ($user_role_name == 'Admin' || $user_role_name == 'Manager' || $user_role_name == 'Viewer'): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="reports.php">Reports</a>
                    </li>
                    <?php endif; ?>
                </ul>
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <span class="navbar-text me-3">
                            Logged in as: <?php echo htmlspecialchars($_SESSION['username']); ?> (<?php echo htmlspecialchars($user_role_name); ?>)
                        </span>
                    </li>
                    <li class="nav-item">
                        <a class="btn btn-outline-light" href="logout.php">Logout</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <h2>Product Management</h2>

        <?php if (isset($_SESSION['message'])): ?>
            <div class="alert alert-<?php echo $_SESSION['message_type']; ?> alert-dismissible fade show" role="alert">
                <?php echo $_SESSION['message']; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            <?php 
            unset($_SESSION['message']);
            unset($_SESSION['message_type']);
            ?>
        <?php endif; ?>

        <!-- Add New Product Form -->
        <div class="card mb-4">
            <div class="card-header">
                Add New Product
            </div>
            <div class="card-body">
                <form action="product_management.php" method="POST" enctype="multipart/form-data">
                    <div class="mb-3">
                        <label for="name" class="form-label">Product Name</label>
                        <input type="text" class="form-control" id="name" name="name" required>
                    </div>
                    <div class="mb-3">
                        <label for="category_id" class="form-label">Category</label>
                        <select class="form-select" id="category_id" name="category_id" required>
                            <?php foreach ($categories as $category): ?>
                                <option value="<?php echo htmlspecialchars($category['id']); ?>"><?php echo htmlspecialchars($category['name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="sku" class="form-label">SKU</label>
                        <input type="text" class="form-control" id="sku" name="sku">
                    </div>
                    <div class="mb-3">
                        <label for="image" class="form-label">Product Image</label>
                        <input type="file" class="form-control" id="image" name="image" accept="image/*">
                    </div>
                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control" id="description" name="description" rows="3"></textarea>
                    </div>
                    <button type="submit" name="add_product" class="btn btn-primary">Add Product</button>
                </form>
            </div>
        </div>

        <!-- Existing Products Table -->
        <div class="card">
            <div class="card-header">
                Existing Products
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Image</th>
                                <th>Name</th>
                                <th>Category</th>
                                <th>SKU</th>
                                <th>Description</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($products)): ?>
                                <tr>
                                    <td colspan="7" class="text-center">No products found.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($products as $product): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($product['id']); ?></td>
                                        <td>
                                            <?php if ($product['image_path']): ?>
                                                <img src="<?php echo htmlspecialchars($product['image_path']); ?>" alt="Product Image" style="width: 50px; height: 50px; object-fit: cover;">
                                            <?php else: ?>
                                                No Image
                                            <?php endif; ?>
                                        </td>
                                        <td><?php echo htmlspecialchars($product['name']); ?></td>
                                        <td><?php echo htmlspecialchars($product['category_name']); ?></td>
                                        <td><?php echo htmlspecialchars($product['sku']); ?></td>
                                        <td><?php echo htmlspecialchars($product['description']); ?></td>
                                        <td>
                                            <button type="button" class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#editProductModal" 
                                                    data-id="<?php echo $product['id']; ?>" 
                                                    data-name="<?php echo htmlspecialchars($product['name']); ?>" 
                                                    data-category_id="<?php echo htmlspecialchars($product['category_id']); ?>" 
                                                    data-sku="<?php echo htmlspecialchars($product['sku']); ?>" 
                                                    data-image_path="<?php echo htmlspecialchars($product['image_path']); ?>" 
                                                    data-description="<?php echo htmlspecialchars($product['description']); ?>">
                                                Edit
                                            </button>
                                            <a href="product_management.php?delete=<?php echo $product['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this product?');">Delete</a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Product Modal -->
    <div class="modal fade" id="editProductModal" tabindex="-1" aria-labelledby="editProductModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editProductModalLabel">Edit Product</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="product_management.php" method="POST" enctype="multipart/form-data">
                    <div class="modal-body">
                        <input type="hidden" id="edit_product_id" name="product_id">
                        <input type="hidden" id="edit_current_image_path" name="current_image_path">
                        <div class="mb-3">
                            <label for="edit_name" class="form-label">Product Name</label>
                            <input type="text" class="form-control" id="edit_name" name="name" required>
                        </div>
                        <div class="mb-3">
                            <label for="edit_category_id" class="form-label">Category</label>
                            <select class="form-select" id="edit_category_id" name="category_id" required>
                                <?php foreach ($categories as $category): ?>
                                    <option value="<?php echo htmlspecialchars($category['id']); ?>"><?php echo htmlspecialchars($category['name']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="edit_sku" class="form-label">SKU</label>
                            <input type="text" class="form-control" id="edit_sku" name="sku">
                        </div>
                        <div class="mb-3">
                            <label for="edit_image" class="form-label">Product Image (leave blank to keep current)</label>
                            <input type="file" class="form-control" id="edit_image" name="image" accept="image/*">
                            <div id="current_image_preview" class="mt-2">
                                <!-- Image preview will be loaded here by JS -->
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="edit_description" class="form-label">Description</label>
                            <textarea class="form-control" id="edit_description" name="description" rows="3"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" name="edit_product" class="btn btn-primary">Save changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        var editProductModal = document.getElementById('editProductModal');
        editProductModal.addEventListener('show.bs.modal', function (event) {
            var button = event.relatedTarget;
            var id = button.getAttribute('data-id');
            var name = button.getAttribute('data-name');
            var category_id = button.getAttribute('data-category_id');
            var sku = button.getAttribute('data-sku');
            var image_path = button.getAttribute('data-image_path');
            var description = button.getAttribute('data-description');

            var modalBodyInputId = editProductModal.querySelector('#edit_product_id');
            var modalBodyInputName = editProductModal.querySelector('#edit_name');
            var modalBodySelectCategory = editProductModal.querySelector('#edit_category_id');
            var modalBodyInputSku = editProductModal.querySelector('#edit_sku');
            var modalBodyInputCurrentImagePath = editProductModal.querySelector('#edit_current_image_path');
            var modalBodyInputDescription = editProductModal.querySelector('#edit_description');
            var currentImagePreview = editProductModal.querySelector('#current_image_preview');

            modalBodyInputId.value = id;
            modalBodyInputName.value = name;
            modalBodySelectCategory.value = category_id;
            modalBodyInputSku.value = sku;
            modalBodyInputCurrentImagePath.value = image_path;
            modalBodyInputDescription.value = description;

            // Display current image preview
            currentImagePreview.innerHTML = '';
            if (image_path) {
                var img = document.createElement('img');
                img.src = image_path;
                img.alt = "Current Product Image";
                img.style.width = "100px";
                img.style.height = "100px";
                img.style.objectFit = "cover";
                currentImagePreview.appendChild(img);
            } else {
                currentImagePreview.innerHTML = 'No current image.';
            }

            // Clear file input
            editProductModal.querySelector('#edit_image').value = '';
        });
    </script>
</body>
</html>