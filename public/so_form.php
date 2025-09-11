<?php 
session_start();
if (!isset($_SESSION['username'])) {
  header('Location: login.php');
  exit();
}
include '../config/db.php'; 
include '../config/auth.php';
requirePermission('view_reports');

// Search and filter parameters
$search = isset($_GET['q']) ? trim($_GET['q']) : '';
$project_filter = isset($_GET['project']) ? trim($_GET['project']) : '';
$cost_center_filter = isset($_GET['cost_center']) ? trim($_GET['cost_center']) : '';

// Build WHERE clause
$where_sql = '';
$params = [];
$param_types = '';

if ($project_filter !== '') {
    $where_sql .= ($where_sql ? ' AND ' : ' WHERE ') . 'ps.project_description = ?';
    $params[] = $project_filter;
    $param_types .= 's';
}

if ($cost_center_filter !== '') {
    $where_sql .= ($where_sql ? ' AND ' : ' WHERE ') . 'ps.cost_center = ?';
    $params[] = $cost_center_filter;
    $param_types .= 's';
}

if ($search !== '') {
    $like = '%' . $search . '%';
    $where_sql .= ($where_sql ? ' AND ' : ' WHERE ') . '(ps.cost_center LIKE ? OR ps.po_number LIKE ? OR ps.vendor_name LIKE ? OR ps.project_description LIKE ?)';
    $params = array_merge($params, [$like, $like, $like, $like]);
    $param_types .= 'ssss';
}

// Get filter options
$projects_query = "SELECT DISTINCT project_description FROM posummary WHERE project_description IS NOT NULL AND project_description != '' ORDER BY project_description";
$projects_result = $conn->query($projects_query);

$cost_centers_query = "SELECT DISTINCT cost_center FROM posummary WHERE cost_center IS NOT NULL AND cost_center != '' ORDER BY cost_center";
$cost_centers_result = $conn->query($cost_centers_query);

function formatCurrency($amount) {
    return '₹ ' . number_format($amount, 2);
}

function formatPercentage($value) {
    return is_numeric($value) ? number_format((float)$value, 2) . '%' : '-';
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>SO Form - Summary Report - Cantik Homemade</title>
    <link crossorigin="" href="https://fonts.gstatic.com/" rel="preconnect"/>
    <link as="style" href="https://fonts.googleapis.com/css2?display=swap&amp;family=Noto+Sans%3Awght%40400%3B500%3B700%3B900&amp;family=Public+Sans%3Awght%40400%3B500%3B600%3B700%3B800" onload="this.rel='stylesheet'" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet"/>
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
</head>
<body class="bg-gray-50 text-gray-900" style='font-family: "Public Sans", "Noto Sans", sans-serif;'>
    <div class="relative flex size-full min-h-screen flex-col overflow-x-hidden">
        <?php include '../src/shared/nav.php'; ?>
        
        <main class="flex-1 px-4 sm:px-6 lg:px-8 py-8">
            <div class="max-w-7xl mx-auto">
                <!-- Header -->
                <div class="flex flex-col md:flex-row items-center justify-between gap-4 mb-6">
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900">SO Form - Summary Report</h1>
                        <p class="text-gray-600 mt-2">Comprehensive overview of projects, billing, and margins</p>
                    </div>
                    <div class="flex gap-2">
                        <button onclick="exportToExcel()" class="flex items-center justify-center gap-2 rounded-full bg-green-600 px-5 py-2.5 text-white text-sm font-semibold shadow-sm hover:bg-green-700 transition-colors">
                            <span class="material-symbols-outlined">download</span>
                            <span class="truncate">Export Excel</span>
                        </button>
                        <button onclick="window.print()" class="flex items-center justify-center gap-2 rounded-full bg-blue-600 px-5 py-2.5 text-white text-sm font-semibold shadow-sm hover:bg-blue-700 transition-colors">
                            <span class="material-symbols-outlined">print</span>
                            <span class="truncate">Print</span>
                        </button>
                    </div>
                </div>

                <!-- Filters -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 mb-6">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h2 class="text-lg font-semibold text-gray-900">Filters</h2>
                    </div>
                    <form method="GET" class="p-6">
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                            <div class="relative">
                                <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-gray-400">search</span>
                                <input class="w-full rounded-lg border-gray-200 bg-gray-50 pl-10 pr-4 py-2 text-sm focus:bg-white focus:border-red-500 focus:ring-red-500" 
                                       placeholder="Search projects, PO numbers, vendors..." type="search" name="q" 
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
                                <select name="cost_center" class="w-full appearance-none rounded-lg border-gray-200 bg-gray-50 px-4 py-2 text-sm text-gray-500 focus:border-red-500 focus:ring-red-500">
                                    <option value="">All Cost Centers</option>
                                    <?php while ($cost_center = $cost_centers_result->fetch_assoc()): ?>
                                    <option value="<?= htmlspecialchars($cost_center['cost_center']) ?>" 
                                            <?= $cost_center_filter == $cost_center['cost_center'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($cost_center['cost_center']) ?>
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
                            <a href="so_form.php" class="bg-gray-300 text-gray-700 py-2 px-4 rounded-lg hover:bg-gray-400 focus:outline-none focus:ring-2 focus:ring-gray-500">
                                Clear Filters
                            </a>
                        </div>
                    </form>
                </div>

                <!-- Summary Report Table -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h2 class="text-lg font-semibold text-gray-900">Project Summary Report</h2>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-3 text-left font-medium text-gray-600 uppercase tracking-wider">Project Details</th>
                                    <th class="px-4 py-3 text-left font-medium text-gray-600 uppercase tracking-wider">Customer PO</th>
                                    <th class="px-4 py-3 text-left font-medium text-gray-600 uppercase tracking-wider">Billing Status</th>
                                    <th class="px-4 py-3 text-left font-medium text-gray-600 uppercase tracking-wider">Vendor Details</th>
                                    <th class="px-4 py-3 text-left font-medium text-gray-600 uppercase tracking-wider">Vendor Billing</th>
                                    <th class="px-4 py-3 text-left font-medium text-gray-600 uppercase tracking-wider">Margins</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <?php
$sql = "
  SELECT
    ps.project_description AS project,
    ps.cost_center,
    ps.po_number AS customer_po_no,
    ps.po_value AS customer_po_value,
    COALESCE(bsum.total_billed, 0) AS billed_till_date,
    GREATEST(ps.po_value - COALESCE(bsum.total_billed, 0), 0) AS remaining_balance_po,
    ps.vendor_name,
    osum.latest_vendor_po_no AS cantik_po_no,
    COALESCE(osum.total_vendor_po_value, 0) AS vendor_po_value,
    COALESCE(osum.total_vendor_invoicing, 0) AS vendor_invoicing_till_date,
    GREATEST(COALESCE(osum.total_vendor_po_value, 0) - COALESCE(osum.total_vendor_invoicing, 0), 0) AS remaining_balance_in_po,
    ROUND(NULLIF(COALESCE(bsum.total_billed, 0), 0) / NULLIF(ps.po_value, 0) * 100, 2) AS margin_till_date,
    ROUND(ps.target_gm * 100, 2) AS target_gm,
    ROUND((NULLIF(COALESCE(bsum.total_billed, 0), 0) / NULLIF(ps.po_value, 0) * 100) - (ps.target_gm * 100), 2) AS variance_in_gm
  FROM posummary ps
  LEFT JOIN (
    SELECT customer_po AS po_number,
           SUM(cantik_inv_value_taxable) AS total_billed
    FROM billing_summary
    GROUP BY customer_po
  ) bsum ON bsum.po_number = ps.po_number
  LEFT JOIN (
    SELECT customer_po,
           MAX(cantik_po_no) AS latest_vendor_po_no,
           SUM(cantik_po_value) AS total_vendor_po_value,
           SUM(vendor_inv_value) AS total_vendor_invoicing
    FROM outsourcing_summary
    GROUP BY customer_po
  ) osum ON osum.customer_po = ps.po_number
  $where_sql
  ORDER BY ps.cost_center, ps.po_number
";

if ($param_types !== '') {
    $stmt = $conn->prepare($sql);
    if ($stmt) {
    $stmt->bind_param($param_types, ...$params);
    $stmt->execute();
    $res = $stmt->get_result();
    } else {
        $prep_error = $conn->error;
        $res = false;
    }
} else {
    $res = $conn->query($sql);
}

if (isset($prep_error)) {
    echo "<tr><td colspan='6' class='text-center py-8 text-red-600'>Query error: " . htmlspecialchars($prep_error) . "</td></tr>";
} elseif ($res && $res->num_rows > 0) {
    while ($r = $res->fetch_assoc()) {
        echo "<tr class='hover:bg-gray-50'>";
                                        
                                        // Project Details
                                        echo "<td class='px-4 py-3'>";
                                        echo "<div class='text-sm font-medium text-gray-900'>" . htmlspecialchars($r['project'] ?? '') . "</div>";
                                        echo "<div class='text-sm text-gray-500'>" . htmlspecialchars($r['cost_center'] ?? '') . "</div>";
                                        echo "</td>";
                                        
                                        // Customer PO
                                        echo "<td class='px-4 py-3'>";
                                        echo "<div class='text-sm font-medium text-gray-900'>" . htmlspecialchars($r['customer_po_no'] ?? '') . "</div>";
                                        echo "<div class='text-sm text-gray-500'>" . formatCurrency($r['customer_po_value'] ?? 0) . "</div>";
                                        echo "</td>";
                                        
                                        // Billing Status
                                        echo "<td class='px-4 py-3'>";
                                        echo "<div class='text-sm text-gray-900'>Billed: " . formatCurrency($r['billed_till_date'] ?? 0) . "</div>";
                                        echo "<div class='text-sm text-gray-500'>Remaining: " . formatCurrency($r['remaining_balance_po'] ?? 0) . "</div>";
                                        echo "</td>";
                                        
                                        // Vendor Details
                                        echo "<td class='px-4 py-3'>";
                                        echo "<div class='text-sm font-medium text-gray-900'>" . htmlspecialchars($r['vendor_name'] ?? 'No vendor') . "</div>";
                                        echo "<div class='text-sm text-gray-500'>PO: " . htmlspecialchars($r['cantik_po_no'] ?? '') . "</div>";
                                        echo "<div class='text-sm text-gray-500'>Value: " . formatCurrency($r['vendor_po_value'] ?? 0) . "</div>";
                                        echo "</td>";
                                        
                                        // Vendor Billing
                                        echo "<td class='px-4 py-3'>";
                                        echo "<div class='text-sm text-gray-900'>Invoiced: " . formatCurrency($r['vendor_invoicing_till_date'] ?? 0) . "</div>";
                                        echo "<div class='text-sm text-gray-500'>Remaining: " . formatCurrency($r['remaining_balance_in_po'] ?? 0) . "</div>";
                                        echo "</td>";
                                        
                                        // Margins
                                        echo "<td class='px-4 py-3'>";
                                        echo "<div class='text-sm text-gray-900'>Current: " . formatPercentage($r['margin_till_date'] ?? 0) . "</div>";
                                        echo "<div class='text-sm text-gray-500'>Target: " . formatPercentage($r['target_gm'] ?? 0) . "</div>";
                                        $variance = $r['variance_in_gm'] ?? 0;
                                        $variance_class = $variance >= 0 ? 'text-green-600' : 'text-red-600';
                                        echo "<div class='text-sm font-medium $variance_class'>Variance: " . formatPercentage($variance) . "</div>";
                                        echo "</td>";
                                        
        echo "</tr>";
    }
} else {
                                    echo "<tr><td colspan='6' class='text-center py-8 text-gray-500'>No records found</td></tr>";
}
?>
</tbody>
</table>
</div>
</div>

                <?php if ($res && $res->num_rows == 0): ?>
                <div class="text-center py-12">
                    <span class="material-symbols-outlined text-6xl text-gray-300">assessment</span>
                    <h3 class="mt-2 text-sm font-medium text-gray-900">No summary data found</h3>
                    <p class="mt-1 text-sm text-gray-500">Try adjusting your filters or check if there are any records in the system.</p>
                </div>
                <?php endif; ?>
</div>
</main>
</div>

    <script>
        function exportToExcel() {
            // Create a simple CSV export
            const table = document.querySelector('table');
            const rows = table.querySelectorAll('tr');
            let csv = [];
            
            rows.forEach(row => {
                const cells = row.querySelectorAll('td, th');
                const rowData = Array.from(cells).map(cell => {
                    return '"' + cell.textContent.replace(/"/g, '""') + '"';
                });
                csv.push(rowData.join(','));
            });
            
            const csvContent = csv.join('\n');
            const blob = new Blob([csvContent], { type: 'text/csv' });
            const url = window.URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = 'so_form_summary_' + new Date().toISOString().split('T')[0] + '.csv';
            a.click();
            window.URL.revokeObjectURL(url);
        }
    </script>
</body>
</html>