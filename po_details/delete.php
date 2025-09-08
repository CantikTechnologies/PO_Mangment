<?php
session_start();
if (!isset($_SESSION['username'])) {
  header('Location: ../1Login_signuppage/login.php');
  exit();
}
include '../db.php';

$id = intval($_GET['id'] ?? 0);

if ($id) {
  $stmt = $conn->prepare("DELETE FROM purchase_orders WHERE po_id = ?");
  $stmt->bind_param("i", $id);
  $stmt->execute();
  $stmt->close();
}

header('Location: list.php');
exit;
