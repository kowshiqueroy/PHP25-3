<?php
require '../config/db.php';
require 'includes/header.php'; 

// --- Action Logic (Approve/Reject) ---
if (isset($_GET['action']) && isset($_GET['id'])) {
    $id = $_GET['id'];
    $action = $_GET['action'];
    // Secure whitelist check
    if(in_array($action, ['approved', 'rejected'])) {
        $stmt = $pdo->prepare("UPDATE admission_applications SET status = ? WHERE id = ?");
        $stmt->execute([$action, $id]);
    }
    echo "<script>window.location.href='admissions.php';</script>";
    exit;
}

$stmt = $pdo->query("SELECT * FROM admission_applications ORDER BY applied_at DESC");
$apps = $stmt->fetchAll();
?>

<style>
    /* Force table to fit inside container */
    .table-responsive {
        width: 100%;
        overflow-x: auto;
        -webkit-overflow-scrolling: touch; /* Smooth scroll on mobile */
        border: 1px solid #eee;
        border-radius: 4px;
    }

    /* Table Styling */
    .custom-table {
        width: 100%;
        border-collapse: collapse;
        white-space: nowrap; /* Default: keep text on one line */
    }
    
    /* Allow address to wrap so it doesn't stretch the table too much */
    .wrap-text {
        white-space: normal;
        min-width: 150px;
        max-width: 250px;
        font-size: 0.85rem;
        line-height: 1.4;
    }

    /* Hide less important details on small mobile screens */
    @media (max-width: 600px) {
        .hide-mobile { display: none; }
    }
</style>

<div style="display: flex; flex-wrap: wrap; justify-content: space-between; align-items: center; margin-bottom: 20px; gap: 10px;">
    <h2 style="margin: 0;">Manage Admissions</h2>
    <span class="btn btn-primary" style="cursor: default;">
        Total: <?php echo count($apps); ?>
    </span>
</div>

<div class="card" style="padding: 0; overflow: hidden;"> 
    
    <div class="table-responsive">
        <table class="custom-table">
            <thead>
                <tr>
                    <th width="5%">ID</th>
                    <th>Student Info</th>
                    <th class="hide-mobile">Class Details</th>
                    <th>Address</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if(count($apps) > 0): ?>
                    <?php foreach($apps as $app): ?>
                    <tr>
                        <td style="font-weight: bold;">#<?php echo $app['id']; ?></td>
                        
                        <td>
                            <div style="font-weight: bold; color: var(--primary);"><?php echo htmlspecialchars($app['student_name']); ?></div>
                            <div style="font-size: 0.55rem; color: #aa5959ff; margin-top: 2px;">
                                <?php echo htmlspecialchars($app['father_name']).' | '.htmlspecialchars($app['mother_name']); ?>
                            </div>
                            <div style="font-size: 0.5rem; color: #777; margin-top: 2px;">
                                <?php echo $app['applied_at']; ?>
                            </div>
                            
                        </td>
                        
                        <td >
                            <span style="font-weight: 500;">Class <?php echo $app['class_req']; ?></span><br>
                            <span style="font-size: 0.8rem; color: #777;">
                                <?php echo $app['gender']; ?> | DOB: <?php echo $app['dob']; ?>
                            </span>
                            <div style="font-size: 0.8rem; color: #666; margin-top: 2px;">
                               <a style="color: var(--primary); text-decoration: none;" href="tel:<?php echo $app['phone']; ?>"> <i class="fas fa-phone-alt"></i> <?php echo htmlspecialchars($app['phone']); ?></a>
                            </div>
                        </td>
                        
                        <td class="wrap-text">
                            <?php echo htmlspecialchars($app['address']); ?>
                        </td>
                        
                        <td>
                            <?php 
                                $badges = [
                                    'pending' => '#f59e0b', // Orange
                                    'approved' => '#10b981', // Green
                                    'rejected' => '#ef4444'  // Red
                                ];
                                $color = $badges[$app['status']] ?? '#ccc';
                            ?>
                            <span style="background: <?php echo $color; ?>; color: white; padding: 4px 8px; border-radius: 4px; font-size: 0.75rem; text-transform: uppercase; font-weight: bold;">
                                <?php echo $app['status']; ?>
                            </span>
                        </td>
                        
                        <td>
                            <?php if($app['status'] == 'pending'): ?>
                                <div style="display: flex; gap: 5px;">
                                    <a href="?action=approved&id=<?php echo $app['id']; ?>" class="btn" style="background: #10b981; color: white; padding: 6px 10px;" onclick="return confirm('Approve?');" title="Approve">
                                        <i class="fas fa-check"></i>
                                    </a>
                                    <a href="?action=rejected&id=<?php echo $app['id']; ?>" class="btn" style="background: #ef4444; color: white; padding: 6px 10px;" onclick="return confirm('Reject?');" title="Reject">
                                        <i class="fas fa-times"></i>
                                    </a>
                                </div>
                            <?php else: ?>
                                <i class="fas fa-lock" style="color: #ccc;"></i>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6" style="text-align: center; padding: 20px; color: #666;">No applications found.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require 'includes/footer.php'; ?>