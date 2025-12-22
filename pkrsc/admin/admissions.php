<?php
require '../config/db.php';
require 'includes/header.php';

$action = $_GET['action'] ?? '';
$id = $_GET['id'] ?? 0;

if ($action && $id) {
    if ($action === 'approve') {
        // Get application data
        $stmt = $pdo->prepare("SELECT * FROM admission_applications WHERE id = ? AND status = 'pending'");
        $stmt->execute([$id]);
        $app = $stmt->fetch();

        if ($app) {
            // Start a transaction
            $pdo->beginTransaction();
            try {
                // 1. Update application status
                $updateStmt = $pdo->prepare("UPDATE admission_applications SET status = 'approved' WHERE id = ?");
                $updateStmt->execute([$id]);

                // 2. Create a new student record
                // Generate a unique roll number for now. This can be improved.
                $roll_no = $app['class_req'] . date('y') . $app['id'];

                $studentStmt = $pdo->prepare(
                    "INSERT INTO students (name, roll_no, class_name, father_name, phone, image_path) 
                     VALUES (?, ?, ?, ?, ?, 'default.png')"
                );
                $studentStmt->execute([$app['student_name'], $roll_no, $app['class_req'], $app['father_name'], $app['phone']]);
                $student_id = $pdo->lastInsertId();

                // 3. Create a user account for the student
                $username = strtolower(str_replace(' ', '', $app['student_name'])) . $student_id;
                $password = 'welcome123'; // Default password
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);

                $userStmt = $pdo->prepare("INSERT INTO users (username, password, role, full_name) VALUES (?, ?, 'student', ?)");
                $userStmt->execute([$username, $hashed_password, $app['student_name']]);
                
                // Commit the transaction
                $pdo->commit();

            } catch (Exception $e) {
                // Rollback the transaction if something failed
                $pdo->rollBack();
                die("Error processing approval: " . $e->getMessage());
            }
        }
    } elseif ($action === 'reject') {
        $stmt = $pdo->prepare("UPDATE admission_applications SET status = 'rejected' WHERE id = ?");
        $stmt->execute([$id]);
    }

    header("Location: admissions.php");
    exit;
}

// Fetch all applications
$apps = $pdo->query("SELECT * FROM admission_applications ORDER BY applied_at DESC")->fetchAll();
?>

<h2 class="mb-4">Manage Admissions</h2>

<div class="card">
    <div class="table-responsive">
        <table class="custom-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Student Info</th>
                    <th>Class</th>
                    <th>Contact</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($apps) > 0): ?>
                    <?php foreach ($apps as $app): ?>
                        <tr>
                            <td>#<?php echo $app['id']; ?></td>
                            <td>
                                <strong><?php echo htmlspecialchars($app['student_name']); ?></strong><br>
                                <small>Father: <?php echo htmlspecialchars($app['father_name']); ?></small>
                            </td>
                            <td>Class <?php echo htmlspecialchars($app['class_req']); ?></td>
                            <td><?php echo htmlspecialchars($app['phone']); ?></td>
                            <td>
                                <span class="badge status-<?php echo htmlspecialchars($app['status']); ?>">
                                    <?php echo htmlspecialchars($app['status']); ?>
                                </span>
                            </td>
                            <td>
                                <?php if ($app['status'] == 'pending'): ?>
                                    <a href="?action=approve&id=<?php echo $app['id']; ?>" class="btn btn-sm btn-success" onclick="return confirm('Are you sure you want to approve this application?');">
                                        <i class="fas fa-check"></i> Approve
                                    </a>
                                    <a href="?action=reject&id=<?php echo $app['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to reject this application?');">
                                        <i class="fas fa-times"></i> Reject
                                    </a>
                                <?php else: ?>
                                    <span class="text-muted">Processed</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6" class="text-center p-4">No new admission applications found.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<style>
.badge {
    padding: 5px 10px;
    border-radius: 15px;
    color: white;
    font-weight: bold;
    text-transform: capitalize;
}
.status-pending { background-color: #f59e0b; }
.status-approved { background-color: #10b981; }
.status-rejected { background-color: #ef4444; }
</style>

<?php require 'includes/footer.php'; ?>
