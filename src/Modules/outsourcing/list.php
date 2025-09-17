<?php
// Use universal includes
include '../../shared/includes.php';

// Check authentication
if (!isset($_SESSION['username'])) {
  header('Location: ' . getLoginUrl());
  exit();
}
requirePermission('view_outsourcing');

// Search and filter parameters
              $search = isset($_GET['q']) ? trim($_GET['q']) : '';
              $project_filter = isset($_GET['project']) ? trim($_GET['project']) : '';
$vendor_filter = isset($_GET['vendor']) ? trim($_GET['vendor']) : '';
$status_filter = isset($_GET['status']) ? trim($_GET['status']) : '';
$date_from = isset($_GET['date_from']) ? $_GET['date_from'] : '';
$date_to = isset($_GET['date_to']) ? $_GET['date_to'] : '';

// Build WHERE clause
                $where_conditions = [];
                $params = [];
                $param_types = '';

if (!empty($search)) {
    $where_conditions[] = "(cantik_po_no LIKE ? OR project_details LIKE ? OR vendor_name LIKE ?)";
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

if (!empty($status_filter)) {
    $where_conditions[] = "payment_status_from_ntt = ?";
    $params[] = $status_filter;
    $param_types .= 's';
}

if (!empty($date_from)) {
    $where_conditions[] = "DATE(FROM_UNIXTIME((cantik_po_date - 25569) * 86400)) >= ?";
    $params[] = $date_from;
    $param_types .= 's';
}

if (!empty($date_to)) {
    $where_conditions[] = "DATE(FROM_UNIXTIME((cantik_po_date - 25569) * 86400)) <= ?";
    $params[] = $date_to;
    $param_types .= 's';
}

$where_clause = !empty($where_conditions) ? 'WHERE ' . implode(' AND ', $where_conditions) : '';

// Get outsourcing details with proper field names from database
$sql = "SELECT * FROM outsourcing_detail $where_clause ORDER BY created_at DESC";
$stmt = $conn->prepare($sql);
if (!empty($params)) {
    $stmt->bind_param($param_types, ...$params);
}
$stmt->execute();
$outsourcing_results = $stmt->get_result();

// Get filter options
$projects_query = "SELECT DISTINCT project_details FROM outsourcing_detail ORDER BY project_details";
$projects_result = $conn->query($projects_query);

$vendors_query = "SELECT DISTINCT vendor_name FROM outsourcing_detail ORDER BY vendor_name";
$vendors_result = $conn->query($vendors_query);

$statuses_query = "SELECT DISTINCT payment_status_from_ntt FROM outsourcing_detail WHERE payment_status_from_ntt IS NOT NULL ORDER BY payment_status_from_ntt";
$statuses_result = $conn->query($statuses_query);

function formatDate($excel_date) {
    if (empty($excel_date)) return '-';
    $unix_date = ($excel_date - 25569) * 86400;
    $formatted = date('d M Y', $unix_date);
    return strtolower($formatted); // 16 jan 2025
}

function formatCurrency($amount) {
    return '₹' . number_format((float)$amount, 2);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Outsourcing - Cantik Homemade</title>
    <link crossorigin="" href="https://fonts.gstatic.com/" rel="preconnect"/>
    <link as="style" href="https://fonts.googleapis.com/css2?display=swap&amp;family=Noto+Sans%3Awght%40400%3B500%3B700%3B900&amp;family=Public+Sans%3Awght%40400%3B500%3B600%3B700%3B800" onload="this.rel='stylesheet'" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet"/>
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
</head>
<body class="bg-gray-50 text-gray-900" style='font-family: "Public Sans", "Noto Sans", sans-serif;'>
    <div class="relative flex size-full min-h-screen flex-col overflow-x-hidden">
        <?php include getSharedIncludePath('nav.php'); ?>
        
        <main class="flex-1 px-4 sm:px-6 lg:px-8 py-8">
            <div class="max-w-7xl mx-auto">
                <!-- Header -->
                <div class="flex flex-col md:flex-row items-center justify-between gap-4 mb-6">
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900">Outsourcing</h1>
                        <p class="text-gray-600 mt-2">Manage and track all outsourcing records</p>
                    </div>
                    <?php if (hasPermission('add_outsourcing')): ?>
                    <div class="flex gap-3">
                      <button onclick="openOutsourcingBulkUpload()" class="flex items-center justify-center gap-2 rounded-full bg-blue-600 px-5 py-2.5 text-white text-sm font-semibold shadow-sm hover:bg-blue-700 transition-colors">
                        <span class="material-symbols-outlined">upload</span>
                        <span class="truncate">Bulk Upload</span>
                      </button>
                      <a href="add.php" class="flex items-center justify-center gap-2 rounded-full bg-red-600 px-5 py-2.5 text-white text-sm font-semibold shadow-sm hover:bg-red-700 transition-colors">
                        <span class="material-symbols-outlined">add</span>
                        <span class="truncate">New Outsourcing</span>
                      </a>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- Filters -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 mb-6">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h2 class="text-lg font-semibold text-gray-900">Filters</h2>
                    </div>
                    <form method="GET" class="p-6">
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-6 gap-4">
                            <div class="relative">
                                <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-gray-400">search</span>
                                <input class="w-full rounded-lg border-gray-200 bg-gray-50 pl-10 pr-4 py-2 text-sm focus:bg-white focus:border-red-500 focus:ring-red-500" 
                                       placeholder="Search PO, project, or vendor" type="search" name="q" 
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
                            
                            <div class="relative">
                                <select name="status" class="w-full appearance-none rounded-lg border-gray-200 bg-gray-50 px-4 py-2 text-sm text-gray-500 focus:border-red-500 focus:ring-red-500">
                                    <option value="">All Statuses</option>
                                    <?php while ($status = $statuses_result->fetch_assoc()): ?>
                                    <option value="<?= htmlspecialchars($status['payment_status_from_ntt']) ?>" 
                                            <?= $status_filter == $status['payment_status_from_ntt'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($status['payment_status_from_ntt']) ?>
                                    </option>
                                    <?php endwhile; ?>
                                </select>
                                <span class="material-symbols-outlined absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 pointer-events-none">expand_more</span>
                            </div>
                            
                            <div>
                                <input class="w-full rounded-lg border-gray-200 bg-gray-50 px-4 py-2 text-sm text-gray-500 placeholder-gray-400 focus:border-red-500 focus:ring-red-500" 
                                       type="text" name="date_from" placeholder="From date" 
                                       onfocus="this.type='date'" onblur="if(!this.value) this.type='text'" 
                                       value="<?= htmlspecialchars($date_from) ?>"/>
                            </div>
                            
                            <div>
                                <input class="w-full rounded-lg border-gray-200 bg-gray-50 px-4 py-2 text-sm text-gray-500 placeholder-gray-400 focus:border-red-500 focus:ring-red-500" 
                                       type="text" name="date_to" placeholder="To date" 
                                       onfocus="this.type='date'" onblur="if(!this.value) this.type='text'" 
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

                <!-- Outsourcing Table -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h2 class="text-lg font-semibold text-gray-900">Outsourcing Records</h2>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">S.No</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">PO Details</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Project</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Invoice</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Financial</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Payment</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <?php $rowNum = 1; while ($record = $outsourcing_results->fetch_assoc()): ?>
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900"><?= $rowNum++ ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div>
                                            <div class="text-sm font-medium text-gray-900"><?= htmlspecialchars($record['cantik_po_no']) ?></div>
                                            <div class="text-sm text-gray-500">Date: <?= formatDate($record['cantik_po_date']) ?></div>
                                            <div class="text-sm text-gray-500">Customer PO: <?= htmlspecialchars($record['customer_po']) ?></div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="text-sm text-gray-900"><?= htmlspecialchars($record['project_details']) ?></div>
                                        <div class="text-sm text-gray-500"><?= htmlspecialchars($record['cost_center']) ?></div>
                                        <div class="text-sm text-gray-500"><?= htmlspecialchars($record['vendor_name']) ?></div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900"><?= htmlspecialchars($record['vendor_inv_number']) ?></div>
                                        <div class="text-sm text-gray-500">Date: <?= formatDate($record['vendor_inv_date']) ?></div>
                                        <div class="text-sm text-gray-500">Freq: <?= htmlspecialchars($record['vendor_invoice_frequency']) ?></div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900">PO Value: <?= formatCurrency($record['cantik_po_value']) ?></div>
                                        <div class="text-sm text-gray-500">Inv Value: <?= formatCurrency($record['vendor_inv_value']) ?></div>
                                        <div class="text-sm text-gray-500">TDS: <?= formatCurrency($record['tds_ded']) ?></div>
                                        <div class="text-sm font-medium text-green-600">Net Payable: <?= formatCurrency($record['net_payble']) ?></div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">Status: <?= htmlspecialchars($record['payment_status_from_ntt'] ?: 'Pending') ?></div>
                                        <?php 
                                          $net = is_numeric($record['net_payble'] ?? null) ? (float)$record['net_payble'] : 0.0;
                                          $paid = is_numeric($record['payment_value'] ?? null) ? (float)$record['payment_value'] : 0.0;
                                          $status = strtolower(trim((string)($record['payment_status_from_ntt'] ?? '')));
                                          if ($paid <= 0.0) {
                                              $pending = ($status === 'paid') ? 0.0 : $net;
                                          } else {
                                              $pending = max($net - $paid, 0.0);
                                              if ($status === 'paid') { $pending = 0.0; }
                                          }
                                          $pendingClass = $pending > 0.0 ? 'text-red-600' : 'text-green-600';
                                        ?>
                                        <div class="text-sm text-gray-500">Payment: <?= formatCurrency($paid) ?></div>
                                        <div class="text-sm text-gray-500">Date: <?= formatDate($record['payment_date']) ?></div>
                                        <div class="text-sm font-medium <?= $pendingClass ?>">Pending: <?= formatCurrency($pending) ?></div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <div class="flex gap-2">
                                            <a href="view.php?id=<?= $record['id'] ?>" class="text-blue-600 hover:text-blue-900">View</a>
                                            <?php if (hasPermission('edit_outsourcing')): ?>
                                            <a href="edit.php?id=<?= $record['id'] ?>" class="text-indigo-600 hover:text-indigo-900">Edit</a>
                                            <?php endif; ?>
                                            <?php if (hasPermission('delete_outsourcing')): ?>
                                            <a href="delete.php?id=<?= $record['id'] ?>" class="text-red-600 hover:text-red-900" onclick="return confirm('Are you sure you want to delete this record?')">Delete</a>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
            </tbody>
          </table>
        </div>
                </div>

                <?php if ($outsourcing_results->num_rows == 0): ?>
                <div class="text-center py-12">
                    <span class="material-symbols-outlined text-6xl text-gray-300">business_center</span>
                    <h3 class="mt-2 text-sm font-medium text-gray-900">No outsourcing records found</h3>
                    <p class="mt-1 text-sm text-gray-500">Get started by creating a new outsourcing record.</p>
                    <?php if (hasPermission('add_outsourcing')): ?>
                    <div class="mt-6">
                        <a href="add.php" class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-red-600 hover:bg-red-700">
                            <span class="material-symbols-outlined mr-2">add</span>
                            New Outsourcing Record
                        </a>
                    </div>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
      </div>
    </main>
  </div>
<!-- Outsourcing Bulk Upload Modal and Script -->
<div id="outsBulkModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
  <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-3/4 lg:w-1/2 shadow-lg rounded-md bg-white">
    <div class="flex items-center justify-between mb-4">
      <h3 class="text-lg font-medium text-gray-900">Bulk Upload Outsourcing</h3>
      <button onclick="closeOutsourcingBulkUpload()" class="text-gray-400 hover:text-gray-600">
        <span class="material-symbols-outlined">close</span>
      </button>
    </div>
    <form id="outsBulkForm" enctype="multipart/form-data">
      <div class="bg-blue-50 border border-blue-200 rounded-md p-4 mb-4">
        <h4 class="text-sm font-medium text-blue-800 mb-2">Upload Format Requirements</h4>
        <div class="text-sm text-blue-700">
          <p class="mb-1">Required columns (case-sensitive). CSV (comma) or TSV (tab) files are supported:</p>
          <ul class="list-disc list-inside ml-4 space-y-1">
            <li><strong>project_details</strong></li>
            <li><strong>cost_center</strong></li>
            <li><strong>customer_po</strong></li>
            <li><strong>vendor_name</strong></li>
            <li><strong>cantik_po_no</strong></li>
            <li><strong>cantik_po_date</strong> - Excel serial or valid date</li>
            <li><strong>cantik_po_value</strong> - numeric</li>
            <li><strong>vendor_invoice_frequency</strong></li>
            <li><strong>vendor_inv_number</strong></li>
            <li><strong>vendor_inv_date</strong> - Excel serial or valid date</li>
            <li><strong>vendor_inv_value</strong> - numeric</li>
          </ul>
          <p class="mt-2">Optional columns: payment_status_from_ntt, payment_value, payment_date, remarks</p>
        </div>
      </div>
      <div class="bg-green-50 border border-green-200 rounded-md p-4 mb-4">
        <h4 class="text-sm font-medium text-green-800 mb-2">Download Template</h4>
        <a href="sample_template.csv" download="outsourcing_template.csv" class="inline-flex items-center gap-2 px-3 py-2 bg-green-600 text-white text-sm rounded-md hover:bg-green-700">
          <span class="material-symbols-outlined text-sm">download</span>
          Download Sample CSV
        </a>
      </div>
      <div class="mb-4">
        <label class="block text-sm font-medium text-gray-700 mb-2">Select CSV/TSV File</label>
        <input type="file" id="outsFile" name="csvFile" accept=".csv,.tsv,.txt" required class="w-full px-3 py-2 border border-gray-300 rounded-md">
      </div>
      <div class="mb-4 flex items-center gap-3">
        <label class="inline-flex items-center gap-2 text-sm text-gray-700">
          <input type="checkbox" id="outsDryRun" class="rounded border-gray-300"> Dry run (validate only)
        </label>
        <button type="button" id="outsDownloadErrors" onclick="downloadOutsErrors()" class="hidden px-3 py-2 bg-yellow-600 text-white rounded-md text-sm hover:bg-yellow-700">Download error CSV</button>
      </div>
      <div id="outsErrors" class="hidden bg-red-50 border border-red-200 rounded-md p-4 mb-4 text-sm text-red-700"></div>
      <div class="flex justify-end gap-3">
        <button type="button" onclick="closeOutsourcingBulkUpload()" class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md">Cancel</button>
        <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md">Upload</button>
      </div>
    </form>
  </div>
</div>
<script>
  let lastOutsErrors = null;
  function openOutsourcingBulkUpload(){ document.getElementById('outsBulkModal').classList.remove('hidden'); }
  function closeOutsourcingBulkUpload(){ document.getElementById('outsBulkModal').classList.add('hidden'); document.getElementById('outsErrors').classList.add('hidden'); }
  document.getElementById('outsBulkForm').addEventListener('submit', async (e)=>{
    e.preventDefault(); const f=document.getElementById('outsFile').files[0]; if(!f){return;}
    const fd=new FormData(); fd.append('csvFile', f);
    if (document.getElementById('outsDryRun').checked) { fd.append('dry_run','1'); }
    const r= await fetch('bulk_upload.php',{method:'POST',body:fd}); const t= await r.text(); let data;
    try{ data=JSON.parse(t);}catch(err){ showOutsErr('Server returned invalid JSON: '+t.substring(0,300)); return; }
    lastOutsErrors = data.errors || [];
    document.getElementById('outsDownloadErrors').classList.toggle('hidden', lastOutsErrors.length === 0);
    if(!data.success){ showOutsErr(data.errors.map(e=>`Row ${e.row}: ${e.message}`).join('<br>')); } else {
      if (document.getElementById('outsDryRun').checked) {
        const ok = data.inserted || 0;
        showOutsErr(`Dry run completed. ${ok} rows would be inserted. ${lastOutsErrors.length} rows have issues.`);
        const el=document.getElementById('outsErrors');
        el.classList.remove('bg-red-50','border-red-200','text-red-700');
        el.classList.add('bg-blue-50','border-blue-200','text-blue-700');
      } else {
        location.reload();
      }
    }
  });
  function showOutsErr(msg){ const el=document.getElementById('outsErrors'); el.innerHTML=msg; el.classList.remove('hidden'); }
  function downloadOutsErrors(){
    if (!lastOutsErrors || lastOutsErrors.length===0) return;
    const header = ['row','message'];
    const lines = [header.join(',')].concat(lastOutsErrors.map(e=>`${e.row},"${(e.message||'').replace(/"/g,'""')}"`));
    const blob = new Blob([lines.join('\n')], {type:'text/csv;charset=utf-8;'});
    const url = URL.createObjectURL(blob);
    const a = document.createElement('a'); a.href=url; a.download='outsourcing_errors.csv'; document.body.appendChild(a); a.click(); a.remove(); URL.revokeObjectURL(url);
  }
</script>
</body>
</html>
