<?php
session_start();
// Use the app's existing database connection
include_once '../db.php'; // provides $conn (mysqli)

if (!$conn) {
  die('Database connection not available.');
}

// Ensure status_of_action defaults to 'Pending' when NULL/empty
if ($conn->query("UPDATE finance_tasks SET status_of_action = 'Pending' WHERE status_of_action IS NULL OR status_of_action = ''") === false) {
  error_log('Failed to normalize status_of_action: ' . $conn->error);
}

// Fetch all tracker updates
$trackers = [];
if ($res = $conn->query("SELECT * FROM finance_tasks ORDER BY request_date DESC, created_at DESC")) {
  while ($row = $res->fetch_assoc()) {
    $trackers[] = $row;
  }
  $res->free();
}

// Calculate statistics
$total_trackers = count($trackers);
$completed_trackers = 0;
$pending_trackers = 0;
$in_progress_trackers = 0;
foreach ($trackers as $t) {
  $status = $t['status_of_action'] ?? '';
  if ($status === 'Completed') $completed_trackers++;
  elseif ($status === 'Pending') $pending_trackers++;
  elseif ($status === 'In Progress') $in_progress_trackers++;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tracker Updates - PO Management</title>
    <link rel="stylesheet" href="tracker_updates_new.css?v=1">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <?php include '../shared/nav.php'; ?>  
    <div class="dashboard-container">
        <!-- Header -->
        <div class="header">
            <div class="header-content">
                <div class="header-info">
                    <h1 class="header-title">Tracker Updates</h1>
                    <p class="header-subtitle">Monitor and manage all your finance tasks</p>
                </div>
                <button class="btn-primary" id="addTrackerBtn">
                    <span class="btn-icon">&#10133;</span>
                    Add New Task
                </button>
            </div>
        </div>

        <!-- Statistics Cards -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-header">
                    <span class="stat-title">Total Tasks</span>
                    <span class="stat-icon">&#128202;</span>
                </div>
                <div class="stat-content">
                    <div class="stat-value" id="totalTrackers"><?= $total_trackers ?></div>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-header">
                    <span class="stat-title">Completed</span>
                    <span class="stat-icon">&#9989;</span>
                </div>
                <div class="stat-content">
                    <div class="stat-value" id="completedTrackers"><?= $completed_trackers ?></div>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-header">
                    <span class="stat-title">Pending</span>
                    <span class="stat-icon">&#9203;</span>
                </div>
                <div class="stat-content">
                    <div class="stat-value" id="pendingTrackers"><?= $pending_trackers ?></div>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-header">
                    <span class="stat-title">In Progress</span>
                    <span class="stat-icon">&#128295;</span>
                </div>
                <div class="stat-content">
                    <div class="stat-value" id="inProgressTrackers"><?= $in_progress_trackers ?></div>
                </div>
            </div>
        </div>

        <!-- Tracker Form Modal -->
        <div class="modal" id="trackerFormModal" style="display: none;">
            <div class="modal-content">
                <div class="modal-header">
                    <h2>Add New Task</h2>
                    <button class="modal-close" id="closeModal">&times;</button>
                </div>
                <form id="trackerForm" class="tracker-form">
                    <input type="hidden" name="id" id="trackerId">
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="action_requested_by">Action Requested By *</label>
                            <input type="text" id="action_requested_by" name="action_requested_by" required placeholder="e.g., Naveen, Maneesh">
                        </div>
                        <div class="form-group">
                            <label for="request_date">Request Date *</label>
                            <input type="date" id="request_date" name="request_date" required>
                        </div>
                        <div class="form-group">
                            <label for="cost_center">Cost Center *</label>
                            <input type="text" id="cost_center" name="cost_center" required placeholder="e.g., Raptokos - PT, BMW-OA">
                        </div>
                        <div class="form-group">
                            <label for="action_owner">Action Owner *</label>
                            <input type="text" id="action_owner" name="action_owner" required placeholder="e.g., Sanjay, Sneha">
                        </div>
                        <div class="form-group form-group-full">
                            <label for="action_required">Action Required *</label>
                            <textarea id="action_required" name="action_required" rows="3" required placeholder="Describe the action required..."></textarea>
                        </div>
                        <div class="form-group">
                            <label for="status_of_action">Status of Action</label>
                            <select id="status_of_action" name="status_of_action">
                                <option value="Pending" selected>Pending</option>
                                <option value="In Progress">In Progress</option>
                                <option value="Completed">Completed</option>
                                <option value="On Hold">On Hold</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="completion_date">Completion Date</label>
                            <input type="date" id="completion_date" name="completion_date">
                        </div>
                        <div class="form-group form-group-full">
                            <label for="remark">Remark</label>
                            <textarea id="remark" name="remark" rows="3" placeholder="Add any additional notes or remarks..."></textarea>
                        </div>
                    </div>
                    
                    <div class="form-actions">
                        <button type="button" class="btn-secondary" id="cancelBtn">Cancel</button>
                        <button type="submit" class="btn-primary">Save Task</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Tracker Table Section -->
        <div class="table-section">
            <div class="table-header">
                <div class="table-title">
                    <span class="table-icon">&#128202;</span>
                    <h3>All Tasks</h3>
                </div>
                <p class="table-subtitle">Manage and track all your finance tasks in one place</p>
            </div>
            
            <div class="table-filters">
                <div class="search-box">
                    <span class="search-icon">&#128269;</span>
                    <input type="text" id="searchInput" placeholder="Search tasks..." 
                           class="search-input">
                </div>
                
                <select id="statusFilter" class="filter-select">
                    <option value="">All Statuses</option>
                    <option value="Pending">Pending</option>
                    <option value="In Progress">In Progress</option>
                    <option value="Completed">Completed</option>
                    <option value="On Hold">On Hold</option>
                </select>
            </div>
            
            <div class="table-container">
                <table class="tracker-table">
                    <thead>
                        <tr>
                            <th>Requested By</th>
                            <th>Request Date</th>
                            <th>Cost Center</th>
                            <th>Action Required</th>
                            <th>Owner</th>
                            <th>Status</th>
                            <th>Completion Date</th>
                            <th>Remark</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="trackerTableBody">
                        <!-- Tracker rows will be populated here by JavaScript -->
                    </tbody>
                </table>
                
                <div id="noDataMessage" class="no-data" style="display: none;">
                    No tasks found matching your criteria
                </div>
            </div>
        </div>
    </div>

    <script src="tracker_updates_new.js?v=<?php echo time(); ?>"></script>
</body>
</html>