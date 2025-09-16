<?php
session_start();
header('Content-Type: application/json');
require_once '../../../config/db.php';

$po = isset($_GET['po_number']) ? trim($_GET['po_number']) : '';
if ($po === '') {
    echo json_encode(['success' => false, 'error' => 'Missing po_number']);
    exit;
}

$sql = "SELECT COALESCE(SUM(vendor_inv_value),0) AS total_booked FROM outsourcing_detail WHERE customer_po = ?";
$stmt = $conn->prepare($sql);
if (!$stmt) {
    echo json_encode(['success' => false, 'error' => $conn->error]);
    exit;
}
$stmt->bind_param('s', $po);
$stmt->execute();
$res = $stmt->get_result();
$row = $res->fetch_assoc();
$stmt->close();

echo json_encode(['success' => true, 'data' => ['total_booked' => (float)($row['total_booked'] ?? 0)]]);
?>


