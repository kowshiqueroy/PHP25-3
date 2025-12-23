<?php
require '../config/db.php';
require 'includes/header.php';

$action = $_GET['action'] ?? 'list';
$id = $_GET['id'] ?? 0;
$error = '';
$success = '';

// Handle Add/Edit Notice
if ($_SERVER['REQUEST_METHOD'] == 'POST' && ($action === 'add' || $action === 'edit')) {
    $title = trim($_POST['title']);
    $content = trim($_POST['content']);
    $publish_date = $_POST['publish_date'] ?: date('Y-m-d');
    $file_path = $_POST['existing_file'] ?? null;

    if (empty($title)) {
        $error = 'Title is required.';
    } else {
        // Handle file upload
        if (!empty($_FILES['notice_file']['name'])) {
            $target_dir = "../assets/uploads/";
            $file_name = time() . '_' . basename($_FILES["notice_file"]["name"]);
            $target_file = $target_dir . $file_name;
            
            // Allow only certain file types
            $allowed_types = ['pdf', 'doc', 'docx', 'jpg', 'jpeg', 'png'];
            $file_ext = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

            if (!in_array($file_ext, $allowed_types)) {
                $error = "Sorry, only PDF, DOC, DOCX, JPG, JPEG, & PNG files are allowed.";
            } elseif ($_FILES["notice_file"]["size"] > 10000000) { // 10MB
                $error = "Sorry, your file is too large.";
            } else {
                if (move_uploaded_file($_FILES["notice_file"]["tmp_name"], $target_file)) {
                    // Delete old file if it exists
                    if ($action === 'edit' && $file_path && file_exists($target_dir . $file_path)) {
                        unlink($target_dir . $file_path);
                    }
                    $file_path = $file_name;
                } else {
                    $error = "Sorry, there was an error uploading your file.";
                }
            }
        }

        if (empty($error)) {
            if ($action === 'add') {
                $stmt = $pdo->prepare("INSERT INTO notices (title, content, publish_date, file_path) VALUES (?, ?, ?, ?)");
                $stmt->execute([$title, $content, $publish_date, $file_path]);
            } elseif ($action === 'edit' && $id) {
                $stmt = $pdo->prepare("UPDATE notices SET title=?, content=?, publish_date=?, file_path=? WHERE id=?");
                $stmt->execute([$title, $content, $publish_date, $file_path, $id]);
            }
            header("Location: notices.php");
            exit();
        }
    }
}

// Handle Delete Notice
if ($action === 'delete' && $id) {
    // Get the file path to delete the file
    $stmt = $pdo->prepare("SELECT file_path FROM notices WHERE id = ?");
    $stmt->execute([$id]);
    $file_to_delete = $stmt->fetchColumn();

    if ($file_to_delete && file_exists("../assets/uploads/" . $file_to_delete)) {
        unlink("../assets/uploads/" . $file_to_delete);
    }

    $stmt = $pdo->prepare("DELETE FROM notices WHERE id = ?");
    $stmt->execute([$id]);
    
    header("Location: notices.php");
    exit();
}

// Fetch notice for editing
$notice = null;
if ($action === 'edit' && $id) {
    $stmt = $pdo->prepare("SELECT * FROM notices WHERE id = ?");
    $stmt->execute([$id]);
    $notice = $stmt->fetch();
}

// Fetch all notices
$notices = $pdo->query("SELECT * FROM notices ORDER BY publish_date DESC")->fetchAll();
?>

<h2 class="mb-4">Manage Notices</h2>

<?php if ($action === 'add' || $action === 'edit'): ?>
    <div class="card">
        <h3><?php echo ucfirst($action); ?> Notice</h3>
        <?php if ($error) echo "<div class='alert alert-danger'>$error</div>"; ?>
        <form method="POST" action="notices.php?action=<?php echo $action; ?><?php if ($id) echo '&id=' . $id; ?>" enctype="multipart/form-data">
            <input type="hidden" name="existing_file" value="<?php echo htmlspecialchars($notice['file_path'] ?? ''); ?>">
            
            <div class="form-group">
                <label>Title</label>
                <input type="text" name="title" class="form-control" value="<?php echo htmlspecialchars($notice['title'] ?? ''); ?>" required>
            </div>
            
            <div class="form-group">
                <label>Publish Date</label>
                <input type="date" name="publish_date" class="form-control" value="<?php echo htmlspecialchars($notice['publish_date'] ?? date('Y-m-d')); ?>">
            </div>

            <div class="form-group">
                <label>Content</label>
                <textarea name="content" rows="5" class="form-control"><?php echo htmlspecialchars($notice['content'] ?? ''); ?></textarea>
            </div>

            <div class="form-group">
                <label>Attachment (PDF, Image, etc.)</label>
                <input type="file" name="notice_file" class="form-control">
                <?php if ($action === 'edit' && !empty($notice['file_path'])): ?>
                    <p class="mt-2">Current file: <a href="../assets/uploads/<?php echo htmlspecialchars($notice['file_path']); ?>" target="_blank"><?php echo htmlspecialchars($notice['file_path']); ?></a></p>
                <?php endif; ?>
            </div>
            
            <button type="submit" class="btn btn-primary mt-3"><?php echo ucfirst($action); ?> Notice</button>
            <a href="notices.php" class="btn btn-secondary mt-3">Cancel</a>
        </form>
    </div>
<?php endif; ?>

<div class="card">
    <a href="notices.php?action=add" class="btn btn-primary mb-3"><i class="fas fa-plus-circle"></i> Add New Notice</a>
    <div class="table-responsive">
        <table class="custom-table">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Title</th>
                    <th>File</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($notices as $n): ?>
                <tr>
                    <td><?php echo date("d M, Y", strtotime($n['publish_date'])); ?></td>
                    <td><?php echo htmlspecialchars($n['title']); ?></td>
                    <td>
                        <?php if ($n['file_path']): ?>
                            <a href="../assets/uploads/<?php echo htmlspecialchars($n['file_path']); ?>" target="_blank">View File</a>
                        <?php endif; ?>
                    </td>
                    <td>
                        <a href="notices.php?action=edit&id=<?php echo $n['id']; ?>" class="btn btn-sm btn-info"><i class="fas fa-edit"></i></a>
                        <a href="notices.php?action=delete&id=<?php echo $n['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure?')"><i class="fas fa-trash"></i></a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require 'includes/footer.php'; ?>
