<?php 
// Use universal includes
include 'src/shared/includes.php';

// Check authentication
if (!isset($_SESSION['username'])) {
  header('Location: login.php');
  exit();
}
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
    // Include derived vendor name in search using subqueries
    $where_sql .= ($where_sql ? ' AND ' : ' WHERE ') . '(
        ps.cost_center LIKE ? OR 
        ps.po_number LIKE ? OR 
        ps.vendor_name LIKE ? OR 
        (SELECT od.vendor_name FROM outsourcing_detail od WHERE od.customer_po = ps.po_number ORDER BY od.id DESC LIMIT 1) LIKE ? OR 
        (SELECT bd.vendor_name FROM billing_details bd WHERE bd.customer_po = ps.po_number ORDER BY bd.id DESC LIMIT 1) LIKE ? OR 
        ps.project_description LIKE ?
    )';
    $params = array_merge($params, [$like, $like, $like, $like, $like, $like]);
    $param_types .= 'ssssss';
}

// Get filter options
$projects_query = "SELECT DISTINCT project_description FROM posummary WHERE project_description IS NOT NULL AND project_description != '' ORDER BY project_description";
$projects_result = $conn->query($projects_query);

$cost_centers_query = "SELECT DISTINCT cost_center FROM posummary WHERE cost_center IS NOT NULL AND cost_center != '' ORDER BY cost_center";
$cost_centers_result = $conn->query($cost_centers_query);

function formatCurrency($amount) {
    return number_format((float)$amount, 2);
}

function formatPercentage($value) {
    return is_numeric($value) ? number_format((float)$value, 2) . '%' : '-';
}

function badgePctClass($pct) {
    $v = is_numeric($pct) ? (float)$pct : 0.0;
    if ($v >= 20) return 'bg-green-100 text-green-700';
    if ($v >= 10) return 'bg-amber-100 text-amber-700';
    return 'bg-red-100 text-red-700';
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
        <?php include 'src/shared/nav.php'; ?>
        
        <main class="flex-1 px-4 sm:px-6 lg:px-8 py-8">
            <div class="max-w-7xl mx-auto">
                <!-- Header -->
                <div class="flex flex-col md:flex-row items-center justify-between gap-4 mb-6">
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900">So Form - Summary Report</h1>
                        <p class="text-gray-600 mt-2">Comprehensive overview of projects, billing, and margins</p>
                    </div>
                    <div class="flex items-center gap-2">
                        <button onclick="exportToExcel()" class="inline-flex items-center justify-center gap-2 rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2">
                            <span class="material-symbols-outlined text-base">download</span>
                            Export Excel
                        </button>
                        <button onclick="window.print()" class="inline-flex items-center justify-center gap-2 rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2">
                            <span class="material-symbols-outlined text-base">print</span>
                            Print
                        </button>
                    </div>
                </div>

                <!-- Filters -->
                <div class="mb-6 rounded-lg border border-gray-200 bg-white p-6 shadow-sm">
                    <div class="pb-4 border-b border-gray-200">
                        <h2 class="text-lg font-semibold text-gray-900">Filters</h2>
                    </div>
                    <form method="GET" class="pt-6">
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
                        <div class="mt-6 flex justify-end gap-3">
                            <a href="so_form.php" class="rounded-md bg-gray-200 px-4 py-2 text-sm font-medium text-gray-800 hover:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-gray-400 focus:ring-offset-2">Reset</a>
                            <button type="submit" class="rounded-md bg-red-600 px-4 py-2 text-sm font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2">Apply Filters</button>
                        </div>
                    </form>
                </div>

                <!-- Summary Report Table -->
                <div class="overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h2 class="text-lg font-semibold text-gray-900">Project summary report</h2>
                        <p class="text-xs text-gray-500 mt-1">Sale Margin Till date = (Billed till date − Vendor Invoicing Till Date) / Billed till date. Variance = Sale Margin − Target GM.</p>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm table-fixed">
                            <thead class="bg-white/70 backdrop-blur supports-backdrop-blur sticky top-0 z-10 border-b">
                                <tr>
                                    <th class="px-3 py-3 w-12 text-left font-semibold text-gray-800 tracking-wide">ID</th>
                                    <th class="px-4 py-3 text-left font-semibold text-gray-800 tracking-wide">Project Name</th>
                                    <th class="px-4 py-3 text-left font-semibold text-gray-800 tracking-wide">Cost Centre</th>
                                    <th class="px-4 py-3 text-left font-semibold text-gray-800 tracking-wide">Customer PO No</th>
                                    <th class="px-4 py-3 text-right font-semibold text-gray-800 tracking-wide">Customer PO Value</th>
                                    <th class="px-4 py-3 text-right font-semibold text-gray-800 tracking-wide">Billed Till Date</th>
                                    <th class="px-4 py-3 text-right font-semibold text-gray-800 tracking-wide">Remaining Balance</th>
                                    <th class="px-4 py-3 text-left font-semibold text-gray-800 tracking-wide">Vendor Name</th>
                                    <th class="px-4 py-3 text-left font-semibold text-gray-800 tracking-wide">Cantik PO No</th>
                                    <th class="px-4 py-3 text-right font-semibold text-gray-800 tracking-wide">Vendor PO Value</th>
                                    <th class="px-4 py-3 text-right font-semibold text-gray-800 tracking-wide">Vendor Invoicing</th>
                                    <th class="px-4 py-3 text-right font-semibold text-gray-800 tracking-wide">Remaining Balance</th>
                                    <th class="px-4 py-3 text-right font-semibold text-gray-800 tracking-wide">Sale Margin</th>
                                    <th class="px-4 py-3 text-right font-semibold text-gray-800 tracking-wide">Target GM</th>
                                    <th class="px-4 py-3 text-right font-semibold text-gray-800 tracking-wide">Variance</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-100">
                                <?php
$sql = "
  SELECT
    ps.project_description AS project,
    ps.cost_center,
    ps.po_number AS customer_po_no,
    ps.po_value AS customer_po_value,
    COALESCE(bsum.total_billed, 0) AS billed_till_date,
    GREATEST(ps.po_value - COALESCE(bsum.total_billed, 0), 0) AS remaining_balance_po,
    COALESCE(ps.vendor_name,
      (SELECT od.vendor_name FROM outsourcing_detail od WHERE od.customer_po = ps.po_number ORDER BY od.id DESC LIMIT 1),
      (SELECT bd.vendor_name FROM billing_details bd WHERE bd.customer_po = ps.po_number ORDER BY bd.id DESC LIMIT 1)
    ) AS vendor_name,
    osum.latest_vendor_po_no AS cantik_po_no,
    COALESCE(osum.total_vendor_po_value, 0) AS vendor_po_value,
    COALESCE(osum.total_vendor_invoicing, 0) AS vendor_invoicing_till_date,
    GREATEST(COALESCE(osum.total_vendor_po_value, 0) - COALESCE(osum.total_vendor_invoicing, 0), 0) AS remaining_balance_in_po,
    COALESCE(ROUND(((COALESCE(bsum.total_billed, 0) - COALESCE(osum.total_vendor_invoicing, 0))
                   / NULLIF(COALESCE(bsum.total_billed, 0), 0)) * 100, 2), 0) AS margin_till_date,
    ROUND(ps.target_gm * 100, 2) AS target_gm,
    COALESCE(ROUND((((COALESCE(bsum.total_billed, 0) - COALESCE(osum.total_vendor_invoicing, 0))
                    / NULLIF(COALESCE(bsum.total_billed, 0), 0)) * 100) - (ps.target_gm * 100), 2), 0) AS variance_in_gm
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
           MAX(cantik_po_value) AS total_vendor_po_value,
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
    $rowNum = 1;
    while ($r = $res->fetch_assoc()) {
        echo "<tr class='hover:bg-gray-50'>";
        echo "<td class='px-3 py-3 text-gray-700'>" . $rowNum++ . "</td>";
        echo "<td class='px-4 py-3'>" . htmlspecialchars($r['project'] ?? '') . "</td>";
        echo "<td class='px-4 py-3'>" . htmlspecialchars($r['cost_center'] ?? '') . "</td>";
        echo "<td class='px-4 py-3'>" . htmlspecialchars($r['customer_po_no'] ?? '') . "</td>";
        echo "<td class='px-4 py-3 text-right'>" . formatCurrency($r['customer_po_value'] ?? 0) . "</td>";
        echo "<td class='px-4 py-3 text-right'>" . formatCurrency($r['billed_till_date'] ?? 0) . "</td>";
        echo "<td class='px-4 py-3 text-right'>" . formatCurrency($r['remaining_balance_po'] ?? 0) . "</td>";
        $vendorName = trim((string)($r['vendor_name'] ?? ''));
        if ($vendorName === '') { $vendorName = '-'; }
        $cantikPo = trim((string)($r['cantik_po_no'] ?? ''));
        if ($cantikPo === '') { $cantikPo = '-'; }
        echo "<td class='px-4 py-3'>" . htmlspecialchars($vendorName) . "</td>";
        echo "<td class='px-4 py-3'>" . htmlspecialchars($cantikPo) . "</td>";
        echo "<td class='px-4 py-3 text-right'>" . formatCurrency($r['vendor_po_value'] ?? 0) . "</td>";
        echo "<td class='px-4 py-3 text-right'>" . formatCurrency($r['vendor_invoicing_till_date'] ?? 0) . "</td>";
        echo "<td class='px-4 py-3 text-right'>" . formatCurrency($r['remaining_balance_in_po'] ?? 0) . "</td>";
        echo "<td class='px-4 py-3 text-right'><span class='inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium " . badgePctClass($r['margin_till_date'] ?? 0) . "'>" . formatPercentage($r['margin_till_date'] ?? 0) . "</span></td>";
        echo "<td class='px-4 py-3 text-right'><span class='inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-700'>" . formatPercentage($r['target_gm'] ?? 0) . "</span></td>";
        $variance = $r['variance_in_gm'] ?? 0;
        $vClass = ($variance >= 0) ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700';
        echo "<td class='px-4 py-3 text-right'><span class='inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium $vClass'>" . formatPercentage($variance) . "</span></td>";
                                        
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