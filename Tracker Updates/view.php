<?php
session_start();
if (!isset($_SESSION['username'])) {
    header('Location: ../1Login_signuppage/login.php');
    exit();
}
include '../db.php';
include '../auth.php';
requirePermission('view_finance_tasks');

$id = intval($_GET['id'] ?? 0);
if (!$id) {
    header('Location: index.php');
    exit();
}

// Get task data
$sql = "SELECT * FROM finance_tasks WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$task = $result->fetch_assoc();
$stmt->close();

if (!$task) {
    header('Location: index.php');
    exit();
}

function getStatusColor($status) {
    switch ($status) {
        case 'Complete':
            return 'bg-green-100 text-green-800';
        case 'In Progress':
            return 'bg-blue-100 text-blue-800';
        case 'Pending':
            return 'bg-yellow-100 text-yellow-800';
        default:
            return 'bg-gray-100 text-gray-800';
    }
}

function formatDate($date) {
    if (empty($date)) return 'Not set';
    return date('M j, Y', strtotime($date));
}

function formatDateTime($datetime) {
    if (empty($datetime)) return 'Not set';
    return date('M j, Y g:i A', strtotime($datetime));
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>View Task - Tracker Updates</title>
    <link crossorigin="" href="https://fonts.gstatic.com/" rel="preconnect"/>
    <link as="style" href="https://fonts.googleapis.com/css2?display=swap&amp;family=Noto+Sans%3Awght%40400%3B500%3B700%3B900&amp;family=Public+Sans%3Awght%40400%3B500%3B600%3B700%3B800" onload="this.rel='stylesheet'" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet"/>
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
</head>
<body class="bg-gray-50 text-gray-900" style='font-family: "Public Sans", "Noto Sans", sans-serif;'>
    <div class="relative flex size-full min-h-screen flex-col overflow-x-hidden">
        <?php include '../shared/nav.php'; ?>
        
        <main class="flex-1 px-4 sm:px-6 lg:px-8 py-8">
            <div class="max-w-4xl mx-auto">
                <!-- Page Header -->
                <div class="mb-8">
                    <div class="flex items-center justify-between">
                        <div>
                            <h1 class="text-3xl font-bold text-gray-900">Task Details</h1>
                            <p class="text-gray-600 mt-2">Task #<?= $task['id'] ?> - <?= htmlspecialchars($task['action_req_by']) ?></p>
                        </div>
                        <div class="flex items-center gap-3">
                            <a href="edit.php?id=<?= $task['id'] ?>" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-red-500">
                                <span class="material-symbols-outlined mr-2 text-sm">edit</span>
                                Edit Task
                            </a>
                            <a href="index.php" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-red-500">
                                <span class="material-symbols-outlined mr-2 text-sm">arrow_back</span>
                                Back to Tasks
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Task Information -->
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <!-- Main Details -->
                    <div class="lg:col-span-2 space-y-6">
                        <!-- Basic Information -->
                        <div class="bg-white rounded-lg shadow-sm border border-gray-200">
                            <div class="px-6 py-4 border-b border-gray-200">
                                <h2 class="text-lg font-semibold text-gray-900">Basic Information</h2>
                            </div>
                            <div class="p-6 space-y-4">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-500 mb-1">Task ID</label>
                                        <p class="text-sm text-gray-900">#<?= $task['id'] ?></p>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-500 mb-1">Status</label>
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?= getStatusColor($task['status']) ?>">
                                            <?= htmlspecialchars($task['status']) ?>
                                        </span>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-500 mb-1">Action Required By</label>
                                        <p class="text-sm text-gray-900"><?= htmlspecialchars($task['action_req_by']) ?></p>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-500 mb-1">Action Owner</label>
                                        <p class="text-sm text-gray-900"><?= htmlspecialchars($task['action_owner']) ?></p>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-500 mb-1">Request Date</label>
                                        <p class="text-sm text-gray-900"><?= formatDate($task['request_date']) ?></p>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-500 mb-1">Completion Date</label>
                                        <p class="text-sm text-gray-900"><?= formatDate($task['completion_date']) ?></p>
                                    </div>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-500 mb-1">Cost Center</label>
                                    <p class="text-sm text-gray-900"><?= htmlspecialchars($task['cost_center']) ?></p>
                                </div>
                            </div>
                        </div>

                        <!-- Action Required -->
                        <div class="bg-white rounded-lg shadow-sm border border-gray-200">
                            <div class="px-6 py-4 border-b border-gray-200">
                                <h2 class="text-lg font-semibold text-gray-900">Action Required</h2>
                            </div>
                            <div class="p-6">
                                <p class="text-sm text-gray-900 whitespace-pre-wrap"><?= htmlspecialchars($task['action_req']) ?></p>
                            </div>
                        </div>

                        <!-- Remarks -->
                        <?php if (!empty($task['remark'])): ?>
                        <div class="bg-white rounded-lg shadow-sm border border-gray-200">
                            <div class="px-6 py-4 border-b border-gray-200">
                                <h2 class="text-lg font-semibold text-gray-900">Remarks</h2>
                            </div>
                            <div class="p-6">
                                <p class="text-sm text-gray-900 whitespace-pre-wrap"><?= htmlspecialchars($task['remark']) ?></p>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>

                    <!-- Sidebar -->
                    <div class="space-y-6">
                        <!-- Quick Actions -->
                        <div class="bg-white rounded-lg shadow-sm border border-gray-200">
                            <div class="px-6 py-4 border-b border-gray-200">
                                <h2 class="text-lg font-semibold text-gray-900">Quick Actions</h2>
                            </div>
                            <div class="p-6 space-y-3">
                                <a href="edit.php?id=<?= $task['id'] ?>" class="w-full inline-flex items-center justify-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500">
                                    <span class="material-symbols-outlined mr-2 text-sm">edit</span>
                                    Edit Task
                                </a>
                                <a href="index.php" class="w-full inline-flex items-center justify-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-red-500">
                                    <span class="material-symbols-outlined mr-2 text-sm">list</span>
                                    View All Tasks
                                </a>
                                <a href="add.php" class="w-full inline-flex items-center justify-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-red-500">
                                    <span class="material-symbols-outlined mr-2 text-sm">add</span>
                                    Add New Task
                                </a>
                            </div>
                        </div>

                        <!-- Task Timeline -->
                        <div class="bg-white rounded-lg shadow-sm border border-gray-200">
                            <div class="px-6 py-4 border-b border-gray-200">
                                <h2 class="text-lg font-semibold text-gray-900">Task Timeline</h2>
                            </div>
                            <div class="p-6 space-y-4">
                                <div class="flex items-start">
                                    <div class="flex-shrink-0">
                                        <div class="w-2 h-2 bg-blue-400 rounded-full mt-2"></div>
                                    </div>
                                    <div class="ml-3">
                                        <p class="text-sm font-medium text-gray-900">Task Created</p>
                                        <p class="text-xs text-gray-500"><?= formatDateTime($task['created_at']) ?></p>
                                    </div>
                                </div>
                                
                                <div class="flex items-start">
                                    <div class="flex-shrink-0">
                                        <div class="w-2 h-2 bg-yellow-400 rounded-full mt-2"></div>
                                    </div>
                                    <div class="ml-3">
                                        <p class="text-sm font-medium text-gray-900">Request Date</p>
                                        <p class="text-xs text-gray-500"><?= formatDate($task['request_date']) ?></p>
                                    </div>
                                </div>

                                <?php if (!empty($task['completion_date'])): ?>
                                <div class="flex items-start">
                                    <div class="flex-shrink-0">
                                        <div class="w-2 h-2 bg-green-400 rounded-full mt-2"></div>
                                    </div>
                                    <div class="ml-3">
                                        <p class="text-sm font-medium text-gray-900">Completed</p>
                                        <p class="text-xs text-gray-500"><?= formatDate($task['completion_date']) ?></p>
                                    </div>
                                </div>
                                <?php endif; ?>

                                <div class="flex items-start">
                                    <div class="flex-shrink-0">
                                        <div class="w-2 h-2 bg-gray-400 rounded-full mt-2"></div>
                                    </div>
                                    <div class="ml-3">
                                        <p class="text-sm font-medium text-gray-900">Last Updated</p>
                                        <p class="text-xs text-gray-500"><?= formatDateTime($task['updated_at']) ?></p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</body>
</html>
