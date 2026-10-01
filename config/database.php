<?php
/**
 * Database Configuration & PDO Connection
 * PaushtikAahar Kitchen - High-Protein Meal Ordering System
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$db_host = 'localhost';
$db_name = 'high_protein_meals';
$db_user = 'root';
$db_pass = '';
$db_charset = 'utf8mb4';

$dsn = "mysql:host={$db_host};dbname={$db_name};charset={$db_charset}";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $db_user, $db_pass, $options);
} catch (PDOException $e) {
    // Graceful error display
    die("Database Connection Error: " . htmlspecialchars($e->getMessage()));
}

// Function to safely get database handle
function get_db_connection() {
    global $pdo;
    return $pdo;
}
