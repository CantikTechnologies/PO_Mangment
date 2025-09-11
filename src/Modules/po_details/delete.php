<?php
session_start();
if (!isset($_SESSION['username'])) {
  header('Location: ../../../login.php');
  exit();
}
include '../../../config/db.php';
include '../../../config/auth.php';
requirePermission('delete_po_details');

$id = intval($_GET['id'] ?? 0);

if ($id) {
  $stmt = $conn->prepare("DELETE FROM po_details WHERE id = ?");
  if ($stmt) {
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();
  }
}

header('Location: list.php');
exit;
