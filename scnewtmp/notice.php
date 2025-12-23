<?php
require 'includes/header.php';
require 'config/db.php';

$id = $_GET['id'] ?? null;

if ($id) {
    // Display a single notice
    $stmt = $pdo->prepare("SELECT * FROM notices WHERE id = ?");
    $stmt->execute([$id]);
    $notice = $stmt->fetch();
    ?>
    <div class="container" style="margin-top: 30px; margin-bottom: 50px;">
        <div class="card">
            <?php if ($notice): ?>
                <div style="text-align:center; padding-bottom: 15px; border-bottom: 1px solid #eee; margin-bottom: 20px;">
                    <h1 style="color: var(--primary);"><?php echo htmlspecialchars($notice['title']); ?></h1>
                    <p>Published on: <?php echo date('d F, Y', strtotime($notice['publish_date'])); ?></p>
                </div>
                
                <div class="notice-content" style="font-size: 1.1rem; line-height: 1.8;">
                    <?php echo nl2br(htmlspecialchars($notice['content'])); ?>
                </div>

                <?php if ($notice['file_path']): ?>
                    <div style="margin-top: 30px; text-align: center;">
                        <a href="assets/uploads/<?php echo htmlspecialchars($notice['file_path']); ?>" target="_blank" class="btn btn-primary">
                            <i class="fas fa-file-download"></i> Download Attached File
                        </a>
                    </div>
                <?php endif; ?>
            <?php else: ?>
                <p style="text-align: center; padding: 20px;">Notice not found.</p>
            <?php endif; ?>
        </div>
        <a href="notice.php" class="btn btn-secondary mt-3"> &larr; Back to all notices</a>
    </div>
    <?php
} else {
    // Display all notices
    $stmt = $pdo->query("SELECT * FROM notices ORDER BY publish_date DESC");
    $notices = $stmt->fetchAll();
    ?>
    <div class="container" style="margin-top: 30px; margin-bottom: 50px;">
        <div style="text-align: center; margin-bottom: 30px;">
            <h1 style="color: var(--primary);">নোটিশ বোর্ড</h1>
            <p>সকল প্রশাসনিক ও একাডেমিক বিজ্ঞপ্তি নিচে দেখুন</p>
        </div>

        <div class="card">
            <?php if(count($notices) > 0): ?>
                <div style="display: flex; flex-direction: column; gap: 0;">
                    <?php foreach($notices as $notice): ?>
                        <a href="notice.php?id=<?php echo $notice['id']; ?>" class="notice-item-link">
                            <div class="notice-item">
                                <div class="notice-date">
                                    <span class="day"><?php echo date('d', strtotime($notice['publish_date'])); ?></span>
                                    <span class="month"><?php echo date('M', strtotime($notice['publish_date'])); ?></span>
                                </div>
                                <div class="notice-details">
                                    <h3><?php echo htmlspecialchars($notice['title']); ?></h3>
                                    <?php if($notice['file_path']): ?>
                                        <span class="download-link"><i class="fas fa-paperclip"></i> Attachment</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </a>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <p style="text-align: center; padding: 20px;">কোনো বিজ্ঞপ্তি পাওয়া যায়নি।</p>
            <?php endif; ?>
        </div>
    </div>

    <style>
        .notice-item-link { text-decoration: none; color: inherit; }
        .notice-item { display: flex; gap: 20px; padding: 20px; border-bottom: 1px solid #eee; align-items: center; transition: background-color 0.3s; }
        .notice-item:hover { background-color: #f9f9f9; }
        .notice-date { background: var(--light); padding: 10px; border-radius: 8px; text-align: center; min-width: 80px; border: 1px solid #ddd; }
        .notice-date .day { display: block; font-size: 1.5rem; font-weight: bold; color: var(--primary); }
        .notice-date .month { display: block; font-size: 0.8rem; text-transform: uppercase; }
        .notice-details h3 { margin: 0 0 5px 0; font-size: 1.2rem; }
        .download-link { font-size: 0.8rem; color: var(--secondary); }
    </style>
    <?php
}

require 'includes/footer.php';
?>
