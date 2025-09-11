<?php
session_start();
if (!isset($_SESSION['username'])) {
    header('Location: ../../public/login.php');
    exit();
}
include '../../../config/db.php';
include '../../../config/auth.php';
requirePermission('add_po_details');

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $project = trim($_POST['project_description']);
    $cost_center = trim($_POST['cost_center']);
    $sow = trim($_POST['sow_number']);
    $start = $_POST['start_date'] ?: null;
    $end = $_POST['end_date'] ?: null;
    $po_number = trim($_POST['po_number']);
    $po_date = $_POST['po_date'] ?: null;
    $po_value = $_POST['po_value'] ?: 0;
    $billing = trim($_POST['billing_frequency']);
    $target_gm = $_POST['target_gm'] ?: null;
    $status = trim($_POST['po_status']);
    $remarks = trim($_POST['remarks']);
    $vendor = trim($_POST['vendor_name']);

    // Convert dates to Excel format if provided
    $start_excel = $start ? (strtotime($start) / 86400) + 25569 : null;
    $end_excel = $end ? (strtotime($end) / 86400) + 25569 : null;
    $po_date_excel = $po_date ? (strtotime($po_date) / 86400) + 25569 : null;

    $sql = "INSERT INTO po_details (project_description, cost_center, sow_number, start_date, end_date, po_number, po_date, po_value, billing_frequency, target_gm, po_status, remarks, vendor_name) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    
    if ($stmt) {
        $stmt->bind_param("sssiisidsdsss", $project, $cost_center, $sow, $start_excel, $end_excel, $po_number, $po_date_excel, $po_value, $billing, $target_gm, $status, $remarks, $vendor);
        
        if ($stmt->execute()) {
            $success = "Purchase order created successfully!";
            $auth->logAction('create_po', 'po_details', $conn->insert_id);
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
    <title>Add Purchase Order - Cantik Homemade</title>
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
                            <h1 class="text-3xl font-bold text-gray-900">Add Purchase Order</h1>
                            <p class="text-gray-600 mt-2">Create a new purchase order record</p>
                        </div>
                        <a href="list.php" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-red-500">
                            <span class="material-symbols-outlined mr-2 text-sm">arrow_back</span>
                            Back to List
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
                        <h2 class="text-lg font-semibold text-gray-900">Purchase Order Details</h2>
                    </div>
                    <form method="POST" class="p-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Project Description -->
                            <div class="md:col-span-2">
                                <label for="project_description" class="block text-sm font-medium text-gray-700 mb-2">Project Description *</label>
                                <input type="text" id="project_description" name="project_description" required
                                       value="<?= htmlspecialchars($_POST['project_description'] ?? '') ?>"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-red-500">
                            </div>

                            <!-- Cost Center -->
                            <div>
                                <label for="cost_center" class="block text-sm font-medium text-gray-700 mb-2">Cost Center *</label>
                                <input type="text" id="cost_center" name="cost_center" required
                                       value="<?= htmlspecialchars($_POST['cost_center'] ?? '') ?>"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-red-500">
                            </div>

                            <!-- SOW Number -->
                            <div>
                                <label for="sow_number" class="block text-sm font-medium text-gray-700 mb-2">SOW Number *</label>
                                <input type="text" id="sow_number" name="sow_number" required
                                       value="<?= htmlspecialchars($_POST['sow_number'] ?? '') ?>"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-red-500">
                            </div>

                            <!-- Start Date -->
                            <div>
                                <label for="start_date" class="block text-sm font-medium text-gray-700 mb-2">Start Date</label>
                                <input type="date" id="start_date" name="start_date"
                                       value="<?= htmlspecialchars($_POST['start_date'] ?? '') ?>"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-red-500">
                            </div>

                            <!-- End Date -->
                            <div>
                                <label for="end_date" class="block text-sm font-medium text-gray-700 mb-2">End Date</label>
                                <input type="date" id="end_date" name="end_date"
                                       value="<?= htmlspecialchars($_POST['end_date'] ?? '') ?>"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-red-500">
                            </div>

                            <!-- PO Number -->
                            <div>
                                <label for="po_number" class="block text-sm font-medium text-gray-700 mb-2">PO Number *</label>
                                <input type="text" id="po_number" name="po_number" required
                                       value="<?= htmlspecialchars($_POST['po_number'] ?? '') ?>"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-red-500">
                            </div>

                            <!-- PO Date -->
                            <div>
                                <label for="po_date" class="block text-sm font-medium text-gray-700 mb-2">PO Date</label>
                                <input type="date" id="po_date" name="po_date"
                                       value="<?= htmlspecialchars($_POST['po_date'] ?? '') ?>"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-red-500">
                            </div>

                            <!-- PO Value -->
                            <div>
                                <label for="po_value" class="block text-sm font-medium text-gray-700 mb-2">PO Value *</label>
                                <input type="number" id="po_value" name="po_value" step="0.01" required
                                       value="<?= htmlspecialchars($_POST['po_value'] ?? '') ?>"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-red-500">
                            </div>

                            <!-- Billing Frequency -->
                            <div>
                                <label for="billing_frequency" class="block text-sm font-medium text-gray-700 mb-2">Billing Frequency *</label>
                                <select id="billing_frequency" name="billing_frequency" required
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-red-500">
                                    <option value="">Select frequency</option>
                                    <option value="Monthly" <?= ($_POST['billing_frequency'] ?? '') === 'Monthly' ? 'selected' : '' ?>>Monthly</option>
                                    <option value="Quarterly" <?= ($_POST['billing_frequency'] ?? '') === 'Quarterly' ? 'selected' : '' ?>>Quarterly</option>
                                    <option value="Half-yearly" <?= ($_POST['billing_frequency'] ?? '') === 'Half-yearly' ? 'selected' : '' ?>>Half-yearly</option>
                                    <option value="Yearly" <?= ($_POST['billing_frequency'] ?? '') === 'Yearly' ? 'selected' : '' ?>>Yearly</option>
                                    <option value="One-time" <?= ($_POST['billing_frequency'] ?? '') === 'One-time' ? 'selected' : '' ?>>One-time</option>
                                </select>
                            </div>

                            <!-- Target GM -->
                            <div>
                                <label for="target_gm" class="block text-sm font-medium text-gray-700 mb-2">Target GM (%)</label>
                                <input type="number" id="target_gm" name="target_gm" step="0.0001" min="0" max="100"
                                       value="<?= htmlspecialchars($_POST['target_gm'] ?? '') ?>"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-red-500">
                            </div>

                            <!-- PO Status -->
                            <div>
                                <label for="po_status" class="block text-sm font-medium text-gray-700 mb-2">PO Status</label>
                                <select id="po_status" name="po_status"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-red-500">
                                    <option value="Active" <?= ($_POST['po_status'] ?? 'Active') === 'Active' ? 'selected' : '' ?>>Active</option>
                                    <option value="Inactive" <?= ($_POST['po_status'] ?? '') === 'Inactive' ? 'selected' : '' ?>>Inactive</option>
                                    <option value="Completed" <?= ($_POST['po_status'] ?? '') === 'Completed' ? 'selected' : '' ?>>Completed</option>
                                    <option value="Cancelled" <?= ($_POST['po_status'] ?? '') === 'Cancelled' ? 'selected' : '' ?>>Cancelled</option>
                                </select>
                            </div>

                            <!-- Vendor Name -->
                            <div>
                                <label for="vendor_name" class="block text-sm font-medium text-gray-700 mb-2">Vendor Name</label>
                                <input type="text" id="vendor_name" name="vendor_name"
                                       value="<?= htmlspecialchars($_POST['vendor_name'] ?? '') ?>"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-red-500">
                            </div>

                            <!-- Remarks -->
                            <div class="md:col-span-2">
                                <label for="remarks" class="block text-sm font-medium text-gray-700 mb-2">Remarks</label>
                                <textarea id="remarks" name="remarks" rows="3"
                                          class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-red-500"><?= htmlspecialchars($_POST['remarks'] ?? '') ?></textarea>
                            </div>
                        </div>

                        <!-- Form Actions -->
                        <div class="mt-8 flex justify-end gap-4">
                            <a href="list.php" class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-red-500">
                                Cancel
                            </a>
                            <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded-md text-sm font-medium hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500">
                                <span class="material-symbols-outlined mr-2 text-sm">save</span>
                                Create Purchase Order
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </main>
    </div>
</body>
</html>