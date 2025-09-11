document.addEventListener('DOMContentLoaded', function() {
    // Global variables
    let allTrackers = [];
    let filteredTrackers = [];

    // DOM elements
    const addTrackerBtn = document.getElementById('addTrackerBtn');
    const trackerFormModal = document.getElementById('trackerFormModal');
    const closeModal = document.getElementById('closeModal');
    const cancelBtn = document.getElementById('cancelBtn');
    const trackerForm = document.getElementById('trackerForm');
    const searchInput = document.getElementById('searchInput');
    const statusFilter = document.getElementById('statusFilter');
    const trackerTableBody = document.getElementById('trackerTableBody');
    const noDataMessage = document.getElementById('noDataMessage');

    // Statistics elements
    const totalTrackersEl = document.getElementById('totalTrackers');
    const completedTrackersEl = document.getElementById('completedTrackers');
    const pendingTrackersEl = document.getElementById('pendingTrackers');
    const inProgressTrackersEl = document.getElementById('inProgressTrackers');

    // Event listeners
    setupEventListeners();
    loadTrackers();

    function setupEventListeners() {
        // Modal controls
        addTrackerBtn.addEventListener('click', openModal);
        closeModal.addEventListener('click', closeModalHandler);
        cancelBtn.addEventListener('click', closeModalHandler);
        
        // Form submission
        trackerForm.addEventListener('submit', handleFormSubmit);
        
        // Search and filter
        searchInput.addEventListener('input', handleSearch);
        statusFilter.addEventListener('change', handleStatusFilter);
        
        // Close modal on outside click
        trackerFormModal.addEventListener('click', function(e) {
            if (e.target === trackerFormModal) {
                closeModalHandler();
            }
        });
        
        // Close modal on Escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && trackerFormModal.style.display === 'block') {
                closeModalHandler();
            }
        });

        // Open modal if URL has #trackerFormModal on load
        if (window.location.hash === '#trackerFormModal') {
            openModal();
        }

        // Open/close modal on hash change
        window.addEventListener('hashchange', function() {
            if (window.location.hash === '#trackerFormModal') {
                openModal();
            } else if (trackerFormModal.style.display === 'block') {
                closeModalHandler();
            }
        });
    }

    // Modal functions
    function openModal() {
        trackerFormModal.style.display = 'block';
        document.body.style.overflow = 'hidden';
    }

    function closeModalHandler() {
        trackerFormModal.style.display = 'none';
        document.body.style.overflow = 'auto';
        trackerForm.reset();
    }

    // Form handling
    async function handleFormSubmit(e) {
        e.preventDefault();
        
        const formData = new FormData(trackerForm);
        const id = formData.get('id');
        const url = id ? 'process.php?action=update_task' : 'process.php?action=add_task';

        try {
            const response = await fetch(url, { method: 'POST', body: formData });
            const result = await response.json();
            if (result.success) {
                showNotification(result.message, 'success');
                closeModalHandler();
                loadTrackers();
            } else {
                showNotification('Error: ' + result.error, 'error');
            }
        } catch (error) {
            showNotification('Error saving tracker: ' + error.message, 'error');
        }
    }

    // Search and filter functions
    function handleSearch() {
        applyFilters();
    }

    function handleStatusFilter() {
        applyFilters();
    }

    function applyFilters() {
        const searchTerm = searchInput.value.toLowerCase();
        const statusValue = statusFilter.value;
        
        filteredTrackers = allTrackers.filter(tracker => {
            const matchesSearch = !searchTerm || 
                (tracker.action_requested_by && tracker.action_requested_by.toLowerCase().includes(searchTerm)) ||
                (tracker.cost_center && tracker.cost_center.toLowerCase().includes(searchTerm)) ||
                (tracker.action_required && tracker.action_required.toLowerCase().includes(searchTerm)) ||
                (tracker.action_owner && tracker.action_owner.toLowerCase().includes(searchTerm));
            
            const matchesStatus = !statusValue || (tracker.status_of_action && tracker.status_of_action === statusValue);
            
            return matchesSearch && matchesStatus;
        });
        
        renderTrackers();
    }

    // Render functions
    function renderTrackers() {
        if (filteredTrackers.length === 0) {
            trackerTableBody.innerHTML = '';
            noDataMessage.style.display = 'block';
            return;
        }
        
        noDataMessage.style.display = 'none';
        
        trackerTableBody.innerHTML = filteredTrackers.map(tracker => {
            const status = tracker.status_of_action || 'Pending';
            const statusClass = status.replace(' ', '-');
            return `
            <tr>
                <td>${tracker.action_requested_by || ''}</td>
                <td>${formatDate(tracker.request_date)}</td>
                <td>${tracker.cost_center || ''}</td>
                <td>${tracker.action_required || ''}</td>
                <td>${tracker.action_owner || ''}</td>
                <td><span class="status-badge status-${statusClass}">${status}</span></td>
                <td>${formatDate(tracker.completion_date)}</td>
                <td>${tracker.remark || ''}</td>
                <td>
                    <button class="btn-secondary" onclick="editTracker(${tracker.id})">Edit</button>
                    <button class="btn-primary" onclick="deleteTracker(${tracker.id})">Delete</button>
                </td>
            </tr>
        `;
        }).join('');
    }

    async function loadTrackers() {
        try {
            const response = await fetch('get_task.php');
            const result = await response.json();
            if (result.success) {
                allTrackers = result.data;
                filteredTrackers = [...allTrackers];
                renderTrackers();
                updateStatistics();
            } else {
                showNotification('Error loading trackers: ' + result.error, 'error');
            }
        } catch (error) {
            showNotification('Error loading trackers: ' + error.message, 'error');
        }
    }

    function updateStatistics() {
        totalTrackersEl.textContent = allTrackers.length;
        completedTrackersEl.textContent = allTrackers.filter(t => t.status_of_action && t.status_of_action === 'Completed').length;
        pendingTrackersEl.textContent = allTrackers.filter(t => t.status_of_action && t.status_of_action === 'Pending').length;
        inProgressTrackersEl.textContent = allTrackers.filter(t => t.status_of_action && t.status_of_action === 'In Progress').length;
    }

    window.editTracker = function(id) {
        const tracker = allTrackers.find(t => t.id === id);
        if (tracker) {
            trackerForm.querySelector('#action_requested_by').value = tracker.action_requested_by || '';
            trackerForm.querySelector('#request_date').value = tracker.request_date || '';
            trackerForm.querySelector('#cost_center').value = tracker.cost_center || '';
            trackerForm.querySelector('#action_required').value = tracker.action_required || '';
            trackerForm.querySelector('#action_owner').value = tracker.action_owner || '';
            trackerForm.querySelector('#status_of_action').value = tracker.status_of_action || 'Pending';
            trackerForm.querySelector('#completion_date').value = tracker.completion_date || '';
            trackerForm.querySelector('#remark').value = tracker.remark || '';
            trackerForm.insertAdjacentHTML('beforeend', `<input type="hidden" name="id" value="${tracker.id}">`);
            openModal();
        }
    }

    window.deleteTracker = async function(id) {
        if (!confirm('Are you sure you want to delete this tracker?')) return;

        const formData = new FormData();
        formData.append('id', id);

        try {
            const response = await fetch('process.php?action=delete_task', { method: 'POST', body: formData });
            const result = await response.json();
            if (result.success) {
                showNotification('Tracker deleted successfully', 'success');
                loadTrackers();
            } else {
                showNotification('Error: ' + result.error, 'error');
            }
        } catch (error) {
            showNotification('Error deleting tracker: ' + error.message, 'error');
        }
    }

    // Utility functions
    function formatDate(dateString) {
        if (!dateString) return '-';
        const date = new Date(dateString);
        return date.toLocaleDateString('en-GB', {
            day: '2-digit',
            month: '2-digit',
            year: 'numeric'
        });
    }

    // Notification system
    function showNotification(message, type = 'info') {
        const existingNotifications = document.querySelectorAll('.notification');
        existingNotifications.forEach(notification => notification.remove());
        
        const notification = document.createElement('div');
        notification.className = `notification notification-${type}`;
        notification.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            background: ${type === 'success' ? 'hsl(var(--success))' : 'hsl(var(--info))'};
            color: white;
            padding: 1rem 1.5rem;
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-elegant);
            z-index: 1001;
            font-weight: 500;
            transform: translateX(100%);
            transition: transform 0.3s ease;
        `;
        
        notification.textContent = message;
        document.body.appendChild(notification);
        
        setTimeout(() => {
            notification.style.transform = 'translateX(0)';
        }, 100);
        
        setTimeout(() => {
            notification.style.transform = 'translateX(100%)';
            setTimeout(() => notification.remove(), 300);
        }, 3000);
    }
});
