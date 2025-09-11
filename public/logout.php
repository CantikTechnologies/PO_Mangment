<?php
session_start();

// Log logout action before destroying session
if (isset($_SESSION['user_id'])) {
    include_once '../config/db.php';
    include_once '../config/auth.php';
    $auth->logAction('logout');
}

session_destroy();
header("Location: login.php");
exit();
?>
