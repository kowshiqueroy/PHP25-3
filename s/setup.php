<?php
// setup.php
// This script initializes the database by executing the setup.sql file.

require_once 'config/config.php';

echo "<pre>";
echo "Attempting to set up the database.../n";

try {
    // Connect to MySQL server without specifying a database
    $pdo = new PDO("mysql:host=" . DB_HOST, DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
    echo "Successfully connected to MySQL server.\n";

    // Read the SQL file
    $sql_template = file_get_contents('db/setup.sql');
    if ($sql_template === false) {
        throw new Exception("Error: Cannot read the setup.sql file. Make sure it exists in the 'db' directory.");
    }
    echo "Read contents of db/setup.sql.\n";

    // Generate a dynamic password hash
    $password_hash = password_hash('password', PASSWORD_DEFAULT);
    echo "Generated new password hash.\n";

    // Replace the placeholder with the real hash
    // Using pdo->quote() is essential to correctly handle the string for SQL
    $sql = str_replace('__PASSWORD_HASH__', $pdo->quote($password_hash), $sql_template);
    echo "Replaced password placeholder in SQL script.\n";

    // Execute the SQL commands
    $pdo->exec($sql);
    echo "Successfully executed SQL script.\n";

    echo "\n----------------------------------------\n";
    echo "DATABASE SETUP COMPLETE!\n";
    echo "----------------------------------------\n";
    echo "You can now log in with the default users:\n";
    echo "- admin / password (Role: Admin)\n";
    echo "- manager1 / password (Role: Manager)\n";
    echo "- sr1 / password (Role: SR)\n";
    echo "- viewer1 / password (Role: Viewer)\n";

} catch (PDOException $e) {
    die("DB ERROR: " . $e->getMessage());
} catch (Exception $e) {
    die("ERROR: " . $e->getMessage());
}

echo "</pre>";

