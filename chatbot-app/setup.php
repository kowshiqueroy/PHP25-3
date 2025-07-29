<?php
require_once __DIR__ . '/db.php';
echo "Running setup...\n";
$pdo = getPDO();
$sql = file_get_contents(__DIR__ . '/setup_schema.sql'); // You can dump the DDL above into this file
$pdo->exec($sql);

// Create default admin
$passwordHash = password_hash('adminpass', PASSWORD_BCRYPT);
$stmt = $pdo->prepare("INSERT IGNORE INTO users (username, password_hash, is_admin) VALUES ('admin', ?, 1)");
$stmt->execute([$passwordHash]);

echo "Setup complete. Admin user created (admin/adminpass).\n";