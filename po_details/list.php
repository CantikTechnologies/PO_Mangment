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
  <title>Purchase Orders - Cantik Homemade</title>
  <meta name="description" content="Manage and track all your purchase orders efficiently">
</head>
<body>
  <div class="container">
    <?php include '../shared/nav.php'; ?>
    
    <main>
      <!-- Page Header -->
      <div class="page-header">
        <div>
          <h1>Purchase Orders</h1>
          <p>Manage and track all your purchase orders</p>
        </div>
        <a href="add.php" class="btn btn-primary">
          <i class="fas fa-plus"></i>
          Add New PO
        </a>
      </div>

      <!-- Search and Filters -->
      <div class="card search-filters">
        <div class="card-header">
          <h2 class="card-title">
            <i class="fas fa-filter"></i>
            Filters
          </h2>
          <p class="card-description">Filter purchase orders by project, status, or search terms</p>
        </div>
        <div class="card-content">
          <form method="get" class="filter-grid">
            <div class="form-field">
              <label for="search_input">Search</label>
              <div class="search-input-wrapper">
                <i class="fas fa-search search-icon"></i>
                <input type="text" name="q" id="search_input" class="search-input" 
                       placeholder="Search PO number, project, vendor..." 
                       value="<?php echo isset($_GET['q']) ? htmlspecialchars($_GET['q']) : ''; ?>" />
              </div>
            </div>
            
            <div class="form-field">
              <label for="project_filter">Project</label>
              <select name="project" id="project_filter">
                <option value="">All Projects</option>
                <?php
                $projects = $conn->query("SELECT DISTINCT project_description FROM purchase_orders WHERE project_description IS NOT NULL AND project_description != '' ORDER BY project_description");
                if ($projects && $projects->num_rows > 0) {
                  while ($proj = $projects->fetch_assoc()) {
                    $selected = (isset($_GET['project']) && $_GET['project'] === $proj['project_description']) ? 'selected' : '';
                    echo '<option value="' . htmlspecialchars($proj['project_description']) . '" ' . $selected . '>' . htmlspecialchars($proj['project_description']) . '</option>';
                  }
                }
                ?>
              </select>
            </div>

            <div class="form-field">
              <label for="status_filter">Status</label>
              <select name="status" id="status_filter">
                <option value="">All Statuses</option>
                <?php
                $statuses = $conn->query("SELECT DISTINCT po_status FROM purchase_orders WHERE po_status IS NOT NULL AND po_status != '' ORDER BY po_status");
                if ($statuses && $statuses->num_rows > 0) {
                  while ($status = $statuses->fetch_assoc()) {
                    $selected = (isset($_GET['status']) && $_GET['status'] === $status['po_status']) ? 'selected' : '';
                    echo '<option value="' . htmlspecialchars($status['po_status']) . '" ' . $selected . '>' . htmlspecialchars($status['po_status']) . '</option>';
                  }
                }
                ?>
              </select>
            </div>

            <div class="form-field" style="display: flex; align-items: end; gap: 0.5rem;">
              <button type="submit" class="btn btn-primary">
                <i class="fas fa-search"></i>
                Search
              </button>
              <?php if (!empty($_GET['q']) || !empty($_GET['project']) || !empty($_GET['status'])): ?>
                <a href="list.php" class="btn btn-outline">
                  <i class="fas fa-times"></i>
                  Clear
                </a>
              <?php endif; ?>
            </div>
          </form>
        </div>
      </div>

      <!-- Results -->
      <div class="card">
        <div class="card-header">
          <div style="display: flex; justify-content: space-between; align-items: center;">
            <h2 class="card-title">Purchase Orders</h2>
            <?php if (!empty($_GET['q']) || !empty($_GET['project']) || !empty($_GET['status'])): ?>
              <div class="badge badge-secondary">
                Filtered Results
              </div>
            <?php endif; ?>
          </div>
        </div>
        <div class="card-content">
          <div class="table-wrap">
            <table>
              <thead>
                <tr>
                  <th>Project Description</th>
                  <th>Cost Center</th>
                  <th>SOW Number</th>
                  <th>PO Number</th>
                  <th>PO Date</th>
                  <th class="text-right">PO Value</th>
                  <th>Billing</th>
                  <th class="text-right">Target GM</th>
                  <th class="text-right">Pending Amount</th>
                  <th>Status</th>
                  <th>Vendor</th>
                  <th class="text-right">Actions</th>
                </tr>
              </thead>
              <tbody>
                <?php
                $res = null;
                $search = isset($_GET['q']) ? trim($_GET['q']) : '';
                $project_filter = isset($_GET['project']) ? trim($_GET['project']) : '';
                $status_filter = isset($_GET['status']) ? trim($_GET['status']) : '';

                if ($search !== '' || $project_filter !== '' || $status_filter !== '') {
                  $where_conditions = [];
                  $params = [];
                  $param_types = '';

                  if ($project_filter !== '') {
                    $where_conditions[] = "po.project_description = ?";
                    $params[] = $project_filter;
                    $param_types .= 's';
                  }

                  if ($status_filter !== '') {
                    $where_conditions[] = "po.po_status = ?";
                    $params[] = $status_filter;
                    $param_types .= 's';
                  }

                  if ($search !== '') {
                    $like = '%' . $search . '%';
                    $where_conditions[] = "(po.po_number LIKE ? OR po.project_description LIKE ? OR po.vendor_name LIKE ? OR po.cost_center LIKE ? OR po.po_status LIKE ?)";
                    $params = array_merge($params, [$like, $like, $like, $like, $like]);
                    $param_types .= 'sssss';
                  }

                  $where_clause = implode(' AND ', $where_conditions);
                  $stmt = $conn->prepare(
                    "SELECT po.*, 
                     COALESCE((SELECT SUM(po_value) FROM purchase_orders WHERE po_id = po.po_id), po.po_value) - 
                     COALESCE((SELECT SUM(receivable) FROM invoices WHERE po_id = po.po_id), 0) as pending_amount
                     FROM purchase_orders po
                     WHERE $where_clause
                     ORDER BY po.po_id DESC"
                  );
                  $stmt->bind_param($param_types, ...$params);
                  $stmt->execute();
                  $res = $stmt->get_result();
                } else {
                  $res = $conn->query(
                    "SELECT po.*, 
                     COALESCE(po.po_value, 0) - COALESCE((SELECT SUM(receivable) FROM invoices WHERE po_id = po.po_id), 0) as pending_amount
                     FROM purchase_orders po 
                     ORDER BY po.po_id DESC"
                  );
                }

                function getBadgeClass($status) {
                  switch (strtolower($status)) {
                    case 'active': return 'badge-accent';
                    case 'completed': return 'badge-primary';
                    case 'pending': return 'badge-warning';
                    case 'cancelled': return 'badge-destructive';
                    default: return 'badge-secondary';
                  }
                }

                function formatCurrency($amount) {
                  return '₹ ' . number_format($amount, 0, '.', ',');
                }

                if ($res && $res->num_rows > 0) {
                  while ($row = $res->fetch_assoc()) {
                    $fmt = fn($v) => $v === null || $v === '' ? '-' : htmlspecialchars((string)$v);
                    echo '<tr>';
                    echo '<td class="font-medium">'.$fmt($row['project_description'] ?? '').'</td>';
                    echo '<td>'.$fmt($row['cost_center'] ?? '').'</td>';
                    echo '<td>'.$fmt($row['sow_number'] ?? '').'</td>';
                    echo '<td>'.$fmt($row['po_number'] ?? '').'</td>';
                    echo '<td>'.($row['po_date'] ? date('M d, Y', strtotime($row['po_date'])) : '-').'</td>';
                    echo '<td class="text-right font-medium">'.formatCurrency($row['po_value'] ?? 0).'</td>';
                    echo '<td>'.$fmt($row['billing_frequency'] ?? '').'</td>';
                    echo '<td class="text-right">' . (
                      is_numeric($row['target_gm'] ?? null)
                        ? rtrim(rtrim(number_format((float)$row['target_gm'] * 100, 2), '0'), '.') . '%'
                        : $fmt($row['target_gm'] ?? '')
                    ) . '</td>';
                    echo '<td class="text-right font-medium">'.formatCurrency($row['pending_amount'] ?? 0).'</td>';
                    echo '<td><span class="badge '.getBadgeClass($row['po_status'] ?? '').'">'.$fmt($row['po_status'] ?? '').'</span></td>';
                    echo '<td>'.$fmt($row['vendor_name'] ?? '').'</td>';
                    echo '<td class="table-actions">
                            <a href="edit.php?id=' . htmlspecialchars((string)($row['po_id'] ?? '')) . '" class="btn btn-sm btn-outline">
                              <i class="fas fa-edit"></i>
                            </a>
                            <a href="delete.php?id=' . htmlspecialchars((string)($row['po_id'] ?? '')) . '" 
                               class="btn btn-sm btn-danger" 
                               onclick="return confirm(\'Are you sure you want to delete this PO? This action cannot be undone.\')">
                              <i class="fas fa-trash"></i>
                            </a>
                          </td>';
                    echo '</tr>';
                  }
                } else {
                  echo '<tr><td colspan="12" class="text-center muted-text" style="padding: 2rem;">No purchase orders found matching your criteria.</td></tr>';
                }
                ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </main>
  </div>
</body>
</html>