<?php
// Use universal includes
include '../../shared/includes.php';

// Check authentication
if (!isset($_SESSION['username'])) {
  header('Location: ' . getLoginUrl());
  exit();
}
requirePermission('view_po_details');

// Search and filter parameters (same as list.php)
$search = isset($_GET['q']) ? trim($_GET['q']) : '';
$project_filter = isset($_GET['project']) ? trim($_GET['project']) : '';
$status_filter = isset($_GET['status']) ? trim($_GET['status']) : '';
$vendor_filter = isset($_GET['vendor']) ? trim($_GET['vendor']) : '';

// Build WHERE clause (same as list.php)
$where_conditions = [];
$params = [];
$param_types = '';

if (!empty($search)) {
    $where_conditions[] = "(po_number LIKE ? OR project_description LIKE ? OR vendor_name LIKE ?)";
    $search_param = "%$search%";
    $params = array_merge($params, [$search_param, $search_param, $search_param]);
    $param_types .= 'sss';
}

if (!empty($project_filter)) {
    $where_conditions[] = "project_description = ?";
    $params[] = $project_filter;
    $param_types .= 's';
}

if (!empty($status_filter)) {
    $where_conditions[] = "po_status = ?";
    $params[] = $status_filter;
    $param_types .= 's';
}

if (!empty($vendor_filter)) {
    // Match vendor against stored vendor_name or derived from outsourcing/billing
    $where_conditions[] = "(vendor_name = ? OR (SELECT od.vendor_name FROM outsourcing_detail od WHERE od.customer_po = po_details.po_number ORDER BY od.id DESC LIMIT 1) = ? OR (SELECT bd.vendor_name FROM billing_details bd WHERE bd.customer_po = po_details.po_number ORDER BY bd.id DESC LIMIT 1) = ?)";
    $params = array_merge($params, [$vendor_filter, $vendor_filter, $vendor_filter]);
    $param_types .= 'sss';
}

$where_clause = !empty($where_conditions) ? 'WHERE ' . implode(' AND ', $where_conditions) : '';

// Get all PO details for export
$sql = "SELECT * FROM po_details $where_clause ORDER BY created_at DESC";
$stmt = $conn->prepare($sql);
if (!empty($params)) {
    $stmt->bind_param($param_types, ...$params);
}
$stmt->execute();
$results = $stmt->get_result();

// Set headers for CSV download
header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="po_details_report_' . date('Y-m-d') . '.csv"');
header('Pragma: no-cache');
header('Expires: 0');

// Create file handle
$output = fopen('php://output', 'w');

// Add BOM for UTF-8
fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

// CSV headers
$headers = [
    'ID',
    'Project Description',
    'Cost Center',
    'SOW Number',
    'Start Date',
    'End Date',
    'PO Number',
    'PO Date',
    'PO Value',
    'Billing Frequency',
    'Target GM',
    'Pending Amount',
    'PO Status',
    'Remarks',
    'Vendor Name',
    'Customer Name',
    'Created At',
    'Updated At'
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
        $row['project_description'],
        $row['cost_center'],
        $row['sow_number'],
        formatDateForCSV($row['start_date']),
        formatDateForCSV($row['end_date']),
        $row['po_number'],
        formatDateForCSV($row['po_date']),
        formatCurrencyPlain($row['po_value']),
        $row['billing_frequency'],
        $row['target_gm'],
        formatCurrencyPlain($row['pending_amount']),
        $row['po_status'],
        $row['remarks'],
        $row['vendor_name'],
        $row['customer_name'],
        $row['created_at'],
        $row['updated_at']
    ];
    fputcsv($output, $csv_row);
}

fclose($output);
$stmt->close();
exit();
?>
