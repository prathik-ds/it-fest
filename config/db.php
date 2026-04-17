<?php
// Database connection configuration

$host = 'localhost';
$db   = 'fusionverse_db';
$user = 'root';
$pass = ''; // Default XAMPP/Wamp password is empty
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
    die("Database connection failed. Check your MySQL configuration: " . $e->getMessage());
}
?>
