<?php
// functions.php - Contains all core procedural functions for the application

require_once 'config.php';

/*******************************************************************************
 * Database Interaction Functions
 ******************************************************************************/

if (!function_exists('db_query')) {
/**
 * Executes a prepared statement with given parameters.
 * @param string $sql The SQL query string.
 * @param array $params Parameters for the prepared statement.
 * @return PDOStatement Returns the PDOStatement object.
 */
function db_query(string $sql, array $params = []): PDOStatement {
    $pdo = get_db_connection();
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt;
}
}

if (!function_exists('db_fetch')) {
/**
 * Fetches a single row from the database.
 * @param string $sql The SQL query string.
 * @param array $params Parameters for the prepared statement.
 * @return array|false Returns the fetched row as an associative array, or false if no row.
 */
function db_fetch(string $sql, array $params = []): array|false {
    $stmt = db_query($sql, $params);
    return $stmt->fetch();
}
}

if (!function_exists('db_fetch_all')) {
/**
 * Fetches all rows from the database.
 * @param string $sql The SQL query string.
 * @param array $params Parameters for the prepared statement.
 * @return array Returns all fetched rows as an array of associative arrays.
 */
function db_fetch_all(string $sql, array $params = []): array {
    $stmt = db_query($sql, $params);
    return $stmt->fetchAll();
}
}

if (!function_exists('db_insert')) {
/**
 * Inserts a new row into a table.
 * @param string $table The table name.
 * @param array $data Associative array of column_name => value.
 * @return int|false Returns the ID of the last inserted row, or false on failure.
 */
function db_insert(string $table, array $data): int|false {
    $pdo = get_db_connection();
    $columns = implode(', ', array_keys($data));
    $placeholders = ':' . implode(', :', array_keys($data));
    $sql = "INSERT INTO `{$table}` ({$columns}) VALUES ({$placeholders})";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($data);
    return $pdo->lastInsertId();
}
}

if (!function_exists('db_update')) {
/**
 * Updates an existing row in a table.
 * @param string $table The table name.
 * @param array $data Associative array of column_name => value to update.
 * @param string $whereColumn The column name for the WHERE clause.
 * @param mixed $whereValue The value for the WHERE clause.
 * @return int Returns the number of affected rows.
 */
function db_update(string $table, array $data, string $whereColumn, mixed $whereValue): int {
    $pdo = get_db_connection();
    $setParts = [];
    foreach ($data as $column => $value) {
        $setParts[] = "`{$column}` = :{$column}";
    }
    $setClause = implode(', ', $setParts);
    $data[$whereColumn] = $whereValue; // Add where value to data for execution
    $sql = "UPDATE `{$table}` SET {$setClause} WHERE `{$whereColumn}` = :{$whereColumn}";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($data);
    return $stmt->rowCount();
}
}

if (!function_exists('db_delete')) {
/**
 * Deletes a row from a table.
 * @param string $table The table name.
 * @param string $whereColumn The column name for the WHERE clause.
 * @param mixed $whereValue The value for the WHERE clause.
 * @return int Returns the number of affected rows.
 */
function db_delete(string $table, string $whereColumn, mixed $whereValue): int {
    $pdo = get_db_connection();
    $sql = "DELETE FROM `{$table}` WHERE `{$whereColumn}` = :{$whereColumn}";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$whereColumn => $whereValue]);
    return $stmt->rowCount();
}
}

/*******************************************************************************
 * Authentication Functions
 ******************************************************************************/

if (!function_exists('hash_password')) {
/**
 * Hashes a password using the Argon2 algorithm.
 * @param string $password The plain-text password.
 * @return string The hashed password.
 */
function hash_password(string $password): string {
    return password_hash($password, PASSWORD_ARGON2ID);
}
}

if (!function_exists('verify_password')) {
/**
 * Verifies a plain-text password against a hashed password.
 * @param string $password The plain-text password.
 * @param string $hash The hashed password.
 * @return bool True if the password matches, false otherwise.
 */
function verify_password(string $password, string $hash): bool {
    return password_verify($password, $hash);
}
}

if (!function_exists('login_user')) {
/**
 * Attempts to log in a user.
 * @param string $username The username.
 * @param string $password The plain-text password.
 * @return array|false Returns user data on success, false on failure.
 */
function login_user(string $username, string $password): array|false {
    $user = db_fetch("SELECT u.*, r.name as role_name FROM users u JOIN roles r ON u.role_id = r.role_id WHERE u.username = :username AND u.is_active = 1", ['username' => $username]);

    if ($user && verify_password($password, $user['password_hash'])) {
        session_regenerate_id(true); // Prevent session fixation

        $_SESSION['user_id'] = $user['user_id'];
        $_SESSION['company_id'] = $user['company_id'];
        $_SESSION['role_id'] = $user['role_id'];
        $_SESSION['role_name'] = $user['role_name'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['logged_in'] = true;

        return $user;
    }
    return false;
}
}

if (!function_exists('logout_user')) {
/**
 * Logs out the current user.
 */
function logout_user(): void {
    $_SESSION = [];
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }
    session_destroy();
}
}

if (!function_exists('is_logged_in')) {
/**
 * Checks if a user is currently logged in.
 * @return bool True if logged in, false otherwise.
 */
function is_logged_in(): bool {
    return isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true;
}
}

if (!function_exists('get_current_user')) {
/**
 * Gets the currently logged-in user's data from the session.
 * @return array|null Returns user data, or null if not logged in.
 */
function get_current_user(): ?array {
    if (is_logged_in()) {
        return [
            'user_id' => $_SESSION['user_id'],
            'company_id' => $_SESSION['company_id'],
            'role_id' => $_SESSION['role_id'],
            'role_name' => $_SESSION['role_name'],
            'username' => $_SESSION['username']
        ];
    }
    return null;
}
}

/*******************************************************************************
 * Authorization Functions (RBAC - Role-Based Access Control)
 ******************************************************************************/

if (!function_exists('has_role')) {
/**
 * Checks if the current user has a specific role.
 * @param string $roleName The name of the role to check.
 * @return bool True if the user has the role, false otherwise.
 */
function has_role(string $roleName): bool {
    $currentUser = get_current_user();
    return $currentUser && $currentUser['role_name'] === $roleName;
}
}

if (!function_exists('has_any_role')) {
/**
 * Checks if the current user has any of the specified roles.
 * @param array $roleNames An array of role names to check.
 * @return bool True if the user has any of the roles, false otherwise.
 */
function has_any_role(array $roleNames): bool {
    $currentUser = get_current_user();
    return $currentUser && in_array($currentUser['role_name'], $roleNames);
}
}

if (!function_exists('can_perform')) {
/**
 * Checks if the current user has permission to perform a specific action.
 * This is a simplified check based on roles.
 * @param string $permission The permission string (e.g., 'manage_users', 'create_invoice').
 * @return bool True if permitted, false otherwise.
 */
function can_perform(string $permission): bool {
    $currentUser = get_current_user();
    if (!$currentUser) {
        return false;
    }

    $role = $currentUser['role_name'];

    switch ($permission) {
        case 'manage_companies':
            return $role === ROLE_ADMIN;
        case 'manage_users':
        case 'manage_routes':
        case 'manage_shops':
        case 'manage_items':
            return $role === ROLE_ADMIN;
        case 'create_invoice':
        case 'edit_own_drafted_invoice':
        case 'confirm_own_invoice':
        case 'insert_cash_collection':
        case 'view_own_collections':
            return $role === ROLE_SALES_REP || $role === ROLE_ADMIN;
        case 'approve_invoice':
        case 'reject_invoice':
        case 'update_invoice_status':
        case 'approve_cash_collection':
        case 'reject_cash_collection':
        case 'print_invoice_serial':
            return $role === ROLE_MANAGER || $role === ROLE_ADMIN;
        case 'view_all_invoices':
        case 'view_all_collections':
        case 'view_shop_balances':
            return has_any_role([ROLE_MANAGER, ROLE_ADMIN, ROLE_VIEWER]);
        case 'view_system_logs':
            return $role === ROLE_ADMIN;
        default:
            return false;
    }
}
}

/*******************************************************************************
 * Helper Functions
 ******************************************************************************/

if (!function_exists('redirect')) {
/**
 * Redirects to a specified URL.
 * @param string $url The URL to redirect to.
 */
function redirect(string $url): void {
    header("Location: " . $url);
    exit();
}
}

if (!function_exists('render_view')) {
/**
 * Renders a view file.
 * @param string $viewName The name of the view file (e.g., 'dashboard').
 * @param array $data Associative array of data to pass to the view.
 */
function render_view(string $viewName, array $data = []): void {
    extract($data); // Extract data into local variables
    require_once __DIR__ . "/views/{$viewName}.php";
}
}

if (!function_exists('get_role_id_by_name')) {
/**
 * Gets the ID for a given role name.
 * @param string $roleName The name of the role.
 * @return int|false The role ID, or false if not found.
 */
function get_role_id_by_name(string $roleName): int|false {
    $result = db_fetch("SELECT role_id FROM roles WHERE name = :role_name", ['role_name' => $roleName]);
    return $result ? (int)$result['role_id'] : false;
}
}

if (!function_exists('get_invoice_status_id_by_name')) {
/**
 * Gets the ID for a given invoice status name.
 * @param string $statusName The name of the invoice status.
 * @return int|false The status ID, or false if not found.
 */
function get_invoice_status_id_by_name(string $statusName): int|false {
    $result = db_fetch("SELECT status_id FROM invoice_statuses WHERE name = :status_name", ['status_name' => $statusName]);
    return $result ? (int)$result['status_id'] : false;
}
}

if (!function_exists('get_cc_status_id_by_name')) {
/**
 * Gets the ID for a given cash collection status name.
 * @param string $statusName The name of the cash collection status.
 * @return int|false The status ID, or false if not found.
 */
function get_cc_status_id_by_name(string $statusName): int|false {
    $result = db_fetch("SELECT status_id FROM cash_collection_statuses WHERE name = :status_name", ['status_name' => $statusName]);
    return $result ? (int)$result['status_id'] : false;
}
}

/*******************************************************************************
 * Entity-Specific CRUD and Data Retrieval Functions (Examples)
 ******************************************************************************/

// --- User Functions ---
if (!function_exists('get_user_by_id')) {
function get_user_by_id(int $userId): array|false {
    return db_fetch("SELECT u.*, r.name as role_name FROM users u JOIN roles r ON u.role_id = r.role_id WHERE u.user_id = :user_id", ['user_id' => $userId]);
}
}

if (!function_exists('get_all_users')) {
function get_all_users(int $companyId): array {
    return db_fetch_all("SELECT u.*, r.name as role_name FROM users u JOIN roles r ON u.role_id = r.role_id WHERE u.company_id = :company_id ORDER BY u.first_name, u.last_name", ['company_id' => $companyId]);
}
}

// --- Company Functions ---
if (!function_exists('get_company_by_id')) {
function get_company_by_id(int $companyId): array|false {
    return db_fetch("SELECT * FROM companies WHERE company_id = :company_id", ['company_id' => $companyId]);
}
}

// --- Route Functions ---
if (!function_exists('get_route_by_id')) {
function get_route_by_id(int $routeId): array|false {
    return db_fetch("SELECT * FROM routes WHERE route_id = :route_id", ['route_id' => $routeId]);
}
}

if (!function_exists('get_all_routes')) {
function get_all_routes(int $companyId): array {
    return db_fetch_all("SELECT * FROM routes WHERE company_id = :company_id ORDER BY name", ['company_id' => $companyId]);
}
}

// --- Shop Functions ---
if (!function_exists('get_shop_by_id')) {
function get_shop_by_id(int $shopId): array|false {
    return db_fetch("SELECT s.*, r.name as route_name FROM shops s LEFT JOIN routes r ON s.route_id = r.route_id WHERE s.shop_id = :shop_id", ['shop_id' => $shopId]);
}
}

if (!function_exists('get_all_shops')) {
function get_all_shops(int $companyId): array {
    return db_fetch_all("SELECT s.*, r.name as route_name FROM shops s LEFT JOIN routes r ON s.route_id = r.route_id WHERE s.company_id = :company_id ORDER BY s.name", ['company_id' => $companyId]);
}
}

// --- Item Functions ---
if (!function_exists('get_item_by_id')) {
function get_item_by_id(int $itemId): array|false {
    return db_fetch("SELECT * FROM items WHERE item_id = :item_id", ['item_id' => $itemId]);
}
}

if (!function_exists('get_all_items')) {
function get_all_items(int $companyId): array {
    return db_fetch_all("SELECT * FROM items WHERE company_id = :company_id ORDER BY name", ['company_id' => $companyId]);
}
}

// --- Invoice Functions ---
if (!function_exists('get_invoice_by_id')) {
/**
 * Retrieves an invoice by its ID, including its items.
 */
function get_invoice_by_id(int $invoiceId): array|false {
    $invoice = db_fetch("SELECT i.*, s.name as shop_name, r.name as route_name, ist.name as status_name,
                            sr.username as sr_username, mgr.username as manager_username
                          FROM invoices i
                          LEFT JOIN shops s ON i.shop_id = s.shop_id
                          LEFT JOIN routes r ON i.route_id = r.route_id
                          LEFT JOIN invoice_statuses ist ON i.status_id = ist.status_id
                          LEFT JOIN users sr ON i.sr_id = sr.user_id
                          LEFT JOIN users mgr ON i.manager_id = mgr.user_id
                          WHERE i.invoice_id = :invoice_id", ['invoice_id' => $invoiceId]);

    if ($invoice) {
        $invoice['items'] = db_fetch_all("SELECT ii.*, it.name as item_name
                                          FROM invoice_items ii
                                          JOIN items it ON ii.item_id = it.item_id
                                          WHERE ii.invoice_id = :invoice_id", ['invoice_id' => $invoiceId]);
    }
    return $invoice;
}
}

if (!function_exists('get_all_invoices')) {
/**
 * Retrieves all invoices for a company with optional filters.
 * @param int $companyId
 * @param array $filters (e.g., 'sr_id', 'shop_id', 'status_id', 'start_date', 'end_date', 'search')
 * @return array
 */
function get_all_invoices(int $companyId, array $filters = []): array {
    $sql = "SELECT i.*, s.name as shop_name, r.name as route_name, ist.name as status_name,
                   sr.username as sr_username, mgr.username as manager_username
            FROM invoices i
            LEFT JOIN shops s ON i.shop_id = s.shop_id
            LEFT JOIN routes r ON i.route_id = r.route_id
            LEFT JOIN invoice_statuses ist ON i.status_id = ist.status_id
            LEFT JOIN users sr ON i.sr_id = sr.user_id
            LEFT JOIN users mgr ON i.manager_id = mgr.user_id
            WHERE i.company_id = :company_id";

    $params = ['company_id' => $companyId];

    if (!empty($filters['sr_id'])) {
        $sql .= " AND i.sr_id = :sr_id";
        $params['sr_id'] = $filters['sr_id'];
    }
    if (!empty($filters['shop_id'])) {
        $sql .= " AND i.shop_id = :shop_id";
        $params['shop_id'] = $filters['shop_id'];
    }
    if (!empty($filters['status_id'])) {
        $sql .= " AND i.status_id = :status_id";
        $params['status_id'] = $filters['status_id'];
    }
    if (!empty($filters['item_id'])) {
        $sql .= " AND i.invoice_id IN (SELECT invoice_id FROM invoice_items WHERE item_id = :filter_item_id)";
        $params['filter_item_id'] = $filters['item_id'];
    }
    if (!empty($filters['start_date'])) {
        $sql .= " AND i.order_date >= :start_date";
        $params['start_date'] = $filters['start_date'];
    }
    if (!empty($filters['end_date'])) {
        $sql .= " AND i.order_date <= :end_date";
        $params['end_date'] = $filters['end_date'];
    }
    if (!empty($filters['search'])) {
        $searchTerm = '%' . $filters['search'] . '%';
        $sql .= " AND (s.name LIKE :search OR r.name LIKE :search OR sr.username LIKE :search OR ist.name LIKE :search)";
        $params['search'] = $searchTerm;
    }

    $sql .= " ORDER BY i.order_date DESC, i.invoice_id DESC";

    $invoices = db_fetch_all($sql, $params);

    foreach ($invoices as &$invoice) {
        $invoice['items'] = db_fetch_all("SELECT ii.*, it.name as item_name FROM invoice_items ii JOIN items it ON ii.item_id = it.item_id WHERE ii.invoice_id = :invoice_id", ['invoice_id' => $invoice['invoice_id']]);
    }
    return $invoices;
}
}

if (!function_exists('create_invoice')) {
/**
 * Creates a new invoice and its items.
 * @param array $invoiceData
 * @return int|false
 * @throws Exception
 */
function create_invoice(array $invoiceData): int|false {
    $pdo = get_db_connection();
    $pdo->beginTransaction();
    try {
        $draftedStatusId = get_invoice_status_id_by_name(STATUS_DRAFTED);
        if (!$draftedStatusId) {
            throw new Exception("Invoice status 'Drafted' not found.");
        }

        $baseInvoiceData = [
            'company_id' => $invoiceData['company_id'],
            'sr_id' => $invoiceData['sr_id'],
            'shop_id' => $invoiceData['shop_id'],
            'route_id' => $invoiceData['route_id'] ?? null,
            'order_date' => $invoiceData['order_date'],
            'delivery_date' => $invoiceData['delivery_date'] ?? null,
            'remarks' => $invoiceData['remarks'] ?? null,
            'total_amount' => 0.00, // Will be updated later
            'status_id' => $draftedStatusId
        ];
        $invoiceId = db_insert('invoices', $baseInvoiceData);

        if (!$invoiceId) {
            throw new Exception("Failed to insert invoice.");
        }

        $totalAmount = 0;
        foreach ($invoiceData['items'] as $itemData) {
            $subtotal = $itemData['quantity'] * $itemData['unit_price'];
            $totalAmount += $subtotal;
            db_insert('invoice_items', [
                'invoice_id' => $invoiceId,
                'item_id' => $itemData['item_id'],
                'quantity' => $itemData['quantity'],
                'unit_price' => $itemData['unit_price'],
                'subtotal' => $subtotal
            ]);
        }

        db_update('invoices', ['total_amount' => $totalAmount], 'invoice_id', $invoiceId);

        $pdo->commit();
        return $invoiceId;
    } catch (Exception $e) {
        $pdo->rollBack();
        throw $e;
    }
}
}

if (!function_exists('update_invoice')) {
/**
 * Updates an existing invoice and its items.
 * Can only update 'Drafted' invoices.
 * @param int $invoiceId
 * @param int $companyId
 * @param array $data
 * @return bool
 * @throws Exception
 */
function update_invoice(int $invoiceId, int $companyId, array $data): bool {
    $pdo = get_db_connection();
    $pdo->beginTransaction();
    try {
        $currentInvoice = get_invoice_by_id($invoiceId);
        if (!$currentInvoice || (int)$currentInvoice['company_id'] !== $companyId) {
            throw new Exception("Invoice not found or unauthorized access.");
        }

        $draftedStatusId = get_invoice_status_id_by_name(STATUS_DRAFTED);
        if ((int)$currentInvoice['status_id'] !== $draftedStatusId) {
            throw new Exception("Only 'Drafted' invoices can be edited.");
        }

        $updateData = [];
        if (isset($data['shop_id'])) $updateData['shop_id'] = $data['shop_id'];
        if (isset($data['route_id'])) $updateData['route_id'] = $data['route_id'];
        if (isset($data['order_date'])) $updateData['order_date'] = $data['order_date'];
        if (isset($data['delivery_date'])) $updateData['delivery_date'] = $data['delivery_date'];
        if (isset($data['remarks'])) $updateData['remarks'] = $data['remarks'];

        if (!empty($updateData)) {
            db_update('invoices', $updateData, 'invoice_id', $invoiceId);
        }

        if (isset($data['items']) && is_array($data['items'])) {
            db_delete('invoice_items', 'invoice_id', $invoiceId); // Delete existing items
            $totalAmount = 0;
            foreach ($data['items'] as $itemData) {
                $subtotal = $itemData['quantity'] * $itemData['unit_price'];
                $totalAmount += $subtotal;
                db_insert('invoice_items', [
                    'invoice_id' => $invoiceId,
                    'item_id' => $itemData['item_id'],
                    'quantity' => $itemData['quantity'],
                    'unit_price' => $itemData['unit_price'],
                    'subtotal' => $subtotal
                ]);
            }
            db_update('invoices', ['total_amount' => $totalAmount], 'invoice_id', $invoiceId);
        }

        $pdo->commit();
        return true;
    } catch (Exception $e) {
        $pdo->rollBack();
        throw $e;
    }
}
}

if (!function_exists('change_invoice_status')) {
/**
 * Changes the status of an invoice.
 * @param int $invoiceId
 * @param int $companyId
 * @param string $newStatusName
 * @param int|null $managerId
 * @param int|null $srSerialOrder
 * @return bool
 * @throws Exception
 */
function change_invoice_status(int $invoiceId, int $companyId, string $newStatusName, ?int $managerId = null, ?int $srSerialOrder = null): bool {
    $pdo = get_db_connection();
    $pdo->beginTransaction();
    try {
        $newStatusId = get_invoice_status_id_by_name($newStatusName);
        if (!$newStatusId) {
            throw new Exception("Invalid invoice status: " . $newStatusName);
        }

        $updateData = ['status_id' => $newStatusId];
        if ($newStatusName === STATUS_APPROVED || $newStatusName === STATUS_REJECTED) {
            if (!$managerId) {
                throw new Exception("Manager ID is required for 'Approved' or 'Rejected' status.");
            }
            $updateData['manager_id'] = $managerId;
        }
        if ($srSerialOrder !== null) {
            $updateData['sr_serial_order'] = $srSerialOrder;
        }

        $updated = db_update('invoices', $updateData, 'invoice_id', $invoiceId);
        $pdo->commit();
        return $updated > 0;
    } catch (Exception $e) {
        $pdo->rollBack();
        throw $e;
    }
}
}

if (!function_exists('delete_invoice')) {
/**
 * Deletes an invoice and its associated items.
 * @param int $invoiceId
 * @param int $companyId
 * @return bool
 * @throws Exception
 */
function delete_invoice(int $invoiceId, int $companyId): bool {
    $pdo = get_db_connection();
    $pdo->beginTransaction();
    try {
        db_delete('invoice_items', 'invoice_id', $invoiceId);
        $deleted = db_delete('invoices', 'invoice_id', $invoiceId);
        $pdo->commit();
        return $deleted > 0;
    } catch (Exception $e) {
        $pdo->rollBack();
        throw $e;
    }
}
}

// --- Cash Collection Functions ---
if (!function_exists('get_cash_collection_by_id')) {
/**
 * Retrieves a cash collection by its ID.
 */
function get_cash_collection_by_id(int $collectionId): array|false {
    return db_fetch("SELECT cc.*, s.name as shop_name, ccs.name as status_name,
                       u.username as user_username, a.username as approver_username
                    FROM cash_collections cc
                    LEFT JOIN shops s ON cc.shop_id = s.shop_id
                    LEFT JOIN cash_collection_statuses ccs ON cc.status_id = ccs.status_id
                    LEFT JOIN users u ON cc.user_id = u.user_id
                    LEFT JOIN users a ON cc.approved_by_manager_id = a.user_id
                    WHERE cc.collection_id = :collection_id", ['collection_id' => $collectionId]);
}
}

if (!function_exists('get_all_cash_collections')) {
/**
 * Retrieves all cash collections for a company with optional filters.
 */
function get_all_cash_collections(int $companyId, array $filters = []): array {
    $sql = "SELECT cc.*, s.name as shop_name, ccs.name as status_name,
                   u.username as user_username, a.username as approver_username
            FROM cash_collections cc
            LEFT JOIN shops s ON cc.shop_id = s.shop_id
            LEFT JOIN cash_collection_statuses ccs ON cc.status_id = ccs.status_id
            LEFT JOIN users u ON cc.user_id = u.user_id
            LEFT JOIN users a ON cc.approved_by_manager_id = a.user_id
            WHERE cc.company_id = :company_id";

    $params = ['company_id' => $companyId];

    if (!empty($filters['user_id'])) {
        $sql .= " AND cc.user_id = :user_id";
        $params['user_id'] = $filters['user_id'];
    }
    if (!empty($filters['shop_id'])) {
        $sql .= " AND cc.shop_id = :shop_id";
        $params['shop_id'] = $filters['shop_id'];
    }
    if (!empty($filters['status_id'])) {
        $sql .= " AND cc.status_id = :status_id";
        $params['status_id'] = $filters['status_id'];
    }
    if (!empty($filters['start_date'])) {
        $sql .= " AND cc.collection_date >= :start_date";
        $params['start_date'] = $filters['start_date'];
    }
    if (!empty($filters['end_date'])) {
        $sql .= " AND cc.collection_date <= :end_date";
        $params['end_date'] = $filters['end_date'];
    }

    $sql .= " ORDER BY cc.collection_date DESC, cc.collection_id DESC";

    return db_fetch_all($sql, $params);
}
}

if (!function_exists('create_cash_collection')) {
/**
 * Creates a new cash collection record.
 * @param array $collectionData
 * @return int|false
 * @throws Exception
 */
function create_cash_collection(array $collectionData): int|false {
    $pdo = get_db_connection();
    $pdo->beginTransaction();
    try {
        $pendingStatusId = get_cc_status_id_by_name(CC_STATUS_PENDING);
        if (!$pendingStatusId) {
            throw new Exception("Cash collection status 'Pending' not found.");
        }

        $baseCollectionData = [
            'company_id' => $collectionData['company_id'],
            'user_id' => $collectionData['user_id'],
            'shop_id' => $collectionData['shop_id'],
            'amount' => $collectionData['amount'],
            'collection_date' => $collectionData['collection_date'],
            'remarks' => $collectionData['remarks'] ?? null,
            'status_id' => $pendingStatusId
        ];
        $collectionId = db_insert('cash_collections', $baseCollectionData);

        $pdo->commit();
        return $collectionId;
    } catch (Exception $e) {
        $pdo->rollBack();
        throw $e;
    }
}
}

if (!function_exists('update_cash_collection_status')) {
/**
 * Updates the status of a cash collection.
 * @param int $collectionId
 * @param int $companyId
 * @param string $newStatusName
 * @param int|null $managerId
 * @return bool
 * @throws Exception
 */
function update_cash_collection_status(int $collectionId, int $companyId, string $newStatusName, ?int $managerId = null): bool {
    $pdo = get_db_connection();
    $pdo->beginTransaction();
    try {
        $newStatusId = get_cc_status_id_by_name($newStatusName);
        if (!$newStatusId) {
            throw new Exception("Invalid cash collection status: " . $newStatusName);
        }

        $updateData = ['status_id' => $newStatusId];
        if ($newStatusName === CC_STATUS_APPROVED || $newStatusName === CC_STATUS_REJECTED) {
            if (!$managerId) {
                throw new Exception("Manager ID is required for 'Approved' or 'Rejected' status.");
            }
            $updateData['approved_by_manager_id'] = $managerId;
            $updateData['approval_date'] = date('Y-m-d H:i:s');
        } else {
            $updateData['approved_by_manager_id'] = null;
            $updateData['approval_date'] = null;
        }

        $updated = db_update('cash_collections', $updateData, 'collection_id', $collectionId);
        $pdo->commit();
        return $updated > 0;
    } catch (Exception $e) {
        $pdo->rollBack();
        throw $e;
    }
}
}

if (!function_exists('get_shop_balance')) {
/**
 * Calculates the balance for a given shop.
 * @param int $shopId
 * @param int $companyId
 * @return array
 */
function get_shop_balance(int $shopId, int $companyId): array {
    $totalInvoiced = db_fetch("
        SELECT SUM(i.total_amount) as total_invoiced
        FROM invoices i
        JOIN invoice_statuses ist ON i.status_id = ist.status_id
        WHERE i.shop_id = :shop_id AND i.company_id = :company_id
          AND ist.name IN ('Approved', 'On Process', 'On Delivery', 'Delivered')
    ", ['shop_id' => $shopId, 'company_id' => $companyId])['total_invoiced'] ?? 0.00;

    $totalCollected = db_fetch("
        SELECT SUM(cc.amount) as total_collected
        FROM cash_collections cc
        JOIN cash_collection_statuses ccs ON cc.status_id = ccs.status_id
        WHERE cc.shop_id = :shop_id AND cc.company_id = :company_id AND ccs.name = 'Approved'
    ", ['shop_id' => $shopId, 'company_id' => $companyId])['total_collected'] ?? 0.00;

    $pendingCollections = db_fetch("
        SELECT SUM(cc.amount) as pending_collections
        FROM cash_collections cc
        JOIN cash_collection_statuses ccs ON cc.status_id = ccs.status_id
        WHERE cc.shop_id = :shop_id AND cc.company_id = :company_id AND ccs.name = 'Pending'
    ", ['shop_id' => $shopId, 'company_id' => $companyId])['pending_collections'] ?? 0.00;

    $balance = $totalInvoiced - $totalCollected;

    return [
        'shop_id' => $shopId,
        'company_id' => $companyId,
        'total_invoiced' => (float)$totalInvoiced,
        'total_collected' => (float)$totalCollected,
        'pending_collections' => (float)$pendingCollections,
        'current_balance' => (float)$balance
    ];
}
}

if (!function_exists('log_action')) {
/**
 * Logs an action to the database.
 * @param int|null $userId
 * @param int|null $companyId
 * @param string $actionType
 * @param string|null $entityType
 * @param int|null $entityId
 * @param array|null $oldValue
 * @param array|null $newValue
 * @param string|null $message
 * @param string|null $ipAddress
 * @return bool
 */
function log_action(
    ?int $userId,
    ?int $companyId,
    string $actionType,
    ?string $entityType = null,
    ?int $entityId = null,
    ?array $oldValue = null,
    ?array $newValue = null,
    ?string $message = null,
    ?string $ipAddress = null
): bool {
    $pdo = get_db_connection();
    
    if ($ipAddress === null && isset($_SERVER['REMOTE_ADDR'])) {
        $ipAddress = $_SERVER['REMOTE_ADDR'];
    } elseif ($ipAddress === null) {
        $ipAddress = 'UNKNOWN';
    }

    $data = [
        'user_id' => $userId,
        'company_id' => $companyId,
        'action_type' => $actionType,
        'entity_type' => $entityType,
        'entity_id' => $entityId,
        'old_value' => ($oldValue !== null) ? json_encode($oldValue) : null,
        'new_value' => ($newValue !== null) ? json_encode($newValue) : null,
        'message' => $message,
        'ip_address' => $ipAddress
    ];

    return db_insert('logs', $data) !== false;
}
}

if (!function_exists('get_all_logs')) {
/**
 * Retrieves all logs for a company with optional filters.
 * @param int $companyId
 * @param array $filters (e.g., 'user_id', 'action_type', 'entity_type', 'start_date', 'end_date', 'search')
 * @return array
 */
function get_all_logs(int $companyId, array $filters = []): array {
    $sql = "SELECT l.*, u.username as user_username
            FROM logs l
            LEFT JOIN users u ON l.user_id = u.user_id
            WHERE l.company_id = :company_id";

    $params = ['company_id' => $companyId];

    if (!empty($filters['user_id'])) {
        $sql .= " AND l.user_id = :filter_user_id";
        $params['filter_user_id'] = $filters['user_id'];
    }
    if (!empty($filters['action_type'])) {
        $sql .= " AND l.action_type = :action_type";
        $params['action_type'] = $filters['action_type'];
    }
    if (!empty($filters['entity_type'])) {
        $sql .= " AND l.entity_type = :entity_type";
        $params['entity_type'] = $filters['entity_type'];
    }
    if (!empty($filters['start_date'])) {
        $sql .= " AND DATE(l.timestamp) >= :start_date";
        $params['start_date'] = $filters['start_date'];
    }
    if (!empty($filters['end_date'])) {
        $sql .= " AND DATE(l.timestamp) <= :end_date";
        $params['end_date'] = $filters['end_date'];
    }
     if (!empty($filters['search'])) {
        $searchTerm = '%' . $filters['search'] . '%';
        $sql .= " AND (l.message LIKE :search OR u.username LIKE :search OR l.ip_address LIKE :search)";
        $params['search'] = $searchTerm;
    }

    $sql .= " ORDER BY l.timestamp DESC";

    return db_fetch_all($sql, $params);
}
}