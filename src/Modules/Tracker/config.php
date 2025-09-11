<?php
// Database Configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'po_management');
define('DB_USER', 'root');
define('DB_PASS', '');

// Enable error reporting for debugging (disable in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Function to create database connection
function getDatabaseConnection() {
    try {
        $pdo = new PDO(
            "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
            DB_USER,
            DB_PASS,
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false
            ]
        );
        return $pdo;
    } catch(PDOException $e) {
        throw new Exception('Database connection failed: ' . $e->getMessage());
    }
}




// Function to test database connection
function testDatabaseConnection() {
    try {
        $pdo = getDatabaseConnection();
        return ['success' => true, 'message' => 'Database connection successful'];
    } catch (Exception $e) {
        return ['success' => false, 'message' => $e->getMessage()];
    }
}
?> 