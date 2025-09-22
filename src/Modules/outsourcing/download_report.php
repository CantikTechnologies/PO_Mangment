<?php
// Use universal includes
include '../../shared/includes.php';

// Check authentication
if (!isset($_SESSION['username'])) {
  header('Location: ' . getLoginUrl());
  exit();
}
requirePermission('view_outsourcing');

// Search and filter parameters (same as list.php)
$search = isset($_GET['q']) ? trim($_GET['q']) : '';
$project_filter = isset($_GET['project']) ? trim($_GET['project']) : '';
$vendor_filter = isset($_GET['vendor']) ? trim($_GET['vendor']) : '';
$status_filter = isset($_GET['status']) ? trim($_GET['status']) : '';
$date_from = isset($_GET['date_from']) ? $_GET['date_from'] : '';
$date_to = isset($_GET['date_to']) ? $_GET['date_to'] : '';

// Build WHERE clause (same as list.php)
$where_conditions = [];
$params = [];
$param_types = '';

if (!empty($search)) {
    $where_conditions[] = "(project_details LIKE ? OR vendor_name LIKE ? OR customer_po LIKE ? OR cantik_po_no LIKE ?)";
    $search_param = "%$search%";
    $params = array_merge($params, [$search_param, $search_param, $search_param, $search_param]);
    $param_types .= 'ssss';
}

if (!empty($project_filter)) {
    $where_conditions[] = "project_details = ?";
    $params[] = $project_filter;
    $param_types .= 's';
}

if (!empty($vendor_filter)) {
    $where_conditions[] = "vendor_name = ?";
    $params[] = $vendor_filter;
    $param_types .= 's';
}

if (!empty($status_filter)) {
    $where_conditions[] = "payment_status_from_ntt = ?";
    $params[] = $status_filter;
    $param_types .= 's';
}

if (!empty($date_from)) {
    $where_conditions[] = "DATE(FROM_UNIXTIME((vendor_inv_date - 25569) * 86400)) >= ?";
    $params[] = $date_from;
    $param_types .= 's';
}

if (!empty($date_to)) {
    $where_conditions[] = "DATE(FROM_UNIXTIME((vendor_inv_date - 25569) * 86400)) <= ?";
    $params[] = $date_to;
    $param_types .= 's';
}

$where_clause = !empty($where_conditions) ? 'WHERE ' . implode(' AND ', $where_conditions) : '';

// Get all outsourcing details for export
$sql = "SELECT * FROM outsourcing_detail $where_clause ORDER BY created_at DESC";
$stmt = $conn->prepare($sql);
if (!empty($params)) {
    $stmt->bind_param($param_types, ...$params);
}
$stmt->execute();
$results = $stmt->get_result();

// Set headers for CSV download
header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="outsourcing_report_' . date('Y-m-d') . '.csv"');
header('Pragma: no-cache');
header('Expires: 0');

// Create file handle
$output = fopen('php://output', 'w');

// Add BOM for UTF-8
fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

// CSV headers
$headers = [
    'ID',
    'Project Details',
    'Cost Center',
    'Customer PO',
    'Vendor Name',
    'Cantik PO No',
    'Cantik PO Date',
    'Cantik PO Value',
    'Remaining Bal in PO',
    'Vendor Invoice Frequency',
    'Vendor Inv Number',
    'Vendor Inv Date',
    'Vendor Inv Value',
    'TDS Ded',
    'Net Payable',
    'Payment Status from NTT',
    'Payment Value',
    'Payment Date',
    'Pending Payment',
    'Remarks',
    'Created At'
];
fputcsv($output, $headers);

// Helper function to format date
function formatDateForCSV($excel_date) {
    if (empty($excel_date)) return '';
    $unix_date = ($excel_date - 25569) * 86400;
    return date('Y-m-d', $unix_date);
}

// Add data rows
while ($row = $results->fetch_assoc()) {
    $csv_row = [
        $row['id'],
        $row['project_details'],
        $row['cost_center'],
        $row['customer_po'],
        $row['vendor_name'],
        $row['cantik_po_no'],
        formatDateForCSV($row['cantik_po_date']),
        $row['cantik_po_value'],
        $row['remaining_bal_in_po'],
        $row['vendor_invoice_frequency'],
        $row['vendor_inv_number'],
        formatDateForCSV($row['vendor_inv_date']),
        $row['vendor_inv_value'],
        $row['tds_ded'],
        $row['net_payble'],
        $row['payment_status_from_ntt'],
        $row['payment_value'],
        formatDateForCSV($row['payment_date']),
        $row['pending_payment'],
        $row['remarks'],
        $row['created_at']
    ];
    fputcsv($output, $csv_row);
}

fclose($output);
$stmt->close();
exit();
?>
