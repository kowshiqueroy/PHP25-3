<?php
// config.php
$host = 'localhost';
$db   = 'pos_db';
$user = 'root';
$pass = '';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
    // If DB doesn't exist, we might be in setup mode
    if(strpos($e->getMessage(), "Unknown database") === false){
         throw new \PDOException($e->getMessage(), (int)$e->getCode());
    }
}
?>