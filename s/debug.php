<?php
// debug.php
// A temporary script to diagnose login issues.

require_once 'config/config.php';

header('Content-Type: text/plain');

echo "--- Sales App Debug Information ---\\n\\n";

// 1. Check PHP Version
echo "[INFO] PHP Version: " . phpversion() . "\n";
if (version_compare(phpversion(), '7.2.0', '<')) {
    echo "[ERROR] Your PHP version is very old. The password hashing may not work as expected. Please upgrade PHP.\n";
}

// 2. Check Database Connection
echo "\\n--- Checking Database Connection ---\\n";
try {
    $pdo = get_db_connection();
    echo "[SUCCESS] Database connection successful.\n";
} catch (PDOException $e) {
    echo "[FATAL ERROR] Could not connect to the database. Please check your settings in 'config/config.php'.\n";
    echo "Error Message: " . $e->getMessage() . "\n";
    exit; // Stop here if we can't connect
}

// 3. Check Users Table
echo "\\n--- Fetching Users from Database ---\\n";
try {
    $stmt = $pdo->query("SELECT id, username, role, password_hash FROM users ORDER BY id");
    $users = $stmt->fetchAll();

    if (empty($users)) {
        echo "[ERROR] The 'users' table is empty. Please make sure you have run setup.php successfully.\n";
    } else {
        echo "[SUCCESS] Found " . count($users) . " users:\\n\\n";
        print_r($users);
    }
} catch (Exception $e) {
    echo "[ERROR] Failed to query the 'users' table. It might not exist. Please run setup.php.\n";
    echo "Error Message: " . $e->getMessage() . "\n";
}

// 4. Check Password Hashing
echo "\\n--- Verifying Default Password ---\\n";
$test_password = 'password';
if (!empty($users)) {
    $admin_user = null;
    foreach($users as $user) {
        if ($user['username'] === 'admin') {
            $admin_user = $user;
            break;
        }
    }

    if ($admin_user) {
        $hash_from_db = $admin_user['password_hash'];
        echo "Hash for 'admin' from DB: " . $hash_from_db . "\n";
        echo "Testing against password: '" . $test_password . "'\n";

        if (password_verify($test_password, $hash_from_db)) {
            echo "[SUCCESS] password_verify() works correctly for the admin user.\n";
        } else {
            echo "[ERROR] password_verify() FAILED. The hash in your database does not match the default password. This is the likely cause of the login issue.\n";
            echo "This can happen if the database was seeded manually or if there's a character encoding issue.\n";
        }
    } else {
        echo "[ERROR] Could not find the 'admin' user to test password verification.\n";
    }
} else {
    echo "[INFO] Skipping password check because no users were found.\n";
}

echo "\\n--- End of Debug ---\\n";

?>
