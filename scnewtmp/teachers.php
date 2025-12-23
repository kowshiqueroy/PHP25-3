<?php 
require 'config/db.php';
require 'includes/header.php'; 

// Fetch Data
$teachers = $pdo->query("SELECT * FROM teachers ORDER BY sort_order ASC")->fetchAll();
?>

<div class="container" style="margin-top: 40px; margin-bottom: 50px;">
    
    <div style="text-align: center; max-width: 600px; margin: 0 auto;">
        <h1 style="color: var(--primary); margin-bottom: 10px;">আমাদের সম্মানীত শিক্ষকবৃন্দ</h1>
        <p style="color: #666;">মানুষ গড়ার কারিগর আমাদের অভিজ্ঞ ও দক্ষ শিক্ষকমণ্ডলী</p>
        <div style="width: 50px; height: 3px; background: var(--secondary); margin: 15px auto;"></div>
    </div>

    <div class="teacher-grid">
        <?php foreach($teachers as $t): ?>
        <div class="teacher-card">
            <?php 
                $imgSrc = "assets/uploads/" . $t['image_path'];
                if(empty($t['image_path']) || !file_exists($imgSrc)) {
                    $imgSrc = "https://via.placeholder.com/150?text=No+Image"; 
                }
            ?>
            <img src="<?php echo $imgSrc; ?>" alt="<?php echo htmlspecialchars($t['name']); ?>" class="teacher-img">
            
            <div class="teacher-info">
                <h3><?php echo htmlspecialchars($t['name']); ?></h3>
                <span><?php echo htmlspecialchars($t['designation']).' - '.htmlspecialchars($t['subject']); ?></span>
                <?php if($t['education']): ?>
                    <p><i class="fas fa-book"></i> <?php echo htmlspecialchars($t['education']); ?></p>
                <?php endif; ?>
                <?php if($t['phone']): ?>
                    <a style="color: var(--primary); text-decoration: none;" href="tel:<?php echo $t['phone']; ?>"><i class="fas fa-phone"></i> <?php echo htmlspecialchars($t['phone']); ?></a>
                <?php endif; ?>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

</div>

<?php require 'includes/footer.php'; ?>