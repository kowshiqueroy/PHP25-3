<?php

require_once 'config.php';

// Attempt to connect to MySQL server
$conn = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

echo "Connected successfully to MySQL server.<br>";

// Create database if it doesn't exist
$sql_create_db = "CREATE DATABASE IF NOT EXISTS " . DB_NAME;
if ($conn->query($sql_create_db) === TRUE) {
    echo "Database '" . DB_NAME . "' created successfully or already exists.<br>";
} else {
    echo "Error creating database: " . $conn->error . "<br>";
}

// Select the database
$conn->select_db(DB_NAME);

// --- Drop Tables (FOR DEVELOPMENT ONLY - REMOVE IN PRODUCTION) ---
// This ensures a clean slate for development and prevents issues with schema changes.
$tables_to_drop = [
    'audit_trail',
    'qc_logs',
    'transactions',
    'product_batches',
    'products',
    'categories',
    'users',
    'stores',
    'roles'
];

foreach ($tables_to_drop as $table) {
    $sql_drop = "DROP TABLE IF EXISTS {$table}";
    if ($conn->query($sql_drop) === TRUE) {
        echo "Table '{$table}' dropped successfully (if it existed).<br>";
    } else {
        echo "Error dropping table '{$table}': " . $conn->error . "<br>";
    }
}

// --- SQL to create tables ---
$sql_tables = [
    "CREATE TABLE roles (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(50) NOT NULL UNIQUE
    )",
    "CREATE TABLE stores (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL UNIQUE,
        location VARCHAR(255),
        config_json TEXT DEFAULT '{}'
    )",
    "ALTER TABLE stores ADD COLUMN IF NOT EXISTS config_json TEXT DEFAULT '{}'",
    "CREATE TABLE users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(50) NOT NULL UNIQUE,
        password_hash VARCHAR(255) NOT NULL,
        role_id INT,
        store_id INT,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (role_id) REFERENCES roles(id),
        FOREIGN KEY (store_id) REFERENCES stores(id)
    )",
    "CREATE TABLE categories (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL UNIQUE,
        description TEXT
    )",
    "CREATE TABLE products (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(255) NOT NULL,
        category_id INT,
        sku VARCHAR(100) UNIQUE,
        image_path VARCHAR(255),
        description TEXT,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (category_id) REFERENCES categories(id)
    )",
    "CREATE TABLE product_batches (
        id INT AUTO_INCREMENT PRIMARY KEY,
        product_id INT NOT NULL,
        store_id INT NOT NULL,
        expiry_date DATE,
        storage_location VARCHAR(255),
        qc_status VARCHAR(50) DEFAULT 'Pending',
        damage_status VARCHAR(50) DEFAULT 'None',
        quantity INT NOT NULL DEFAULT 0,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (product_id) REFERENCES products(id),
        FOREIGN KEY (store_id) REFERENCES stores(id)
    )",
    "CREATE TABLE transactions (
        id INT AUTO_INCREMENT PRIMARY KEY,
        type VARCHAR(50) NOT NULL, -- IN, OUT, Return to Store, Return to Supplier, Mark as Damaged, Expiry Isolation
        user_id INT NOT NULL,
        store_id INT NOT NULL,
        transaction_date DATETIME DEFAULT CURRENT_TIMESTAMP,
        comments TEXT,
        person_name VARCHAR(255),
        contact_text VARCHAR(255),
        slip_number VARCHAR(100),
        qc_status VARCHAR(50) DEFAULT 'Pending',
        total_amount DECIMAL(10, 2) DEFAULT 0.00,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id),
        FOREIGN KEY (store_id) REFERENCES stores(id)
    )",
    "CREATE TABLE transaction_items (
        id INT AUTO_INCREMENT PRIMARY KEY,
        transaction_id INT NOT NULL,
        product_id INT NOT NULL,
        batch_id INT,
        quantity INT NOT NULL,
        unit_price DECIMAL(10, 2) NOT NULL,
        subtotal DECIMAL(10, 2) NOT NULL,
        FOREIGN KEY (transaction_id) REFERENCES transactions(id),
        FOREIGN KEY (product_id) REFERENCES products(id),
        FOREIGN KEY (batch_id) REFERENCES product_batches(id)
    )"
    "CREATE TABLE qc_logs (
        id INT AUTO_INCREMENT PRIMARY KEY,
        transaction_id INT NOT NULL,
        user_id INT NOT NULL,
        status VARCHAR(50) NOT NULL, -- Approved, Rejected
        comments TEXT,
        log_date DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (transaction_id) REFERENCES transactions(id),
        FOREIGN KEY (user_id) REFERENCES users(id)
    )",
    "CREATE TABLE audit_trail (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT,
        action VARCHAR(255) NOT NULL,
        entity_type VARCHAR(100),
        entity_id INT,
        old_value TEXT,
        new_value TEXT,
        timestamp DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id)
    )"
];

foreach ($sql_tables as $sql) {
    if ($conn->query($sql) === TRUE) {
        echo "Table created successfully.<br>";
    } else {
        echo "Error creating table: " . $conn->error . "<br>";
    }
}

// --- Seed sample data ---

// Roles
$roles = ['Admin', 'Manager', 'Data Entry', 'Viewer', 'Purchaser', 'QC'];
foreach ($roles as $role) {
    $stmt = $conn->prepare("INSERT IGNORE INTO roles (name) VALUES (?)");
    $stmt->bind_param("s", $role);
    $stmt->execute();
    if ($stmt->affected_rows > 0) {
        echo "Role '{$role}' added.<br>";
    } else {
        echo "Role '{$role}' already exists.<br>";
    }
    $stmt->close();
}

// Default Admin Account
$admin_username = DEFAULT_ADMIN_USERNAME;
$admin_password_hash = password_hash(DEFAULT_ADMIN_PASSWORD, PASSWORD_DEFAULT);

// Get Admin role ID
$admin_role_id = null;
$result = $conn->query("SELECT id FROM roles WHERE name = 'Admin'");
if ($result->num_rows > 0) {
    $admin_role_id = $result->fetch_assoc()['id'];
}

// Insert default store if not exists
$default_store_id = null;
$stmt = $conn->prepare("INSERT IGNORE INTO stores (name, location, config_json) VALUES (?, ?, ?)");
$store_name = 'Main Store';
$store_location = 'Central Warehouse';
$default_config = json_encode([
    'unit' => 'pcs',
    'currency' => 'USD',
    'expiry_alert_days' => 30
]);
$stmt->bind_param("sss", $store_name, $store_location, $default_config);
$stmt->execute();
if ($stmt->affected_rows > 0) {
    echo "Default store '{$store_name}' added.<br>";
    $default_store_id = $stmt->insert_id;
} else {
    echo "Default store '{$store_name}' already exists.<br>";
    $result = $conn->query("SELECT id FROM stores WHERE name = 'Main Store'");
    if ($result->num_rows > 0) {
        $default_store_id = $result->fetch_assoc()['id'];
    }
}
$stmt->close();


if ($admin_role_id && $default_store_id) {
    $stmt = $conn->prepare("INSERT IGNORE INTO users (username, password_hash, role_id, store_id) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssii", $admin_username, $admin_password_hash, $admin_role_id, $default_store_id);
    $stmt->execute();
    if ($stmt->affected_rows > 0) {
        echo "Default admin user '{$admin_username}' created.<br>";
    } else {
        echo "Default admin user '{$admin_username}' already exists.<br>";
    }
    $stmt->close();
} else {
    echo "Could not create default admin user: Admin role or Default store not found.<br>";
}

// Sample Categories
$categories = ['Electronics', 'Apparel', 'Home Goods', 'Food & Beverage'];
foreach ($categories as $category) {
    $stmt = $conn->prepare("INSERT IGNORE INTO categories (name) VALUES (?)");
    $stmt->bind_param("s", $category);
    $stmt->execute();
    if ($stmt->affected_rows > 0) {
        echo "Category '{$category}' added.<br>";
    } else {
        echo "Category '{$category}' already exists.<br>";
    }
    $stmt->close();
}

// Sample Products
$products = [
    ['Laptop', 'Electronics', 'LAP001', 'Powerful laptop'],
    ['T-Shirt', 'Apparel', 'TSH001', 'Cotton T-shirt'],
    ['Coffee Maker', 'Home Goods', 'CFM001', 'Automatic coffee maker'],
    ['Energy Drink', 'Food & Beverage', 'END001', 'High energy drink']
];

foreach ($products as $product_data) {
    list($name, $category_name, $sku, $description) = $product_data;
    $category_id = null;
    $result = $conn->query("SELECT id FROM categories WHERE name = '{$category_name}'");
    if ($result->num_rows > 0) {
        $category_id = $result->fetch_assoc()['id'];
    }

    if ($category_id) {
        $stmt = $conn->prepare("INSERT IGNORE INTO products (name, category_id, sku, description) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("siss", $name, $category_id, $sku, $description);
        $stmt->execute();
        if ($stmt->affected_rows > 0) {
            echo "Product '{$name}' added.<br>";
        } else {
            echo "Product '{$name}' already exists.<br>";
        }
        $stmt->close();
    } else {
        echo "Category '{$category_name}' not found for product '{$name}'.<br>";
    }
}

// Sample Product Batches
// Assuming product IDs and store ID are known after insertion
$sample_batches = [
    ['Laptop', 10, '2026-12-31', 'Aisle 1, Shelf 3', 'Approved', 'None'],
    ['T-Shirt', 50, NULL, 'Rack B, Section 2', 'Approved', 'None'],
    ['Energy Drink', 100, '2025-08-15', 'Fridge C', 'Pending', 'None']
];

foreach ($sample_batches as $batch_data) {
    list($product_name, $quantity, $expiry_date, $storage_location, $qc_status, $damage_status) = $batch_data;
    $product_id = null;
    $result = $conn->query("SELECT id FROM products WHERE name = '{$product_name}'");
    if ($result->num_rows > 0) {
        $product_id = $result->fetch_assoc()['id'];
    }

    if ($product_id && $default_store_id) {
        $stmt = $conn->prepare("INSERT INTO product_batches (product_id, store_id, expiry_date, storage_location, qc_status, damage_status, quantity) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("iissisi", $product_id, $default_store_id, $expiry_date, $storage_location, $qc_status, $damage_status, $quantity);
        $stmt->execute();
        if ($stmt->affected_rows > 0) {
            echo "Batch for '{$product_name}' added.<br>";
        } else {
            echo "Error adding batch for '{$product_name}': " . $stmt->error . "<br>";
        }
        $stmt->close();
    } else {
        echo "Could not add batch for '{$product_name}': Product or Store not found.<br>";
    }
}

$conn->close();

echo "<br>Setup complete. You can now navigate to index.php to login.";

?>