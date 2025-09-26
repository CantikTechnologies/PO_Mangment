<?php
header('Content-Type: application/json');
echo json_encode([
    'success' => false, 
    'errors' => [['row' => 0, 'message' => 'TEST: This is the test_upload.php file - path is correct!']], 
    'warnings' => []
]);
?>
