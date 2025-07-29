<?php
// db.php
// English: Connects to MySQL database using PDO.
// বাংলা: PDO ব্যবহার করে MySQL সার্ভারে সংযোগ স্থাপন করে।

$host   = 'localhost';
$db     = 'chatbot';       // replace with your database name
$user   = 'root';      // replace with your DB user
$pass   = '';      // replace with your DB password
$charset= 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (PDOException $e) {
    // English: If connection fails, stop and show error message.
    // বাংলা: সংযোগ ব্যর্থ হলে, ত্রুটি দেখিয়ে স্ক্রিপ্ট বন্ধ করে দেয়।
    exit('Database connection failed: ' . $e->getMessage());
}