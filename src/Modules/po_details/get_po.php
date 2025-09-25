<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['username'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

include '../../../config/db.php';
include '../../../config/auth.php';

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

// Compute live pending balance: PO value - sum of billed taxable for this PO
$totalBilled = 0.0;
if (!empty($po['po_number'])) {
    if ($sumStmt = $conn->prepare("SELECT COALESCE(SUM(cantik_inv_value_taxable),0) AS total_billed FROM billing_details WHERE customer_po = ?")) {
        $sumStmt->bind_param('s', $po['po_number']);
        if ($sumStmt->execute()) {
            $sumRes = $sumStmt->get_result();
            if ($sumRow = $sumRes->fetch_assoc()) {
                $totalBilled = (float)$sumRow['total_billed'];
            }
        }
        $sumStmt->close();
    }
}
$poValue = (float)($po['po_value'] ?? 0);
$computedPending = max(0, $poValue - $totalBilled);

// Fetch latest Cantik PO number and value for this customer PO from outsourcing_detail
$latestCantikPoNo = null;
$latestCantikPoValue = 0.0;
if (!empty($po['po_number'])) {
    if ($latestStmt = $conn->prepare("SELECT 
            MAX(cantik_po_no) AS latest_cantik_po_no,
            MAX(cantik_po_value) AS latest_cantik_po_value
        FROM outsourcing_detail
        WHERE customer_po = ?")) {
        $latestStmt->bind_param('s', $po['po_number']);
        if ($latestStmt->execute()) {
            $latestRes = $latestStmt->get_result();
            if ($latestRow = $latestRes->fetch_assoc()) {
                $latestCantikPoNo = $latestRow['latest_cantik_po_no'] ?? null;
                $latestCantikPoValue = (float)($latestRow['latest_cantik_po_value'] ?? 0);
            }
        }
        $latestStmt->close();
    }
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
    // Prefer live-computed pending amount to avoid stale values
    'pending_amount' => $computedPending,
    'pending_amount_source' => 'computed',
    'latest_cantik_po_no' => $latestCantikPoNo,
    'latest_cantik_po_value' => $latestCantikPoValue,
];

echo json_encode(['data' => $payload]);
?>


