<?php
session_start();
if (!isset($_SESSION['username'])) {
    header('Location: ../../public/login.php');
    exit();
}
include '../../../config/db.php';
include '../../../config/auth.php';
requirePermission('delete_finance_tasks');

$id = intval($_GET['id'] ?? 0);
if (!$id) {
    header('Location: index.php');
    exit();
}

// Get task data for confirmation
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

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm_delete'])) {
    $sql = "DELETE FROM finance_tasks WHERE id = ?";
    $stmt = $conn->prepare($sql);
    
    if ($stmt) {
        $stmt->bind_param("i", $id);
        
        if ($stmt->execute()) {
            $auth->logAction('delete_finance_task', 'finance_tasks', $id);
            header('Location: index.php?deleted=1');
            exit();
        } else {
            $error = "Error deleting task: " . $stmt->error;
        }
        $stmt->close();
    } else {
        $error = "Error preparing statement: " . $conn->error;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Delete Task - Tracker Updates</title>
    <link crossorigin="" href="https://fonts.gstatic.com/" rel="preconnect"/>
    <link as="style" href="https://fonts.googleapis.com/css2?display=swap&amp;family=Noto+Sans%3Awght%40400%3B500%3B700%3B900&amp;family=Public+Sans%3Awght%40400%3B500%3B600%3B700%3B800" onload="this.rel='stylesheet'" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet"/>
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
</head>
<body class="bg-gray-50 text-gray-900" style='font-family: "Public Sans", "Noto Sans", sans-serif;'>
    <div class="relative flex size-full min-h-screen flex-col overflow-x-hidden">
        <?php include '../../shared/nav.php'; ?>
        
        <main class="flex-1 px-4 sm:px-6 lg:px-8 py-8">
            <div class="max-w-2xl mx-auto">
                <!-- Page Header -->
                <div class="mb-8">
                    <div class="flex items-center justify-between">
                        <div>
                            <h1 class="text-3xl font-bold text-gray-900">Delete Task</h1>
                            <p class="text-gray-600 mt-2">Confirm deletion of task #<?= $task['id'] ?></p>
                        </div>
                        <a href="view.php?id=<?= $task['id'] ?>" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-red-500">
                            <span class="material-symbols-outlined mr-2 text-sm">arrow_back</span>
                            Back to Task
                        </a>
                    </div>
                </div>

                <?php if (isset($error)): ?>
                    <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-lg">
                        <p class="text-red-800"><?= htmlspecialchars($error) ?></p>
                    </div>
                <?php endif; ?>

                <!-- Warning Card -->
                <div class="bg-red-50 border border-red-200 rounded-lg p-6 mb-6">
                    <div class="flex items-start">
                        <div class="flex-shrink-0">
                            <span class="material-symbols-outlined text-2xl text-red-600">warning</span>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-lg font-medium text-red-800">Are you sure you want to delete this task?</h3>
                            <p class="mt-2 text-sm text-red-700">This action cannot be undone. The task will be permanently removed from the system.</p>
                        </div>
                    </div>
                </div>

                <!-- Task Details -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 mb-6">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h2 class="text-lg font-semibold text-gray-900">Task Information</h2>
                    </div>
                    <div class="p-6 space-y-4">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-500 mb-1">Task ID</label>
                                <p class="text-sm text-gray-900">#<?= $task['id'] ?></p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-500 mb-1">Status</label>
                                <p class="text-sm text-gray-900"><?= htmlspecialchars($task['status']) ?></p>
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
                                <p class="text-sm text-gray-900"><?= date('M j, Y', strtotime($task['request_date'])) ?></p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-500 mb-1">Cost Center</label>
                                <p class="text-sm text-gray-900"><?= htmlspecialchars($task['cost_center']) ?></p>
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-500 mb-1">Action Required</label>
                            <p class="text-sm text-gray-900"><?= htmlspecialchars($task['action_req']) ?></p>
                        </div>
                    </div>
                </div>

                <!-- Confirmation Form -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h2 class="text-lg font-semibold text-gray-900">Confirm Deletion</h2>
                    </div>
                    <form method="POST" class="p-6">
                        <div class="mb-6">
                            <label class="flex items-start">
                                <input type="checkbox" name="confirm_delete" required
                                       class="mt-1 h-4 w-4 text-red-600 focus:ring-red-500 border-gray-300 rounded">
                                <span class="ml-2 text-sm text-gray-700">
                                    I understand that this action cannot be undone and I want to permanently delete this task.
                                </span>
                            </label>
                        </div>

                        <div class="flex justify-end gap-4">
                            <a href="view.php?id=<?= $task['id'] ?>" class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-red-500">
                                Cancel
                            </a>
                            <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded-md text-sm font-medium hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500">
                                <span class="material-symbols-outlined mr-2 text-sm">delete</span>
                                Delete Task
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </main>
    </div>
</body>
</html>
