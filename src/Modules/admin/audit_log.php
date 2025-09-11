<?php
session_start();
if (!isset($_SESSION['username'])) {
    header('Location: ../../../login.php');
    exit();
}

include '../../../config/db.php';
include '../../../config/auth.php';

// Require admin access
requireAdmin();


// Pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 50;
$offset = ($page - 1) * $limit;

// Filters
$user_filter = isset($_GET['user']) ? (int)$_GET['user'] : '';
$action_filter = isset($_GET['action']) ? $_GET['action'] : '';
$date_from = isset($_GET['date_from']) ? $_GET['date_from'] : '';
$date_to = isset($_GET['date_to']) ? $_GET['date_to'] : '';

// Build query
$where_conditions = [];
$params = [];
$param_types = '';

if ($user_filter) {
    $where_conditions[] = "al.user_id = ?";
    $params[] = $user_filter;
    $param_types .= 'i';
}

if ($action_filter) {
    $where_conditions[] = "al.action LIKE ?";
    $params[] = "%$action_filter%";
    $param_types .= 's';
}

if ($date_from) {
    $where_conditions[] = "DATE(al.created_at) >= ?";
    $params[] = $date_from;
    $param_types .= 's';
}

if ($date_to) {
    $where_conditions[] = "DATE(al.created_at) <= ?";
    $params[] = $date_to;
    $param_types .= 's';
}

$where_clause = !empty($where_conditions) ? 'WHERE ' . implode(' AND ', $where_conditions) : '';

// Get total count
$count_sql = "SELECT COUNT(*) as total FROM audit_log al $where_clause";
$count_stmt = $conn->prepare($count_sql);
if (!empty($params)) {
    $count_stmt->bind_param($param_types, ...$params);
}
$count_stmt->execute();
$total_records = $count_stmt->get_result()->fetch_assoc()['total'];
$total_pages = ceil($total_records / $limit);

// Get audit log entries
$sql = "SELECT al.*, u.username, u.first_name, u.last_name 
        FROM audit_log al 
        JOIN users_login_signup u ON al.user_id = u.id 
        $where_clause
        ORDER BY al.created_at DESC 
        LIMIT ? OFFSET ?";
$params[] = $limit;
$params[] = $offset;
$param_types .= 'ii';

$stmt = $conn->prepare($sql);
$stmt->bind_param($param_types, ...$params);
$stmt->execute();
$audit_logs = $stmt->get_result();

// Get users for filter dropdown
$users_sql = "SELECT id, username, first_name, last_name FROM users_login_signup ORDER BY username";
$users_result = $conn->query($users_sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Audit Log - Cantik Homemade</title>
    <link crossorigin="" href="https://fonts.gstatic.com/" rel="preconnect"/>
    <link as="style" href="https://fonts.googleapis.com/css2?display=swap&amp;family=Noto+Sans%3Awght%40400%3B500%3B700%3B900&amp;family=Public+Sans%3Awght%40400%3B500%3B600%3B700%3B800" onload="this.rel='stylesheet'" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet"/>
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
</head>
<body class="bg-gray-50 text-gray-900" style='font-family: "Public Sans", "Noto Sans", sans-serif;'>
    <div class="relative flex size-full min-h-screen flex-col overflow-x-hidden">
        <?php include '../../shared/nav.php'; ?>
        
        <main class="flex-1 px-4 sm:px-6 lg:px-8 py-8">
            <div class="max-w-7xl mx-auto">
                <div class="mb-8">
                    <h1 class="text-3xl font-bold text-gray-900">Audit Log</h1>
                    <p class="text-gray-600 mt-2">Track all user actions and system changes</p>
                </div>

                <!-- Filters -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 mb-6">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h2 class="text-lg font-semibold text-gray-900">Filters</h2>
                    </div>
                    <form method="GET" class="p-6">
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">User</label>
                                <select name="user" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-red-500">
                                    <option value="">All Users</option>
                                    <?php while ($user = $users_result->fetch_assoc()): ?>
                                    <option value="<?= $user['id'] ?>" <?= $user_filter == $user['id'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($user['first_name'] . ' ' . $user['last_name'] ?: $user['username']) ?>
                                    </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Action</label>
                                <input type="text" name="action" value="<?= htmlspecialchars($action_filter) ?>" 
                                       placeholder="e.g., login, create_user" 
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-red-500">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Date From</label>
                                <input type="date" name="date_from" value="<?= htmlspecialchars($date_from) ?>" 
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-red-500">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Date To</label>
                                <input type="date" name="date_to" value="<?= htmlspecialchars($date_to) ?>" 
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-red-500">
                            </div>
                        </div>
                        <div class="flex gap-4 mt-4">
                            <button type="submit" class="bg-red-600 text-white py-2 px-4 rounded-md hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500">
                                Apply Filters
                            </button>
                            <a href="audit_log.php" class="bg-gray-300 text-gray-700 py-2 px-4 rounded-md hover:bg-gray-400 focus:outline-none focus:ring-2 focus:ring-gray-500">
                                Clear Filters
                            </a>
                        </div>
                    </form>
                </div>

                <!-- Audit Log Table -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h2 class="text-lg font-semibold text-gray-900">
                            Audit Log Entries 
                            <span class="text-sm font-normal text-gray-500">
                                (<?= number_format($total_records) ?> total records)
                            </span>
                        </h2>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">User</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Action</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Table/Record</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">IP Address</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Timestamp</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Details</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <?php while ($log = $audit_logs->fetch_assoc()): ?>
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div>
                                            <div class="text-sm font-medium text-gray-900">
                                                <?= htmlspecialchars($log['first_name'] . ' ' . $log['last_name']) ?: htmlspecialchars($log['username']) ?>
                                            </div>
                                            <div class="text-sm text-gray-500"><?= htmlspecialchars($log['username']) ?></div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                            <?= htmlspecialchars($log['action']) ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        <?php if ($log['table_name']): ?>
                                            <div><?= htmlspecialchars($log['table_name']) ?></div>
                                            <?php if ($log['record_id']): ?>
                                                <div class="text-gray-500">ID: <?= $log['record_id'] ?></div>
                                            <?php endif; ?>
                                        <?php else: ?>
                                            <span class="text-gray-400">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        <?= htmlspecialchars($log['ip_address'] ?: '-') ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        <?= date('M j, Y g:i A', strtotime($log['created_at'])) ?>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-900">
                                        <?php if ($log['old_values'] || $log['new_values']): ?>
                                            <button onclick="toggleDetails(<?= $log['id'] ?>)" class="text-red-600 hover:text-red-900">
                                                View Details
                                            </button>
                                            <div id="details-<?= $log['id'] ?>" class="hidden mt-2 p-3 bg-gray-50 rounded text-xs">
                                                <?php if ($log['old_values']): ?>
                                                    <div class="mb-2">
                                                        <strong>Old Values:</strong>
                                                        <pre class="mt-1"><?= htmlspecialchars(json_encode(json_decode($log['old_values']), JSON_PRETTY_PRINT)) ?></pre>
                                                    </div>
                                                <?php endif; ?>
                                                <?php if ($log['new_values']): ?>
                                                    <div>
                                                        <strong>New Values:</strong>
                                                        <pre class="mt-1"><?= htmlspecialchars(json_encode(json_decode($log['new_values']), JSON_PRETTY_PRINT)) ?></pre>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        <?php else: ?>
                                            <span class="text-gray-400">-</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Pagination -->
                <?php if ($total_pages > 1): ?>
                <div class="mt-6 flex items-center justify-between">
                    <div class="text-sm text-gray-700">
                        Showing <?= $offset + 1 ?> to <?= min($offset + $limit, $total_records) ?> of <?= number_format($total_records) ?> results
                    </div>
                    <div class="flex gap-2">
                        <?php if ($page > 1): ?>
                            <a href="?page=<?= $page - 1 ?>&<?= http_build_query($_GET) ?>" class="px-3 py-2 border border-gray-300 rounded-md text-sm hover:bg-gray-50">Previous</a>
                        <?php endif; ?>
                        
                        <?php for ($i = max(1, $page - 2); $i <= min($total_pages, $page + 2); $i++): ?>
                            <a href="?page=<?= $i ?>&<?= http_build_query($_GET) ?>" 
                               class="px-3 py-2 border border-gray-300 rounded-md text-sm <?= $i == $page ? 'bg-red-600 text-white border-red-600' : 'hover:bg-gray-50' ?>">
                                <?= $i ?>
                            </a>
                        <?php endfor; ?>
                        
                        <?php if ($page < $total_pages): ?>
                            <a href="?page=<?= $page + 1 ?>&<?= http_build_query($_GET) ?>" class="px-3 py-2 border border-gray-300 rounded-md text-sm hover:bg-gray-50">Next</a>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </main>
    </div>

    <script>
        function toggleDetails(id) {
            const details = document.getElementById('details-' + id);
            details.classList.toggle('hidden');
        }
    </script>
</body>
</html>
