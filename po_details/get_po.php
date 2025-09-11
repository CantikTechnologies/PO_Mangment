<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['username'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

require_once '../db.php';
require_once '../auth.php';

// Optional: permission gate for reading POs
if (function_exists('requirePermission')) {
    requirePermission('view_po_details');
}

$po_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$po_number = isset($_GET['po_number']) ? trim($_GET['po_number']) : '';

if (!$po_id && $po_number === '') {
    http_response_code(400);
    echo json_encode(['error' => 'Missing id or po_number']);
    exit();
}

if ($po_id) {
    $sql = "SELECT * FROM po_details WHERE id = ? LIMIT 1";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $po_id);
} else {
    $sql = "SELECT * FROM po_details WHERE po_number = ? LIMIT 1";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('s', $po_number);
}

if (!$stmt) {
    http_response_code(500);
    echo json_encode(['error' => 'DB prepare failed']);
    exit();
}

$stmt->execute();
$res = $stmt->get_result();
$po = $res->fetch_assoc();
$stmt->close();

if (!$po) {
    http_response_code(404);
    echo json_encode(['error' => 'PO not found']);
    exit();
}

// Helper to convert Excel date int to ISO date
$toIso = function($excelInt) {
    if (!$excelInt) return null;
    $ts = ($excelInt - 25569) * 86400;
    return date('Y-m-d', $ts);
};

$payload = [
    'id' => (int)$po['id'],
    'project_description' => $po['project_description'] ?? '',
    'cost_center' => $po['cost_center'] ?? '',
    'sow_number' => $po['sow_number'] ?? '',
    'start_date' => $toIso($po['start_date'] ?? null),
    'end_date' => $toIso($po['end_date'] ?? null),
    'po_number' => $po['po_number'] ?? '',
    'po_date' => $toIso($po['po_date'] ?? null),
    'po_value' => (float)($po['po_value'] ?? 0),
    'billing_frequency' => $po['billing_frequency'] ?? '',
    'target_gm' => $po['target_gm'] ?? null,
    'po_status' => $po['po_status'] ?? '',
    'remarks' => $po['remarks'] ?? '',
    'vendor_name' => $po['vendor_name'] ?? '',
    'pending_amount' => isset($po['pending_amount']) ? (float)$po['pending_amount'] : 0.0,
];

echo json_encode(['data' => $payload]);
?>


