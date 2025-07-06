<?php
session_start();

require_once 'config.php'; // Include database configuration
// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

function createStaffsTable($conn) {
    $sql = "CREATE TABLE IF NOT EXISTS staffs (
        id INT AUTO_INCREMENT PRIMARY KEY,
        logo_link VARCHAR(255),
        company_name VARCHAR(50),
        photo_link VARCHAR(255),
        name VARCHAR(100),
        position VARCHAR(30),
        department VARCHAR(50),
        section VARCHAR(50),
        phone VARCHAR(15),
        joining_date DATE,
        status TINYINT(1) DEFAULT 1,
        exit_date DATE NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        created_by VARCHAR(50) DEFAULT 'system',
        updated_by VARCHAR(50) DEFAULT 'system'
    )";
    $conn->query($sql);

    // Add columns if table already exists
    $conn->query("ALTER TABLE staffs ADD COLUMN IF NOT EXISTS status TINYINT(1) DEFAULT 1");
    $conn->query("ALTER TABLE staffs ADD COLUMN IF NOT EXISTS exit_date DATE NULL");
}

// Get today's date in GMT+6
$dt = new DateTime('now', new DateTimeZone('Asia/Dhaka')); // GMT+6
$today_gmt6 = $dt->format('Y-m-d');

if (isset($_SESSION['username'])) {
    createStaffsTable($conn);

    // Handle Add/Edit
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
$logo_link = '';
    $photo_link = '';     

$logo_link = $conn->real_escape_string($_POST['logo_link']);
$photo_link = $conn->real_escape_string($_POST['photo_link']);
$google_drive_url_pattern = '/https:\/\/drive\.google\.com\/file\/d\/([^\/]+)\/view\?usp=sharing/';

if (preg_match($google_drive_url_pattern, $photo_link, $matches)) {
    $photo_link = "https://lh3.googleusercontent.com/d/" . $matches[1];
}

if (preg_match($google_drive_url_pattern, $logo_link, $matches)) {
    $logo_link = "https://lh3.googleusercontent.com/d/" . $matches[1];
}


         $company_name = $conn->real_escape_string($_POST['company_name']);
        $name = $conn->real_escape_string($_POST['name']);
        $position = $conn->real_escape_string($_POST['position']);
        $department = $conn->real_escape_string($_POST['department']);
        $section = $conn->real_escape_string($_POST['section']);
        $phone = $conn->real_escape_string($_POST['phone']);
        $joining_date = $conn->real_escape_string($_POST['joining_date']);
        $status = isset($_POST['status']) ? intval($_POST['status']) : 1;
        $exit_date = isset($_POST['exit_date']) && $_POST['exit_date'] !== '' ? "'".$conn->real_escape_string($_POST['exit_date'])."'" : "NULL";

        if ($id > 0) {
            // Update
            $updated_by = $conn->real_escape_string($_SESSION['username']);
            $sql = "UPDATE staffs SET logo_link='$logo_link', company_name='$company_name', photo_link='$photo_link', name='$name', position='$position', department='$department', section='$section', phone='$phone', joining_date='$joining_date', status=$status, exit_date=$exit_date, updated_by='$updated_by' WHERE id=$id";
            


        
        
        
        } else {
            // Insert
            $created_by = $conn->real_escape_string($_SESSION['username']);
            $sql = "INSERT INTO staffs (logo_link, company_name, photo_link, name, position, department, section, phone, joining_date, status, exit_date, created_by) VALUES ('$logo_link', '$company_name', '$photo_link', '$name', '$position', '$department', '$section', '$phone', '$joining_date', $status, $exit_date, '$created_by')";
        }
        $conn->query($sql);
        header("Location: staffs.php");
        exit;
    }

    // Handle Edit Request
    $editStaff = null;
    if (isset($_GET['edit'])) {
        $editId = intval($_GET['edit']);
        $res = $conn->query("SELECT * FROM staffs WHERE id=$editId");
        $editStaff = $res->fetch_assoc();
    }

    // Fetch all staffs
    $staffs = [];
    $res = $conn->query("SELECT * FROM staffs ORDER BY id DESC");
    while ($row = $res->fetch_assoc()) {
        $staffs[] = $row;
    }
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <title>Staffs Admin Panel</title>
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
        <style>
            body { background: #f4f6fa; }
            .container { margin-top: 40px; max-width: 1200px; }
            .table img { max-width: 48px; border-radius: 6px; }
            .admin-header {
                display: flex;
                justify-content: space-between;
                align-items: center;
                margin-bottom: 2rem;
            }
            .logout-btn {
                background: #e74c3c;
                color: #fff;
                border: none;
                padding: 0.5rem 1.2rem;
                border-radius: 4px;
                font-weight: 500;
                transition: background 0.2s;
            }
            .logout-btn:hover { background: #c0392b; }
            .search-bar .form-control, .search-bar .btn { min-width: 120px; }
            @media (max-width: 767px) {
                .container { margin-top: 10px; }
                .table img { max-width: 32px; }
                .admin-header { flex-direction: column; gap: 1rem; }
            }
        </style>
    </head>
    <body>
    <div class="container">
        <div class="admin-header">
            <h2 class="mb-0">Staffs Admin Panel</h2>
            <button type="button" class="logout-btn" onclick="window.location.href='?logout=1'">Logout</button>
        </div>

        <?php // Handle logout
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: index.php");
    exit;
}
?>
        <div class="card mb-4">
            <div class="card-header"><?= $editStaff ? 'Edit Staff' : 'Add Staff' ?></div>
            <div class="card-body">
                <form method="post">
                    <?php if ($editStaff): ?>
                        <input type="hidden" name="id" value="<?= $editStaff['id'] ?>">
                    <?php endif; ?>
                    <div class="row g-2">
                        <div class="col-md-3 col-12 mb-2">
                            <input type="text" name="logo_link" class="form-control" placeholder="Logo Link" value="<?= htmlspecialchars($editStaff['logo_link'] ?? '../logo.png') ?>" required>
                        </div>
                        <div class="col-md-3 col-12 mb-2 position-relative">
                            <input type="text" name="company_name" class="form-control" id="company-name-input" placeholder="Company Name" value="<?= htmlspecialchars($editStaff['company_name'] ?? '') ?>" autocomplete="off" required>
                            <div id="company-suggestions" class="list-group position-absolute w-100" style="z-index: 10; display: none; max-height: 200px; overflow-y: auto;"></div>
                        </div>
                        <script>
                            // Example company names, replace with AJAX if needed
                            const companies = [
                                "Ovijat Group", "SHARM", "Ovijat Food",
                            <?php
                            // Fetch unique company names from the database
                            $companyNames = [];
                            $res = $conn->query("SELECT DISTINCT company_name FROM staffs WHERE company_name IS NOT NULL AND company_name != '' ORDER BY company_name ASC");
                            while ($row = $res->fetch_assoc()) {
                                // Escape for JS string
                                $jsCompany = addslashes($row['company_name']);
                                echo "                                \"$jsCompany\",\n";
                            }
                            ?>
                            ];

                            const input = document.getElementById('company-name-input');
                            const suggestions = document.getElementById('company-suggestions');

                            input.addEventListener('input', function() {
                                const val = this.value.trim().toLowerCase();
                                suggestions.innerHTML = '';
                                if (!val) {
                                    suggestions.style.display = 'none';
                                    return;
                                }
                                const matches = companies.filter(c => c.toLowerCase().includes(val));
                                if (matches.length === 0) {
                                    suggestions.style.display = 'none';
                                    return;
                                }
                                matches.forEach(company => {
                                    const item = document.createElement('button');
                                    item.type = 'button';
                                    item.className = 'list-group-item list-group-item-action';
                                    item.textContent = company;
                                    item.onclick = function() {
                                        input.value = company;
                                        suggestions.style.display = 'none';
                                    };
                                    suggestions.appendChild(item);
                                });
                                suggestions.style.display = 'block';
                            });

                            // Show suggestions on click, even if input is empty
                            input.addEventListener('click', function() {
                                const val = this.value.trim().toLowerCase();
                                suggestions.innerHTML = '';
                                let matches = [];
                                if (!val) {
                                    matches = companies;
                                } else {
                                    matches = companies.filter(c => c.toLowerCase().includes(val));
                                }
                                if (matches.length === 0) {
                                    suggestions.style.display = 'none';
                                    return;
                                }
                                matches.forEach(company => {
                                    const item = document.createElement('button');
                                    item.type = 'button';
                                    item.className = 'list-group-item list-group-item-action';
                                    item.textContent = company;
                                    item.onclick = function() {
                                        input.value = company;
                                        suggestions.style.display = 'none';
                                    };
                                    suggestions.appendChild(item);
                                });
                                suggestions.style.display = 'block';
                            });

                            document.addEventListener('click', function(e) {
                                if (!input.contains(e.target) && !suggestions.contains(e.target)) {
                                    suggestions.style.display = 'none';
                                }
                            });
                        </script>
                        <div class="col-md-3 col-12 mb-2">
                            <input type="text" name="photo_link" class="form-control" placeholder="Photo Link" value="<?= htmlspecialchars($editStaff['photo_link'] ?? '') ?>" required>
                        </div>
                        <div class="col-md-3 col-12 mb-2">
                            <input type="text" name="name" class="form-control" placeholder="Name" value="<?= htmlspecialchars($editStaff['name'] ?? '') ?>" required>
                        </div>
                    </div>
                    <div class="row g-2 mt-2">
                        <div class="col-md-2 col-12 mb-2 position-relative">
                            <input type="text" name="position" class="form-control" id="position-input" placeholder="Position" value="<?= htmlspecialchars($editStaff['position'] ?? '') ?>" autocomplete="off" required>
                            <div id="position-suggestions" class="list-group position-absolute w-100" style="z-index: 10; display: none; max-height: 200px; overflow-y: auto;"></div>
                        </div>
                        <div class="col-md-2 col-12 mb-2 position-relative">
                            <input type="text" name="department" class="form-control" id="department-input" placeholder="Department" value="<?= htmlspecialchars($editStaff['department'] ?? '') ?>" autocomplete="off" required>
                            <div id="department-suggestions" class="list-group position-absolute w-100" style="z-index: 10; display: none; max-height: 200px; overflow-y: auto;"></div>
                        </div>
                        <div class="col-md-2 col-12 mb-2 position-relative">
                            <input type="text" name="section" class="form-control" id="section-input" placeholder="Section" value="<?= htmlspecialchars($editStaff['section'] ?? '') ?>" autocomplete="off" required>
                            <div id="section-suggestions" class="list-group position-absolute w-100" style="z-index: 10; display: none; max-height: 200px; overflow-y: auto;"></div>
                        </div>
                        <script>
                            // Fetch unique values from PHP for suggestions
                            <?php
                            // Position
                            $positions = [];
                            $res = $conn->query("SELECT DISTINCT position FROM staffs WHERE position IS NOT NULL AND position != '' ORDER BY position ASC");
                            while ($row = $res->fetch_assoc()) {
                                $positions[] = addslashes($row['position']);
                            }
                            // Department
                            $departments = [];
                            $res = $conn->query("SELECT DISTINCT department FROM staffs WHERE department IS NOT NULL AND department != '' ORDER BY department ASC");
                            while ($row = $res->fetch_assoc()) {
                                $departments[] = addslashes($row['department']);
                            }
                            // Section
                            $sections = [];
                            $res = $conn->query("SELECT DISTINCT section FROM staffs WHERE section IS NOT NULL AND section != '' ORDER BY section ASC");
                            while ($row = $res->fetch_assoc()) {
                                $sections[] = addslashes($row['section']);
                            }
                            ?>
                            const positions = <?= json_encode($positions) ?>;
                            const departments = <?= json_encode($departments) ?>;
                            const sections = <?= json_encode($sections) ?>;

                            function setupSuggestion(inputId, suggestionsId, dataList) {
                                const input = document.getElementById(inputId);
                                const suggestions = document.getElementById(suggestionsId);

                                input.addEventListener('input', function() {
                                    const val = this.value.trim().toLowerCase();
                                    suggestions.innerHTML = '';
                                    if (!val) {
                                        suggestions.style.display = 'none';
                                        return;
                                    }
                                    const matches = dataList.filter(item => item.toLowerCase().includes(val));
                                    if (matches.length === 0) {
                                        suggestions.style.display = 'none';
                                        return;
                                    }
                                    matches.forEach(item => {
                                        const btn = document.createElement('button');
                                        btn.type = 'button';
                                        btn.className = 'list-group-item list-group-item-action';
                                        btn.textContent = item;
                                        btn.onclick = function() {
                                            input.value = item;
                                            suggestions.style.display = 'none';
                                        };
                                        suggestions.appendChild(btn);
                                    });
                                    suggestions.style.display = 'block';
                                });

                                input.addEventListener('click', function() {
                                    const val = this.value.trim().toLowerCase();
                                    suggestions.innerHTML = '';
                                    let matches = [];
                                    if (!val) {
                                        matches = dataList;
                                    } else {
                                        matches = dataList.filter(item => item.toLowerCase().includes(val));
                                    }
                                    if (matches.length === 0) {
                                        suggestions.style.display = 'none';
                                        return;
                                    }
                                    matches.forEach(item => {
                                        const btn = document.createElement('button');
                                        btn.type = 'button';
                                        btn.className = 'list-group-item list-group-item-action';
                                        btn.textContent = item;
                                        btn.onclick = function() {
                                            input.value = item;
                                            suggestions.style.display = 'none';
                                        };
                                        suggestions.appendChild(btn);
                                    });
                                    suggestions.style.display = 'block';
                                });

                                document.addEventListener('click', function(e) {
                                    if (!input.contains(e.target) && !suggestions.contains(e.target)) {
                                        suggestions.style.display = 'none';
                                    }
                                });
                            }

                            setupSuggestion('position-input', 'position-suggestions', positions);
                            setupSuggestion('department-input', 'department-suggestions', departments);
                            setupSuggestion('section-input', 'section-suggestions', sections);
                        </script>
                        <div class="col-md-2 col-12 mb-2">
                            <input type="text" name="phone" class="form-control" placeholder="Phone" value="<?= htmlspecialchars($editStaff['phone'] ?? '') ?>" required>
                        </div>
                        <div class="col-md-2 col-12 mb-2">
                            <input type="date" name="joining_date" class="form-control" value="<?= htmlspecialchars($editStaff['joining_date'] ?? $today_gmt6) ?>" required>
                        </div>
                        <div class="col-md-2 col-12 mb-2">
                            <select name="status" class="form-control" id="status-select" required onchange="toggleExitDate()">
                                <option value="1" <?= (isset($editStaff['status']) && $editStaff['status'] == 1) ? 'selected' : '' ?>>Active</option>
                                <option value="0" <?= (isset($editStaff['status']) && $editStaff['status'] == 0) ? 'selected' : '' ?>>Canceled</option>
                                <option value="2" <?= (isset($editStaff['status']) && $editStaff['status'] == 2) ? 'selected' : '' ?>>Resign</option>
                                <option value="3" <?= (isset($editStaff['status']) && $editStaff['status'] == 3) ? 'selected' : '' ?>>Dissmiss</option>
                                <option value="4" <?= (isset($editStaff['status']) && $editStaff['status'] == 4) ? 'selected' : '' ?>>Susspend</option>
                                <option value="5" <?= (isset($editStaff['status']) && $editStaff['status'] == 5) ? 'selected' : '' ?>>Hold</option>
                                <option value="6" <?= (isset($editStaff['status']) && $editStaff['status'] == 6) ? 'selected' : '' ?>>Unkown</option>
                            </select>
                        </div>
                    </div>
                    <div class="row g-2 mt-2">
                        <div class="col-md-2 col-12 mb-2">
                            <input type="date" name="exit_date" id="exit-date" class="form-control" placeholder="Exit Date" value="<?= htmlspecialchars($editStaff['exit_date'] ?? $today_gmt6) ?>">
                        </div>
                        <div class="col-md-2 col-12 mb-2">
                            <button class="btn btn-primary w-100" type="submit"><?= $editStaff ? 'Update' : 'Add' ?></button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Search Bar -->
        <form method="get" class="search-bar mb-3">
            <div class="row g-2 align-items-end">
            <div class="col-lg-1 col-md-2 col-6">
                <input type="text" name="search_id" class="form-control" placeholder="ID" value="<?= htmlspecialchars($_GET['search_id'] ?? '') ?>">
            </div>
           
            <div class="col-lg-2 col-md-2 col-6 position-relative">
                <input type="text" name="search_company" class="form-control" id="search-company-input" placeholder="Company" value="<?= htmlspecialchars($_GET['search_company'] ?? '') ?>" autocomplete="off">
                <div id="search-company-suggestions" class="list-group position-absolute w-100" style="z-index: 10; display: none; max-height: 200px; overflow-y: auto;"></div>
            </div>
            <div class="col-lg-2 col-md-2 col-6 position-relative">
                <input type="text" name="search_department" class="form-control" id="search-department-input" placeholder="Department" value="<?= htmlspecialchars($_GET['search_department'] ?? '') ?>" autocomplete="off">
                <div id="search-department-suggestions" class="list-group position-absolute w-100" style="z-index: 10; display: none; max-height: 200px; overflow-y: auto;"></div>
            </div>
            <div class="col-lg-2 col-md-2 col-6 position-relative">
                <input type="text" name="search_section" class="form-control" id="search-section-input" placeholder="Section" value="<?= htmlspecialchars($_GET['search_section'] ?? '') ?>" autocomplete="off">
                <div id="search-section-suggestions" class="list-group position-absolute w-100" style="z-index: 10; display: none; max-height: 200px; overflow-y: auto;"></div>
            </div>
            <div class="col-lg-2 col-md-2 col-6 position-relative">
                <input type="text" name="search_position" class="form-control" id="search-position-input" placeholder="Position" value="<?= htmlspecialchars($_GET['search_position'] ?? '') ?>" autocomplete="off">
                <div id="search-position-suggestions" class="list-group position-absolute w-100" style="z-index: 10; display: none; max-height: 200px; overflow-y: auto;"></div>
            </div>
            <script>
                // Fetch unique values from PHP for suggestions
                <?php
                // Company
                $searchCompanies = [];
                $res = $conn->query("SELECT DISTINCT company_name FROM staffs WHERE company_name IS NOT NULL AND company_name != '' ORDER BY company_name ASC");
                while ($row = $res->fetch_assoc()) {
                    $searchCompanies[] = addslashes($row['company_name']);
                }
                // Department
                $searchDepartments = [];
                $res = $conn->query("SELECT DISTINCT department FROM staffs WHERE department IS NOT NULL AND department != '' ORDER BY department ASC");
                while ($row = $res->fetch_assoc()) {
                    $searchDepartments[] = addslashes($row['department']);
                }
                // Section
                $searchSections = [];
                $res = $conn->query("SELECT DISTINCT section FROM staffs WHERE section IS NOT NULL AND section != '' ORDER BY section ASC");
                while ($row = $res->fetch_assoc()) {
                    $searchSections[] = addslashes($row['section']);
                }
                // Position
                $searchPositions = [];
                $res = $conn->query("SELECT DISTINCT position FROM staffs WHERE position IS NOT NULL AND position != '' ORDER BY position ASC");
                while ($row = $res->fetch_assoc()) {
                    $searchPositions[] = addslashes($row['position']);
                }
                ?>
                const searchCompanies = <?= json_encode($searchCompanies) ?>;
                const searchDepartments = <?= json_encode($searchDepartments) ?>;
                const searchSections = <?= json_encode($searchSections) ?>;
                const searchPositions = <?= json_encode($searchPositions) ?>;

                function setupSuggestion(inputId, suggestionsId, dataList) {
                    const input = document.getElementById(inputId);
                    const suggestions = document.getElementById(suggestionsId);

                    input.addEventListener('input', function() {
                        const val = this.value.trim().toLowerCase();
                        suggestions.innerHTML = '';
                        if (!val) {
                            suggestions.style.display = 'none';
                            return;
                        }
                        const matches = dataList.filter(item => item.toLowerCase().includes(val));
                        if (matches.length === 0) {
                            suggestions.style.display = 'none';
                            return;
                        }
                        matches.forEach(item => {
                            const btn = document.createElement('button');
                            btn.type = 'button';
                            btn.className = 'list-group-item list-group-item-action';
                            btn.textContent = item;
                            btn.onclick = function() {
                                input.value = item;
                                suggestions.style.display = 'none';
                            };
                            suggestions.appendChild(btn);
                        });
                        suggestions.style.display = 'block';
                    });

                    input.addEventListener('click', function() {
                        const val = this.value.trim().toLowerCase();
                        suggestions.innerHTML = '';
                        let matches = [];
                        if (!val) {
                            matches = dataList;
                        } else {
                            matches = dataList.filter(item => item.toLowerCase().includes(val));
                        }
                        if (matches.length === 0) {
                            suggestions.style.display = 'none';
                            return;
                        }
                        matches.forEach(item => {
                            const btn = document.createElement('button');
                            btn.type = 'button';
                            btn.className = 'list-group-item list-group-item-action';
                            btn.textContent = item;
                            btn.onclick = function() {
                                input.value = item;
                                suggestions.style.display = 'none';
                            };
                            suggestions.appendChild(btn);
                        });
                        suggestions.style.display = 'block';
                    });

                    document.addEventListener('click', function(e) {
                        if (!input.contains(e.target) && !suggestions.contains(e.target)) {
                            suggestions.style.display = 'none';
                        }
                    });
                }

                setupSuggestion('search-company-input', 'search-company-suggestions', searchCompanies);
                setupSuggestion('search-department-input', 'search-department-suggestions', searchDepartments);
                setupSuggestion('search-section-input', 'search-section-suggestions', searchSections);
                setupSuggestion('search-position-input', 'search-position-suggestions', searchPositions);
            </script>
            <div class="col-lg-2 col-md-2 col-6">
                <input type="text" name="search_name" class="form-control" placeholder="Name" value="<?= htmlspecialchars($_GET['search_name'] ?? '') ?>">
            </div>
            <div class="col-lg-1 col-md-2 col-6">
                <button type="submit" class="btn btn-secondary w-100">Search</button>
            </div>
            </div>
        </form>

        <?php
        // Filtering logic
        $where = [];
        if (!empty($_GET['search_id'])) {
            $id = intval($_GET['search_id']);
            $where[] = "id = $id";
        }
         if (!empty($_GET['search_position'])) {
             $position = $conn->real_escape_string($_GET['search_position']);
            $where[] = "position LIKE '%$position%'";
        }
        if (!empty($_GET['search_company'])) {
            $company = $conn->real_escape_string($_GET['search_company']);
            $where[] = "company_name LIKE '%$company%'";
        }
        if (!empty($_GET['search_department'])) {
            $department = $conn->real_escape_string($_GET['search_department']);
            $where[] = "department LIKE '%$department%'";
        }
        if (!empty($_GET['search_section'])) {
            $section = $conn->real_escape_string($_GET['search_section']);
            $where[] = "section LIKE '%$section%'";
        }
        if (!empty($_GET['search_name'])) {
            $name = $conn->real_escape_string($_GET['search_name']);
            $where[] = "name LIKE '%$name%'";
        }
        $whereSql = $where ? 'WHERE ' . implode(' AND ', $where) : '';

        // Show all logic
        $showAll = isset($_GET['show']) && $_GET['show'] === 'all';

        // Fetch filtered staffs
        $staffs = [];
        $limitSql = $showAll ? '' : 'LIMIT 5';
        $res = $conn->query("SELECT * FROM staffs $whereSql ORDER BY id DESC $limitSql");
        while ($row = $res->fetch_assoc()) {
            $staffs[] = $row;
        }
        ?>

        <div class="d-flex justify-content-between align-items-center mb-2">
            <div>
                <span class="fw-bold"><?= $showAll ? 'All Staffs' : 'Last 5 Staffs' ?></span>
                <?php if ($where): ?>
                    <span class="badge bg-info text-dark ms-2" style="cursor:pointer;" onclick="window.location.href='staffs.php'">Filtered <?= count($staffs) ?> X</span>
                <?php endif; ?>
            </div>
            <?php if (!$showAll): ?>
                <a href="?<?= http_build_query(array_merge($_GET, ['show' => 'all'])) ?>" class="btn btn-outline-primary btn-sm">Show All</a>
            <?php else: ?>
                <a href="?<?= http_build_query(array_diff_key($_GET, ['show' => ''])) ?>" class="btn btn-outline-secondary btn-sm">Show Last 5</a>
            <?php endif; ?>
        </div>
        <div class="table-responsive">
        <table class="table table-striped table-hover align-middle">
            <thead class="table-dark">
            <tr>
                <th>ID</th>
                <th>Logo</th>
                <th>Company</th>
                <th>Photo</th>
                <th>Name</th>
                <th>Position</th>
                <th>Department</th>
                <th>Section</th>
                <th>Phone</th>
                <th>Joining Date</th>
                <th>Status</th>
                <th>Exit Date</th>
                <th>Action</th>
                <th>Meta Data</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($staffs as $staff): ?>
            <tr>
                <td><?= $staff['id'] ?></td>
                <td>
                    <?php
                    $logo = htmlspecialchars($staff['logo_link']);
                    if (strpos($logo, '../') === 0) {
                        $logo = substr($logo, 3);
                    }
                    ?>
                    <img src="<?= $logo ?>" alt="Logo">
                </td>
                <td><?= htmlspecialchars($staff['company_name']) ?></td>
                <td><img src="<?= htmlspecialchars($staff['photo_link']) ?>" alt="Photo"></td>
                <td><?= htmlspecialchars($staff['name']) ?></td>
                <td><?= htmlspecialchars($staff['position']) ?></td>
                <td><?= htmlspecialchars($staff['department']) ?></td>
                <td><?= htmlspecialchars($staff['section']) ?></td>
                <td><?= htmlspecialchars($staff['phone']) ?></td>
                <td><?= htmlspecialchars($staff['joining_date']) ?></td>
                <td>
                <?php
                $statusLabels = [
                    0 => ['Canceled', 'danger'],
                    1 => ['Active', 'success'],
                    2 => ['Resign', 'warning'],
                    3 => ['Dissmiss', 'secondary'],
                    4 => ['Susspend', 'info'],
                    5 => ['Hold', 'dark'],
                    6 => ['Unkown', 'secondary'],
                ];
                $label = $statusLabels[$staff['status']] ?? ['Unknown', 'secondary'];
                ?>
                <span class="badge bg-<?= $label[1] ?>"><?= $label[0] ?></span>
                </td>
                <td><?= htmlspecialchars($staff['exit_date']) ?></td>
                <td>
                <a href="?edit=<?= $staff['id'] ?>" class="btn btn-sm btn-warning mb-1">Edit</a>
                <a href="verify?id=<?= $staff['id'] ?>" target="_blank" class="btn btn-sm btn-info mb-1">Print</a>
                </td>
                <td>
                    <div class="text-muted">
                         <small>Created: <?= htmlspecialchars($staff['created_at']) ?> by <?= htmlspecialchars($staff['created_by']) ?></small>
                        <?php if ($staff['updated_by'] !== 'system'): ?>
                           <br>
                            <small>Updated: <?= htmlspecialchars($staff['updated_at']) ?> by <?= htmlspecialchars($staff['updated_by']) ?></small>
                        <?php endif; ?>
                    </div>
                </td>
            </tr>
            <?php endforeach; ?>
            <?php if (empty($staffs)): ?>
            <tr><td colspan="13" class="text-center text-muted">No staff found.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
        <?php if (!empty($staffs)): ?>
            <?php
            $ids = implode(',', array_column($staffs, 'id'));
            ?>
            <div class="text-end mt-3">
            <a href="verify?id=<?= $ids ?>" target="_blank" class="btn btn-success">
                Print All Shown
            </a>
            </div>
        <?php endif; ?>
        </div>
    </div>
    <script>
    function toggleExitDate() {
        var status = document.getElementById('status-select').value;
        var exitDate = document.getElementById('exit-date');
        if (status != '1') {
            exitDate.required = true;
            exitDate.disabled = false;
            if (!exitDate.value) {
                // Set exit date to today if not already set
                const today = new Date().toISOString().split('T')[0];
                exitDate.value = today;
            }
        } else {
            exitDate.required = false;
            exitDate.value = '';
            exitDate.disabled = true;
            // exitDate.style.display = 'none';
        }
    }
    document.addEventListener('DOMContentLoaded', function() {
        toggleExitDate();
        document.getElementById('status-select').addEventListener('change', toggleExitDate);
    });
    </script>
    </body>
    </html>
    <?php
} else {
    echo "Not logged in.";
}
?>