<?php
session_start();
if (!isset($_SESSION['username'])) {
  header('Location: ../1Login_signuppage/login.php');
  exit();
}
include '../db.php';
include '../auth.php';
requirePermission('delete_invoices');

$id = intval($_GET['id'] ?? 0);

if ($id) {
  $stmt = $conn->prepare("DELETE FROM billing_details WHERE id = ?");
  if ($stmt) {
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();
  }
}

header('Location: list.php');
exit;