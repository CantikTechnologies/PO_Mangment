<?php
// Simple test file to debug upload issues
header('Content-Type: application/json');

session_start();

// Test basic functionality
$response = [
    'success' => true,
    'message' => 'Test endpoint working',
    'session' => isset($_SESSION['username']) ? 'Logged in as: ' . $_SESSION['username'] : 'Not logged in',
    'post_data' => $_POST,
    'files' => $_FILES,
    'method' => $_SERVER['REQUEST_METHOD']
];

echo json_encode($response);
?>
