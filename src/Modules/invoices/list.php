<?php
// Use universal includes
include '../../shared/includes.php';

// Check authentication
if (!isset($_SESSION['username'])) {
  header('Location: ' . getLoginUrl());
  exit();
}
requirePermission('view_invoices');

// Success message handling
$success_message = '';
if (isset($_GET['success'])) {
    if ($_GET['success'] === 'created') {
        $success_message = 'Invoice created successfully!';
    } elseif ($_GET['success'] === 'updated') {
        $success_message = 'Invoice updated successfully!';
    }
}

// Search and filter parameters
$search = isset($_GET['q']) ? trim($_GET['q']) : '';
$project_filter = isset($_GET['project']) ? trim($_GET['project']) : '';
$vendor_filter = isset($_GET['vendor']) ? trim($_GET['vendor']) : '';
$date_from = isset($_GET['date_from']) ? $_GET['date_from'] : '';
$date_to = isset($_GET['date_to']) ? $_GET['date_to'] : '';
// Pagination params
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$per_page_raw = isset($_GET['per_page']) ? $_GET['per_page'] : 'all';
$per_page = strtolower($per_page_raw) === 'all' ? 'all' : max(1, (int)$per_page_raw);

// Build WHERE clause
$where_conditions = [];
$params = [];
$param_types = '';

if (!empty($search)) {
    $where_conditions[] = "(cantik_invoice_no LIKE ? OR project_details LIKE ? OR vendor_name LIKE ? OR customer_po LIKE ?)";
    $search_param = "%$search%";
    $params[] = $search_param;
    $params[] = $search_param;
    $params[] = $search_param;
    $params[] = $search_param;
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

// Total count for pagination
$count_sql = "SELECT COUNT(*) AS total FROM billing_details $where_clause";
$count_stmt = $conn->prepare($count_sql);
if (!empty($params)) {
    $count_stmt->bind_param($param_types, ...$params);
}
$count_stmt->execute();
$count_res = $count_stmt->get_result();
$total_rows = (int)($count_res->fetch_assoc()['total'] ?? 0);
$count_stmt->close();

// Get billing details (invoices) with optional pagination
if ($per_page === 'all') {
    $sql = "SELECT * FROM billing_details $where_clause ORDER BY created_at DESC";
    $stmt = $conn->prepare($sql);
    if (!empty($params)) {
        $stmt->bind_param($param_types, ...$params);
    }
} else {
    $offset = ($page - 1) * $per_page;
    $sql = "SELECT * FROM billing_details $where_clause ORDER BY created_at DESC LIMIT ?, ?";
    $stmt = $conn->prepare($sql);
    // Bind dynamic params + limit/offset
    if (!empty($params)) {
        $bind_types = $param_types . 'ii';
        $stmt->bind_param($bind_types, ...array_merge($params, [$offset, $per_page]));
    } else {
        $stmt->bind_param('ii', $offset, $per_page);
    }
}
$stmt->execute();
$invoice_results = $stmt->get_result();

// Get filter options
$projects_query = "SELECT DISTINCT project_details FROM billing_details WHERE project_details IS NOT NULL AND project_details <> '' ORDER BY project_details";
$projects_result = $conn->query($projects_query);

$vendors_query = "SELECT DISTINCT vendor_name FROM billing_details WHERE vendor_name IS NOT NULL AND vendor_name <> '' ORDER BY vendor_name";
$vendors_result = $conn->query($vendors_query);

// Get summary totals for current filtered results
$summary_sql = "SELECT 
    COUNT(*) as total_invoices,
    SUM(cantik_inv_value_taxable) as total_cantik_inv_value_taxable,
    SUM(tds) as total_tds,
    SUM(receivable) as total_receivable
    FROM billing_details $where_clause";
$summary_stmt = $conn->prepare($summary_sql);
if (!empty($params)) {
    $summary_stmt->bind_param($param_types, ...$params);
}
$summary_stmt->execute();
$summary_result = $summary_stmt->get_result();
$summary_data = $summary_result->fetch_assoc();
$summary_stmt->close();

function formatDate($excel_date) {
    if (empty($excel_date) || !is_numeric($excel_date)) return '-';
    $unix_date = ((int)$excel_date - 25569) * 86400;
    // Use GMT to avoid timezone-related off-by-one day issues
    $formatted = gmdate('d M Y', $unix_date);
    return strtolower($formatted); // 16 jan 2025
}

// Formatting functions are now included from shared/formatting.php
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Invoices - Cantik</title>
    <link crossorigin="" href="https://fonts.gstatic.com/" rel="preconnect"/>
    <link as="style" href="https://fonts.googleapis.com/css2?display=swap&amp;family=Noto+Sans%3Awght%40400%3B500%3B700%3B900&amp;family=Public+Sans%3Awght%40400%3B500%3B600%3B700%3B800" onload="this.rel='stylesheet'" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet"/>
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
</head>
<body class="bg-gradient-to-br from-rose-100 via-sky-100 to-indigo-100 text-gray-900" style='font-family: "Public Sans", "Noto Sans", sans-serif;'>
    <div class="relative flex size-full min-h-screen flex-col overflow-x-hidden">
        <?php include getSharedIncludePath('nav.php'); ?>
        
        <main class="flex-1 px-4 sm:px-6 lg:px-8 py-8">
            <div class="max-w-7xl mx-auto">
                <!-- Success Message -->
                <?php if (!empty($success_message)): ?>
                    <div class="mb-6 p-4 bg-green-50 border border-green-200 rounded-lg">
                        <p class="text-green-800"><?= htmlspecialchars($success_message) ?></p>
                    </div>
                <?php endif; ?>

                <!-- Header (Styled like index hero) -->
                <div class="mb-6">
                    <div class="relative overflow-hidden rounded-2xl bg-white border border-gray-300 shadow-sm">
                        <div class="px-6 sm:px-8 py-6">
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 items-center">
                                <div class="md:col-span-2 min-w-0">
                                    <div class="inline-flex items-center gap-2 text-[11px] font-semibold uppercase tracking-wider text-gray-600">
                                        <span class="material-symbols-outlined text-sm">receipt</span>
                                        Invoices
                                    </div>
                                    <h1 class="mt-1 text-2xl sm:text-3xl font-bold tracking-tight text-gray-900 truncate">
                                        Manage and track all billing invoices
                                    </h1>
                                    <p class="mt-1 text-sm text-gray-600">Search, filter, analyze and export invoices.</p>
                                </div>
                                <div class="justify-self-start md:justify-self-end w-full md:w-auto">
                                    <div class="flex items-center gap-2">
                                        <button onclick="downloadReport()" class="inline-flex items-center justify-center w-10 h-10 rounded-lg border-2 border-indigo-500 text-indigo-700 bg-indigo-50/40 hover:bg-indigo-100 hover:border-indigo-600 transition-colors shadow-sm" title="Download Report">
                                            <span class="material-symbols-outlined text-base">download</span>
                                        </button>
                                        <button onclick="openSummaryDialog()" class="inline-flex items-center justify-center w-10 h-10 rounded-lg border-2 border-green-500 text-green-700 bg-green-50/40 hover:bg-green-100 hover:border-green-600 transition-colors shadow-sm" title="Summary">
                                            <span class="material-symbols-outlined text-base">analytics</span>
                                        </button>
                                        <?php if (hasPermission('add_invoices')): ?>
                                        <button onclick="openInvoiceBulkUpload()" class="inline-flex items-center justify-center w-10 h-10 rounded-lg border-2 border-blue-500 text-blue-700 bg-blue-50/40 hover:bg-blue-100 hover:border-blue-600 transition-colors shadow-sm" title="Bulk Upload">
                                            <span class="material-symbols-outlined text-base">upload</span>
                                        </button>
                                        <a href="add.php" class="inline-flex items-center justify-center w-10 h-10 rounded-lg border-2 border-red-500 text-red-700 bg-red-50/40 hover:bg-red-100 hover:border-red-600 transition-colors shadow-sm" title="New Invoice">
                                            <span class="material-symbols-outlined text-base">add</span>
                                        </a>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Filters -->
                <div class="bg-white rounded-2xl shadow-md border border-gray-300 border-l-4 border-l-blue-600 mb-6">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h2 class="text-lg font-semibold text-gray-900 inline-flex items-center gap-2">
                            <span class="material-symbols-outlined text-blue-600">tune</span>
                            Filters
                        </h2>
                    </div>
                    <form method="GET" class="p-6">
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
                            <div class="relative">
                                <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-gray-400">search</span>
                                <input class="w-full rounded-lg border-gray-200 bg-gray-50 pl-10 pr-4 py-2 text-sm focus:bg-white focus:border-red-500 focus:ring-red-500" 
                                       placeholder="Search invoice, project, vendor, or PO" type="search" name="q" 
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
                                <input class="w-full rounded-lg border-gray-200 bg-gray-50 px-4 py-2 text-sm text-gray-500 placeholder-gray-400 focus:border-red-500 focus:ring-red-500" 
                                       type="text" name="date_from" placeholder="From Date" 
                                       onfocus="this.type='date'" onblur="if(!this.value) this.type='text'" 
                                       value="<?= htmlspecialchars($date_from) ?>"/>
          </div>
                            
          <div>
                                <input class="w-full rounded-lg border-gray-200 bg-gray-50 px-4 py-2 text-sm text-gray-500 placeholder-gray-400 focus:border-red-500 focus:ring-red-500" 
                                       type="text" name="date_to" placeholder="To Date" 
                                       onfocus="this.type='date'" onblur="if(!this.value) this.type='text'" 
                                       value="<?= htmlspecialchars($date_to) ?>"/>
          </div>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4 mt-4">
          <div>
            <select name="per_page" class="w-full appearance-none rounded-lg border-gray-200 bg-gray-50 px-4 py-2 text-sm text-gray-500 focus:border-red-500 focus:ring-red-500">
              <?php $pps = ['50','100','250','all']; foreach ($pps as $pp): ?>
                <option value="<?= $pp ?>" <?= (string)$per_page_raw === (string)$pp ? 'selected' : '' ?>>Show <?= strtoupper($pp) === 'ALL' ? 'All' : $pp ?></option>
              <?php endforeach; ?>
            </select>
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
                <div class="bg-white rounded-2xl shadow-md border border-gray-300 border-l-4 border-l-indigo-600">
                    <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
                        <h2 class="text-lg font-semibold text-gray-900 inline-flex items-center gap-2">
                            <span class="material-symbols-outlined text-indigo-600">receipt</span>
                            Billing Invoices
                        </h2>
                        <div class="text-xs text-gray-500">Showing <?= number_format($invoice_results->num_rows) ?> results</div>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead class="bg-gray-100/70 sticky top-0 z-10">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Invoice Details</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Project</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Financial</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Payment</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
              </tr>
            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <?php $rowNum = 1; while ($invoice = $invoice_results->fetch_assoc()): ?>
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700"><?= $rowNum++ ?></td>
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

                <!-- Pagination footer -->
                <div class="flex items-center justify-between mt-4 text-sm text-gray-600">
                  <div>
                    <?php
                      $from = ($per_page === 'all') ? 1 : (($page - 1) * (int)$per_page + 1);
                      $to = ($per_page === 'all') ? $total_rows : min($page * (int)$per_page, $total_rows);
                    ?>
                    Showing <?= $total_rows ? $from : 0 ?>–<?= $to ?> of <?= $total_rows ?>
                  </div>
                  <?php if ($per_page !== 'all' && $total_rows > $per_page): ?>
                  <div class="flex gap-2">
                    <?php $baseQuery = $_GET; unset($baseQuery['page']); $qs = http_build_query($baseQuery); ?>
                    <?php if ($page > 1): ?>
                      <a class="px-3 py-1 border rounded" href="?<?= $qs ?>&page=<?= $page-1 ?>">Prev</a>
                    <?php endif; ?>
                    <?php $maxPage = (int)ceil($total_rows / (int)$per_page); if ($page < $maxPage): ?>
                      <a class="px-3 py-1 border rounded" href="?<?= $qs ?>&page=<?= $page+1 ?>">Next</a>
                    <?php endif; ?>
                  </div>
                  <?php endif; ?>
                </div>
      </div>
    </main>
  </div>
<!-- Invoice Bulk Upload Modal and Script -->
<div id="invoiceBulkModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
  <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-3/4 lg:w-1/2 shadow-lg rounded-md bg-white">
    <div class="flex items-center justify-between mb-4">
      <h3 class="text-lg font-medium text-gray-900">Bulk Upload Invoices</h3>
      <button onclick="closeInvoiceBulkUpload()" class="text-gray-400 hover:text-gray-600">
        <span class="material-symbols-outlined">close</span>
      </button>
    </div>
    <form id="invoiceBulkForm" enctype="multipart/form-data">
      <div class="bg-blue-50 border border-blue-200 rounded-md p-4 mb-4">
        
        <h4 class="text-sm font-medium text-blue-800 mb-2">Upload Format Requirements</h4>
        <div class="text-sm text-blue-700">
          <p class="mb-1">Required columns (case-sensitive). CSV (comma) or TSV (tab) files are supported:</p>
          <ul class="list-disc list-inside ml-4 space-y-1">
            <li><strong>project_details</strong></li>
            <li><strong>cost_center</strong></li>
            <li><strong>customer_po</strong></li>
            <li><strong>cantik_invoice_no</strong></li>
            <li><strong>cantik_invoice_date</strong> - Excel serial or a valid date (e.g., 16/Jan/2025, 31-03-2025)</li>
            <li><strong>cantik_inv_value_taxable</strong> - numeric</li>
          </ul>
          <p class="mt-2">Optional columns:</p>
          <ul class="list-disc list-inside ml-4 space-y-1">
            <li><strong>against_vendor_inv_number</strong></li>
            <li><strong>payment_receipt_date</strong> - Excel serial or valid date</li>
            <li><strong>payment_advise_no</strong></li>
            <li><strong>vendor_name</strong></li>
          </ul>
        </div>
      </div>
      <div class="bg-green-50 border border-green-200 rounded-md p-4 mb-4">
        <h4 class="text-sm font-medium text-green-800 mb-2">Download Template</h4>
        <a href="sample_template.csv" download="invoice_template.csv" class="inline-flex items-center gap-2 px-3 py-2 bg-green-600 text-white text-sm rounded-md hover:bg-green-700">
          <span class="material-symbols-outlined text-sm">download</span>
          Download Sample CSV
        </a>
      </div>
      <div class="mb-4">
        <label class="block text-sm font-medium text-gray-700 mb-2">Select CSV/TSV File</label>
        <input type="file" id="invoiceFile" name="csvFile" accept=".csv,.tsv,.txt" required class="w-full px-3 py-2 border border-gray-300 rounded-md">
      </div>
      <div class="mb-4 flex items-center gap-3">
        <label class="inline-flex items-center gap-2 text-sm text-gray-700">
          <input type="checkbox" id="invoiceDryRun" class="rounded border-gray-300"> Dry run (validate only)
        </label>
        <button type="button" id="invoiceDownloadErrors" onclick="downloadInvoiceErrors()" class="hidden px-3 py-2 bg-yellow-600 text-white rounded-md text-sm hover:bg-yellow-700">Download error CSV</button>
      </div>
      <div id="invoiceErrors" class="hidden bg-red-50 border border-red-200 rounded-md p-4 mb-4 text-sm text-red-700"></div>
      <div class="flex justify-end gap-3">
        <button type="button" onclick="closeInvoiceBulkUpload()" class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md">Cancel</button>
        <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md">Upload</button>
      </div>
    </form>
  </div>
</div>
<script>
  let lastInvoiceErrors = null;
  function openInvoiceBulkUpload(){ document.getElementById('invoiceBulkModal').classList.remove('hidden'); }
  function closeInvoiceBulkUpload(){ document.getElementById('invoiceBulkModal').classList.add('hidden'); document.getElementById('invoiceErrors').classList.add('hidden'); }
  document.getElementById('invoiceBulkForm').addEventListener('submit', async (e)=>{
    e.preventDefault(); const f=document.getElementById('invoiceFile').files[0]; if(!f){return;}
    const fd=new FormData(); fd.append('csvFile', f);
    if (document.getElementById('invoiceDryRun').checked) { fd.append('dry_run','1'); }
    const r= await fetch('bulk_upload.php',{method:'POST',body:fd}); const t= await r.text(); let data;
    try{ data=JSON.parse(t);}catch(err){ showInvErr('Server returned invalid JSON: '+t.substring(0,300)); return; }
    lastInvoiceErrors = data.errors || [];
    document.getElementById('invoiceDownloadErrors').classList.toggle('hidden', lastInvoiceErrors.length === 0);
    if(!data.success){ showInvErr(data.errors.map(e=>`Row ${e.row}: ${e.message}`).join('<br>')); } else {
      if (document.getElementById('invoiceDryRun').checked) {
        const ok = data.inserted || 0;
        showInvErr(`Dry run completed. ${ok} rows would be inserted. ${lastInvoiceErrors.length} rows have issues.`);
        document.getElementById('invoiceErrors').classList.remove('bg-red-50','border-red-200','text-red-700');
        document.getElementById('invoiceErrors').classList.add('bg-blue-50','border-blue-200','text-blue-700');
      } else {
        location.reload();
      }
    }
  });
  function showInvErr(msg){ const el=document.getElementById('invoiceErrors'); el.innerHTML=msg; el.classList.remove('hidden'); }
  function downloadInvoiceErrors(){
    if (!lastInvoiceErrors || lastInvoiceErrors.length===0) return;
    const header = ['row','message'];
    const lines = [header.join(',')].concat(lastInvoiceErrors.map(e=>`${e.row},"${(e.message||'').replace(/"/g,'""')}"`));
    const blob = new Blob([lines.join('\n')], {type:'text/csv;charset=utf-8;'});
    const url = URL.createObjectURL(blob);
    const a = document.createElement('a'); a.href=url; a.download='invoice_errors.csv'; document.body.appendChild(a); a.click(); a.remove(); URL.revokeObjectURL(url);
  }

  // Date parsing functionality for search filters
  (function() {
    const toIso = (str) => {
      if (!str) return null; 
      const s = String(str).trim();
      
      // Handle yyyy-mm-dd format (already ISO)
      let m = /^(\d{4})-(\d{1,2})-(\d{1,2})$/i.exec(s);
      if (m) {
        const y = m[1];
        const mo = m[2].padStart(2,'0');
        const d = m[3].padStart(2,'0');
        if (+mo >= 1 && +mo <= 12 && +d >= 1 && +d <= 31) {
          return `${y}-${mo}-${d}`;
        }
        return null;
      }
      
      // Handle dd/mm/yyyy or dd-mm-yyyy formats
      m = /^(\d{1,2})[-\/](\d{1,2})[-\/]?(\d{4})$/i.exec(s);
      if (m) {
        const d = m[1].padStart(2,'0'); 
        const mo = m[2].padStart(2,'0'); 
        const y = m[3]; 
        if (+mo >= 1 && +mo <= 12 && +d >= 1 && +d <= 31) {
          return `${y}-${mo}-${d}`;
        }
        return null;
      }
      
      // Handle dd-mmm-yyyy or dd mmm yyyy formats
      m = /^(\d{1,2})[-\s]([A-Za-z]{3,})[-\s](\d{4})$/i.exec(s);
      if (m) {
        const d = m[1].padStart(2,'0'); 
        const mon = m[2].slice(0,3).toLowerCase(); 
        const y = m[3]; 
        const map = {
          jan:'01', feb:'02', mar:'03', apr:'04', may:'05', jun:'06',
          jul:'07', aug:'08', sep:'09', oct:'10', nov:'11', dec:'12'
        }; 
        const mo = map[mon]; 
        if (mo && +d >= 1 && +d <= 31) {
          return `${y}-${mo}-${d}`;
        }
        return null;
      }
      
      // Handle dd-mmm-yy format (2-digit year)
      m = /^(\d{1,2})[-\s]([A-Za-z]{3,})[-\s](\d{2})$/i.exec(s);
      if (m) {
        const d = m[1].padStart(2,'0'); 
        const mon = m[2].slice(0,3).toLowerCase(); 
        let y = m[3]; 
        // Convert 2-digit year to 4-digit (assuming 20xx for years 00-99)
        if (+y >= 0 && +y <= 99) {
          y = '20' + y.padStart(2,'0');
        }
        const map = {
          jan:'01', feb:'02', mar:'03', apr:'04', may:'05', jun:'06',
          jul:'07', aug:'08', sep:'09', oct:'10', nov:'11', dec:'12'
        }; 
        const mo = map[mon]; 
        if (mo && +d >= 1 && +d <= 31) {
          return `${y}-${mo}-${d}`;
        }
        return null;
      }
      
      // Handle mm/dd/yyyy format (US style)
      m = /^(\d{1,2})\/(\d{1,2})\/(\d{4})$/i.exec(s);
      if (m) {
        const mo = m[1].padStart(2,'0'); 
        const d = m[2].padStart(2,'0'); 
        const y = m[3]; 
        if (+mo >= 1 && +mo <= 12 && +d >= 1 && +d <= 31) {
          return `${y}-${mo}-${d}`;
        }
        return null;
      }
      
      return null;
    };
    
    const wire = (input) => {
      const convert = () => {
        const v = input.value;
        const iso = toIso(v);
        if (iso) {
          input.value = iso;
          input.type = 'date';
        }
      };
      
      input.addEventListener('blur', convert);
      input.addEventListener('paste', (e) => {
        // Get the pasted text immediately
        const pastedText = (e.clipboardData || window.clipboardData).getData('text');
        const iso = toIso(pastedText);
        if (iso) {
          e.preventDefault();
          input.value = iso;
          input.type = 'date';
        } else {
          // If not a valid date format, let the paste happen normally and check after
          setTimeout(() => {
            const text = input.value;
            const iso = toIso(text);
            if (iso) {
              input.value = iso;
              input.type = 'date';
            }
          }, 10);
        }
      });
      
      // Also handle input event for better paste support
      input.addEventListener('input', (e) => {
        const v = e.target.value;
        if (v.length >= 8) { // Minimum length for a date
          const iso = toIso(v);
          if (iso) {
            input.value = iso;
            input.type = 'date';
          }
        }
      });
    };
    
    // Apply to date filter inputs
    document.querySelectorAll('input[name="date_from"], input[name="date_to"]').forEach(wire);
  })();

  // Summary dialog functionality
  function openSummaryDialog() {
    document.getElementById('summaryDialog').classList.remove('hidden');
  }
  
  function closeSummaryDialog() {
    document.getElementById('summaryDialog').classList.add('hidden');
  }

  // Download report functionality
  function downloadReport() {
    // Get current filter parameters
    const urlParams = new URLSearchParams(window.location.search);
    const queryString = urlParams.toString();
    
    // Create download URL with current filters
    const downloadUrl = 'download_report.php?' + queryString;
    
    // Create a temporary link and trigger download
    const link = document.createElement('a');
    link.href = downloadUrl;
    link.download = 'invoices_report_' + new Date().toISOString().split('T')[0] + '.csv';
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
  }
</script>

<!-- Summary Dialog -->
<div id="summaryDialog" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
  <div class="relative top-10 mx-auto p-0 border-0 w-full max-w-6xl shadow-2xl rounded-lg bg-white">
    <div class="bg-gradient-to-r from-red-600 to-red-700 text-white p-6 rounded-t-lg">
      <div class="flex items-center justify-between">
        <div class="flex items-center space-x-3">
          <span class="material-symbols-outlined text-2xl">analytics</span>
          <h3 class="text-xl font-semibold">Invoice Summary</h3>
        </div>
        <button onclick="closeSummaryDialog()" class="text-white hover:text-gray-200 transition-colors">
          <span class="material-symbols-outlined text-2xl">close</span>
        </button>
      </div>
    </div>
    
    <div class="p-6">
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <!-- Total Invoices -->
        <div class="bg-white p-6 rounded-lg border border-gray-200 shadow-sm">
          <div class="flex items-center justify-between">
            <div class="flex-1 min-w-0">
              <p class="text-sm font-medium text-gray-600 uppercase tracking-wide">Total Invoices</p>
              <p class="text-2xl font-bold text-gray-900 mt-2 truncate"><?= number_format($summary_data['total_invoices'] ?? 0) ?></p>
            </div>
            <div class="bg-gray-100 p-3 rounded-full ml-3">
              <span class="material-symbols-outlined text-gray-600 text-xl">receipt</span>
            </div>
          </div>
        </div>
        
        <!-- Total Taxable Value -->
        <div class="bg-white p-6 rounded-lg border border-gray-200 shadow-sm">
          <div class="flex items-center justify-between">
            <div class="flex-1 min-w-0">
              <p class="text-sm font-medium text-gray-600 uppercase tracking-wide">Taxable Value</p>
              <p class="text-2xl font-bold text-gray-900 mt-2 truncate"><?= formatCurrency($summary_data['total_cantik_inv_value_taxable'] ?? 0) ?></p>
            </div>
            <div class="bg-gray-100 p-3 rounded-full ml-3">
              <span class="material-symbols-outlined text-gray-600 text-xl">account_balance_wallet</span>
            </div>
          </div>
        </div>
        
        <!-- Total TDS -->
        <div class="bg-white p-6 rounded-lg border border-gray-200 shadow-sm">
          <div class="flex items-center justify-between">
            <div class="flex-1 min-w-0">
              <p class="text-sm font-medium text-gray-600 uppercase tracking-wide">Total TDS</p>
              <p class="text-2xl font-bold text-gray-900 mt-2 truncate"><?= formatCurrency($summary_data['total_tds'] ?? 0) ?></p>
            </div>
            <div class="bg-gray-100 p-3 rounded-full ml-3">
              <span class="material-symbols-outlined text-gray-600 text-xl">savings</span>
            </div>
          </div>
        </div>
        
        <!-- Total Receivable -->
        <div class="bg-white p-6 rounded-lg border border-gray-200 shadow-sm">
          <div class="flex items-center justify-between">
            <div class="flex-1 min-w-0">
              <p class="text-sm font-medium text-gray-600 uppercase tracking-wide">Total Receivable</p>
              <p class="text-2xl font-bold text-gray-900 mt-2 truncate"><?= formatCurrency($summary_data['total_receivable'] ?? 0) ?></p>
            </div>
            <div class="bg-gray-100 p-3 rounded-full ml-3">
              <span class="material-symbols-outlined text-gray-600 text-xl">payments</span>
            </div>
          </div>
        </div>
      </div>
      
      <!-- Additional Info -->
      <div class="mt-8 bg-gray-50 p-6 rounded-xl">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
          <div class="text-center">
            <p class="text-sm text-gray-600">Average Invoice Value</p>
            <p class="text-xl font-semibold text-gray-900">
              <?= formatCurrency(($summary_data['total_invoices'] > 0) ? ($summary_data['total_cantik_inv_value_taxable'] / $summary_data['total_invoices']) : 0) ?>
            </p>
          </div>
          <div class="text-center">
            <p class="text-sm text-gray-600">TDS Rate</p>
            <p class="text-xl font-semibold text-gray-900">
              <?= number_format(($summary_data['total_cantik_inv_value_taxable'] > 0) ? (($summary_data['total_tds'] / $summary_data['total_cantik_inv_value_taxable']) * 100) : 0, 1) ?>%
            </p>
          </div>
          <div class="text-center">
            <p class="text-sm text-gray-600">Net Margin</p>
            <p class="text-xl font-semibold text-gray-900">
              <?= number_format(($summary_data['total_cantik_inv_value_taxable'] > 0) ? (($summary_data['total_receivable'] / $summary_data['total_cantik_inv_value_taxable']) * 100) : 0, 1) ?>%
            </p>
          </div>
        </div>
      </div>
      
      <div class="flex justify-end mt-6">
        <button onclick="closeSummaryDialog()" class="px-6 py-3 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors font-medium">
          <span class="material-symbols-outlined mr-2">close</span>
          Close
        </button>
      </div>
    </div>
  </div>
</div>

<<<<<<< Updated upstream
<script src="../../assets/indian-numbering.js"></script>
=======
<!-- Bulk Upload Modal -->
<div id="invoiceBulkUploadModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
  <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-3/4 lg:w-1/2 shadow-lg rounded-md bg-white">
    <div class="mt-3">
      <div class="flex items-center justify-between mb-4">
        <h3 class="text-lg font-medium text-gray-900">Bulk Upload Invoices</h3>
        <button onclick="closeInvoiceBulkUploadModal()" class="text-gray-400 hover:text-gray-600">
          <span class="material-symbols-outlined">close</span>
        </button>
      </div>
      
      <div class="mb-4">
        <div class="bg-green-50 border border-green-200 rounded-md p-4 mb-4">
          <h4 class="text-sm font-medium text-green-800 mb-2">Need a Template?</h4>
          <p class="text-sm text-green-700 mb-2">Download our sample CSV template to see the correct format:</p>
          <a href="invoice_template.csv" download="invoice_template.csv" 
             class="inline-flex items-center gap-2 px-3 py-2 bg-green-600 text-white text-sm rounded-md hover:bg-green-700 transition-colors">
            <span class="material-symbols-outlined text-sm">download</span>
            Download Template
          </a>
        </div>
      </div>

      <form id="invoiceBulkUploadForm" enctype="multipart/form-data">
        <div class="mb-4">
          <label for="invoiceCsvFile" class="block text-sm font-medium text-gray-700 mb-2">Select CSV/TSV File</label>
          <input type="file" id="invoiceCsvFile" name="csvFile" accept=".csv,.tsv,.txt" required 
                 class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
        </div>
        
        <div id="invoiceUploadProgress" class="hidden mb-4">
          <div class="bg-gray-200 rounded-full h-2.5">
            <div id="invoiceProgressBar" class="bg-blue-600 h-2.5 rounded-full transition-all duration-300" style="width: 0%"></div>
          </div>
          <p id="invoiceProgressText" class="text-sm text-gray-600 mt-1">Processing...</p>
        </div>
        
        <div id="invoiceUploadResults" class="hidden mb-4">
          <div id="invoiceSuccessResults" class="hidden bg-green-50 border border-green-200 rounded-md p-4 mb-2">
            <h4 class="text-sm font-medium text-green-800 mb-2">Success:</h4>
            <p id="invoiceSuccessMessage" class="text-sm text-green-700"></p>
          </div>
          <div id="invoiceWarningResults" class="hidden bg-yellow-50 border border-yellow-200 rounded-md p-4 mb-2">
            <div class="flex items-center justify-between cursor-pointer" onclick="toggleInvoiceWarnings()">
              <h4 class="text-sm font-medium text-yellow-800">Warnings:</h4>
              <span id="invoiceWarningToggle" class="text-yellow-600">▼</span>
            </div>
            <div id="invoiceWarningList" class="text-sm text-yellow-700 mt-2 hidden"></div>
          </div>
          <div id="invoiceErrorResults" class="hidden bg-red-50 border border-red-200 rounded-md p-4">
            <h4 class="text-sm font-medium text-red-800 mb-2">Errors:</h4>
            <div id="invoiceErrorList" class="text-sm text-red-700"></div>
          </div>
        </div>
        
        <div class="flex justify-end space-x-3">
          <button type="button" onclick="validateInvoiceCsv()" 
                  class="px-4 py-2 bg-amber-600 text-white rounded-md hover:bg-amber-700 focus:outline-none focus:ring-2 focus:ring-amber-500">
            Validate File
          </button>
          <button type="button" onclick="closeInvoiceBulkUploadModal()" 
                  class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400 focus:outline-none focus:ring-2 focus:ring-gray-500">
            Cancel
          </button>
          <button type="submit" id="invoiceUploadBtn" 
                  class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
            Upload CSV
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
  function openInvoiceBulkUpload() {
    document.getElementById('invoiceBulkUploadModal').classList.remove('hidden');
    resetInvoiceUploadForm();
  }

  function closeInvoiceBulkUploadModal() {
    document.getElementById('invoiceBulkUploadModal').classList.add('hidden');
    resetInvoiceUploadForm();
  }

  function resetInvoiceUploadForm() {
    document.getElementById('invoiceBulkUploadForm').reset();
    document.getElementById('invoiceUploadProgress').classList.add('hidden');
    document.getElementById('invoiceUploadResults').classList.add('hidden');
    document.getElementById('invoiceSuccessResults').classList.add('hidden');
    document.getElementById('invoiceWarningResults').classList.add('hidden');
    document.getElementById('invoiceErrorResults').classList.add('hidden');
    document.getElementById('invoiceUploadBtn').disabled = false;
  }

  function toggleInvoiceWarnings() {
    const warningList = document.getElementById('invoiceWarningList');
    const warningToggle = document.getElementById('invoiceWarningToggle');
    
    if (warningList.classList.contains('hidden')) {
      warningList.classList.remove('hidden');
      warningToggle.textContent = '▲';
    } else {
      warningList.classList.add('hidden');
      warningToggle.textContent = '▼';
    }
  }

  // Validate CSV/TSV content: basic file structure check only
  function validateInvoiceCsv(){
    const input = document.getElementById('invoiceCsvFile');
    const file = input && input.files && input.files[0];
    if (!file){ alert('Please select a CSV/TSV file first.'); return; }
    const reader = new FileReader();
    reader.onload = function(e){
      const text = (e.target.result || '').toString().replace(/\r\n/g,'\n').replace(/\r/g,'\n');
      const lines = text.split('\n').filter(Boolean);
      if (lines.length < 2){ 
        alert('The file has no data rows.'); 
        return; 
      }
      
      // Basic file structure validation passed
      const preview = document.getElementById('invoiceUploadResults');
      const ok = document.getElementById('invoiceSuccessResults');
      if (preview && ok){
        document.getElementById('invoiceErrorResults').classList.add('hidden');
        document.getElementById('invoiceWarningResults').classList.add('hidden');
        ok.classList.remove('hidden');
        preview.classList.remove('hidden');
        document.getElementById('invoiceSuccessMessage').textContent = 'File structure validation passed. Server will handle detailed validation during upload.';
      } else {
        alert('File structure validation passed. You can proceed with upload.');
      }
    };
    reader.readAsText(file);
  }

  // Handle form submission
  document.getElementById('invoiceBulkUploadForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData();
    const fileInput = document.getElementById('invoiceCsvFile');
    
    if (!fileInput.files[0]) {
      alert('Please select a CSV file');
      return;
    }
    
    formData.append('csvFile', fileInput.files[0]);
    
    // Show progress
    document.getElementById('invoiceUploadProgress').classList.remove('hidden');
    document.getElementById('invoiceUploadBtn').disabled = true;
    
    // Reset results
    document.getElementById('invoiceUploadResults').classList.add('hidden');
    document.getElementById('invoiceSuccessResults').classList.add('hidden');
    document.getElementById('invoiceErrorResults').classList.add('hidden');
    document.getElementById('invoiceWarningResults').classList.add('hidden');
    
    // Simulate progress
    let progress = 0;
    const progressInterval = setInterval(() => {
      progress += 10;
      document.getElementById('invoiceProgressBar').style.width = progress + '%';
      if (progress >= 90) {
        clearInterval(progressInterval);
      }
    }, 100);
    
    fetch('bulk_upload.php', {
      method: 'POST',
      body: formData
    })
    .then(response => {
      if (!response.ok) {
        throw new Error(`HTTP error! status: ${response.status}`);
      }
      return response.text();
    })
    .then(text => {
      try {
        return JSON.parse(text);
      } catch (e) {
        console.error('Invalid JSON response:', text);
        throw new Error('Server returned invalid JSON. Response: ' + text.substring(0, 200));
      }
    })
    .then(data => {
      clearInterval(progressInterval);
      document.getElementById('invoiceProgressBar').style.width = '100%';
      document.getElementById('invoiceProgressText').textContent = 'Complete!';
      
      setTimeout(() => {
        document.getElementById('invoiceUploadProgress').classList.add('hidden');
        document.getElementById('invoiceUploadResults').classList.remove('hidden');
        
        if (data.success) {
          document.getElementById('invoiceSuccessResults').classList.remove('hidden');
          document.getElementById('invoiceSuccessMessage').textContent = 
            `Successfully uploaded ${data.inserted} records. ${data.skipped} records were skipped due to duplicates.`;
        }
        
        // Show warnings if any (even on success)
        if (data.warnings && data.warnings.length > 0) {
          document.getElementById('invoiceWarningResults').classList.remove('hidden');
          const warningList = document.getElementById('invoiceWarningList');
          warningList.innerHTML = '';
          data.warnings.forEach(warning => {
            const warningItem = document.createElement('div');
            warningItem.className = 'mb-1';
            warningItem.textContent = `Row ${warning.row}: ${warning.message}`;
            warningList.appendChild(warningItem);
          });
        }
        
        // Show errors if any
        if (data.errors && data.errors.length > 0) {
          document.getElementById('invoiceErrorResults').classList.remove('hidden');
          const errorList = document.getElementById('invoiceErrorList');
          errorList.innerHTML = '';
          data.errors.forEach(error => {
            const errorItem = document.createElement('div');
            errorItem.className = 'mb-1';
            errorItem.textContent = `Row ${error.row}: ${error.message}`;
            errorList.appendChild(errorItem);
          });
        }
        
        document.getElementById('invoiceUploadBtn').disabled = false;
        
        // Refresh the page after successful upload
        if (data.success && data.inserted > 0) {
          setTimeout(() => {
            window.location.reload();
          }, 2000);
        }
      }, 500);
    })
    .catch(error => {
      clearInterval(progressInterval);
      document.getElementById('invoiceUploadProgress').classList.add('hidden');
      document.getElementById('invoiceUploadResults').classList.remove('hidden');
      document.getElementById('invoiceErrorResults').classList.remove('hidden');
      document.getElementById('invoiceErrorList').innerHTML = '<div class="mb-1">Upload failed: ' + error.message + '</div>';
      document.getElementById('invoiceUploadBtn').disabled = false;
    });
  });

  // Close modal when clicking outside
  document.getElementById('invoiceBulkUploadModal').addEventListener('click', function(e) {
    if (e.target === this) {
      closeInvoiceBulkUploadModal();
    }
  });

  // Summary dialog functionality
  function openSummaryDialog() {
    document.getElementById('summaryDialog').classList.remove('hidden');
  }
  
  function closeSummaryDialog() {
    document.getElementById('summaryDialog').classList.add('hidden');
  }

  // Download report functionality
  function downloadReport() {
    // Get current filter parameters
    const urlParams = new URLSearchParams(window.location.search);
    const queryString = urlParams.toString();
    
    // Create download URL with current filters
    const downloadUrl = 'download_report.php?' + queryString;
    
    // Create a temporary link and trigger download
    const link = document.createElement('a');
    link.href = downloadUrl;
    link.download = 'invoice_report_' + new Date().toISOString().split('T')[0] + '.csv';
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
  }
</script>

>>>>>>> Stashed changes
</body>
</html>
