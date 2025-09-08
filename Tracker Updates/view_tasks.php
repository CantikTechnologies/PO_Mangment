<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>CANTIK Technology - View Tasks</title>
	<link rel="preconnect" href="https://fonts.googleapis.com">
	<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
	<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
	<link rel="stylesheet" href="styles.css?v=4">
	<style>
		.container { max-width: 1500px; margin: 0 auto; }
		.page-header { display: flex; align-items: center; justify-content: space-between; gap: 1rem; margin: 18px 0 10px; }
		.page-title { font-size: 1.6rem; font-weight: 700; color: #1f2937; margin: 0; }
		.page-subtitle { color: #6b7280; font-size: .95rem; margin-top: 2px; }
		.toolbar { display: flex; flex-wrap: wrap; gap: .75rem; align-items: center; margin: 12px 0 18px; }
		.input, .select { padding: .55rem .7rem; border: 1px solid #e5e7eb; border-radius: 8px; background: #fff; outline: none; font-family: inherit; }
		.input:focus, .select:focus { border-color: #93c5fd; box-shadow: 0 0 0 3px rgba(147,197,253,.35); }
		.toolbar .spacer { flex: 1 1 auto; }
		.summary-pill { background: #f3f4f6; border-radius: 999px; padding: .35rem .65rem; font-size: .85rem; color: #374151; }
		.table-container { background: #fff; border: 1px solid #e5e7eb; border-radius: 12px; box-shadow: 0 8px 20px rgba(0,0,0,.04); }
		.tasks-table { width: 100%; border-collapse: separate; border-spacing: 0; }
		.tasks-table thead th { position: sticky; top: 0; background: #111827; color: #fff; z-index: 2; }
		.tasks-table th, .tasks-table td { padding: 10px 12px; border-bottom: 1px solid #f3f4f6; }
		.tasks-table tbody tr:hover { background: #f9fafb; }
		.tasks-table tbody tr:last-child td { border-bottom: 0; }
		.tasks-table td .status-badge { padding: .25rem .5rem; border-radius: 999px; font-size: .8rem; font-weight: 600; }
		.status-incomplete { background: #fee2e2; color: #991b1b; }
		.status-pending { background: #fef3c7; color: #92400e; }
		.status-complete { background: #dcfce7; color: #065f46; }
		.table-actions { display: flex; gap: .5rem; }
		.empty-state { text-align: center; padding: 28px; color: #6b7280; }
	</style>
</head>
<body>
	<div class="container">
		<header class="navbar">
			<div class="logo">
				<a href="../2dashboard-app/dashboard.php"><img src="cantik_logo.png" alt="Cantik Logo"></a>
			</div>
			<div class="nav-actions">
				<nav class="nav-links">
					<a href="index.php">📝 Add Task</a>
					<a href="view_tasks.php">👀 View Tasks</a>
				</nav>
			</div>
		</header>

		<main>
			<div class="page-header">
				<div>
					<h1 class="page-title">All Tasks</h1>
					<div class="page-subtitle">Browse and manage your tracker updates</div>
				</div>
				<span class="summary-pill"><span id="tasksCount">0</span> items</span>
			</div>

			<div class="toolbar">
				<input id="searchInput" class="input" type="search" placeholder="Search by action, owner, cost center..." aria-label="Search tasks">
				<select id="statusFilter" class="select" aria-label="Filter by status">
					<option value="">All Statuses</option>
					<option value="Complete">✅ Complete</option>
					<option value="Pending">⏸️ Pending</option>
					<option value="Incomplete">⏳ Incomplete</option>
				</select>
				<div class="spacer"></div>
				<button id="refreshBtn" class="btn" onclick="location.reload()" title="Refresh">🔄 Refresh</button>
			</div>

			<div class="table-container">
				<h2 style="color:#2d3748; text-align:center; margin-bottom:10px; font-size: 1.25rem; font-weight: 700; padding-top: 12px;">📊 All Tasks</h2>
				<table class="tasks-table">
					<thead>
						<tr>
							<th>Action Requested By</th>
							<th>Request Date</th>
							<th>Cost Center</th>
							<th>Action Required</th>
							<th>Action Owner</th>
							<th>Status</th>
							<th>Completion Date</th>
							<th>Remark</th>
							<th>Created</th>
							<th>Actions</th>
						</tr>
					</thead>
					<tbody id="tasksTableBody">
						<tr><td colspan="10" class="empty-state">Loading tasks...</td></tr>
					</tbody>
				</table>
			</div>

			<!-- Edit Task Modal/Form -->
			<div id="editForm" class="edit-modal">
				<div class="edit-modal-content">
					<h3>✏️ Edit Task</h3>
					<form id="editTaskForm">
						<input type="hidden" id="editTaskId">
						
						<div class="form-row">
							<div class="form-group">
								<label for="editActionReqBy">Action Requested By *</label>
								<input type="text" id="editActionReqBy" name="actionReqBy" required>
							</div>
							<div class="form-group">
								<label for="editRequestDate">Request Date *</label>
								<input type="date" id="editRequestDate" name="requestDate" required>
							</div>
						</div>

						<div class="form-row">
							<div class="form-group">
								<label for="editCostCenter">Cost Center *</label>
								<input type="text" id="editCostCenter" name="costCenter" required>
							</div>
							<div class="form-group">
								<label for="editActionOwner">Action Owner *</label>
								<input type="text" id="editActionOwner" name="actionOwner" required>
							</div>
						</div>

						<div class="form-group full-width">
							<label for="editActionReq">Action Required *</label>
							<textarea id="editActionReq" name="actionReq" rows="4" required></textarea>
						</div>

						<div class="form-row">
							<div class="form-group">
								<label for="editStatus">Status *</label>
								<select id="editStatus" name="status" required>
									<option value="Incomplete">⏳ Incomplete</option>
									<option value="Pending">⏸️ Pending</option>
									<option value="Complete">✅ Complete</option>
								</select>
							</div>
							<div class="form-group">
								<label for="editCompletionDate">Completion Date</label>
								<input type="date" id="editCompletionDate" name="completionDate">
							</div>
						</div>

						<div class="form-group full-width">
							<label for="editRemark">Remark</label>
							<textarea id="editRemark" name="remark" rows="3"></textarea>
						</div>

						<div class="edit-modal-actions">
							<button type="submit" class="btn-submit">💾 Save Changes</button>
							<button type="button" onclick="closeEditModal()" class="btn-reset">❌ Cancel</button>
						</div>
					</form>
				</div>
			</div>
		</main>
	</div>

	<script src="view.js?v=3"></script>
	<script>
		// Lightweight client-side filter hookup
		document.addEventListener('DOMContentLoaded', function() {
			var search = document.getElementById('searchInput');
			var filter = document.getElementById('statusFilter');
			window._applyTaskFilters = function() {
				if (!window._allTasks) return;
				var q = (search.value || '').toLowerCase();
				var s = filter.value || '';
				var tasks = window._allTasks.filter(function(t) {
					var matchesQ = !q || [t.action_req, t.action_owner, t.cost_center, t.action_req_by].some(function(v){ return (v||'').toLowerCase().includes(q); });
					var matchesS = !s || t.status === s;
					return matchesQ && matchesS;
				});
				if (window._renderTasks) window._renderTasks(tasks);
			};
			['input','change'].forEach(function(evt){ search.addEventListener(evt, window._applyTaskFilters); filter.addEventListener(evt, window._applyTaskFilters); });
		});
	</script>
</body>
</html>

