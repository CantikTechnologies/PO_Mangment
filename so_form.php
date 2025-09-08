<?php 
session_start();
if (!isset($_SESSION['username'])) {
  header('Location: 1Login_signuppage/login.php');
  exit();
}
include 'db.php'; 
?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <title>SO Form Report</title>
  <link rel="stylesheet" href="assets/style.css?v=<?php echo time(); ?>">
</head>
<body>
  <div class="container so-form-container">
    <?php include 'shared/nav.php'; ?>
    <main>
      <div class="page-header">
        <h2>SO Form - Summary Report</h2>
        <a href="index.php" class="btn">Back</a>
      </div>

      <form method="get" class="card" style="margin: 16px 0;">
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 16px;">
          <div>
            <label for="project_filter">Filter by Project:</label>
            <select name="project" id="project_filter" onchange="this.form.submit()">
              <option value="">All Projects</option>
              <?php
              $projects = $conn->query("SELECT DISTINCT project_description AS project FROM po_summary WHERE project_description IS NOT NULL AND project_description != '' ORDER BY project_description");
              if ($projects && $projects->num_rows > 0) {
                while ($proj = $projects->fetch_assoc()) {
                  $selected = (isset($_GET['project']) && $_GET['project'] === $proj['project']) ? 'selected' : '';
                  echo '<option value="' . htmlspecialchars($proj['project']) . '" ' . $selected . '>' . htmlspecialchars($proj['project']) . '</option>';
                }
              }
              ?>
            </select>
          </div>
          <div>
            <label for="search_input">Search:</label>
            <input type="text" name="q" id="search_input" placeholder="Search by Cost Centre, Customer PO, Vendor, Vendor PO..." value="<?php echo isset($_GET['q']) ? htmlspecialchars($_GET['q']) : ''; ?>" />
          </div>
        </div>
        <div class="search-bar">
          <button class="btn" type="submit">Search</button>
          <?php if (!empty($_GET['q']) || !empty($_GET['project'])): ?>
            <a class="btn muted" href="so_form.php">Reset</a>
          <?php endif; ?>
        </div>
      </form>

      <div class="card">
        <div class="table-wrap">
          <table>
            <thead>
                             <tr>
                 <th>Project Description</th>
                 <th>Cost Centre</th>
                 <th>Customer PO No</th>
                 <th>Customer PO Value</th>
                 <th>Billed Till Date</th>
                 <th>Remaining Balance (PO)</th>
                 <th>Vendor Name</th>
                 <th>Vendor PO No</th>
                 <th>Vendor PO Value</th>
                 <th>Vendor Invoicing Till Date</th>
                 <th>Remaining Vendor Balance</th>
                 <th>Sale Margin (%)</th>
                 <th>Target GM (%)</th>
                 <th>Variance GM (%)</th>
               </tr>
            </thead>
            <tbody>
            <?php
            $res = null;
            $search = isset($_GET['q']) ? trim($_GET['q']) : '';
            $project_filter = isset($_GET['project']) ? trim($_GET['project']) : '';

            $where_sql = '';
            $params = [];
            $param_types = '';

            if ($project_filter !== '') {
              $where_sql .= ($where_sql ? ' AND ' : ' WHERE ') . 'ps.project_description = ?';
              $params[] = $project_filter;
              $param_types .= 's';
            }
            if ($search !== '') {
              $like = '%' . $search . '%';
              $where_sql .= ($where_sql ? ' AND ' : ' WHERE ') . '(ps.cost_center LIKE ? OR ps.po_number LIKE ? OR ps.vendor_name LIKE ?)';
              $params = array_merge($params, [$like, $like, $like]);
              $param_types .= 'sss';
            }

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
              FROM po_summary ps
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
              $stmt->bind_param($param_types, ...$params);
              $stmt->execute();
              $res = $stmt->get_result();
            } else {
              $res = $conn->query($sql);
            }

            if ($res && $res->num_rows > 0) {
              while ($r = $res->fetch_assoc()) {
                $num = function ($v) { return is_numeric($v) ? number_format((float)$v, 2) : '-'; };
                echo "<tr>";
                echo "<td>".htmlspecialchars($r['project'] ?? '')."</td>";
                echo "<td>".htmlspecialchars($r['cost_center'] ?? '')."</td>";
                echo "<td>".htmlspecialchars($r['customer_po_no'] ?? '')."</td>";
                echo "<td>".$num($r['customer_po_value'] ?? null)."</td>";
                echo "<td>".$num($r['billed_till_date'] ?? null)."</td>";
                echo "<td>".$num($r['remaining_balance_po'] ?? null)."</td>";
                echo "<td>".htmlspecialchars($r['vendor_name'] ?? '')."</td>";
                echo "<td>".htmlspecialchars($r['cantik_po_no'] ?? '')."</td>";
                echo "<td>".$num($r['vendor_po_value'] ?? null)."</td>";
                echo "<td>".$num($r['vendor_invoicing_till_date'] ?? null)."</td>";
                echo "<td>".$num($r['remaining_balance_in_po'] ?? null)."</td>";
                echo "<td>".$num($r['margin_till_date'] ?? null)."%</td>";
                echo "<td>".$num($r['target_gm'] ?? null)."%</td>";
                echo "<td>".$num($r['variance_in_gm'] ?? null)."%</td>";
                echo "</tr>";
              }
            } else {
              echo "<tr><td colspan='14'>No records found</td></tr>";
            }
            ?>
            </tbody>
          </table>
        </div>
      </div>
    </main>
  </div>
</body>
</html>
