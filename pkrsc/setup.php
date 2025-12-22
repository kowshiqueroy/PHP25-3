<?php
// Include the database connection file
require_once 'config/db.php';

try {
    // Read the SQL file
    $sql = file_get_contents('database.sql');

    // Split the SQL file into individual queries
    $sql_queries = explode(';', $sql);

    // Execute CREATE TABLE queries
    echo "Creating tables...\n";
    foreach ($sql_queries as $query) {
        if (strpos(trim($query), 'CREATE TABLE') !== false) {
            $pdo->exec($query);
        }
    }
    echo "Tables created successfully (if they didn't exist).\n";

    // Check and insert default menus
    $stmt = $pdo->query("SELECT COUNT(*) FROM menus");
    if ($stmt->fetchColumn() == 0) {
        echo "Inserting default menus...\n";
        foreach ($sql_queries as $query) {
            if (strpos(trim($query), 'INSERT INTO menus') !== false) {
                $pdo->exec($query);
            }
        }
        echo "Default menus inserted.\n";
    } else {
        echo "Menus table already has data, skipping insertion.\n";
    }

    // Check and insert default settings
    $stmt = $pdo->query("SELECT COUNT(*) FROM site_settings");
    if ($stmt->fetchColumn() == 0) {
        echo "Inserting default settings...\n";
        foreach ($sql_queries as $query) {
            if (strpos(trim($query), 'INSERT INTO site_settings') !== false) {
                $pdo->exec($query);
            }
        }
        echo "Default settings inserted.\n";
    } else {
        echo "Site settings table already has data, skipping insertion.\n";
    }

    echo "Database setup completed successfully.";

} catch (PDOException $e) {
    die("Database setup failed: " . $e->getMessage());
}
?>