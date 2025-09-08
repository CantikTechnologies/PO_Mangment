<?php
header('Content-Type: application/json');
include_once '../db.php';

if (!$conn) {
  http_response_code(500);
  echo json_encode(['success' => false, 'error' => 'DB connection not available']);
  exit;
}

if (isset($_GET['id']) && is_numeric($_GET['id'])) {
  $tracker_id = (int)$_GET['id'];
  $stmt = $conn->prepare("SELECT * FROM finance_tasks WHERE id = ?");
  $stmt->bind_param('i', $tracker_id);
  if ($stmt->execute()) {
    $res = $stmt->get_result();
    $tracker = $res->fetch_assoc();
    if (!$tracker) { http_response_code(404); echo json_encode(['success' => false, 'error' => 'Tracker not found']); exit; }
    echo json_encode(['success' => true, 'data' => $tracker]);
    exit;
  }
  http_response_code(500);
  echo json_encode(['success' => false, 'error' => 'Query failed']);
  exit;
}

$res = $conn->query("SELECT * FROM finance_tasks ORDER BY request_date DESC, created_at DESC");
$rows = [];
if ($res) { while ($row = $res->fetch_assoc()) { $rows[] = $row; } }
echo json_encode(['success' => true, 'data' => $rows]);
?>
