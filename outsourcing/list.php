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
  <title>Outsourcing - Cantik Homemade</title>
  <meta name="description" content="Manage and track all your outsourcing records efficiently">
</head>
<body>
  <div class="container">
    <?php include '../shared/nav.php'; ?>
    
    <main>
      <!-- Page Header -->
      <div class="page-header">
        <div>
          <h1>Outsourcing</h1>
          <p>Manage and track all your outsourcing records</p>
        </div>
        <a href="add.php" class="btn btn-primary">
          <i class="fas fa-plus"></i>
          Add New Record
        </a>
      </div>

      <!-- Search and Filters -->
      <div class="card search-filters">
        <div class="card-header">
          <h2 class="card-title">
            <i class="fas fa-filter"></i>
            Filters
          </h2>
          <p class="card-description">Filter outsourcing records by project or search terms</p>
        </div>
        <div class="card-content">
          <form method="get" class="filter-grid">
            <div class="form-field">
              <label for="search_input">Search</label>
              <div class="search-input-wrapper">
                <i class="fas fa-search search-icon"></i>
                <input type="text" name="q" id="search_input" class="search-input" 
                       placeholder="Search project, vendor..." 
                       value="<?php echo isset($_GET['q']) ? htmlspecialchars($_GET['q']) : ''; ?>" />
              </div>
            </div>
            
            <div class="form-field">
              <label for="project_filter">Project</label>
              <select name="project" id="project_filter">
                <option value="">All Projects</option>
                <?php
                $projects = $conn->query("SELECT DISTINCT project_description FROM outsourcing_details WHERE project_description IS NOT NULL AND project_description != '' ORDER BY project_description");
                if ($projects && $projects->num_rows > 0) {
                  while ($proj = $projects->fetch_assoc()) {
                    $selected = (isset($_GET['project']) && $_GET['project'] === $proj['project_description']) ? 'selected' : '';
                    echo '<option value="' . htmlspecialchars($proj['project_description']) . '" ' . $selected . '>' . htmlspecialchars($proj['project_description']) . '</option>';
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
              <?php if (!empty($_GET['q']) || !empty($_GET['project'])): ?>
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
            <h2 class="card-title">Outsourcing Records</h2>
            <?php if (!empty($_GET['q']) || !empty($_GET['project'])): ?>
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
                  <th>Vendor Name</th>
                  <th>Work Description</th>
                  <th>Start Date</th>
                  <th>End Date</th>
                  <th class="text-right">Total Amount</th>
                  <th class="text-right">Pending Payment</th>
                  <th class="text-right">Actions</th>
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
                    $where_conditions[] = "project_description = ?";
                    $params[] = $project_filter;
                    $param_types .= 's';
                  }

                  if ($search !== '') {
                    $like = '%' . $search . '%';
                    $where_conditions[] = "(project_description LIKE ? OR vendor_name LIKE ? OR work_description LIKE ?)";
                    $params = array_merge($params, [$like, $like, $like]);
                    $param_types .= 'sss';
                  }

                  $where_clause = implode(' AND ', $where_conditions);
                  $stmt = $conn->prepare("SELECT * FROM outsourcing_details WHERE $where_clause ORDER BY id DESC");
                  $stmt->bind_param($param_types, ...$params);
                  $stmt->execute();
                  $res = $stmt->get_result();
                } else {
                  $res = $conn->query("SELECT * FROM outsourcing_details ORDER BY id DESC");
                }

                function formatCurrency($amount) {
                  return '₹ ' . number_format($amount, 0, '.', ',');
                }

                if ($res && $res->num_rows > 0) {
                  while ($row = $res->fetch_assoc()) {
                    $fmt = fn($v) => $v === null || $v === '' ? '-' : htmlspecialchars((string)$v);
                    echo '<tr>';
                    echo '<td class="font-medium">'.$fmt($row['project_description'] ?? '').'</td>';
                    echo '<td>'.$fmt($row['vendor_name'] ?? '').'</td>';
                    echo '<td>'.$fmt($row['work_description'] ?? '').'</td>';
                    echo '<td>'.($row['start_date'] ? date('M d, Y', strtotime($row['start_date'])) : '-').'</td>';
                    echo '<td>'.($row['end_date'] ? date('M d, Y', strtotime($row['end_date'])) : '-').'</td>';
                    echo '<td class="text-right font-medium">'.formatCurrency($row['total_amount'] ?? 0).'</td>';
                    echo '<td class="text-right font-medium">'.formatCurrency($row['pending_payment'] ?? 0).'</td>';
                    echo '<td class="table-actions">
                            <a href="edit.php?id=' . htmlspecialchars((string)($row['id'] ?? '')) . '" class="btn btn-sm btn-outline">
                              <i class="fas fa-edit"></i>
                            </a>
                            <a href="delete.php?id=' . htmlspecialchars((string)($row['id'] ?? '')) . '" 
                               class="btn btn-sm btn-danger" 
                               onclick="return confirm(\'Are you sure you want to delete this record? This action cannot be undone.\')">
                              <i class="fas fa-trash"></i>
                            </a>
                          </td>';
                    echo '</tr>';
                  }
                } else {
                  echo '<tr><td colspan="8" class="text-center muted-text" style="padding: 2rem;">No outsourcing records found matching your criteria.</td></tr>';
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