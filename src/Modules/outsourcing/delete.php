<?php
// Use universal includes
include '../../shared/includes.php';

// Check authentication
if (!isset($_SESSION['username'])) {
  header('Location: ' . getLoginUrl());
  exit();
}
requirePermission('delete_outsourcing');

$id = intval($_GET['id'] ?? 0);

if ($id) {
  $stmt = $conn->prepare("DELETE FROM outsourcing_detail WHERE id = ?");
  if ($stmt) {
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();
  }
}

header('Location: list.php');
exit;
