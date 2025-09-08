document.addEventListener('DOMContentLoaded', function() {     
    const form = document.getElementById('financeTrackerForm');
    const tasksList = document.getElementById('tasksList');

    // ... inside DOMContentLoaded ...
window.editTask = function(id, status) {
    document.getElementById('editTaskId').value = id;
    document.getElementById('editStatus').value = status;
    document.getElementById('editForm').style.display = 'block';
};

window.closeEditModal = function() {
    document.getElementById('editForm').style.display = 'none';
};

window.submitEdit = function() {
    const id = document.getElementById('editTaskId').value;
    const status = document.getElementById('editStatus').value;

    fetch('update_task.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: `id=${encodeURIComponent(id)}&status=${encodeURIComponent(status)}`
    })
    .then(response => response.json())
    .then(data => {
        alert(data.message);
        closeEditModal();
        loadTasks();
    });
};

window.deleteTask = function(id) {
    if (confirm('Are you sure you want to delete this task?')) {
        fetch('delete_task.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: `id=${encodeURIComponent(id)}`
        })
        .then(response => response.json())
        .then(data => {
            alert(data.message);
            loadTasks();
        })
        .catch(error => {
            alert('Error deleting task');
            console.error('Error:', error);
        });
    }
};

    // Load tasks on page load (only if the tasks list exists on this page)
    if (tasksList) {
        loadTasks();
    }

    // Handle form submission
    if (form) {
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(form);
        
        // Show loading state
        const submitBtn = form.querySelector('.btn-submit');
        const originalText = submitBtn.textContent;
        submitBtn.textContent = 'Submitting...';
        submitBtn.disabled = true;

        fetch('process.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showMessage(data.message, 'success');
                form.reset();
                loadTasks(); // Reload tasks after successful submission
            } else {
                showMessage(data.message, 'error');
            }
        })
        .catch(error => {
            showMessage('An error occurred while submitting the form.', 'error');
            console.error('Error:', error);
        })
        .finally(() => {
            submitBtn.textContent = originalText;
            submitBtn.disabled = false;
        });
    });
    }

    // Function to load tasks
    function loadTasks() {
        if (!tasksList) return;
        tasksList.innerHTML = '<div class="loading">Loading tasks...</div>';

        fetch('process.php?action=get_tasks')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    displayTasks(data.tasks);
                } else {
                    tasksList.innerHTML = '<div class="message error">' + data.message + '</div>';
                }
            })
            .catch(error => {
                tasksList.innerHTML = '<div class="message error">Error loading tasks</div>';
                console.error('Error:', error);
            });
    }

    // Function to display tasks
    function displayTasks(tasks) {
        if (tasks.length === 0) {
            tasksList.innerHTML = '<div class="message">No tasks found</div>';
            return;
        }

        const tasksHTML = tasks.map(task => {
            const statusClass = 'status-' + task.status.toLowerCase();
            const completionDate = task.completion_date ? 
                `<strong>Completion Date:</strong> ${formatDate(task.completion_date)}<br>` : '';
            return `
                <div class="task-item">
                    <div class="task-header">
                        <span class="task-date">${formatDate(task.task_date)}</span>
                        <span class="task-status ${statusClass}">${task.status}</span>
                        <button onclick="editTask(${task.id}, '${task.status}')">Edit</button>
                        <button onclick="deleteTask(${task.id})" class="btn-delete">Delete</button>
                    </div>
                    <div class="task-details">
                        <strong>Department:</strong> ${task.emp_dept}<br>
                        <strong>Employee ID:</strong> ${task.emp_id}<br>
                        <strong>Action Required By:</strong> ${task.action_req_by}<br>
                        <strong>Action Required:</strong> ${task.action_req}<br>
                        <strong>Action Owner:</strong> ${task.action_owner}<br>
                        ${completionDate}
                        ${task.remark ? `<strong>Remark:</strong> ${task.remark}<br>` : ''}
                        <strong>Created:</strong> ${formatDateTime(task.created_at)}
                    </div>
                </div>
            `;
        }).join('');

        tasksList.innerHTML = tasksHTML;
    }



    // Function to format date
    function formatDate(dateString) {
        const date = new Date(dateString);
        return date.toLocaleDateString('en-US', {
            year: 'numeric',
            month: 'short',
            day: 'numeric'
        });
    }

    // Function to format date and time
    function formatDateTime(dateString) {
        const date = new Date(dateString);
        return date.toLocaleString('en-US', {
            year: 'numeric',
            month: 'short',
            day: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        });
    }

    // Function to show messages
    function showMessage(message, type) {
        // Remove existing messages
        const existingMessages = document.querySelectorAll('.message');
        existingMessages.forEach(msg => msg.remove());

        // Create new message
        const messageDiv = document.createElement('div');
        messageDiv.className = `message ${type}`;
        messageDiv.textContent = message;

        // Insert message at the top of the body for slider effect
        document.body.appendChild(messageDiv);

        // Trigger reflow and animate in
        setTimeout(() => {
            messageDiv.style.opacity = '1';
            messageDiv.style.top = '40px';
        }, 10);

        // Auto-remove message after 5 seconds with fade out
        setTimeout(() => {
            messageDiv.style.opacity = '0';
            messageDiv.style.top = '20px';
            setTimeout(() => {
                if (messageDiv.parentNode) {
                    messageDiv.remove();
                }
            }, 400);
        }, 5000);
    }

    // Auto-completion date when status is "Complete"
    const statusSelect = document.getElementById('status');
    const completionDateInput = document.getElementById('completionDate');

    if (statusSelect && completionDateInput) {
        statusSelect.addEventListener('change', function() {
            if (this.value === 'Complete') {
                const today = new Date().toISOString().split('T')[0];
                completionDateInput.value = today;
            } else {
                completionDateInput.value = '';
            }
        });
    }

    // Form validation
    if (form) {
        const requiredFields = form.querySelectorAll('[required]');
        requiredFields.forEach(field => {
            field.addEventListener('blur', function() {
                if (!this.value.trim()) {
                    this.style.borderColor = '#c62828';
                } else {
                    this.style.borderColor = '#e0e0e0';
                }
            });

            field.addEventListener('input', function() {
                if (this.value.trim()) {
                    this.style.borderColor = '#e0e0e0';
                }
            });
        });
    }

    // Auto-refresh tasks every 30 seconds
    if (tasksList) {
        setInterval(loadTasks, 30000);
    }
}); 