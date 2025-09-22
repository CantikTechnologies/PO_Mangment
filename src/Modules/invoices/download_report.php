<?php
// Use universal includes
include '../../shared/includes.php';

// Check authentication
if (!isset($_SESSION['username'])) {
  header('Location: ' . getLoginUrl());
  exit();
}
requirePermission('view_invoices');

// Search and filter parameters (same as list.php)
$search = isset($_GET['q']) ? trim($_GET['q']) : '';
$project_filter = isset($_GET['project']) ? trim($_GET['project']) : '';
$vendor_filter = isset($_GET['vendor']) ? trim($_GET['vendor']) : '';
$date_from = isset($_GET['date_from']) ? $_GET['date_from'] : '';
$date_to = isset($_GET['date_to']) ? $_GET['date_to'] : '';

// Build WHERE clause (same as list.php)
$where_conditions = [];
$params = [];
$param_types = '';

if (!empty($search)) {
    $where_conditions[] = "(cantik_invoice_no LIKE ? OR project_details LIKE ? OR vendor_name LIKE ? OR customer_po LIKE ?)";
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

if (!empty($date_from)) {
    $where_conditions[] = "DATE(FROM_UNIXTIME((cantik_invoice_date - 25569) * 86400)) >= ?";
    $params[] = $date_from;
    $param_types .= 's';
}

if (!empty($date_to)) {
    $where_conditions[] = "DATE(FROM_UNIXTIME((cantik_invoice_date - 25569) * 86400)) <= ?";
    $params[] = $date_to;
    $param_types .= 's';
}

$where_clause = !empty($where_conditions) ? 'WHERE ' . implode(' AND ', $where_conditions) : '';

// Get all billing details for export
$sql = "SELECT * FROM billing_details $where_clause ORDER BY created_at DESC";
$stmt = $conn->prepare($sql);
if (!empty($params)) {
    $stmt->bind_param($param_types, ...$params);
}
$stmt->execute();
$results = $stmt->get_result();

// Set headers for CSV download
header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="invoices_report_' . date('Y-m-d') . '.csv"');
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
    'Remaining Balance in PO',
    'Cantik Invoice No',
    'Cantik Invoice Date',
    'Cantik Inv Value Taxable',
    'TDS',
    'Receivable',
    'Against Vendor Inv Number',
    'Payment Receipt Date',
    'Payment Advise No',
    'Vendor Name',
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
        $row['remaining_balance_in_po'],
        $row['cantik_invoice_no'],
        formatDateForCSV($row['cantik_invoice_date']),
        $row['cantik_inv_value_taxable'],
        $row['tds'],
        $row['receivable'],
        $row['against_vendor_inv_number'],
        formatDateForCSV($row['payment_receipt_date']),
        $row['payment_advise_no'],
        $row['vendor_name'],
        $row['created_at']
    ];
    fputcsv($output, $csv_row);
}

fclose($output);
$stmt->close();
exit();
?>
