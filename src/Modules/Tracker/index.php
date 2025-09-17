<?php
session_start();
if (!isset($_SESSION['username'])) {
    header('Location: ' . getLoginUrl());
    exit();
}
// Friendly redirects for paths like /Tracker Updates/index.php/add.php
$reqUri = $_SERVER['REQUEST_URI'] ?? '';
if (strpos($reqUri, 'index.php/add.php') !== false) {
    header('Location: add.php');
    exit();
}
if (strpos($reqUri, 'index.php/edit.php') !== false) {
    header('Location: edit.php');
    exit();
}
if (strpos($reqUri, 'index.php/view.php') !== false) {
    header('Location: view.php');
    exit();
}
if (strpos($reqUri, 'index.php/delete.php') !== false) {
    header('Location: delete.php');
    exit();
}
    include '../../../config/db.php';
    include '../../../config/auth.php';
requirePermission('view_finance_tasks');

// Role and ownership helpers
$isAdmin = false;
try { $isAdmin = isAdmin(); } catch (Throwable $e) { $isAdmin = (($_SESSION['role'] ?? '') === 'admin'); }

// Build identifiers for ownership checks
$currentUserIdentifiers = [];
$uUsername = trim(strtolower((string)($_SESSION['username'] ?? '')));
if ($uUsername !== '') { $currentUserIdentifiers[] = $uUsername; }
$uEmail = trim(strtolower((string)($_SESSION['email'] ?? '')));
if ($uEmail !== '') { $currentUserIdentifiers[] = $uEmail; }
$uFirst = trim((string)($_SESSION['first_name'] ?? ''));
$uLast = trim((string)($_SESSION['last_name'] ?? ''));
if ($uFirst !== '' || $uLast !== '') {
    $full = strtolower(trim($uFirst . ' ' . $uLast));
    if ($full !== '') { $currentUserIdentifiers[] = $full; }
}

function currentUserOwnsTask(array $task, array $identifiers): bool {
    $owner = strtolower(trim((string)($task['action_owner'] ?? '')));
    if ($owner === '') return false;
    foreach ($identifiers as $idn) {
        if ($idn !== '' && $owner === $idn) { return true; }
    }
    return false;
}

// Ensure status_of_action defaults to 'Pending' when NULL/empty
if ($conn->query("UPDATE finance_tasks SET status = 'Pending' WHERE status IS NULL OR status = ''") === false) {
    error_log('Failed to normalize status: ' . $conn->error);
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
    $status = trim((string)($t['status'] ?? ''));
    if (in_array($status, ['Complete', 'Completed'], true)) {
        $completed_trackers++;
    } elseif ($status === 'Pending') {
        $pending_trackers++;
    } elseif (in_array($status, ['In Progress', 'In-Progress'], true)) {
        $in_progress_trackers++;
    }
}

function getStatusColor($status) {
    switch ($status) {
        case 'Complete':
        case 'Completed':
            return 'bg-green-100 text-green-800';
        case 'In Progress':
        case 'In-Progress':
            return 'bg-blue-100 text-blue-800';
        case 'Pending':
            return 'bg-yellow-100 text-yellow-800';
        default:
            return 'bg-gray-100 text-gray-800';
    }
}

function formatDate($date) {
    if (empty($date)) return '-';
    return date('M j, Y', strtotime($date));
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Tracker Updates - PO Management</title>
    <link crossorigin="" href="https://fonts.gstatic.com/" rel="preconnect"/>
    <link as="style" href="https://fonts.googleapis.com/css2?display=swap&amp;family=Noto+Sans%3Awght%40400%3B500%3B700%3B900&amp;family=Public+Sans%3Awght%40400%3B500%3B600%3B700%3B800" onload="this.rel='stylesheet'" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet"/>
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
</head>
<body class="bg-gray-50 text-gray-900" style='font-family: "Public Sans", "Noto Sans", sans-serif;'>
    <div class="relative flex size-full min-h-screen flex-col overflow-x-hidden">
        <?php include getSharedIncludePath('nav.php'); ?>
        
        <main class="flex-1 px-4 sm:px-6 lg:px-8 py-8">
            <div class="max-w-7xl mx-auto">
                <!-- Page Header -->
                <div class="mb-8">
                    <div class="flex items-center justify-between">
                        <div>
                            <h1 class="text-3xl font-bold text-gray-900">Tracker Updates</h1>
                            <p class="text-gray-600 mt-2">
                                Manage and track finance tasks and updates  </p>
                        </div>
                        <a href="add.php" class="inline-flex items-center px-4 py-2 bg-red-600 text-white rounded-md text-sm font-medium hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500">
                            <span class="material-symbols-outlined mr-2 text-sm">add</span>
                            Add New Task
                        </a>
                    </div>
                </div>

                <!-- Stats Cards -->
                <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-600">Total Tasks</p>
                                <p class="text-2xl font-bold text-gray-900"><?= $total_trackers ?></p>
                            </div>
                            <div class="w-12 h-12 bg-gray-100 rounded-lg flex items-center justify-center">
                                <span class="material-symbols-outlined text-2xl text-gray-600">assignment</span>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-600">Pending</p>
                                <p class="text-2xl font-bold text-yellow-600"><?= $pending_trackers ?></p>
                            </div>
                            <div class="w-12 h-12 bg-yellow-100 rounded-lg flex items-center justify-center">
                                <span class="material-symbols-outlined text-2xl text-yellow-600">schedule</span>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-600">In Progress</p>
                                <p class="text-2xl font-bold text-blue-600"><?= $in_progress_trackers ?></p>
                            </div>
                            <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                                <span class="material-symbols-outlined text-2xl text-blue-600">trending_up</span>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-600">Completed</p>
                                <p class="text-2xl font-bold text-green-600"><?= $completed_trackers ?></p>
                            </div>
                            <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                                <span class="material-symbols-outlined text-2xl text-green-600">check_circle</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tasks Table -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h2 class="text-lg font-semibold text-gray-900">All Tasks</h2>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Action Required By</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Request Date</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Cost Center</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Action Required</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Action Owner</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Completion Date</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <?php if (empty($trackers)): ?>
                                    <tr>
                                        <td colspan="9" class="px-6 py-12 text-center text-gray-500">
                                            <span class="material-symbols-outlined text-4xl text-gray-300 mb-4 block">assignment</span>
                                            No tasks found. <a href="add.php" class="text-red-600 hover:text-red-800">Create your first task</a>
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($trackers as $task): ?>
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                            #<?= $task['id'] ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            <?= htmlspecialchars($task['action_req_by']) ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            <?= formatDate($task['request_date']) ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            <?= htmlspecialchars($task['cost_center']) ?>
                                        </td>
                                        <td class="px-6 py-4 text-sm text-gray-900 max-w-xs">
                                            <div class="truncate" title="<?= htmlspecialchars($task['action_req']) ?>">
                                                <?= htmlspecialchars(substr($task['action_req'], 0, 50)) ?><?= strlen($task['action_req']) > 50 ? '...' : '' ?>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            <?= htmlspecialchars($task['action_owner']) ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?= getStatusColor($task['status']) ?>">
                                                <?= htmlspecialchars($task['status']) ?>
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            <?= formatDate($task['completion_date']) ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            <div class="flex items-center gap-2">
                                                <?php if ($isAdmin || currentUserOwnsTask($task, $currentUserIdentifiers)): ?>
                                                <a href="edit.php?id=<?= $task['id'] ?>" class="text-red-600 hover:text-red-900" title="Edit">
                                                    <span class="material-symbols-outlined text-sm">edit</span>
                                                </a>
                                                <?php endif; ?>
                                                <a href="view.php?id=<?= $task['id'] ?>" class="text-blue-600 hover:text-blue-900" title="View">
                                                    <span class="material-symbols-outlined text-sm">visibility</span>
                                                </a>
                                                <?php if ($isAdmin): ?>
                                                <a href="delete.php?id=<?= $task['id'] ?>" class="text-red-600 hover:text-red-900" onclick="return confirm('Are you sure you want to delete this task?')" title="Delete">
                                                    <span class="material-symbols-outlined text-sm">delete</span>
                                                </a>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </main>
    </div>
</body>
</html>