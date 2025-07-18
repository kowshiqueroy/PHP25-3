<?php
// Include database connection
require_once "config/conn.php";

// Function to execute SQL queries safely
function executeQuery($conn, $sql) {
    if (mysqli_query($conn, $sql)) {
        echo "<div class='success'>Query executed successfully: " . $sql . "</div>";
        return true;
    } else {
        echo "<div class='error'>Error executing query: " . mysqli_error($conn) . "</div>";
        return false;
    }
}

// Check if setup confirmation is received
if (isset($_POST['confirm_setup']) && $_POST['confirm_setup'] == 'yes') {
    // Drop existing database if exists
    $sql = "DROP DATABASE IF EXISTS `cms`";
    executeQuery($conn, $sql);
    
    // Create database
    $sql = "CREATE DATABASE IF NOT EXISTS `cms` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci";
    executeQuery($conn, $sql);
    
    // Select the database
    $sql = "USE `cms`";
    executeQuery($conn, $sql);
    
    // Create users table
    $sql = "CREATE TABLE IF NOT EXISTS `users` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `username` varchar(50) NOT NULL,
        `password` varchar(255) NOT NULL,
        `email` varchar(100) NOT NULL,
        `full_name` varchar(100) NOT NULL,
        `role` enum('admin','manager','entry','viewer') NOT NULL,
        `module_access` varchar(255) DEFAULT NULL,
        `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
        `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        `last_login` datetime DEFAULT NULL,
        `status` enum('active','inactive') DEFAULT 'active',
        PRIMARY KEY (`id`),
        UNIQUE KEY `username` (`username`),
        UNIQUE KEY `email` (`email`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    executeQuery($conn, $sql);
    
    // Create default admin user
    $admin_password = password_hash('admin123', PASSWORD_DEFAULT);
    $sql = "INSERT INTO `users` (`username`, `password`, `email`, `full_name`, `role`, `module_access`) 
            VALUES ('admin', '$admin_password', 'admin@example.com', 'System Administrator', 'admin', 'all')";
    executeQuery($conn, $sql);
    
    // Create employees table
    $sql = "CREATE TABLE IF NOT EXISTS `employees` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `employee_id` varchar(20) NOT NULL,
        `full_name` varchar(100) NOT NULL,
        `email` varchar(100) DEFAULT NULL,
        `phone` varchar(20) DEFAULT NULL,
        `department` varchar(50) DEFAULT NULL,
        `designation` varchar(50) DEFAULT NULL,
        `joining_date` date DEFAULT NULL,
        `salary` decimal(10,2) DEFAULT NULL,
        `address` text DEFAULT NULL,
        `photo` varchar(255) DEFAULT NULL,
        `status` enum('active','inactive') DEFAULT 'active',
        `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
        `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        UNIQUE KEY `employee_id` (`employee_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    executeQuery($conn, $sql);
    
    // Create attendance table
    $sql = "CREATE TABLE IF NOT EXISTS `attendance` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `employee_id` int(11) NOT NULL,
        `date` date NOT NULL,
        `time_in` time DEFAULT NULL,
        `time_out` time DEFAULT NULL,
        `status` enum('present','absent','late','half-day') DEFAULT NULL,
        `notes` text DEFAULT NULL,
        `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
        `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        KEY `employee_id` (`employee_id`),
        CONSTRAINT `attendance_ibfk_1` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    executeQuery($conn, $sql);
    
    // Create products table
    $sql = "CREATE TABLE IF NOT EXISTS `products` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `product_code` varchar(50) NOT NULL,
        `name` varchar(100) NOT NULL,
        `category` varchar(50) DEFAULT NULL,
        `description` text DEFAULT NULL,
        `unit` varchar(20) DEFAULT NULL,
        `purchase_price` decimal(10,2) DEFAULT NULL,
        `selling_price` decimal(10,2) DEFAULT NULL,
        `tax_rate` decimal(5,2) DEFAULT NULL,
        `min_stock_level` int(11) DEFAULT NULL,
        `status` enum('active','inactive') DEFAULT 'active',
        `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
        `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        UNIQUE KEY `product_code` (`product_code`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    executeQuery($conn, $sql);
    
    // Create inventory table
    $sql = "CREATE TABLE IF NOT EXISTS `inventory` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `product_id` int(11) NOT NULL,
        `godown_id` int(11) NOT NULL,
        `batch_number` varchar(50) DEFAULT NULL,
        `quantity` int(11) NOT NULL DEFAULT 0,
        `expiry_date` date DEFAULT NULL,
        `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
        `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        KEY `product_id` (`product_id`),
        KEY `godown_id` (`godown_id`),
        CONSTRAINT `inventory_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    executeQuery($conn, $sql);
    
    // Create godowns table
    $sql = "CREATE TABLE IF NOT EXISTS `godowns` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `name` varchar(100) NOT NULL,
        `location` varchar(255) DEFAULT NULL,
        `manager_id` int(11) DEFAULT NULL,
        `capacity` varchar(50) DEFAULT NULL,
        `status` enum('active','inactive') DEFAULT 'active',
        `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
        `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    executeQuery($conn, $sql);
    
    // Add foreign key to inventory table for godown
    $sql = "ALTER TABLE `inventory` ADD CONSTRAINT `inventory_ibfk_2` FOREIGN KEY (`godown_id`) REFERENCES `godowns` (`id`)";
    executeQuery($conn, $sql);
    
    // Create purchase_orders table
    $sql = "CREATE TABLE IF NOT EXISTS `purchase_orders` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `po_number` varchar(50) NOT NULL,
        `supplier_id` int(11) NOT NULL,
        `order_date` date NOT NULL,
        `delivery_date` date DEFAULT NULL,
        `status` enum('draft','pending','approved','received','cancelled') DEFAULT 'draft',
        `total_amount` decimal(12,2) DEFAULT NULL,
        `tax_amount` decimal(10,2) DEFAULT NULL,
        `discount_amount` decimal(10,2) DEFAULT NULL,
        `grand_total` decimal(12,2) DEFAULT NULL,
        `payment_terms` text DEFAULT NULL,
        `notes` text DEFAULT NULL,
        `created_by` int(11) DEFAULT NULL,
        `approved_by` int(11) DEFAULT NULL,
        `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
        `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        UNIQUE KEY `po_number` (`po_number`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    executeQuery($conn, $sql);
    
    // Create purchase_order_items table
    $sql = "CREATE TABLE IF NOT EXISTS `purchase_order_items` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `po_id` int(11) NOT NULL,
        `product_id` int(11) NOT NULL,
        `quantity` int(11) NOT NULL,
        `unit_price` decimal(10,2) NOT NULL,
        `tax_rate` decimal(5,2) DEFAULT NULL,
        `tax_amount` decimal(10,2) DEFAULT NULL,
        `discount_percent` decimal(5,2) DEFAULT NULL,
        `discount_amount` decimal(10,2) DEFAULT NULL,
        `total_amount` decimal(12,2) NOT NULL,
        `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
        `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        KEY `po_id` (`po_id`),
        KEY `product_id` (`product_id`),
        CONSTRAINT `purchase_order_items_ibfk_1` FOREIGN KEY (`po_id`) REFERENCES `purchase_orders` (`id`) ON DELETE CASCADE,
        CONSTRAINT `purchase_order_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    executeQuery($conn, $sql);
    
    // Create suppliers table
    $sql = "CREATE TABLE IF NOT EXISTS `suppliers` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `name` varchar(100) NOT NULL,
        `contact_person` varchar(100) DEFAULT NULL,
        `email` varchar(100) DEFAULT NULL,
        `phone` varchar(20) DEFAULT NULL,
        `address` text DEFAULT NULL,
        `tax_number` varchar(50) DEFAULT NULL,
        `payment_terms` varchar(100) DEFAULT NULL,
        `status` enum('active','inactive') DEFAULT 'active',
        `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
        `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    executeQuery($conn, $sql);
    
    // Add foreign key to purchase_orders table for supplier
    $sql = "ALTER TABLE `purchase_orders` ADD CONSTRAINT `purchase_orders_ibfk_1` FOREIGN KEY (`supplier_id`) REFERENCES `suppliers` (`id`)";
    executeQuery($conn, $sql);
    
    // Create inventory_transactions table
    $sql = "CREATE TABLE IF NOT EXISTS `inventory_transactions` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `transaction_type` enum('purchase','sale','transfer','return','adjustment') NOT NULL,
        `reference_id` varchar(50) DEFAULT NULL,
        `product_id` int(11) NOT NULL,
        `from_godown_id` int(11) DEFAULT NULL,
        `to_godown_id` int(11) DEFAULT NULL,
        `quantity` int(11) NOT NULL,
        `transaction_date` datetime NOT NULL,
        `notes` text DEFAULT NULL,
        `created_by` int(11) DEFAULT NULL,
        `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        KEY `product_id` (`product_id`),
        KEY `from_godown_id` (`from_godown_id`),
        KEY `to_godown_id` (`to_godown_id`),
        CONSTRAINT `inventory_transactions_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`),
        CONSTRAINT `inventory_transactions_ibfk_2` FOREIGN KEY (`from_godown_id`) REFERENCES `godowns` (`id`),
        CONSTRAINT `inventory_transactions_ibfk_3` FOREIGN KEY (`to_godown_id`) REFERENCES `godowns` (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    executeQuery($conn, $sql);
    
    // Create accounting_accounts table
    $sql = "CREATE TABLE IF NOT EXISTS `accounting_accounts` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `account_code` varchar(20) NOT NULL,
        `account_name` varchar(100) NOT NULL,
        `account_type` enum('asset','liability','equity','revenue','expense') NOT NULL,
        `parent_account_id` int(11) DEFAULT NULL,
        `opening_balance` decimal(12,2) DEFAULT 0.00,
        `current_balance` decimal(12,2) DEFAULT 0.00,
        `description` text DEFAULT NULL,
        `is_active` tinyint(1) DEFAULT 1,
        `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
        `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        UNIQUE KEY `account_code` (`account_code`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    executeQuery($conn, $sql);
    
    // Create accounting_transactions table
    $sql = "CREATE TABLE IF NOT EXISTS `accounting_transactions` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `transaction_date` date NOT NULL,
        `reference_no` varchar(50) DEFAULT NULL,
        `transaction_type` varchar(50) NOT NULL,
        `description` text DEFAULT NULL,
        `total_amount` decimal(12,2) NOT NULL,
        `created_by` int(11) DEFAULT NULL,
        `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
        `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    executeQuery($conn, $sql);
    
    // Create accounting_entries table
    $sql = "CREATE TABLE IF NOT EXISTS `accounting_entries` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `transaction_id` int(11) NOT NULL,
        `account_id` int(11) NOT NULL,
        `debit_amount` decimal(12,2) DEFAULT 0.00,
        `credit_amount` decimal(12,2) DEFAULT 0.00,
        `description` text DEFAULT NULL,
        `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        KEY `transaction_id` (`transaction_id`),
        KEY `account_id` (`account_id`),
        CONSTRAINT `accounting_entries_ibfk_1` FOREIGN KEY (`transaction_id`) REFERENCES `accounting_transactions` (`id`) ON DELETE CASCADE,
        CONSTRAINT `accounting_entries_ibfk_2` FOREIGN KEY (`account_id`) REFERENCES `accounting_accounts` (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    executeQuery($conn, $sql);
    
    // Create sales table
    $sql = "CREATE TABLE IF NOT EXISTS `sales` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `invoice_number` varchar(50) NOT NULL,
        `customer_id` int(11) DEFAULT NULL,
        `sale_date` date NOT NULL,
        `total_amount` decimal(12,2) NOT NULL,
        `tax_amount` decimal(10,2) DEFAULT NULL,
        `discount_amount` decimal(10,2) DEFAULT NULL,
        `grand_total` decimal(12,2) NOT NULL,
        `payment_method` varchar(50) DEFAULT NULL,
        `payment_status` enum('paid','partial','unpaid') DEFAULT 'unpaid',
        `notes` text DEFAULT NULL,
        `created_by` int(11) DEFAULT NULL,
        `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
        `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        UNIQUE KEY `invoice_number` (`invoice_number`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    executeQuery($conn, $sql);
    
    // Create sale_items table
    $sql = "CREATE TABLE IF NOT EXISTS `sale_items` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `sale_id` int(11) NOT NULL,
        `product_id` int(11) NOT NULL,
        `quantity` int(11) NOT NULL,
        `unit_price` decimal(10,2) NOT NULL,
        `tax_rate` decimal(5,2) DEFAULT NULL,
        `tax_amount` decimal(10,2) DEFAULT NULL,
        `discount_percent` decimal(5,2) DEFAULT NULL,
        `discount_amount` decimal(10,2) DEFAULT NULL,
        `total_amount` decimal(12,2) NOT NULL,
        `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        KEY `sale_id` (`sale_id`),
        KEY `product_id` (`product_id`),
        CONSTRAINT `sale_items_ibfk_1` FOREIGN KEY (`sale_id`) REFERENCES `sales` (`id`) ON DELETE CASCADE,
        CONSTRAINT `sale_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    executeQuery($conn, $sql);
    
    // Create customers table
    $sql = "CREATE TABLE IF NOT EXISTS `customers` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `name` varchar(100) NOT NULL,
        `contact_person` varchar(100) DEFAULT NULL,
        `email` varchar(100) DEFAULT NULL,
        `phone` varchar(20) DEFAULT NULL,
        `address` text DEFAULT NULL,
        `tax_number` varchar(50) DEFAULT NULL,
        `credit_limit` decimal(12,2) DEFAULT NULL,
        `status` enum('active','inactive') DEFAULT 'active',
        `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
        `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    executeQuery($conn, $sql);
    
    // Add foreign key to sales table for customer
    $sql = "ALTER TABLE `sales` ADD CONSTRAINT `sales_ibfk_1` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`)";
    executeQuery($conn, $sql);
    
    // Create quality_checks table
    $sql = "CREATE TABLE IF NOT EXISTS `quality_checks` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `reference_id` varchar(50) NOT NULL,
        `reference_type` enum('purchase','return','production') NOT NULL,
        `product_id` int(11) NOT NULL,
        `batch_number` varchar(50) DEFAULT NULL,
        `quantity_checked` int(11) NOT NULL,
        `quantity_passed` int(11) NOT NULL,
        `quantity_failed` int(11) NOT NULL,
        `check_date` date NOT NULL,
        `checked_by` int(11) DEFAULT NULL,
        `remarks` text DEFAULT NULL,
        `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
        `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        KEY `product_id` (`product_id`),
        CONSTRAINT `quality_checks_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    executeQuery($conn, $sql);
    
    // Create payroll table
    $sql = "CREATE TABLE IF NOT EXISTS `payroll` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `employee_id` int(11) NOT NULL,
        `pay_period` varchar(20) NOT NULL,
        `basic_salary` decimal(10,2) NOT NULL,
        `allowances` decimal(10,2) DEFAULT NULL,
        `deductions` decimal(10,2) DEFAULT NULL,
        `tax` decimal(10,2) DEFAULT NULL,
        `net_salary` decimal(10,2) NOT NULL,
        `payment_date` date DEFAULT NULL,
        `payment_method` varchar(50) DEFAULT NULL,
        `status` enum('pending','paid','cancelled') DEFAULT 'pending',
        `created_by` int(11) DEFAULT NULL,
        `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
        `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        KEY `employee_id` (`employee_id`),
        CONSTRAINT `payroll_ibfk_1` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    executeQuery($conn, $sql);
    
    // Create audit_trail table
    $sql = "CREATE TABLE IF NOT EXISTS `audit_trail` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `user_id` int(11) DEFAULT NULL,
        `action` varchar(100) NOT NULL,
        `module` varchar(50) NOT NULL,
        `record_id` int(11) DEFAULT NULL,
        `old_value` text DEFAULT NULL,
        `new_value` text DEFAULT NULL,
        `ip_address` varchar(50) DEFAULT NULL,
        `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        KEY `user_id` (`user_id`),
        CONSTRAINT `audit_trail_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    executeQuery($conn, $sql);
    
    echo "<div class='success'><h2>Database setup completed successfully!</h2></div>";
    echo "<p>You can now <a href='index.php'>go to the homepage</a> or <a href='login.php'>login</a> with the default admin credentials:</p>";
    echo "<p>Username: admin<br>Password: admin123</p>";
    echo "<p><strong>Important:</strong> Please change the default admin password after your first login.</p>";
} else {
    // Display setup confirmation form
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ERP System Setup</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            margin: 0;
            padding: 20px;
            background-color: #f5f5f5;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        h1 {
            color: #333;
            border-bottom: 1px solid #ddd;
            padding-bottom: 10px;
        }
        .warning {
            background-color: #fff3cd;
            color: #856404;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 5px;
            border-left: 5px solid #ffeeba;
        }
        .success {
            background-color: #d4edda;
            color: #155724;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 5px;
            border-left: 5px solid #c3e6cb;
        }
        .error {
            background-color: #f8d7da;
            color: #721c24;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 5px;
            border-left: 5px solid #f5c6cb;
        }
        form {
            margin-top: 20px;
        }
        button {
            background-color: #dc3545;
            color: white;
            border: none;
            padding: 10px 15px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
        }
        button:hover {
            background-color: #c82333;
        }
        a {
            color: #007bff;
            text-decoration: none;
        }
        a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>ERP System Setup</h1>
        
        <div class="warning">
            <strong>Warning!</strong> This setup will drop all existing tables in the 'cms' database and create new ones.
            All existing data will be lost. This action cannot be undone.
        </div>
        
        <p>This setup will create the following:</p>
        <ul>
            <li>User management system with role-based access control</li>
            <li>HR module with employee management and attendance tracking</li>
            <li>Inventory management system with multiple godowns</li>
            <li>Purchase order and supplier management</li>
            <li>Sales and customer management</li>
            <li>Accounting and financial management</li>
            <li>Quality control system</li>
            <li>Audit trail for system activities</li>
        </ul>
        
        <form method="post" onsubmit="return confirm('Are you sure you want to proceed with the database setup? All existing data will be lost!')">
            <input type="hidden" name="confirm_setup" value="yes">
            <button type="submit">Proceed with Setup</button>
            <a href="index.php" style="margin-left: 10px;">Cancel</a>
        </form>
    </div>
</body>
</html>
<?php
}

// Close connection
mysqli_close($conn);
?>