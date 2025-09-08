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
  <title>PO Details</title>
  <link rel="stylesheet" href="../assets/style.css?v=<?php echo time(); ?>">
</head>
<body>
  <div class="container">
    <?php include '../shared/nav.php'; ?>
    <main>
      <div class="page-header">
        <h1>Purchase Orders</h1>
        <a class="btn" href="add.php">+ Add New PO</a>
      </div>

      <form method="get" class="card" style="margin-bottom: 16px;">
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 16px;">
          <div>
            <label for="project_filter">Filter by Project:</label>
            <select name="project" id="project_filter" onchange="this.form.submit()">
              <option value="">All Projects</option>
              <?php
              $projects = $conn->query("SELECT DISTINCT project_description FROM po_summary WHERE project_description IS NOT NULL AND project_description != '' ORDER BY project_description");
              if ($projects && $projects->num_rows > 0) {
                while ($proj = $projects->fetch_assoc()) {
                  $selected = (isset($_GET['project']) && $_GET['project'] === $proj['project_description']) ? 'selected' : '';
                  echo '<option value="' . htmlspecialchars($proj['project_description']) . '" ' . $selected . '>' . htmlspecialchars($proj['project_description']) . '</option>';
                }
              }
              ?>
            </select>
          </div>
          <div>
            <label for="search_input">Search:</label>
            <input type="text" name="q" id="search_input" placeholder="Search by PO number, project, vendor, status..." value="<?php echo isset($_GET['q']) ? htmlspecialchars($_GET['q']) : ''; ?>" />
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
                <th>Project Description</th>
                <th>Cost Center</th>
                <th>SOW Number</th>
                <th>Start Date</th>
                <th>End Date</th>
                <th>PO Number</th>
                <th>PO Date</th>
                <th>PO Value</th>
                <th>Billing Frequency</th>
                <th>Target GM</th>
                <th>Pending Amount in PO</th>
                <th>PO Status</th>
                <th>Remarks</th>
                <th>Vendor Name</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              <?php
              $res = null;
              $search = isset($_GET['q']) ? trim($_GET['q']) : '';
              $project_filter = isset($_GET['project']) ? trim($_GET['project']) : '';

              if ($search !== '' || $project_filter !== '') {
                $where_conditions = [];
                $params = [];
                $param_types = '';

                if ($project_filter !== '') {
                  $where_conditions[] = "ps.project_description = ?";
                  $params[] = $project_filter;
                  $param_types .= 's';
                }

                if ($search !== '') {
                  $like = '%' . $search . '%';
                  $where_conditions[] = "(ps.po_number LIKE ? OR ps.project_description LIKE ? OR ps.vendor_name LIKE ? OR ps.cost_center LIKE ? OR ps.po_status LIKE ?)";
                  $params = array_merge($params, [$like, $like, $like, $like, $like]);
                  $param_types .= 'sssss';
                }

                $where_clause = implode(' AND ', $where_conditions);
                $stmt = $conn->prepare(
                  "SELECT ps.*
                   FROM po_summary ps
                   WHERE $where_clause
                   ORDER BY ps.id DESC"
                );
                $stmt->bind_param($param_types, ...$params);
                $stmt->execute();
                $res = $stmt->get_result();
              } else {
                $res = $conn->query(
                  "SELECT ps.* FROM po_summary ps ORDER BY ps.id DESC"
                );
              }

              if ($res && $res->num_rows > 0) {
                while ($row = $res->fetch_assoc()) {
                  $fmt = fn($v) => $v === null || $v === '' ? '-' : htmlspecialchars((string)$v);
                  echo '<tr>';
                  echo '<td>'.$fmt($row['project_description'] ?? '').'</td>';
                  echo '<td>'.$fmt($row['cost_center'] ?? '').'</td>';
                  echo '<td>'.$fmt($row['sow_number'] ?? '').'</td>';
                  echo '<td>'.$fmt($row['start_date'] ?? '').'</td>';
                  echo '<td>'.$fmt($row['end_date'] ?? '').'</td>';
                  echo '<td>'.$fmt($row['po_number'] ?? '').'</td>';
                  echo '<td>'.$fmt($row['po_date'] ?? '').'</td>';
                  echo '<td>'.$fmt($row['po_value'] ?? '').'</td>';
                  echo '<td>'.$fmt($row['billing_frequency'] ?? '').'</td>';
                  echo '<td>' . (
                    is_numeric($row['target_gm'] ?? null)
                      ? rtrim(rtrim(number_format((float)$row['target_gm'] * 100, 2), '0'), '.') . '%'
                      : $fmt($row['target_gm'] ?? '')
                  ) . '</td>';
                  echo '<td>'.$fmt($row['pending_amount'] ?? '').'</td>';
                  echo '<td>'.$fmt($row['po_status'] ?? '').'</td>';
                  echo '<td>'.$fmt($row['remarks'] ?? '').'</td>';
                  echo '<td>'.$fmt($row['vendor_name'] ?? '').'</td>';
                  echo '<td class="actions">
                          <a href="edit.php?id=' . htmlspecialchars((string)($row['po_id'] ?? '')) . '" class="btn muted">Edit</a>
                          <a href="delete.php?id=' . htmlspecialchars((string)($row['po_id'] ?? '')) . '" class="btn danger" onclick="return confirm(\'Are you sure you want to delete this PO? This action cannot be undone.\')">Delete</a>
                        </td>';
                  echo '</tr>';
                }
              } else {
                echo '<tr><td colspan="15">No purchase orders found.</td></tr>';
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
