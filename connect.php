<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

$host = "127.0.0.1"; // Usually localhost
$port = 3306; // Default MySQL port
$user = "root"; // Your database username
$password = ""; // Your database password (usually none for root)
$dbname = "autorepairshop"; // Name of your database

try {
    $dbh = new PDO("mysql:host={$host};port={$port};dbname={$dbname}", $user, $password);
    $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); // Enable error handling
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}
?>
