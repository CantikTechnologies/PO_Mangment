<?php
// Include path configuration
if (!defined('PATHS_LOADED')) {
    include_once __DIR__ . '/paths.php';
}

$host = "localhost";
$user = "root";   // change if needed
$pass = "";       // change if needed
$db   = "po_management";

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
