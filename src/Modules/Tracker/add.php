<?php
session_start();
if (!isset($_SESSION['username'])) {
    header('Location: ../../public/login.php');
    exit();
}
include '../../../config/db.php';
include '../../../config/auth.php';
requirePermission('add_finance_tasks');

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action_req_by = trim($_POST['action_req_by']);
    $request_date = $_POST['request_date'];
    $cost_center = trim($_POST['cost_center']);
    $action_req = trim($_POST['action_req']);
    $action_owner = trim($_POST['action_owner']);
    $status = $_POST['status'] ?: 'Pending';
    $completion_date = $_POST['completion_date'] ?: null;
    $remark = trim($_POST['remark']);

    $sql = "INSERT INTO finance_tasks (action_req_by, request_date, cost_center, action_req, action_owner, status, completion_date, remark) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    
    if ($stmt) {
        $stmt->bind_param("ssssssss", $action_req_by, $request_date, $cost_center, $action_req, $action_owner, $status, $completion_date, $remark);
        
        if ($stmt->execute()) {
            $success = "Task created successfully!";
            $auth->logAction('create_finance_task', 'finance_tasks', $conn->insert_id);
            $_POST = array(); // Clear form data
        } else {
            $error = "Error: " . $stmt->error;
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
    <title>Add Task - Tracker Updates</title>
    <link crossorigin="" href="https://fonts.gstatic.com/" rel="preconnect"/>
    <link as="style" href="https://fonts.googleapis.com/css2?display=swap&amp;family=Noto+Sans%3Awght%40400%3B500%3B700%3B900&amp;family=Public+Sans%3Awght%40400%3B500%3B600%3B700%3B800" onload="this.rel='stylesheet'" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet"/>
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
</head>
<body class="bg-gray-50 text-gray-900" style='font-family: "Public Sans", "Noto Sans", sans-serif;'>
    <div class="relative flex size-full min-h-screen flex-col overflow-x-hidden">
        <?php include '../../shared/nav.php'; ?>
        
        <main class="flex-1 px-4 sm:px-6 lg:px-8 py-8">
            <div class="max-w-4xl mx-auto">
                <!-- Page Header -->
                <div class="mb-8">
                    <div class="flex items-center justify-between">
                        <div>
                            <h1 class="text-3xl font-bold text-gray-900">Add New Task</h1>
                            <p class="text-gray-600 mt-2">Create a new finance task for tracking</p>
                        </div>
                        <a href="index.php" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-red-500">
                            <span class="material-symbols-outlined mr-2 text-sm">arrow_back</span>
                            Back to Tasks
                        </a>
                    </div>
                </div>

                <?php if ($success): ?>
                    <div class="mb-6 p-4 bg-green-50 border border-green-200 rounded-lg">
                        <p class="text-green-800"><?= htmlspecialchars($success) ?></p>
                    </div>
                <?php endif; ?>

                <?php if ($error): ?>
                    <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-lg">
                        <p class="text-red-800"><?= htmlspecialchars($error) ?></p>
                    </div>
                <?php endif; ?>

                <!-- Form -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h2 class="text-lg font-semibold text-gray-900">Task Details</h2>
                    </div>
                    <form method="POST" class="p-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Action Required By -->
                            <div>
                                <label for="action_req_by" class="block text-sm font-medium text-gray-700 mb-2">Action Required By *</label>
                                <input type="text" id="action_req_by" name="action_req_by" required
                                       value="<?= htmlspecialchars($_POST['action_req_by'] ?? '') ?>"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-red-500">
                            </div>

                            <!-- Request Date -->
                            <div>
                                <label for="request_date" class="block text-sm font-medium text-gray-700 mb-2">Request Date *</label>
                                <input type="date" id="request_date" name="request_date" required
                                       value="<?= htmlspecialchars($_POST['request_date'] ?? date('Y-m-d')) ?>"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-red-500">
                            </div>

                            <!-- Cost Center -->
                            <div>
                                <label for="cost_center" class="block text-sm font-medium text-gray-700 mb-2">Cost Center *</label>
                                <input type="text" id="cost_center" name="cost_center" required
                                       value="<?= htmlspecialchars($_POST['cost_center'] ?? '') ?>"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-red-500">
                            </div>

                            <!-- Action Owner -->
                            <div>
                                <label for="action_owner" class="block text-sm font-medium text-gray-700 mb-2">Action Owner *</label>
                                <input type="text" id="action_owner" name="action_owner" required
                                       value="<?= htmlspecialchars($_POST['action_owner'] ?? '') ?>"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-red-500">
                            </div>

                            <!-- Status -->
                            <div>
                                <label for="status" class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                                <select id="status" name="status"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-red-500">
                                    <option value="Pending" <?= ($_POST['status'] ?? 'Pending') === 'Pending' ? 'selected' : '' ?>>Pending</option>
                                    <option value="In Progress" <?= ($_POST['status'] ?? '') === 'In Progress' ? 'selected' : '' ?>>In Progress</option>
                                    <option value="Complete" <?= ($_POST['status'] ?? '') === 'Complete' ? 'selected' : '' ?>>Complete</option>
                                </select>
                            </div>

                            <!-- Completion Date -->
                            <div>
                                <label for="completion_date" class="block text-sm font-medium text-gray-700 mb-2">Completion Date</label>
                                <input type="date" id="completion_date" name="completion_date"
                                       value="<?= htmlspecialchars($_POST['completion_date'] ?? '') ?>"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-red-500">
                            </div>

                            <!-- Action Required -->
                            <div class="md:col-span-2">
                                <label for="action_req" class="block text-sm font-medium text-gray-700 mb-2">Action Required *</label>
                                <textarea id="action_req" name="action_req" rows="4" required
                                          class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-red-500"><?= htmlspecialchars($_POST['action_req'] ?? '') ?></textarea>
                            </div>

                            <!-- Remark -->
                            <div class="md:col-span-2">
                                <label for="remark" class="block text-sm font-medium text-gray-700 mb-2">Remark</label>
                                <textarea id="remark" name="remark" rows="3"
                                          class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-red-500"><?= htmlspecialchars($_POST['remark'] ?? '') ?></textarea>
                            </div>
                        </div>

                        <!-- Form Actions -->
                        <div class="mt-8 flex justify-end gap-4">
                            <a href="index.php" class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-red-500">
                                Cancel
                            </a>
                            <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded-md text-sm font-medium hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500">
                                <span class="material-symbols-outlined mr-2 text-sm">save</span>
                                Create Task
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </main>
    </div>
</body>
</html>
