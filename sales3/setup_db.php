<?php
// setup_db.php - Script to initialize database schema and insert initial data

require_once 'config.php';
require_once 'functions.php'; // For db_query and hash_password

echo "Starting database setup...\n";

try {
    $pdo = get_db_connection();
    echo "Database connection successful.\n";

    // 1. Drop existing tables (if any, for clean setup)
    echo "Dropping existing tables...\n";
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 0;"); // Disable FK checks for dropping tables
    $tables = [
        'logs', 'cash_collections', 'invoice_items', 'invoices',
        'items', 'shops', 'routes', 'users', 'companies',
        'invoice_statuses', 'cash_collection_statuses', 'roles'
    ];
    foreach ($tables as $table) {
        $pdo->exec("DROP TABLE IF EXISTS `{$table}`;");
    }
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 1;"); // Re-enable FK checks
    echo "Existing tables dropped.\n";

    // 2. Create tables
    echo "Creating new tables...\n";
    $pdo->exec("\n        CREATE TABLE `companies` (\n            `company_id` INT AUTO_INCREMENT PRIMARY KEY,\n            `name` VARCHAR(255) NOT NULL UNIQUE,\n            `address` TEXT,\n            `phone` VARCHAR(50),\n            `email` VARCHAR(255),\n            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,\n            `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP\n        );\n\n        CREATE TABLE `roles` (\n            `role_id` INT AUTO_INCREMENT PRIMARY KEY,\n            `name` VARCHAR(50) NOT NULL UNIQUE\n        );\n\n        CREATE TABLE `users` (\n            `user_id` INT AUTO_INCREMENT PRIMARY KEY,\n            `company_id` INT NOT NULL,\n            `role_id` INT NOT NULL,\n            `username` VARCHAR(255) NOT NULL,\n            `password_hash` VARCHAR(255) NOT NULL,\n            `email` VARCHAR(255) NOT NULL,\n            `first_name` VARCHAR(100),\n            `last_name` VARCHAR(100),\n            `is_active` BOOLEAN DEFAULT TRUE,\n            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,\n            `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,\n            CONSTRAINT `fk_user_company` FOREIGN KEY (`company_id`) REFERENCES `companies` (`company_id`) ON DELETE RESTRICT ON UPDATE CASCADE,\n            CONSTRAINT `fk_user_role` FOREIGN KEY (`role_id`) REFERENCES `roles` (`role_id`) ON DELETE RESTRICT ON UPDATE CASCADE,\n            CONSTRAINT `uq_username_per_company` UNIQUE (`company_id`, `username`),\n            CONSTRAINT `uq_email_per_company` UNIQUE (`company_id`, `email`)\n        );\n\n        CREATE TABLE `routes` (\n            `route_id` INT AUTO_INCREMENT PRIMARY KEY,\n            `company_id` INT NOT NULL,\n            `name` VARCHAR(255) NOT NULL,\n            `description` TEXT,\n            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,\n            `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,\n            CONSTRAINT `fk_route_company` FOREIGN KEY (`company_id`) REFERENCES `companies` (`company_id`) ON DELETE RESTRICT ON UPDATE CASCADE,\n            CONSTRAINT `uq_route_name_per_company` UNIQUE (`company_id`, `name`)\n        );\n\n        CREATE TABLE `shops` (\n            `shop_id` INT AUTO_INCREMENT PRIMARY KEY,\n            `company_id` INT NOT NULL,\n            `route_id` INT,\n            `name` VARCHAR(255) NOT NULL,\n            `address` TEXT,\n            `phone` VARCHAR(50),\n            `email` VARCHAR(255),\n            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,\n            `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,\n            CONSTRAINT `fk_shop_company` FOREIGN KEY (`company_id`) REFERENCES `companies` (`company_id`) ON DELETE RESTRICT ON UPDATE CASCADE,\n            CONSTRAINT `fk_shop_route` FOREIGN KEY (`route_id`) REFERENCES `routes` (`route_id`) ON DELETE SET NULL ON UPDATE CASCADE,\n            CONSTRAINT `uq_shop_name_per_company` UNIQUE (`company_id`, `name`)\n        );\n\n        CREATE TABLE `items` (\n            `item_id` INT AUTO_INCREMENT PRIMARY KEY,\n            `company_id` INT NOT NULL,\n            `name` VARCHAR(255) NOT NULL,\n            `description` TEXT,\n            `unit_price` DECIMAL(10, 2) NOT NULL DEFAULT 0.00,\n            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,\n            `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,\n            CONSTRAINT `fk_item_company` FOREIGN KEY (`company_id`) REFERENCES `companies` (`company_id`) ON DELETE RESTRICT ON UPDATE CASCADE,\n            CONSTRAINT `uq_item_name_per_company` UNIQUE (`company_id`, `name`)\n        );\n\n        CREATE TABLE `invoice_statuses` (\n            `status_id` INT AUTO_INCREMENT PRIMARY KEY,\n            `name` VARCHAR(50) NOT NULL UNIQUE\n        );\n\n        CREATE TABLE `invoices` (\n            `invoice_id` INT AUTO_INCREMENT PRIMARY KEY,\n            `company_id` INT NOT NULL,\n            `sr_id` INT NOT NULL,\n            `manager_id` INT,\n            `shop_id` INT NOT NULL,\n            `route_id` INT,\n            `order_date` DATE NOT NULL,\n            `delivery_date` DATE,\n            `remarks` TEXT,\n            `total_amount` DECIMAL(10, 2) NOT NULL DEFAULT 0.00,\n            `status_id` INT NOT NULL,\n            `sr_serial_order` INT,\n            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,\n            `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,\n            CONSTRAINT `fk_invoice_company` FOREIGN KEY (`company_id`) REFERENCES `companies` (`company_id`) ON DELETE RESTRICT ON UPDATE CASCADE,\n            CONSTRAINT `fk_invoice_sr` FOREIGN KEY (`sr_id`) REFERENCES `users` (`user_id`) ON DELETE RESTRICT ON UPDATE CASCADE,\n            CONSTRAINT `fk_invoice_manager` FOREIGN KEY (`manager_id`) REFERENCES `users` (`user_id`) ON DELETE SET NULL ON UPDATE CASCADE,\n            CONSTRAINT `fk_invoice_shop` FOREIGN KEY (`shop_id`) REFERENCES `shops` (`shop_id`) ON DELETE RESTRICT ON UPDATE CASCADE,\n            CONSTRAINT `fk_invoice_route` FOREIGN KEY (`route_id`) REFERENCES `routes` (`route_id`) ON DELETE SET NULL ON UPDATE CASCADE,\n            CONSTRAINT `fk_invoice_status` FOREIGN KEY (`status_id`) REFERENCES `invoice_statuses` (`status_id`) ON DELETE RESTRICT ON UPDATE CASCADE\n        );\n\n        CREATE TABLE `invoice_items` (\n            `invoice_item_id` INT AUTO_INCREMENT PRIMARY KEY,\n            `invoice_id` INT NOT NULL,\n            `item_id` INT NOT NULL,\n            `quantity` INT NOT NULL DEFAULT 1,\n            `unit_price` DECIMAL(10, 2) NOT NULL,\n            `subtotal` DECIMAL(10, 2) NOT NULL,\n            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,\n            `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,\n            CONSTRAINT `fk_invoice_item_invoice` FOREIGN KEY (`invoice_id`) REFERENCES `invoices` (`invoice_id`) ON DELETE CASCADE ON UPDATE CASCADE,\n            CONSTRAINT `fk_invoice_item_item` FOREIGN KEY (`item_id`) REFERENCES `items` (`item_id`) ON DELETE RESTRICT ON UPDATE CASCADE,\n            CONSTRAINT `uq_invoice_item_unique` UNIQUE (`invoice_id`, `item_id`)\n        );\n\n        CREATE TABLE `cash_collection_statuses` (\n            `status_id` INT AUTO_INCREMENT PRIMARY KEY,\n            `name` VARCHAR(50) NOT NULL UNIQUE\n        );\n\n        CREATE TABLE `cash_collections` (\n            `collection_id` INT AUTO_INCREMENT PRIMARY KEY,\n            `company_id` INT NOT NULL,\n            `user_id` INT NOT NULL,\n            `shop_id` INT NOT NULL,\n            `amount` DECIMAL(10, 2) NOT NULL,\n            `collection_date` DATE NOT NULL,\n            `remarks` TEXT,\n            `status_id` INT NOT NULL,\n            `approved_by_manager_id` INT,\n            `approval_date` TIMESTAMP,\n            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,\n            `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,\n            CONSTRAINT `fk_collection_company` FOREIGN KEY (`company_id`) REFERENCES `companies` (`company_id`) ON DELETE RESTRICT ON UPDATE CASCADE,\n            CONSTRAINT `fk_collection_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE RESTRICT ON UPDATE CASCADE,\n            CONSTRAINT `fk_collection_shop` FOREIGN KEY (`shop_id`) REFERENCES `shops` (`shop_id`) ON DELETE RESTRICT ON UPDATE CASCADE,\n            CONSTRAINT `fk_collection_status` FOREIGN KEY (`status_id`) REFERENCES `cash_collection_statuses` (`status_id`) ON DELETE RESTRICT ON UPDATE CASCADE,\n            CONSTRAINT `fk_collection_approved_by` FOREIGN KEY (`approved_by_manager_id`) REFERENCES `users` (`user_id`) ON DELETE SET NULL ON UPDATE CASCADE\n        );\n\n        CREATE TABLE `logs` (\n            `log_id` INT AUTO_INCREMENT PRIMARY KEY,\n            `user_id` INT,\n            `company_id` INT,\n            `action_type` VARCHAR(100) NOT NULL,\n            `entity_type` VARCHAR(50),\n            `entity_id` INT,\n            `old_value` JSON,\n            `new_value` JSON,\n            `message` TEXT,\n            `ip_address` VARCHAR(45),\n            `timestamp` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,\n            CONSTRAINT `fk_log_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE SET NULL ON UPDATE CASCADE,\n            CONSTRAINT `fk_log_company` FOREIGN KEY (`company_id`) REFERENCES `companies` (`company_id`) ON DELETE SET NULL ON UPDATE CASCADE\n        );\n    ");
    echo "Tables created successfully.\n";

    // 3. Insert initial data (Roles, Statuses, Default Company, Admin User)
    echo "Inserting initial data...\n";

    // Roles
    db_query("INSERT INTO `roles` (`name`) VALUES (:name)", ['name' => ROLE_ADMIN]);
    db_query("INSERT INTO `roles` (`name`) VALUES (:name)", ['name' => ROLE_SALES_REP]);
    db_query("INSERT INTO `roles` (`name`) VALUES (:name)", ['name' => ROLE_MANAGER]);
    db_query("INSERT INTO `roles` (`name`) VALUES (:name)", ['name' => ROLE_VIEWER]);
    echo "Roles inserted.\n";

    // Invoice Statuses
    db_query("INSERT INTO `invoice_statuses` (`name`) VALUES (:name)", ['name' => STATUS_DRAFTED]);
    db_query("INSERT INTO `invoice_statuses` (`name`) VALUES (:name)", ['name' => STATUS_CONFIRMED]);
    db_query("INSERT INTO `invoice_statuses` (`name`) VALUES (:name)", ['name' => STATUS_APPROVED]);
    db_query("INSERT INTO `invoice_statuses` (`name`) VALUES (:name)", ['name' => STATUS_REJECTED]);
    db_query("INSERT INTO `invoice_statuses` (`name`) VALUES (:name)", ['name' => STATUS_ON_PROCESS]);
    db_query("INSERT INTO `invoice_statuses` (`name`) VALUES (:name)", ['name' => STATUS_ON_DELIVERY]);
    db_query("INSERT INTO `invoice_statuses` (`name`) VALUES (:name)", ['name' => STATUS_DELIVERED]);
    db_query("INSERT INTO `invoice_statuses` (`name`) VALUES (:name)", ['name' => STATUS_RETURNED]);
    db_query("INSERT INTO `invoice_statuses` (`name`) VALUES (:name)", ['name' => STATUS_DAMAGED]);
    echo "Invoice statuses inserted.\n";

    // Cash Collection Statuses
    db_query("INSERT INTO `cash_collection_statuses` (`name`) VALUES (:name)", ['name' => CC_STATUS_PENDING]);
    db_query("INSERT INTO `cash_collection_statuses` (`name`) VALUES (:name)", ['name' => CC_STATUS_APPROVED]);
    db_query("INSERT INTO `cash_collection_statuses` (`name`) VALUES (:name)", ['name' => CC_STATUS_REJECTED]);
    echo "Cash collection statuses inserted.\n";

    // Default Company
    $defaultCompanyData = [
        'name' => DEFAULT_COMPANY_NAME,
        'address' => '123 Main St, Anytown',
        'phone' => '555-1234',
        'email' => 'info@' . strtolower(str_replace(' ', '', DEFAULT_COMPANY_NAME)) . '.com'
    ];
    $companyId = db_insert('companies', $defaultCompanyData);
    if (!$companyId) {
        throw new Exception("Failed to insert default company.");
    }
    echo "Default company '" . DEFAULT_COMPANY_NAME . "' inserted (ID: {$companyId}).\n";

    // Admin User
    $adminRoleId = get_role_id_by_name(ROLE_ADMIN);
    $adminPasswordHash = hash_password(DEFAULT_ADMIN_PASSWORD);
    $adminUserData = [
        'company_id' => $companyId,
        'role_id' => $adminRoleId,
        'username' => DEFAULT_ADMIN_USERNAME,
        'password_hash' => $adminPasswordHash,
        'email' => 'admin@' . strtolower(str_replace(' ', '', DEFAULT_COMPANY_NAME)) . '.com',
        'first_name' => 'Super',
        'last_name' => 'Admin',
        'is_active' => 1
    ];
    $adminUserId = db_insert('users', $adminUserData);
    if (!$adminUserId) {
        throw new Exception("Failed to insert default admin user.");
    }
    echo "Default Admin user '" . DEFAULT_ADMIN_USERNAME . "' (password: " . DEFAULT_ADMIN_PASSWORD . ") inserted (ID: {$adminUserId}).\n";

    echo "Database setup complete. You can now log in with username: " . DEFAULT_ADMIN_USERNAME . " and password: " . DEFAULT_ADMIN_PASSWORD . "\n";

} catch (PDOException $e) {
    echo "Database error: " . $e->getMessage() . "\n";
    echo "Please ensure your MySQL server is running and database '" . DB_NAME . "' exists or the user has privileges to create it.\n";
    exit(1);
} catch (Exception $e) {
    echo "General error during setup: " . $e->getMessage() . "\n";
    exit(1);
}

