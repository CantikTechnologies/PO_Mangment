<?php
session_start();
if (!isset($_SESSION['username'])) {
  header('Location: ../../../login.php');
  exit();
}
include '../../../config/db.php';
include '../../../config/auth.php';
requirePermission('view_invoices');

// Search and filter parameters
$search = isset($_GET['q']) ? trim($_GET['q']) : '';
$project_filter = isset($_GET['project']) ? trim($_GET['project']) : '';
$vendor_filter = isset($_GET['vendor']) ? trim($_GET['vendor']) : '';
$date_from = isset($_GET['date_from']) ? $_GET['date_from'] : '';
$date_to = isset($_GET['date_to']) ? $_GET['date_to'] : '';

// Build WHERE clause
$where_conditions = [];
$params = [];
$param_types = '';

if (!empty($search)) {
    $where_conditions[] = "(cantik_invoice_no LIKE ? OR project_details LIKE ? OR vendor_name LIKE ?)";
    $search_param = "%$search%";
    $params[] = $search_param;
    $params[] = $search_param;
    $params[] = $search_param;
    $param_types .= 'sss';
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

// Get billing details (invoices) with proper field names from database
$sql = "SELECT * FROM billing_details $where_clause ORDER BY created_at DESC";
$stmt = $conn->prepare($sql);
if (!empty($params)) {
    $stmt->bind_param($param_types, ...$params);
}
$stmt->execute();
$invoice_results = $stmt->get_result();

// Get filter options
$projects_query = "SELECT DISTINCT project_details FROM billing_details ORDER BY project_details";
$projects_result = $conn->query($projects_query);

$vendors_query = "SELECT DISTINCT vendor_name FROM billing_details WHERE vendor_name IS NOT NULL ORDER BY vendor_name";
$vendors_result = $conn->query($vendors_query);

function formatDate($excel_date) {
    if (empty($excel_date)) return '-';
    $unix_date = ($excel_date - 25569) * 86400;
    return date('d-m-Y', $unix_date);
}

function formatCurrency($amount) {
    return '₹ ' . number_format($amount, 2);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Invoices - Cantik Homemade</title>
    <link crossorigin="" href="https://fonts.gstatic.com/" rel="preconnect"/>
    <link as="style" href="https://fonts.googleapis.com/css2?display=swap&amp;family=Noto+Sans%3Awght%40400%3B500%3B700%3B900&amp;family=Public+Sans%3Awght%40400%3B500%3B600%3B700%3B800" onload="this.rel='stylesheet'" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet"/>
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
</head>
<body class="bg-gray-50 text-gray-900" style='font-family: "Public Sans", "Noto Sans", sans-serif;'>
    <div class="relative flex size-full min-h-screen flex-col overflow-x-hidden">
        <?php include '../../shared/nav.php'; ?>
        
        <main class="flex-1 px-4 sm:px-6 lg:px-8 py-8">
            <div class="max-w-7xl mx-auto">
                <!-- Header -->
                <div class="flex flex-col md:flex-row items-center justify-between gap-4 mb-6">
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900">Invoices</h1>
                        <p class="text-gray-600 mt-2">Manage and track all billing invoices</p>
                    </div>
                    <?php if (hasPermission('add_invoices')): ?>
                    <a href="add.php" class="flex items-center justify-center gap-2 rounded-full bg-red-600 px-5 py-2.5 text-white text-sm font-semibold shadow-sm hover:bg-red-700 transition-colors">
                        <span class="material-symbols-outlined">add</span>
                        <span class="truncate">New Invoice</span>
                    </a>
                    <?php endif; ?>
                </div>

                <!-- Filters -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 mb-6">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h2 class="text-lg font-semibold text-gray-900">Filters</h2>
                    </div>
                    <form method="GET" class="p-6">
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
                            <div class="relative">
                                <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-gray-400">search</span>
                                <input class="w-full rounded-lg border-gray-200 bg-gray-50 pl-10 pr-4 py-2 text-sm focus:bg-white focus:border-red-500 focus:ring-red-500" 
                                       placeholder="Search invoice, project, or vendor" type="search" name="q" 
                                       value="<?= htmlspecialchars($search) ?>"/>
      </div>

                            <div class="relative">
                                <select name="project" class="w-full appearance-none rounded-lg border-gray-200 bg-gray-50 px-4 py-2 text-sm text-gray-500 focus:border-red-500 focus:ring-red-500">
              <option value="">All Projects</option>
                                    <?php while ($project = $projects_result->fetch_assoc()): ?>
                                    <option value="<?= htmlspecialchars($project['project_details']) ?>" 
                                            <?= $project_filter == $project['project_details'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($project['project_details']) ?>
                                    </option>
                                    <?php endwhile; ?>
                                </select>
                                <span class="material-symbols-outlined absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 pointer-events-none">expand_more</span>
                            </div>
                            
                            <div class="relative">
                                <select name="vendor" class="w-full appearance-none rounded-lg border-gray-200 bg-gray-50 px-4 py-2 text-sm text-gray-500 focus:border-red-500 focus:ring-red-500">
                                    <option value="">All Vendors</option>
                                    <?php while ($vendor = $vendors_result->fetch_assoc()): ?>
                                    <option value="<?= htmlspecialchars($vendor['vendor_name']) ?>" 
                                            <?= $vendor_filter == $vendor['vendor_name'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($vendor['vendor_name']) ?>
                                    </option>
                                    <?php endwhile; ?>
            </select>
                                <span class="material-symbols-outlined absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 pointer-events-none">expand_more</span>
                            </div>
                            
                            <div>
                                <input class="w-full rounded-lg border-gray-200 bg-gray-50 px-4 py-2 text-sm text-gray-500 focus:border-red-500 focus:ring-red-500" 
                                       type="date" name="date_from" placeholder="From Date" 
                                       value="<?= htmlspecialchars($date_from) ?>"/>
          </div>
                            
          <div>
                                <input class="w-full rounded-lg border-gray-200 bg-gray-50 px-4 py-2 text-sm text-gray-500 focus:border-red-500 focus:ring-red-500" 
                                       type="date" name="date_to" placeholder="To Date" 
                                       value="<?= htmlspecialchars($date_to) ?>"/>
          </div>
        </div>
                        <div class="flex gap-4 mt-4">
                            <button type="submit" class="bg-red-600 text-white py-2 px-4 rounded-lg hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500">
                                Apply Filters
                            </button>
                            <a href="list.php" class="bg-gray-300 text-gray-700 py-2 px-4 rounded-lg hover:bg-gray-400 focus:outline-none focus:ring-2 focus:ring-gray-500">
                                Clear Filters
                            </a>
        </div>
      </form>
                </div>

                <!-- Invoices Table -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h2 class="text-lg font-semibold text-gray-900">Billing Invoices</h2>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Invoice Details</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Project</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Financial</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Payment</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
              </tr>
            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <?php while ($invoice = $invoice_results->fetch_assoc()): ?>
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div>
                                            <div class="text-sm font-medium text-gray-900"><?= htmlspecialchars($invoice['cantik_invoice_no']) ?></div>
                                            <div class="text-sm text-gray-500">Date: <?= formatDate($invoice['cantik_invoice_date']) ?></div>
                                            <div class="text-sm text-gray-500">PO: <?= htmlspecialchars($invoice['customer_po']) ?></div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="text-sm text-gray-900"><?= htmlspecialchars($invoice['project_details']) ?></div>
                                        <div class="text-sm text-gray-500"><?= htmlspecialchars($invoice['cost_center']) ?></div>
                                        <div class="text-sm text-gray-500"><?= htmlspecialchars($invoice['vendor_name'] ?: 'No vendor') ?></div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900">Taxable: <?= formatCurrency($invoice['cantik_inv_value_taxable']) ?></div>
                                        <div class="text-sm text-gray-500">TDS: <?= formatCurrency($invoice['tds']) ?></div>
                                        <div class="text-sm font-medium text-green-600">Receivable: <?= formatCurrency($invoice['receivable']) ?></div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">Vendor Inv: <?= htmlspecialchars($invoice['against_vendor_inv_number'] ?: '-') ?></div>
                                        <div class="text-sm text-gray-500">Payment Date: <?= formatDate($invoice['payment_receipt_date']) ?></div>
                                        <div class="text-sm text-gray-500">Advise No: <?= htmlspecialchars($invoice['payment_advise_no'] ?: '-') ?></div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <div class="flex gap-2">
                                            <a href="view.php?id=<?= $invoice['id'] ?>" class="text-blue-600 hover:text-blue-900">View</a>
                                            <?php if (hasPermission('edit_invoices')): ?>
                                            <a href="edit.php?id=<?= $invoice['id'] ?>" class="text-indigo-600 hover:text-indigo-900">Edit</a>
                                            <?php endif; ?>
                                            <?php if (hasPermission('delete_invoices')): ?>
                                            <a href="delete.php?id=<?= $invoice['id'] ?>" class="text-red-600 hover:text-red-900" onclick="return confirm('Are you sure you want to delete this invoice?')">Delete</a>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
            </tbody>
          </table>
        </div>
                </div>

                <?php if ($invoice_results->num_rows == 0): ?>
                <div class="text-center py-12">
                    <span class="material-symbols-outlined text-6xl text-gray-300">receipt</span>
                    <h3 class="mt-2 text-sm font-medium text-gray-900">No invoices found</h3>
                    <p class="mt-1 text-sm text-gray-500">Get started by creating a new invoice.</p>
                    <?php if (hasPermission('add_invoices')): ?>
                    <div class="mt-6">
                        <a href="add.php" class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-red-600 hover:bg-red-700">
                            <span class="material-symbols-outlined mr-2">add</span>
                            New Invoice
                        </a>
                    </div>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
      </div>
    </main>
  </div>
</body>
</html>
