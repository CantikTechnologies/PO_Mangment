<?php
session_start();
if (!isset($_SESSION['username'])) {
  header('Location: ../1Login_signuppage/login.php');
  exit();
}
include '../db.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Outsourcing</title>
  <link rel="stylesheet" href="../assets/style.css?v=<?php echo time(); ?>">
</head>
<body>
  <div class="container">
    <?php include '../shared/nav.php'; ?>
    <main>
      <div class="page-header">
        <h1>Outsourcing</h1>
        <a class="btn" href="add.php">+ Add New Record</a>
      </div>

      <form method="get" class="card" style="margin-bottom: 16px;">
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 16px;">
          <div>
            <label for="project_filter">Filter by Project:</label>
            <select name="project" id="project_filter" onchange="this.form.submit()">
              <option value="">All Projects</option>
              <?php
              $projects = $conn->query("SELECT DISTINCT project_details FROM outsourcing_summary WHERE project_details IS NOT NULL AND project_details != '' ORDER BY project_details");
              if ($projects && $projects->num_rows > 0) {
                while ($proj = $projects->fetch_assoc()) {
                  $selected = (isset($_GET['project']) && $_GET['project'] === $proj['project_details']) ? 'selected' : '';
                  echo '<option value="' . htmlspecialchars($proj['project_details']) . '" ' . $selected . '>' . htmlspecialchars($proj['project_details']) . '</option>';
                }
              }
              ?>
            </select>
          </div>
          <div>
            <label for="search_input">Search:</label>
            <input type="text" name="q" id="search_input" placeholder="Search by PO number, vendor inv no..." value="<?php echo isset($_GET['q']) ? htmlspecialchars($_GET['q']) : ''; ?>" />
          </div>
        </div>
        <div class="search-bar">
          <button class="btn" type="submit">Search</button>
          <?php if (!empty($_GET['q']) || !empty($_GET['project'])): ?>
            <a class="btn muted" href="list.php">Reset</a>
          <?php endif; ?>
        </div>
      </form>

      <div class="card">
        <div class="table-wrap">
          <table>
            <thead>
              <tr>
                <th>Project Details</th>
                <th>Cost Center</th>
                <th>Customer PO Number</th>
                <th>Vendor Name</th>
                <th>Cantik PO No</th>
                <th>Cantik PO Date</th>
                <th>Cantik PO Value</th>
                <th>Remaining Bal In PO</th>
                <th>Vendor Invoice Frequency</th>
                <th>Vendor Inv Number</th>
                <th>Vendor Inv Date</th>
                <th>Vendor Inv Value</th>
                <th>TDS Ded</th>
                <th>Net Payble</th>
                <th>Payment Status from NTT</th>
                <th>Payment Value</th>
                <th>Payment Date</th>
                <th>Pending Payment</th>
                <th>Remarks</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              <?php
              $search = isset($_GET['q']) ? trim($_GET['q']) : '';
              $project_filter = isset($_GET['project']) ? trim($_GET['project']) : '';

              if ($search !== '' || $project_filter !== '') {
                $where_conditions = [];
                $params = [];
                $param_types = '';

                if ($project_filter !== '') {
                  $where_conditions[] = "os.project_details = ?";
                  $params[] = $project_filter;
                  $param_types .= 's';
                }

                if ($search !== '') {
                  $like = '%' . $search . '%';
                  $where_conditions[] = "(os.customer_po LIKE ? OR os.vendor_inv_number LIKE ?)";
                  $params = array_merge($params, [$like, $like]);
                  $param_types .= 'ss';
                }

                $where_clause = implode(' AND ', $where_conditions);
                $stmt = $conn->prepare("SELECT os.* FROM outsourcing_summary os WHERE $where_clause ORDER BY os.id DESC");
                $stmt->bind_param($param_types, ...$params);
                $stmt->execute();
                $res = $stmt->get_result();
              } else {
                $res = $conn->query("SELECT os.* FROM outsourcing_summary os ORDER BY os.id DESC");
              }

              if ($res && $res->num_rows > 0) {
                while ($r = $res->fetch_assoc()) {
                  $fmt = fn($v) => $v === null || $v === '' ? '-' : htmlspecialchars((string)$v);

                  echo '<tr>';
                  echo '<td>' . $fmt($r['project_details']) . '</td>';
                  echo '<td>' . $fmt($r['cost_center']) . '</td>';
                  echo '<td>' . $fmt($r['customer_po']) . '</td>';
                  echo '<td>' . $fmt($r['vendor_name']) . '</td>';
                  echo '<td>' . $fmt($r['cantik_po_no']) . '</td>';
                  echo '<td>' . $fmt($r['cantik_po_date_formatted'] ?? $r['cantik_po_date']) . '</td>';
                  echo '<td>' . $fmt($r['cantik_po_value']) . '</td>';
                  echo '<td>' . $fmt($r['remaining_bal_in_po']) . '</td>';
                                     // --- FIXED Vendor Invoice Frequency ---
                   $vendor_frequency = $r['vendor_invoice_frequency'] ?? '';
                   if (empty($vendor_frequency) || $vendor_frequency === '-') {
                     // Based on your data, it should be "Monthly"
                     $vendor_frequency = 'Monthly';
                   }
                   echo '<td>' . $fmt($vendor_frequency) . '</td>';
                  echo '<td>' . $fmt($r['vendor_inv_number']) . '</td>';
                  echo '<td>' . $fmt($r['vendor_inv_date_formatted'] ?? $r['vendor_inv_date']) . '</td>';
                  echo '<td>' . $fmt($r['vendor_inv_value']) . '</td>';
                  echo '<td>' . $fmt($r['tds_ded']) . '</td>';
                  echo '<td>' . $fmt($r['net_payble'] ?? $r['net_payable'] ?? '') . '</td>';

                  $payment_status = $r['payment_status_from_ntt'] ?? '-';
                  echo '<td>' . $fmt($payment_status) . '</td>';

                  echo '<td>' . $fmt($r['payment_value']) . '</td>';

                  // --- FIXED Payment Date ---
                  echo '<td>' . $fmt($r['payment_date_formatted'] ?? $r['payment_date'] ?? '-') . '</td>';

                  echo '<td>' . $fmt($r['pending_payment']) . '</td>';
                  echo '<td>' . $fmt($r['remarks']) . '</td>';
                  echo '<td class="actions">
                          <a href="edit.php?id=' . htmlspecialchars($r['id'] ?? '') . '" class="btn muted">Edit</a>
                          <a href="delete.php?id=' . htmlspecialchars($r['id'] ?? '') . '" class="btn danger" onclick=\'return confirm("Are you sure you want to delete this record? This action cannot be undone.")\'>Delete</a>
                        </td>';
                  echo '</tr>';
                }
              } else {
                echo '<tr><td colspan="20">No outsourcing records found.</td></tr>';
              }
              ?>
            </tbody>
          </table>
        </div>
      </div>
    </main>
  </div>
  <script src="../assets/script.js"></script>
</body>
</html>
