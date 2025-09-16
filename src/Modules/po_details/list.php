<?php
session_start();
if (!isset($_SESSION['username'])) {
  header('Location: ../../../login.php');
  exit();
}
include '../../../config/db.php';
include '../../../config/auth.php';
requirePermission('view_po_details');

// Search and filter parameters
              $search = isset($_GET['q']) ? trim($_GET['q']) : '';
              $project_filter = isset($_GET['project']) ? trim($_GET['project']) : '';
$status_filter = isset($_GET['status']) ? trim($_GET['status']) : '';
$vendor_filter = isset($_GET['vendor']) ? trim($_GET['vendor']) : '';

// Build WHERE clause
                $where_conditions = [];
                $params = [];
                $param_types = '';

if (!empty($search)) {
    $where_conditions[] = "(po_number LIKE ? OR project_description LIKE ? OR vendor_name LIKE ?)";
    $search_param = "%$search%";
    $params[] = $search_param;
    $params[] = $search_param;
    $params[] = $search_param;
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
    $params[] = $vendor_filter;
    $params[] = $vendor_filter;
    $params[] = $vendor_filter;
    $param_types .= 'sss';
}

$where_clause = !empty($where_conditions) ? 'WHERE ' . implode(' AND ', $where_conditions) : '';

// Get PO details plus derived vendor from outsourcing or billing
$sql = "SELECT po_details.*, 
        (SELECT od.vendor_name FROM outsourcing_detail od WHERE od.customer_po = po_details.po_number ORDER BY od.id DESC LIMIT 1) AS vendor_from_outsourcing,
        (SELECT bd.vendor_name FROM billing_details bd WHERE bd.customer_po = po_details.po_number ORDER BY bd.id DESC LIMIT 1) AS vendor_from_billing
        FROM po_details 
        $where_clause 
        ORDER BY created_at DESC";
$stmt = $conn->prepare($sql);
if (!empty($params)) {
                $stmt->bind_param($param_types, ...$params);
}
                $stmt->execute();
$po_results = $stmt->get_result();

// Get filter options
$projects_query = "SELECT DISTINCT project_description FROM po_details ORDER BY project_description";
$projects_result = $conn->query($projects_query);

$statuses_query = "SELECT DISTINCT po_status FROM po_details ORDER BY po_status";
$statuses_result = $conn->query($statuses_query);

$vendors_query = "SELECT DISTINCT COALESCE(po.vendor_name,
                  (SELECT od.vendor_name FROM outsourcing_detail od WHERE od.customer_po = po.po_number ORDER BY od.id DESC LIMIT 1),
                  (SELECT bd.vendor_name FROM billing_details bd WHERE bd.customer_po = po.po_number ORDER BY bd.id DESC LIMIT 1)) AS vendor_name
                  FROM po_details po
                  HAVING vendor_name IS NOT NULL AND vendor_name <> ''
                  ORDER BY vendor_name";
$vendors_result = $conn->query($vendors_query);

function formatDate($excel_date) {
    if ($excel_date === null || $excel_date === '' || !is_numeric($excel_date)) return '-';
    $unix_date = ((int)$excel_date - 25569) * 86400;
    // Use GMT to avoid timezone-related off-by-one day issues
    return gmdate('d-m-Y', $unix_date);
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
    <title>Purchase Orders - Cantik Homemade</title>
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
                        <h1 class="text-3xl font-bold text-gray-900">Purchase Orders</h1>
                        <p class="text-gray-600 mt-2">Manage and track all purchase orders</p>
                    </div>
                    <div class="flex gap-3">
                        <?php if (hasPermission('add_po_details')): ?>
                        <button onclick="openBulkUploadModal()" class="flex items-center justify-center gap-2 rounded-full bg-blue-600 px-5 py-2.5 text-white text-sm font-semibold shadow-sm hover:bg-blue-700 transition-colors">
                            <span class="material-symbols-outlined">upload</span>
                            <span class="truncate">Bulk Upload</span>
                        </button>
                        <a href="add.php" class="flex items-center justify-center gap-2 rounded-full bg-red-600 px-5 py-2.5 text-white text-sm font-semibold shadow-sm hover:bg-red-700 transition-colors">
                            <span class="material-symbols-outlined">add</span>
                            <span class="truncate">New Purchase Order</span>
                        </a>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Filters -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 mb-6">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h2 class="text-lg font-semibold text-gray-900">Filters</h2>
                    </div>
                    <form method="GET" class="p-6">
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                            <div class="relative">
                                <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-gray-400">search</span>
                                <input class="w-full rounded-lg border-gray-200 bg-gray-50 pl-10 pr-4 py-2 text-sm focus:bg-white focus:border-red-500 focus:ring-red-500" 
                                       placeholder="Search PO number, project, or vendor" type="search" name="q" 
                                       value="<?= htmlspecialchars($search) ?>"/>
                            </div>
                            
                            <div class="relative">
                                <select name="project" class="w-full appearance-none rounded-lg border-gray-200 bg-gray-50 px-4 py-2 text-sm text-gray-500 focus:border-red-500 focus:ring-red-500">
                                    <option value="">All Projects</option>
                                    <?php while ($project = $projects_result->fetch_assoc()): ?>
                                    <option value="<?= htmlspecialchars($project['project_description']) ?>" 
                                            <?= $project_filter == $project['project_description'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($project['project_description']) ?>
                                    </option>
                                    <?php endwhile; ?>
                                </select>
                                <span class="material-symbols-outlined absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 pointer-events-none">expand_more</span>
                            </div>
                            
                            <div class="relative">
                                <select name="status" class="w-full appearance-none rounded-lg border-gray-200 bg-gray-50 px-4 py-2 text-sm text-gray-500 focus:border-red-500 focus:ring-red-500">
                                    <option value="">All Statuses</option>
                                    <?php while ($status = $statuses_result->fetch_assoc()): ?>
                                    <option value="<?= htmlspecialchars($status['po_status']) ?>" 
                                            <?= $status_filter == $status['po_status'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($status['po_status']) ?>
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

                <!-- PO Details Table -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h2 class="text-lg font-semibold text-gray-900">Purchase Orders</h2>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">PO Details</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Project</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Dates</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Financial</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <?php while ($po = $po_results->fetch_assoc()): ?>
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div>
                                            <div class="text-sm font-medium text-gray-900"><?= htmlspecialchars($po['po_number']) ?></div>
                                            <div class="text-sm text-gray-500">SOW: <?= htmlspecialchars($po['sow_number']) ?></div>
                                            <?php 
                                                $derived_vendor = $po['vendor_name'] ?: ($po['vendor_from_outsourcing'] ?: $po['vendor_from_billing']);
                                            ?>
                                            <div class="text-sm text-gray-500"><?= htmlspecialchars($derived_vendor ?: 'No vendor') ?></div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="text-sm text-gray-900"><?= htmlspecialchars($po['project_description']) ?></div>
                                        <div class="text-sm text-gray-500"><?= htmlspecialchars($po['cost_center']) ?></div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">Start: <?= formatDate($po['start_date']) ?></div>
                                        <div class="text-sm text-gray-900">End: <?= formatDate($po['end_date']) ?></div>
                                        <div class="text-sm text-gray-500">PO Date: <?= formatDate($po['po_date']) ?></div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900"><?= formatCurrency($po['po_value']) ?></div>
                                        <div class="text-sm text-gray-500">Pending: <?= formatCurrency($po['pending_amount']) ?></div>
                                        <div class="text-sm text-gray-500">GM: <?= is_numeric($po['target_gm']) ? (($po['target_gm'] < 1) ? number_format($po['target_gm'] * 100, 1) : number_format($po['target_gm'], 1)) : '0' ?>%</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?= $po['po_status'] === 'Active' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' ?>">
                                            <?= htmlspecialchars($po['po_status']) ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <div class="flex gap-2">
                                            <a href="view.php?id=<?= $po['id'] ?>" class="text-blue-600 hover:text-blue-900">View</a>
                                            <?php if (hasPermission('edit_po_details')): ?>
                                            <a href="edit.php?id=<?= $po['id'] ?>" class="text-indigo-600 hover:text-indigo-900">Edit</a>
                                            <?php endif; ?>
                                            <?php if (hasPermission('delete_po_details')): ?>
                                            <a href="delete.php?id=<?= $po['id'] ?>" class="text-red-600 hover:text-red-900" onclick="return confirm('Are you sure you want to delete this PO?')">Delete</a>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
            </tbody>
          </table>
        </div>
                </div>

                <?php if ($po_results->num_rows == 0): ?>
                <div class="text-center py-12">
                    <span class="material-symbols-outlined text-6xl text-gray-300">description</span>
                    <h3 class="mt-2 text-sm font-medium text-gray-900">No purchase orders found</h3>
                    <p class="mt-1 text-sm text-gray-500">Get started by creating a new purchase order.</p>
                    <?php if (hasPermission('add_po_details')): ?>
                    <div class="mt-6">
                        <a href="add.php" class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-red-600 hover:bg-red-700">
                            <span class="material-symbols-outlined mr-2">add</span>
                            New Purchase Order
                        </a>
                    </div>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
      </div>
    </main>
  </div>

  <!-- Bulk Upload Modal -->
  <div id="bulkUploadModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
    <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-3/4 lg:w-1/2 shadow-lg rounded-md bg-white">
      <div class="mt-3">
        <div class="flex items-center justify-between mb-4">
          <h3 class="text-lg font-medium text-gray-900">Bulk Upload Purchase Orders</h3>
          <button onclick="closeBulkUploadModal()" class="text-gray-400 hover:text-gray-600">
            <span class="material-symbols-outlined">close</span>
          </button>
        </div>
        
        <div class="mb-4">
          <div class="bg-blue-50 border border-blue-200 rounded-md p-4 mb-4">
            <h4 class="text-sm font-medium text-blue-800 mb-2">Upload Format Requirements:</h4>
            <div class="text-sm text-blue-700">
              <p class="mb-1">Required columns (case-sensitive). You can upload CSV (comma) or TSV (tab) files:</p>
              <ul class="list-disc list-inside ml-4 space-y-1">
                <li><strong>project_description</strong> - Project name/description</li>
                <li><strong>cost_center</strong> - Cost center code</li>
                <li><strong>sow_number</strong> - Statement of Work number</li>
                <li><strong>start_date</strong> - Start date (Excel serial number format)</li>
                <li><strong>end_date</strong> - End date (Excel serial number format)</li>
                <li><strong>po_number</strong> - Purchase Order number (must be unique)</li>
                <li><strong>po_date</strong> - PO date (Excel serial number format)</li>
                <li><strong>po_value</strong> - PO value (numeric)</li>
                <li><strong>billing_frequency</strong> - Billing frequency (e.g., Monthly, Quarterly)</li>
                <li><strong>target_gm</strong> - Target gross margin (decimal, e.g., 0.05 for 5%)</li>
                <li><strong>vendor_name</strong> - Vendor name (optional)</li>
                <li><strong>remarks</strong> - Additional remarks (optional)</li>
              </ul>
            </div>
          </div>
          
          <div class="bg-yellow-50 border border-yellow-200 rounded-md p-4 mb-4">
            <h4 class="text-sm font-medium text-yellow-800 mb-2">Date Format Note:</h4>
            <p class="text-sm text-yellow-700">Dates should be in Excel serial number format. For example, 45668 represents a specific date. You can use Excel's DATEVALUE function to convert regular dates.</p>
          </div>
          
          <div class="bg-green-50 border border-green-200 rounded-md p-4 mb-4">
            <h4 class="text-sm font-medium text-green-800 mb-2">Need a Template?</h4>
            <p class="text-sm text-green-700 mb-2">Download our sample CSV template to see the correct format:</p>
            <a href="sample_template.csv" download="po_template.csv" 
               class="inline-flex items-center gap-2 px-3 py-2 bg-green-600 text-white text-sm rounded-md hover:bg-green-700 transition-colors">
              <span class="material-symbols-outlined text-sm">download</span>
              Download Template
            </a>
          </div>
        </div>

        <form id="bulkUploadForm" enctype="multipart/form-data">
          <div class="mb-4">
            <label for="csvFile" class="block text-sm font-medium text-gray-700 mb-2">Select CSV/TSV File</label>
            <input type="file" id="csvFile" name="csvFile" accept=".csv,.tsv,.txt" required 
                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                   onchange="previewCSV(this)">
          </div>
          
          <div id="csvPreview" class="hidden mb-4">
            <div class="flex justify-between items-center mb-2">
              <h4 class="text-sm font-medium text-gray-700">CSV Preview (First 5 rows):</h4>
              <span id="rowCount" class="text-sm text-gray-500"></span>
            </div>
            <div class="bg-gray-50 border border-gray-200 rounded-md p-4 max-h-64 overflow-auto">
              <table id="previewTable" class="w-full text-xs">
                <thead id="previewHeaders" class="bg-gray-100"></thead>
                <tbody id="previewBody"></tbody>
              </table>
            </div>
          </div>
          
          <div id="uploadProgress" class="hidden mb-4">
            <div class="bg-gray-200 rounded-full h-2.5">
              <div id="progressBar" class="bg-blue-600 h-2.5 rounded-full transition-all duration-300" style="width: 0%"></div>
            </div>
            <p id="progressText" class="text-sm text-gray-600 mt-1">Processing...</p>
          </div>
          
          <div id="uploadResults" class="hidden mb-4">
            <div id="successResults" class="hidden bg-green-50 border border-green-200 rounded-md p-4 mb-2">
              <h4 class="text-sm font-medium text-green-800 mb-2">Success:</h4>
              <p id="successMessage" class="text-sm text-green-700"></p>
            </div>
            <div id="errorResults" class="hidden bg-red-50 border border-red-200 rounded-md p-4">
              <h4 class="text-sm font-medium text-red-800 mb-2">Errors:</h4>
              <div id="errorList" class="text-sm text-red-700"></div>
            </div>
          </div>
          
          <div class="flex justify-end space-x-3">
            <button type="button" onclick="testConnection()" 
                    class="px-4 py-2 bg-yellow-600 text-white rounded-md hover:bg-yellow-700 focus:outline-none focus:ring-2 focus:ring-yellow-500">
              Test Connection
            </button>
            <button type="button" onclick="closeBulkUploadModal()" 
                    class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400 focus:outline-none focus:ring-2 focus:ring-gray-500">
              Cancel
            </button>
            <button type="submit" id="uploadBtn" 
                    class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
              Upload CSV
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <script>
    function openBulkUploadModal() {
      document.getElementById('bulkUploadModal').classList.remove('hidden');
      resetUploadForm();
    }

    function closeBulkUploadModal() {
      document.getElementById('bulkUploadModal').classList.add('hidden');
      resetUploadForm();
    }

    function resetUploadForm() {
      document.getElementById('bulkUploadForm').reset();
      document.getElementById('uploadProgress').classList.add('hidden');
      document.getElementById('uploadResults').classList.add('hidden');
      document.getElementById('successResults').classList.add('hidden');
      document.getElementById('errorResults').classList.add('hidden');
      document.getElementById('csvPreview').classList.add('hidden');
      document.getElementById('uploadBtn').disabled = false;
    }

    function previewCSV(input) {
      const file = input.files[0];
      if (!file) {
        document.getElementById('csvPreview').classList.add('hidden');
        return;
      }

      const reader = new FileReader();
      reader.onload = function(e) {
        const csv = e.target.result;
        const lines = csv.split(/\r?\n/);
        // Auto-detect delimiter (tab or comma)
        const headerLine = lines[0].replace(/^\uFEFF/, '');
        const commaCount = (headerLine.match(/,/g) || []).length;
        const tabCount = (headerLine.match(/\t/g) || []).length;
        const delimiter = tabCount > commaCount ? '\t' : ',';
        const headers = headerLine.split(delimiter).map(h => h.trim().replace(/"/g, ''));
        
        // Show preview
        const previewDiv = document.getElementById('csvPreview');
        const headersRow = document.getElementById('previewHeaders');
        const bodyRows = document.getElementById('previewBody');
        
        // Clear previous content
        headersRow.innerHTML = '';
        bodyRows.innerHTML = '';
        
        // Add headers
        const headerRow = document.createElement('tr');
        headers.forEach(header => {
          const th = document.createElement('th');
          th.className = 'px-2 py-1 text-left font-medium text-gray-700';
          th.textContent = header;
          headerRow.appendChild(th);
        });
        headersRow.appendChild(headerRow);
        
        // Add first 5 data rows
        for (let i = 1; i <= Math.min(5, lines.length - 1); i++) {
          if (lines[i].trim()) {
            const row = document.createElement('tr');
            const cells = lines[i].split(delimiter).map(c => c.trim().replace(/"/g, ''));
            cells.forEach(cell => {
              const td = document.createElement('td');
              td.className = 'px-2 py-1 border-b border-gray-200';
              td.textContent = cell || '-';
              row.appendChild(td);
            });
            bodyRows.appendChild(row);
          }
        }
        
        // Show row count
        const totalRows = lines.length - 1; // Subtract header row
        document.getElementById('rowCount').textContent = `Total rows: ${totalRows}`;
        
        // Validate headers
        const requiredHeaders = [
          'project_description', 'cost_center', 'sow_number', 'start_date', 
          'end_date', 'po_number', 'po_date', 'po_value', 'billing_frequency', 'target_gm'
        ];
        
        const missingHeaders = requiredHeaders.filter(header => !headers.includes(header));
        if (missingHeaders.length > 0) {
          document.getElementById('rowCount').innerHTML = 
            `Total rows: ${totalRows} <span class="text-red-600">(Missing headers: ${missingHeaders.join(', ')})</span>`;
        }
        
        previewDiv.classList.remove('hidden');
      };
      
      reader.readAsText(file);
    }

    document.getElementById('bulkUploadForm').addEventListener('submit', function(e) {
      e.preventDefault();
      
      const formData = new FormData();
      const fileInput = document.getElementById('csvFile');
      
      if (!fileInput.files[0]) {
        alert('Please select a CSV file');
        return;
      }
      
      formData.append('csvFile', fileInput.files[0]);
      
      // Show progress
      document.getElementById('uploadProgress').classList.remove('hidden');
      document.getElementById('uploadBtn').disabled = true;
      
      // Reset results
      document.getElementById('uploadResults').classList.add('hidden');
      document.getElementById('successResults').classList.add('hidden');
      document.getElementById('errorResults').classList.add('hidden');
      
      // Simulate progress
      let progress = 0;
      const progressInterval = setInterval(() => {
        progress += 10;
        document.getElementById('progressBar').style.width = progress + '%';
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
        document.getElementById('progressBar').style.width = '100%';
        document.getElementById('progressText').textContent = 'Complete!';
        
        setTimeout(() => {
          document.getElementById('uploadProgress').classList.add('hidden');
          document.getElementById('uploadResults').classList.remove('hidden');
          
          if (data.success) {
            document.getElementById('successResults').classList.remove('hidden');
            document.getElementById('successMessage').textContent = 
              `Successfully uploaded ${data.inserted} records. ${data.skipped} records were skipped due to duplicates.`;
          } else {
            document.getElementById('errorResults').classList.remove('hidden');
            const errorList = document.getElementById('errorList');
            errorList.innerHTML = '';
            data.errors.forEach(error => {
              const errorItem = document.createElement('div');
              errorItem.className = 'mb-1';
              errorItem.textContent = `Row ${error.row}: ${error.message}`;
              errorList.appendChild(errorItem);
            });
          }
          
          document.getElementById('uploadBtn').disabled = false;
          
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
        document.getElementById('uploadProgress').classList.add('hidden');
        document.getElementById('uploadResults').classList.remove('hidden');
        document.getElementById('errorResults').classList.remove('hidden');
        document.getElementById('errorList').innerHTML = '<div class="mb-1">Upload failed: ' + error.message + '</div>';
        document.getElementById('uploadBtn').disabled = false;
      });
    });

    function testConnection() {
      fetch('test_upload.php', {
        method: 'POST'
      })
      .then(response => response.text())
      .then(text => {
        try {
          const data = JSON.parse(text);
          alert('Test successful!\n\nResponse: ' + JSON.stringify(data, null, 2));
        } catch (e) {
          alert('Test failed - Invalid JSON response:\n\n' + text.substring(0, 500));
        }
      })
      .catch(error => {
        alert('Test failed - Network error:\n\n' + error.message);
      });
    }

    // Close modal when clicking outside
    document.getElementById('bulkUploadModal').addEventListener('click', function(e) {
      if (e.target === this) {
        closeBulkUploadModal();
      }
    });
  </script>
</body>
</html>
