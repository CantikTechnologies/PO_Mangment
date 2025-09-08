<?php
header('Content-Type: application/json');
include_once '../db.php'; // provides $conn (mysqli)

if (!$conn) {
  echo json_encode(['success' => false, 'error' => 'DB connection not available']);
  exit;
}

$action = $_GET['action'] ?? '';

function json_ok($data = []) { echo json_encode(array_merge(['success' => true], $data)); exit; }
function json_err($msg, $code = 400) { http_response_code($code); echo json_encode(['success' => false, 'error' => $msg]); exit; }

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  if ($action === 'add_task') {
    $action_requested_by = trim($_POST['action_requested_by'] ?? '');
    $request_date = $_POST['request_date'] ?? '';
    $cost_center = trim($_POST['cost_center'] ?? '');
    $action_required = trim($_POST['action_required'] ?? '');
    $action_owner = trim($_POST['action_owner'] ?? '');
    $status_of_action = trim($_POST['status_of_action'] ?? 'Pending');
    $completion_date = $_POST['completion_date'] ?? null;
    $remark = trim($_POST['remark'] ?? '');

    if ($action_requested_by === '' || $request_date === '' || $cost_center === '' || $action_required === '' || $action_owner === '') {
      json_err('Required fields missing');
    }

    $stmt = $conn->prepare("INSERT INTO finance_tasks (action_requested_by, request_date, cost_center, action_required, action_owner, status_of_action, completion_date, remark) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param('ssssssss', $action_requested_by, $request_date, $cost_center, $action_required, $action_owner, $status_of_action, $completion_date, $remark);
    if (!$stmt->execute()) json_err('Insert failed: ' . $stmt->error, 500);
    json_ok(['message' => 'Task added']);
  }

  if ($action === 'update_task') {
    $id = intval($_POST['id'] ?? 0);
    if ($id <= 0) json_err('Invalid ID');
    $action_requested_by = trim($_POST['action_requested_by'] ?? '');
    $request_date = $_POST['request_date'] ?? '';
    $cost_center = trim($_POST['cost_center'] ?? '');
    $action_required = trim($_POST['action_required'] ?? '');
    $action_owner = trim($_POST['action_owner'] ?? '');
    $status_of_action = trim($_POST['status_of_action'] ?? 'Pending');
    $completion_date = $_POST['completion_date'] ?? null;
    $remark = trim($_POST['remark'] ?? '');

    $stmt = $conn->prepare("UPDATE finance_tasks SET action_requested_by=?, request_date=?, cost_center=?, action_required=?, action_owner=?, status_of_action=?, completion_date=?, remark=? WHERE id=?");
    $stmt->bind_param('ssssssssi', $action_requested_by, $request_date, $cost_center, $action_required, $action_owner, $status_of_action, $completion_date, $remark, $id);
    if (!$stmt->execute()) json_err('Update failed: ' . $stmt->error, 500);
    json_ok(['message' => 'Task updated']);
  }

  if ($action === 'delete_task') {
    $id = intval($_POST['id'] ?? 0);
    if ($id <= 0) json_err('Invalid ID');
    $stmt = $conn->prepare("DELETE FROM finance_tasks WHERE id=?");
    $stmt->bind_param('i', $id);
    if (!$stmt->execute()) json_err('Delete failed: ' . $stmt->error, 500);
    json_ok(['message' => 'Task deleted']);
  }

  json_err('Unknown action');
}

// Fallback: return error for invalid method
json_err('Invalid request method', 405);
?>