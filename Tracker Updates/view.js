document.addEventListener('DOMContentLoaded', function() {
	const tableBody = document.getElementById('tasksTableBody');
	const tasksCountEl = document.getElementById('tasksCount');

	// Add form submission handler for edit form
	document.getElementById('editTaskForm').addEventListener('submit', function(e) {
		e.preventDefault();
		submitEdit();
	});

	function formatDate(dateString) {
		const date = new Date(dateString);
		return isNaN(date) ? '-' : date.toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' });
	}

	function formatDateTime(dateString) {
		const date = new Date(dateString);
		return isNaN(date) ? '-' : date.toLocaleString('en-US', { year: 'numeric', month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit' });
	}

	function renderTasks(list) {
		if (!tableBody) return;
		if (!list || list.length === 0) {
			tableBody.innerHTML = '<tr><td class="empty-state" colspan="10">No tasks found</td></tr>';
			if (tasksCountEl) tasksCountEl.textContent = '0';
			return;
		}
		tableBody.innerHTML = list.map(task => {
			return `
				<tr>
					<td>${task.action_req_by}</td>
					<td>${formatDate(task.request_date)}</td>
					<td>${task.cost_center}</td>
					<td>${task.action_req}</td>
					<td>${task.action_owner}</td>
					<td><span class="status-badge status-${(task.status||'').toLowerCase()}">${task.status||'-'}</span></td>
					<td>${task.completion_date ? formatDate(task.completion_date) : '-'}</td>
					<td>${task.remark || '-'}</td>
					<td>${formatDateTime(task.created_at)}</td>
					<td class="table-actions">
						<button onclick="editTask(${task.id}, '${task.action_req_by}', '${task.request_date}', '${task.cost_center}', '${(task.action_req||'').replace(/'/g, "&#39;")}', '${task.action_owner}', '${task.status}', '${task.completion_date||''}', '${(task.remark||'').replace(/'/g, "&#39;")}')" class="btn-edit">✏️ Edit</button>
						<button class="btn-delete" onclick="deleteTask(${task.id})">🗑️ Delete</button>
					</td>
				</tr>
			`;
		}).join('');
		if (tasksCountEl) tasksCountEl.textContent = String(list.length);
	}

	function loadAllTasks() {
		if (!tableBody) return;
		tableBody.innerHTML = '<tr><td colspan="10" class="empty-state">Loading tasks...</td></tr>';
		fetch('process.php?action=get_tasks&limit=all')
			.then(r => r.json())
			.then(data => {
				if (!data.success) {
					tableBody.innerHTML = '<tr><td colspan="10" class="empty-state">' + (data.message || 'Error loading tasks') + '</td></tr>';
					if (tasksCountEl) tasksCountEl.textContent = '0';
					return;
				}
				window._allTasks = data.tasks || [];
				window._renderTasks = renderTasks;
				if (window._applyTaskFilters) window._applyTaskFilters(); else renderTasks(window._allTasks);
			})
			.catch(err => {
				console.error(err);
				tableBody.innerHTML = '<tr><td colspan="10" class="empty-state">Error loading tasks</td></tr>';
				if (tasksCountEl) tasksCountEl.textContent = '0';
			});
	}

	// Expose edit/delete handlers
	window.editTask = function(id, actionReqBy, requestDate, costCenter, actionReq, actionOwner, status, completionDate, remark) {
		document.getElementById('editTaskId').value = id;
		document.getElementById('editActionReqBy').value = actionReqBy;
		document.getElementById('editRequestDate').value = requestDate;
		document.getElementById('editCostCenter').value = costCenter;
		document.getElementById('editActionReq').value = actionReq;
		document.getElementById('editActionOwner').value = actionOwner;
		document.getElementById('editStatus').value = status;
		document.getElementById('editCompletionDate').value = completionDate || '';
		document.getElementById('editRemark').value = remark || '';
		document.getElementById('editForm').style.display = 'block';
	};

	window.closeEditModal = function() {
		document.getElementById('editForm').style.display = 'none';
	};

	window.submitEdit = function() {
		const formData = new FormData(document.getElementById('editTaskForm'));
		const id = document.getElementById('editTaskId').value;
		
		// Convert FormData to URL-encoded string
		const data = new URLSearchParams();
		data.append('id', id);
		for (let [key, value] of formData.entries()) {
			data.append(key, value);
		}

		fetch('update_task.php', {
			method: 'POST',
			headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
			body: data.toString()
		})
		.then(r => r.json())
		.then(data => {
			alert(data.message || 'Updated');
			closeEditModal();
			loadAllTasks();
		})
		.catch(err => {
			console.error(err);
			alert('Error updating task');
		});
	};

	window.deleteTask = function(id) {
		if (!confirm('Are you sure you want to delete this task?')) return;
		fetch('delete_task.php', {
			method: 'POST',
			headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
			body: `id=${encodeURIComponent(id)}`
		})
		.then(r => r.json())
		.then(data => {
			alert(data.message || 'Deleted');
			loadAllTasks();
		})
		.catch(err => {
			console.error(err);
			alert('Error deleting task');
		});
	};

	loadAllTasks();
});

