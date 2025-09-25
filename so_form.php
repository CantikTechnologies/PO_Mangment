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
$projects_query = "SELECT DISTINCT project_description FROM po_details WHERE project_description IS NOT NULL AND project_description != '' ORDER BY project_description";
$projects_result = $conn->query($projects_query);

$cost_centers_query = "SELECT DISTINCT cost_center FROM po_details WHERE cost_center IS NOT NULL AND cost_center != '' ORDER BY cost_center";
$cost_centers_result = $conn->query($cost_centers_query);

// Formatting functions are now included from shared/formatting.php
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>SO Form - Summary Report - Cantik</title>
    <link crossorigin="" href="https://fonts.gstatic.com/" rel="preconnect"/>
    <link as="style" href="https://fonts.googleapis.com/css2?display=swap&amp;family=Noto+Sans%3Awght%40400%3B500%3B700%3B900&amp;family=Public+Sans%3Awght%40400%3B500%3B600%3B700%3B800" onload="this.rel='stylesheet'" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet"/>
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
</head>
<body class="bg-gradient-to-br from-rose-100 via-sky-100 to-indigo-100 text-gray-900" style='font-family: "Public Sans", "Noto Sans", sans-serif;'>
    <div class="relative flex size-full min-h-screen flex-col overflow-x-hidden">
        <?php include 'src/shared/nav.php'; ?>
        
        <main class="flex-1 px-4 sm:px-6 lg:px-8 py-8">
            <div class="max-w-7xl mx-auto">
                <!-- Header (Styled like index hero) -->
                <div class="mb-6">
                    <div class="relative overflow-hidden rounded-2xl bg-white border border-gray-300 shadow-sm">
                        <div class="px-6 sm:px-8 py-6">
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 items-center">
                                <div class="md:col-span-2 min-w-0">
                                    <div class="inline-flex items-center gap-2 text-[11px] font-semibold uppercase tracking-wider text-gray-600">
                                        <span class="material-symbols-outlined text-sm">assessment</span>
                                        SO Form - Summary Report
                                    </div>
                                    <h1 class="mt-1 text-2xl sm:text-3xl font-bold tracking-tight text-gray-900 truncate">
                                        Comprehensive overview of projects, billing, and margins
                                    </h1>
                                    <p class="mt-1 text-sm text-gray-600">Search, filter, analyze and export summary data.</p>
                                </div>
                                <div class="justify-self-start md:justify-self-end w-full md:w-auto">
                                    <div class="flex items-center gap-2">
                                        <button onclick="exportToExcel()" class="inline-flex items-center justify-center w-10 h-10 rounded-lg border-2 border-green-500 text-green-700 bg-green-50/40 hover:bg-green-100 hover:border-green-600 transition-colors shadow-sm" title="Export Excel">
                                            <span class="material-symbols-outlined text-base">download</span>
                                        </button>
                                        <button onclick="window.print()" class="inline-flex items-center justify-center w-10 h-10 rounded-lg border-2 border-blue-500 text-blue-700 bg-blue-50/40 hover:bg-blue-100 hover:border-blue-600 transition-colors shadow-sm" title="Print">
                                            <span class="material-symbols-outlined text-base">print</span>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Filters -->
                <div class="mb-6 rounded-2xl border border-gray-300 border-l-4 border-l-blue-600 bg-white p-6 shadow-md">
                    <div class="pb-4 border-b border-gray-200">
                        <h2 class="text-lg font-semibold text-gray-900 inline-flex items-center gap-2">
                            <span class="material-symbols-outlined text-blue-600">tune</span>
                            Filters
                        </h2>
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
                <div id="reportCard" class="overflow-hidden rounded-2xl border border-gray-300 border-l-4 border-l-indigo-600 bg-white shadow-md">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <div class="flex items-center justify-between gap-3">
                            <div>
                                <h2 class="text-lg font-semibold text-gray-900 inline-flex items-center gap-2">
                                    <span class="material-symbols-outlined text-indigo-600">insights</span>
                                    Project summary report
                                </h2>
                                <p class="text-xs text-gray-600 mt-1">Sale Margin Till date = (Billed till date − Vendor Invoicing Till Date) / Billed till date. Variance = Sale Margin − Target GM.</p>
                            </div>
                            <div class="flex items-center gap-2">
                                <button type="button" onclick="toggleFullReport()" class="inline-flex items-center justify-center w-10 h-10 rounded-lg border-2 border-rose-500 text-rose-700 bg-rose-50/40 hover:bg-rose-100 hover:border-rose-600 transition-colors shadow-sm" title="Full View">
                                    <span class="material-symbols-outlined text-base">fullscreen</span>
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm table-fixed border border-gray-300 border-collapse">
                            <thead class="sticky top-0 z-10 border-b border-gray-300 bg-gradient-to-r from-indigo-600 via-sky-600 to-cyan-600 text-white">
                                <tr>
                                    <th class="px-3 py-2 w-14 text-left font-semibold tracking-wide border border-indigo-500/40">ID</th>
                                    <th class="px-3 py-2 w-64 text-left font-semibold tracking-wide border border-indigo-500/40">Project Name</th>
                                    <th class="px-3 py-2 w-40 text-left font-semibold tracking-wide border border-indigo-500/40">Cost Centre</th>
                                    <th class="px-3 py-2 w-44 text-left font-semibold tracking-wide border border-indigo-500/40">Customer PO No</th>
                                    <th class="px-3 py-2 w-40 text-right font-semibold tracking-wide border border-indigo-500/40">Customer PO Value</th>
                                    <th class="px-3 py-2 w-40 text-right font-semibold tracking-wide border border-indigo-500/40">Billed Till Date</th>
                                    <th class="px-3 py-2 w-44 text-right font-semibold tracking-wide border border-indigo-500/40">Remaining Balance</th>
                                    <th class="px-3 py-2 w-56 text-left font-semibold tracking-wide border border-indigo-500/40">Vendor Name</th>
                                    <th class="px-3 py-2 w-40 text-left font-semibold tracking-wide border border-indigo-500/40">Cantik PO No</th>
                                    <th class="px-3 py-2 w-40 text-right font-semibold tracking-wide border border-indigo-500/40">Vendor PO Value</th>
                                    <th class="px-3 py-2 w-40 text-right font-semibold tracking-wide border border-indigo-500/40">Vendor Invoicing</th>
                                    <th class="px-3 py-2 w-44 text-right font-semibold tracking-wide border border-indigo-500/40">Remaining Balance</th>
                                    <th class="px-3 py-2 w-36 text-right font-semibold tracking-wide border border-indigo-500/40">Sale Margin</th>
                                    <th class="px-3 py-2 w-32 text-right font-semibold tracking-wide border border-indigo-500/40">Target GM</th>
                                    <th class="px-3 py-2 w-32 text-right font-semibold tracking-wide border border-indigo-500/40">Variance</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white">
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
    osum.latest_cantik_po_no AS cantik_po_no,
    COALESCE(osum.vendor_po_value, 0) AS vendor_po_value,
    COALESCE(osum.vendor_invoicing_till_date, 0) AS vendor_invoicing_till_date,
    GREATEST(COALESCE(osum.vendor_po_value, 0) - COALESCE(osum.vendor_invoicing_till_date, 0), 0) AS remaining_balance_in_po,
    COALESCE(ROUND(((COALESCE(bsum.total_billed, 0) - COALESCE(osum.vendor_invoicing_till_date, 0))
                   / NULLIF(COALESCE(bsum.total_billed, 0), 0)) * 100, 2), 0) AS margin_till_date,
    ROUND(ps.target_gm * 100, 2) AS target_gm,
    COALESCE(ROUND((((COALESCE(bsum.total_billed, 0) - COALESCE(osum.vendor_invoicing_till_date, 0))
                    / NULLIF(COALESCE(bsum.total_billed, 0), 0)) * 100) - (ps.target_gm * 100), 2), 0) AS variance_in_gm
  FROM po_details ps
  LEFT JOIN (
    SELECT customer_po AS po_number,
           SUM(cantik_inv_value_taxable) AS total_billed
    FROM billing_details
    GROUP BY customer_po
  ) bsum ON bsum.po_number = ps.po_number
  LEFT JOIN (
    SELECT customer_po,
           MAX(cantik_po_no) AS latest_cantik_po_no,
           MAX(cantik_po_value) AS vendor_po_value,
           SUM(vendor_inv_value) AS vendor_invoicing_till_date
    FROM outsourcing_detail
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
        echo "<tr class='hover:bg-gray-50 even:bg-gray-50'>";
        echo "<td class='px-3 py-2 text-gray-700 border border-gray-200 align-top'>" . $rowNum++ . "</td>";
        echo "<td class='px-3 py-2 border border-gray-200 align-top whitespace-normal'>" . htmlspecialchars($r['project'] ?? '') . "</td>";
        echo "<td class='px-3 py-2 border border-gray-200 align-top whitespace-normal'>" . htmlspecialchars($r['cost_center'] ?? '') . "</td>";
        echo "<td class='px-3 py-2 border border-gray-200 align-top whitespace-normal'>" . htmlspecialchars($r['customer_po_no'] ?? '') . "</td>";
        echo "<td class='px-3 py-2 text-right border border-gray-200 align-top'>" . formatCurrency($r['customer_po_value'] ?? 0) . "</td>";
        echo "<td class='px-3 py-2 text-right border border-gray-200 align-top'>" . formatCurrency($r['billed_till_date'] ?? 0) . "</td>";
        echo "<td class='px-3 py-2 text-right border border-gray-200 align-top'>" . formatCurrency($r['remaining_balance_po'] ?? 0) . "</td>";
        $vendorName = trim((string)($r['vendor_name'] ?? ''));
        if ($vendorName === '') { 
            // Try to manually fetch vendor name from related tables
            $po_number = $r['customer_po_no'] ?? '';
            if ($po_number) {
                // Check outsourcing_detail first
                $vendor_sql = "SELECT vendor_name FROM outsourcing_detail WHERE customer_po = ? AND vendor_name IS NOT NULL AND vendor_name != '' ORDER BY id DESC LIMIT 1";
                $vendor_stmt = $conn->prepare($vendor_sql);
                $vendor_stmt->bind_param('s', $po_number);
                $vendor_stmt->execute();
                $vendor_result = $vendor_stmt->get_result();
                if ($vendor_row = $vendor_result->fetch_assoc()) {
                    $vendorName = trim($vendor_row['vendor_name']);
                } else {
                    // Check billing_details
                    $vendor_sql2 = "SELECT vendor_name FROM billing_details WHERE customer_po = ? AND vendor_name IS NOT NULL AND vendor_name != '' ORDER BY id DESC LIMIT 1";
                    $vendor_stmt2 = $conn->prepare($vendor_sql2);
                    $vendor_stmt2->bind_param('s', $po_number);
                    $vendor_stmt2->execute();
                    $vendor_result2 = $vendor_stmt2->get_result();
                    if ($vendor_row2 = $vendor_result2->fetch_assoc()) {
                        $vendorName = trim($vendor_row2['vendor_name']);
                    }
                    $vendor_stmt2->close();
                }
                $vendor_stmt->close();
            }
            if ($vendorName === '') { $vendorName = '-'; }
        }
        $cantikPo = trim((string)($r['cantik_po_no'] ?? ''));
        if ($cantikPo === '') { $cantikPo = '-'; }
        echo "<td class='px-3 py-2 border border-gray-200 align-top whitespace-normal'>" . htmlspecialchars($vendorName) . "</td>";
        echo "<td class='px-3 py-2 border border-gray-200 align-top whitespace-normal'>" . htmlspecialchars($cantikPo) . "</td>";
        echo "<td class='px-3 py-2 text-right border border-gray-200 align-top'>" . formatCurrency($r['vendor_po_value'] ?? 0) . "</td>";
        echo "<td class='px-3 py-2 text-right border border-gray-200 align-top'>" . formatCurrency($r['vendor_invoicing_till_date'] ?? 0) . "</td>";
        echo "<td class='px-3 py-2 text-right border border-gray-200 align-top'>" . formatCurrency($r['remaining_balance_in_po'] ?? 0) . "</td>";
        echo "<td class='px-3 py-2 text-right border border-gray-200 align-top'><span class='inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium " . badgePctClass($r['margin_till_date'] ?? 0) . "'>" . formatPercentage($r['margin_till_date'] ?? 0) . "</span></td>";
        echo "<td class='px-3 py-2 text-right border border-gray-200 align-top'><span class='inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-700'>" . formatPercentage($r['target_gm'] ?? 0) . "</span></td>";
        $variance = $r['variance_in_gm'] ?? 0;
        $vClass = ($variance >= 0) ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700';
        echo "<td class='px-3 py-2 text-right border border-gray-200 align-top'><span class='inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium $vClass'>" . formatPercentage($variance) . "</span></td>";
                                        
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
            // Create Excel-compatible CSV export with BOM
            const table = document.querySelector('table');
            const rows = table.querySelectorAll('tr');
            let csv = [];
            
            // Add BOM for UTF-8 to ensure proper Excel compatibility
            csv.push('\uFEFF');
            
            rows.forEach(row => {
                const cells = row.querySelectorAll('td, th');
                const rowData = Array.from(cells).map(cell => {
                    return '"' + cell.textContent.replace(/"/g, '""') + '"';
                });
                csv.push(rowData.join(','));
            });
            
            const csvContent = csv.join('\n');
            const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
            const url = window.URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = 'so_form_summary_' + new Date().toISOString().split('T')[0] + '.csv';
            a.click();
            window.URL.revokeObjectURL(url);
        }
    </script>

<script src="assets/indian-numbering.js"></script>
</body>
</html>
<script>
  // Toggle full screen width/height for the report card
  function toggleFullReport(){
    const card = document.getElementById('reportCard');
    const table = card.querySelector('table');
    const headers = card.querySelectorAll('thead th');
    const cells = card.querySelectorAll('tbody td');
    const isFull = card.classList.contains('fixed');
    if (!isFull){
      // Enter full view overlay
      card.classList.add('fixed','inset-4','z-50');
      card.classList.remove('shadow-md');
      card.classList.add('shadow-2xl');
      // Prevent background scroll
      document.body.style.overflow = 'hidden';

      // Save previous classes to restore later
      if (table && !table.dataset.prevTableClass) {
        table.dataset.prevTableClass = table.className;
      }
      headers.forEach((th)=>{
        if (!th.dataset.prevClass) th.dataset.prevClass = th.className;
        // remove fixed width classes like w-64, w-40, etc.
        th.className = th.className
          .split(' ')
          .filter(c => c && !/^w-/.test(c))
          .join(' ');
        th.style.width = 'auto';
      });
      cells.forEach((td)=>{
        if (!td.dataset.prevClass) td.dataset.prevClass = td.className;
        td.className = td.className
          .split(' ')
          .filter(c => c && !/^w-/.test(c))
          .join(' ');
        td.style.width = 'auto';
      });
      if (table) {
        table.classList.remove('table-fixed');
        table.classList.add('table-auto');
      }
    } else {
      // Exit full view
      card.classList.remove('fixed','inset-4','z-50');
      card.classList.remove('shadow-2xl');
      card.classList.add('shadow-md');
      document.body.style.overflow = '';

      // Restore previous classes
      if (table && table.dataset.prevTableClass) {
        table.className = table.dataset.prevTableClass;
      }
      headers.forEach((th)=>{
        if (th.dataset.prevClass) th.className = th.dataset.prevClass;
        th.style.width = '';
      });
      cells.forEach((td)=>{
        if (td.dataset.prevClass) td.className = td.dataset.prevClass;
        td.style.width = '';
      });
    }
  }
</script>
</body>
</html>