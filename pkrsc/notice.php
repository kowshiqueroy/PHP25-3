<?php
require 'includes/header.php';

// Fetch all notices
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
                    <div class="notice-item" style="display: flex; gap: 20px; padding: 20px; border-bottom: 1px solid #eee; align-items: center;">
                        
                        <div style="background: var(--light); padding: 10px; border-radius: 8px; text-align: center; min-width: 80px; border: 1px solid #ddd;">
                            <span style="display: block; font-size: 1.5rem; font-weight: bold; color: var(--primary);">
                                <?php echo date('d', strtotime($notice['publish_date'])); ?>
                            </span>
                            <span style="display: block; font-size: 0.8rem; text-transform: uppercase;">
                                <?php echo date('M', strtotime($notice['publish_date'])); ?>
                            </span>
                        </div>

                        <div style="flex: 1;">
                            <h3 style="margin: 0 0 5px 0; font-size: 1.2rem;">
                                <?php echo htmlspecialchars($notice['title']); ?>
                            </h3>
                            <?php if($notice['content']): ?>
                                <p style="color: #666; font-size: 0.95rem; margin-bottom: 5px;">
                                    <?php echo htmlspecialchars($notice['content']); ?>
                                </p>
                            <?php endif; ?>
                            
                            <?php if($notice['file_path']): ?>
                                <a href="assets/uploads/<?php echo $notice['file_path']; ?>" target="_blank" class="btn" style="padding: 5px 15px; font-size: 0.8rem; background: var(--secondary);">
                                    <i class="fas fa-file-download"></i> ডাউনলোড
                                </a>
                            <?php endif; ?>
                        </div>

                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <p style="text-align: center; padding: 20px;">কোনো বিজ্ঞপ্তি পাওয়া যায়নি।</p>
        <?php endif; ?>
    </div>

</div>

<?php require 'includes/footer.php'; ?>