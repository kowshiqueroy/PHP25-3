<?php
require_once '../config/db.php';

$message = '';
$knowledgeEntries = [];

// Fetch all knowledge entries
try {
    $stmt = $pdo->query("SELECT kb.id, kb.entity, kb.definition, u.username, kb.created_at FROM knowledge_base kb JOIN users u ON kb.source_user_id = u.id ORDER BY kb.created_at DESC");
    $knowledgeEntries = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $message = "Error fetching knowledge entries: " . $e->getMessage();
}

// Handle deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
    $deleteId = $_POST['delete_id'];
    try {
        $stmt = $pdo->prepare("DELETE FROM knowledge_base WHERE id = :id");
        if ($stmt->execute(['id' => $deleteId])) {
            $message = "Knowledge entry deleted successfully.";
            // Refresh entries after deletion
            header('Location: manageKnowledge.php');
            exit();
        } else {
            $message = "Error deleting knowledge entry.";
        }
    } catch (PDOException $e) {
        $message = "Error deleting knowledge entry: " . $e->getMessage();
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage shishuBot Knowledge</title>
    <link rel="stylesheet" href="../assets/style.css">
    <style>
        .knowledge-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        .knowledge-table th, .knowledge-table td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        .knowledge-table th {
            background-color: #f2f2f2;
        }
        .delete-form {
            display: inline;
        }
        .delete-button {
            background-color: #dc3545;
            color: white;
            border: none;
            padding: 5px 10px;
            border-radius: 4px;
            cursor: pointer;
        }
        .delete-button:hover {
            background-color: #c82333;
        }
    </style>
</head>
<body>
    <div class="login-container" style="max-width: 800px;">
        <h1>Manage shishuBot Knowledge</h1>
        <?php if (!empty($message)): ?>
            <p><?php echo $message; ?></p>
        <?php endif; ?>

        <?php if (empty($knowledgeEntries)): ?>
            <p>No knowledge entries found.</p>
        <?php else: ?>
            <table class="knowledge-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Entity</th>
                        <th>Definition</th>
                        <th>Source User</th>
                        <th>Created At</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($knowledgeEntries as $entry): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($entry['id']); ?></td>
                            <td><?php echo htmlspecialchars($entry['entity']); ?></td>
                            <td><?php echo htmlspecialchars($entry['definition']); ?></td>
                            <td><?php echo htmlspecialchars($entry['username']); ?></td>
                            <td><?php echo htmlspecialchars($entry['created_at']); ?></td>
                            <td>
                                <form method="POST" class="delete-form" onsubmit="return confirm('Are you sure you want to delete this entry?');">
                                    <input type="hidden" name="delete_id" value="<?php echo $entry['id']; ?>">
                                    <button type="submit" class="delete-button">Delete</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
        <p><a href="../shishubot/index.php">Go to Login</a></p>
    </div>
</body>
</html>
