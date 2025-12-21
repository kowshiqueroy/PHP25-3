<?php
require '../config/db.php';
require 'includes/header.php'; // The Magic Line

// Logic specific to this page (Add/Delete)
$msg = "";
// Delete logic
if (isset($_GET['delete'])) {
    $delete_id = $_GET['delete'];
    $stmt = $pdo->prepare("DELETE FROM notices WHERE id = ?");
    if ($stmt->execute([$delete_id])) {
        $msg = "Notice deleted successfully.";
    }
}
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['add_notice'])) {
        $title = $_POST['title'];
        $content = $_POST['content'];
        $publish_date = $_POST['publish_date'] ?: date('Y-m-d');

        // Handle file upload
        $file_name = null;
        if (!empty($_FILES['notice_file']['name'])) {
            $file_name = time() . '_' . $_FILES['notice_file']['name'];
            move_uploaded_file($_FILES['notice_file']['tmp_name'], "../assets/uploads/" . $file_name);
        }

        $stmt = $pdo->prepare("INSERT INTO notices (title, content, publish_date, file_path) VALUES (?, ?, ?, ?)");
        if ($stmt->execute([$title, $content, $publish_date, $file_name])) {
            $msg = "Notice published successfully.";
        }
    }
}
// Fetch logic
$notices = $pdo->query("SELECT * FROM notices ORDER BY publish_date DESC")->fetchAll();
?>

<div class="card">
    <h3 style="margin-top: 0;">Publish New Notice</h3>
    
    <form method="POST" enctype="multipart/form-data">
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
            <div>
                <label style="display: block; margin-bottom: 5px;">Title</label>
                <input type="text" name="title" class="form-control" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px;" required>
            </div>
            <div>
                <label style="display: block; margin-bottom: 5px;">Date</label>
                <input type="date" name="publish_date" value="<?php echo date('Y-m-d'); ?>" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px;">
            </div>
        </div>
        
        <div style="margin-top: 15px;">
            <label style="display: block; margin-bottom: 5px;">Content</label>
            <textarea name="content" rows="3" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px;"></textarea>
        </div>

        <div style="margin-top: 15px;">
             <label style="display: block; margin-bottom: 5px;">File (PDF/Image)</label>
             <input type="file" name="notice_file">
        </div>

        <button type="submit" name="add_notice" class="btn btn-primary" style="margin-top: 15px;">Publish Notice</button>
    </form>
</div>

<div class="card">
    <h3>Notice List</h3>
    <div style="overflow-x: auto;"> <table>
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Title</th>
                    <th>Content</th>
                    <th>File</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($notices as $n): ?>
                <tr>
                    <td><?php echo $n['publish_date']; ?></td>
                    <td><?php echo htmlspecialchars($n['title']); ?></td>
                    <td><?php echo nl2br(htmlspecialchars($n['content'])); ?></td>
                    <td>
                        <?php if ($n['file_path']): ?>
                            <a href="../assets/uploads/<?php echo $n['file_path']; ?>" target="_blank">View File</a>
                        <?php else: ?>
                            N/A
                        <?php endif; ?>
                    </td>
                    <td>
                        <a href="?delete=<?php echo $n['id']; ?>" class="btn btn-danger" style="padding: 5px 10px; font-size: 0.8rem;" onclick="return confirm('Delete?')">
                            <i class="fas fa-trash"></i>
                        </a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require 'includes/footer.php'; ?>