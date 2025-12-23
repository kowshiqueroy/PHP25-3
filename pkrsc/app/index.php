<?php require 'header.php'; ?>

<div class="row">
    <div class="col-md-12 mb-4">
        <h2 class="fw-bold">Dashboard</h2>
        <p class="text-muted">Welcome to <?php echo htmlspecialchars($school_name); ?> Management System.</p>
    </div>
</div>

<div class="row g-4 mb-4">
    <?php
    // Fetch counts securely
    $classCount = $pdo->query("SELECT count(*) FROM classes")->fetchColumn();
    $subjectCount = $pdo->query("SELECT count(*) FROM subjects")->fetchColumn();
    $entryCount = $pdo->query("SELECT count(DISTINCT CONCAT(class_id, exam_term)) FROM marks")->fetchColumn();
    ?>
    
    <div class="col-md-4">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-body d-flex align-items-center">
                <div class="bg-primary text-white rounded-circle p-3 me-3">
                    <i class="fa-solid fa-chalkboard-user fa-xl"></i>
                </div>
                <div>
                    <h6 class="text-muted mb-1">Total Classes</h6>
                    <h3 class="mb-0 fw-bold"><?php echo $classCount; ?></h3>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-body d-flex align-items-center">
                <div class="bg-success text-white rounded-circle p-3 me-3">
                    <i class="fa-solid fa-book fa-xl"></i>
                </div>
                <div>
                    <h6 class="text-muted mb-1">Active Subjects</h6>
                    <h3 class="mb-0 fw-bold"><?php echo $subjectCount; ?></h3>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-body d-flex align-items-center">
                <div class="bg-warning text-dark rounded-circle p-3 me-3">
                    <i class="fa-solid fa-file-pen fa-xl"></i>
                </div>
                <div>
                    <h6 class="text-muted mb-1">Exam Entries</h6>
                    <h3 class="mb-0 fw-bold"><?php echo $entryCount; ?></h3>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-6 mb-3">
        <div class="card shadow-sm h-100">
            <div class="card-header bg-white fw-bold">Quick Actions</div>
            <div class="list-group list-group-flush">
                <a href="marks_entry.php" class="list-group-item list-group-item-action">
                    <i class="fa-solid fa-plus-circle text-primary me-2"></i> Enter New Marks
                </a>
                <a href="classes.php" class="list-group-item list-group-item-action">
                    <i class="fa-solid fa-list text-primary me-2"></i> Manage Class Lists
                </a>
                <a href="settings.php" class="list-group-item list-group-item-action">
                    <i class="fa-solid fa-gear text-secondary me-2"></i> Update School Settings
                </a>
            </div>
        </div>
    </div>
    <div class="col-md-6 mb-3">
        <div class="card shadow-sm h-100">
            <div class="card-header bg-white fw-bold">Recent Updates</div>
            <div class="card-body text-center text-muted py-5">
                <i class="fa-solid fa-clock-rotate-left fa-2x mb-2"></i>
                <p>No recent activity logs available.</p>
            </div>
        </div>
    </div>
</div>

<?php require 'footer.php'; ?>