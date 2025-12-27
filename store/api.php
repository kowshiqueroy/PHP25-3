<?php
require_once 'config.php';
checkAuth();

$action = $_GET['action'] ?? '';

if ($action == 'search_type') {
    $q = $_GET['q'] ?? '';
    $stmt = $pdo->prepare("SELECT DISTINCT p_type as id, p_type as text FROM products WHERE p_type LIKE ?");
    $stmt->execute(["%$q%"]);
    echo json_encode(['results' => $stmt->fetchAll()]);
}

if ($action == 'search_name') {
    $q = $_GET['q'] ?? '';
    $type = $_GET['type'] ?? '';
    $stmt = $pdo->prepare("SELECT DISTINCT p_name as id, p_name as text FROM products WHERE p_type = ? AND p_name LIKE ?");
    $stmt->execute([$type, "%$q%"]);
    echo json_encode(['results' => $stmt->fetchAll()]);
}

if ($action == 'get_product_details') {
    $name = $_GET['name'] ?? '';
    $type = $_GET['type'] ?? '';
    
    // Get Unit
    $stmt = $pdo->prepare("SELECT p_unit FROM products WHERE p_type = ? AND p_name = ? LIMIT 1");
    $stmt->execute([$type, $name]);
    $prod = $stmt->fetch();
    
    // Calculate Stock
    $sql = "SELECT 
        SUM(CASE WHEN txn_type = 'IN' THEN quantity ELSE 0 END) - 
        SUM(CASE WHEN txn_type = 'OUT' THEN quantity ELSE 0 END) as current_stock
        FROM transactions 
        LEFT JOIN products p ON transactions.product_id = p.id
        WHERE p.p_type = ? AND p.p_name = ?";
    $stmtStock = $pdo->prepare($sql);
    $stmtStock->execute([$type, $name]);
    $stock = $stmtStock->fetch();

    echo json_encode([
        'unit' => $prod['p_unit'] ?? '', 
        'stock' => $stock['current_stock'] ?? 0
    ]);
}

if ($action == 'search_all_products') {
    $q = $_GET['q'] ?? '';
    // Return ID and formatted Text for Select2
    $stmt = $pdo->prepare("SELECT id, CONCAT(p_type, ' - ', p_name) as text FROM products WHERE p_name LIKE ? OR p_type LIKE ? LIMIT 20");
    $stmt->execute(["%$q%", "%$q%"]);
    echo json_encode(['results' => $stmt->fetchAll()]);
}

// ... inside api.php ...

if ($action == 'get_offline_dictionary') {
    // 1. Get all Product Types
    $types = $pdo->query("SELECT DISTINCT p_type FROM products WHERE p_type IS NOT NULL AND p_type != ''")->fetchAll(PDO::FETCH_COLUMN);
    
    // 2. Get all Product Names
    $names = $pdo->query("SELECT DISTINCT p_name FROM products WHERE p_name IS NOT NULL AND p_name != ''")->fetchAll(PDO::FETCH_COLUMN);
    
    // 3. Get distinct Locations, Sections, etc from Transactions history
    $locs = $pdo->query("SELECT DISTINCT location FROM transactions WHERE location IS NOT NULL")->fetchAll(PDO::FETCH_COLUMN);
    $sections = $pdo->query("SELECT DISTINCT section FROM transactions WHERE section IS NOT NULL")->fetchAll(PDO::FETCH_COLUMN);
    $froms = $pdo->query("SELECT DISTINCT from_to FROM transactions WHERE from_to IS NOT NULL")->fetchAll(PDO::FETCH_COLUMN);
    $bys = $pdo->query("SELECT DISTINCT handled_by FROM transactions WHERE handled_by IS NOT NULL")->fetchAll(PDO::FETCH_COLUMN);

    echo json_encode([
        'types' => $types,
        'names' => $names,
        'locations' => $locs,
        'sections' => $sections,
        'from_to' => $froms,
        'handled_by' => $bys
    ]);
    exit;
}

// ... inside api.php ...
// ... inside api.php ...

// 1. CHECK SIMILAR TYPES
if ($action == 'check_type_similarity') {
    $input = trim($_GET['q']);
    $inputLower = strtolower($input);
    
    $stmt = $pdo->query("SELECT DISTINCT p_type FROM products");
    $existing = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    $found = false;
    $similar = [];
    
    foreach($existing as $db_type) {
        // Exact match (case insensitive)
        if(strtolower($db_type) === $inputLower) {
            $found = true;
        }
        // Similarity check (Levenshtein distance)
        // If distance is small (<= 2 characters difference), suggest it
        if(levenshtein($inputLower, strtolower($db_type)) <= 2) {
            $similar[] = $db_type;
        }
    }
    
    echo json_encode(['exists' => $found, 'similar' => $similar]);
    exit;
}

// 2. CHECK SIMILAR NAMES (Scoped to a Type)
if ($action == 'check_name_similarity') {
    $type = trim($_GET['type']);
    $name = trim($_GET['name']);
    $nameLower = strtolower($name);
    
    $stmt = $pdo->prepare("SELECT p_name FROM products WHERE p_type = ?");
    $stmt->execute([$type]);
    $existing = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    $found = false;
    $similar = [];
    
    foreach($existing as $db_name) {
        if(strtolower($db_name) === $nameLower) $found = true;
        if(levenshtein($nameLower, strtolower($db_name)) <= 2) $similar[] = $db_name;
    }
    
    echo json_encode(['exists' => $found, 'similar' => $similar]);
    exit;
}

?>