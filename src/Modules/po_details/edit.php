<?php
// Use universal includes
include '../../shared/includes.php';

// Check authentication
if (!isset($_SESSION['username'])) {
  header('Location: ' . getLoginUrl());
  exit();
}
requirePermission('edit_po_details');

$id = intval($_GET['id'] ?? 0);
if (!$id) {
    header('Location: list.php');
    exit();
}

$success = '';
$error = '';

// Get existing PO data
$sql = "SELECT * FROM po_details WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$po = $result->fetch_assoc();
$stmt->close();

if (!$po) {
    header('Location: list.php');
    exit();
}

// Derive vendor name from outsourcing/billing if missing
if (empty(trim((string)($po['vendor_name'] ?? '')))) {
    $derivedVendor = '';
    // Prefer latest from outsourcing_detail
    if ($stmt = $conn->prepare("SELECT vendor_name FROM outsourcing_detail WHERE customer_po = ? AND vendor_name IS NOT NULL AND vendor_name <> '' ORDER BY id DESC LIMIT 1")) {
        $stmt->bind_param('s', $po['po_number']);
        $stmt->execute();
        $rs = $stmt->get_result();
        if ($row = $rs->fetch_assoc()) { $derivedVendor = trim((string)$row['vendor_name']); }
        $stmt->close();
    }
    // Fallback to billing_details
    if ($derivedVendor === '' && ($stmt = $conn->prepare("SELECT vendor_name FROM billing_details WHERE customer_po = ? AND vendor_name IS NOT NULL AND vendor_name <> '' ORDER BY id DESC LIMIT 1"))) {
        $stmt->bind_param('s', $po['po_number']);
        $stmt->execute();
        $rs = $stmt->get_result();
        if ($row = $rs->fetch_assoc()) { $derivedVendor = trim((string)$row['vendor_name']); }
        $stmt->close();
    }
    if ($derivedVendor !== '') {
        $po['vendor_name'] = $derivedVendor;
    }
}

// Convert Excel dates to readable format
function excelToDate($excel_date) {
    if (empty($excel_date)) return '';
    $unix_date = ($excel_date - 25569) * 86400;
    return date('Y-m-d', $unix_date);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $project = trim($_POST['project_description'] ?? '');
    $cost_center = trim($_POST['cost_center'] ?? '');
    $sow = trim($_POST['sow_number'] ?? '');
    $start = $_POST['start_date'] ?: null;
    $end = $_POST['end_date'] ?: null;
    $po_number = trim($_POST['po_number'] ?? '');
    $po_date = $_POST['po_date'] ?: null;
    $po_value = $_POST['po_value'] ?: 0;
    $billing = trim($_POST['billing_frequency'] ?? '');
    $target_gm = !empty($_POST['target_gm']) ? ($_POST['target_gm'] / 100) : 0; // Convert percentage to decimal, default to 0 if empty
    $status = trim($_POST['po_status'] ?? 'Active');
    $remarks = trim($_POST['remarks'] ?? '');
    $vendor = trim($_POST['vendor_name'] ?? '');
    if ($vendor === '') {
        // If still empty, attempt to derive using the posted PO number
        $derivedVendor = '';
        if ($stmt = $conn->prepare("SELECT vendor_name FROM outsourcing_detail WHERE customer_po = ? AND vendor_name IS NOT NULL AND vendor_name <> '' ORDER BY id DESC LIMIT 1")) {
            $stmt->bind_param('s', $po_number);
            $stmt->execute();
            $rs = $stmt->get_result();
            if ($row = $rs->fetch_assoc()) { $derivedVendor = trim((string)$row['vendor_name']); }
            $stmt->close();
        }
        if ($derivedVendor === '' && ($stmt = $conn->prepare("SELECT vendor_name FROM billing_details WHERE customer_po = ? AND vendor_name IS NOT NULL AND vendor_name <> '' ORDER BY id DESC LIMIT 1"))) {
            $stmt->bind_param('s', $po_number);
            $stmt->execute();
            $rs = $stmt->get_result();
            if ($row = $rs->fetch_assoc()) { $derivedVendor = trim((string)$row['vendor_name']); }
            $stmt->close();
        }
        if ($derivedVendor !== '') { $vendor = $derivedVendor; }
    }
    $customer_name = trim($_POST['customer_name'] ?? '');
    
    // Only validate PO Number as required
    if (empty($po_number)) {
        $error = "PO Number is required.";
    }

    // Only proceed with database update if no validation errors
    if (empty($error)) {
        // Convert dates to Excel format if provided, allow NULL if empty
        $start_excel = $start ? (int)floor((strtotime($start) / 86400) + 25569) : null;
        $end_excel = $end ? (int)floor((strtotime($end) / 86400) + 25569) : null;
        $po_date_excel = $po_date ? (int)floor((strtotime($po_date) / 86400) + 25569) : null;

        $sql = "UPDATE po_details SET project_description = ?, cost_center = ?, sow_number = ?, start_date = ?, end_date = ?, po_number = ?, po_date = ?, po_value = ?, billing_frequency = ?, target_gm = ?, po_status = ?, remarks = ?, vendor_name = ?, customer_name = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        
        if ($stmt) {
            $stmt->bind_param("sssiisidsdssssi", $project, $cost_center, $sow, $start_excel, $end_excel, $po_number, $po_date_excel, $po_value, $billing, $target_gm, $status, $remarks, $vendor, $customer_name, $id);
            
            if ($stmt->execute()) {
                $success = "Purchase order updated successfully!";
                $auth->logAction('update_po', 'po_details', $id);
                // Refresh PO data
                $sql = "SELECT * FROM po_details WHERE id = ?";
                $stmt2 = $conn->prepare($sql);
                if ($stmt2) {
                    $stmt2->bind_param("i", $id);
                    $stmt2->execute();
                    $result = $stmt2->get_result();
                    $po = $result->fetch_assoc();
                    $stmt2->close();
                }
            } else {
                $error = "Error: " . $stmt->error;
            }
            if ($stmt) { $stmt->close(); }
        } else {
            $error = "Error preparing statement: " . $conn->error;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Edit Purchase Order - Cantik Homemade</title>
    <link crossorigin="" href="https://fonts.gstatic.com/" rel="preconnect"/>
    <link as="style" href="https://fonts.googleapis.com/css2?display=swap&amp;family=Noto+Sans%3Awght%40400%3B500%3B700%3B900&amp;family=Public+Sans%3Awght%40400%3B500%3B600%3B700%3B800" onload="this.rel='stylesheet'" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet"/>
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
</head>
<body class="bg-gray-50 text-gray-900" style='font-family: "Public Sans", "Noto Sans", sans-serif;'>
    <div class="relative flex size-full min-h-screen flex-col overflow-x-hidden">
        <?php include getSharedIncludePath('nav.php'); ?>
        
        <main class="flex-1 px-4 sm:px-6 lg:px-8 py-8">
            <div class="max-w-4xl mx-auto">
                <!-- Page Header -->
                <div class="mb-8">
                    <div class="flex items-center justify-between">
                        <div>
                            <h1 class="text-3xl font-bold text-gray-900">Edit Purchase Order</h1>
                            <p class="text-gray-600 mt-2">Update purchase order #<?= htmlspecialchars($po['po_number']) ?></p>
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
                                <label for="project_description" class="block text-sm font-medium text-gray-700 mb-2">Project Description</label>
                                <input type="text" id="project_description" name="project_description"
                                       value="<?= htmlspecialchars($po['project_description']) ?>"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-red-500">
                            </div>

                            <!-- Cost Center -->
                            <div>
                                <label for="cost_center" class="block text-sm font-medium text-gray-700 mb-2">Cost Center</label>
                                <input type="text" id="cost_center" name="cost_center"
                                       value="<?= htmlspecialchars($po['cost_center']) ?>"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-red-500">
                            </div>

                            <!-- SOW Number -->
                            <div>
                                <label for="sow_number" class="block text-sm font-medium text-gray-700 mb-2">SOW Number</label>
                                <input type="text" id="sow_number" name="sow_number"
                                       value="<?= htmlspecialchars($po['sow_number']) ?>"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-red-500">
                            </div>

                            <!-- Start Date -->
                            <div>
                                <label for="start_date" class="block text-sm font-medium text-gray-700 mb-2">Start Date</label>
                                <input type="date" id="start_date" name="start_date" data-accept-ddmmyyyy placeholder="dd-mmm-yyyy"
                                       value="<?= excelToDate($po['start_date']) ?>"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-red-500">
                                <p class="mt-1 text-xs text-gray-500">Enter date as dd-mm-yyyy or dd-mmm-yyyy (e.g., 03-Jun-2025). You can paste.</p>
                            </div>

                            <!-- End Date -->
                            <div>
                                <label for="end_date" class="block text-sm font-medium text-gray-700 mb-2">End Date</label>
                                <input type="date" id="end_date" name="end_date" data-accept-ddmmyyyy placeholder="dd-mmm-yyyy"
                                       value="<?= excelToDate($po['end_date']) ?>"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-red-500">
                                <p class="mt-1 text-xs text-gray-500">Enter date as dd-mm-yyyy or dd-mmm-yyyy (e.g., 03-Jun-2025). You can paste.</p>
                            </div>

                            <!-- PO Number -->
                            <div>
                                <label for="po_number" class="block text-sm font-medium text-gray-700 mb-2">PO Number *</label>
                                <input type="text" id="po_number" name="po_number" required
                                       value="<?= htmlspecialchars($po['po_number']) ?>"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-red-500">
                            </div>

                            <!-- PO Date -->
                            <div>
                                <label for="po_date" class="block text-sm font-medium text-gray-700 mb-2">PO Date</label>
                                <input type="text" id="po_date" name="po_date" data-accept-ddmmyyyy placeholder="dd-mmm-yyyy"
                                       onfocus="this.type='date'" onblur="if(!this.value) this.type='text'"
                                       value="<?= excelToDate($po['po_date']) ?>"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-red-500">
                                <p class="mt-1 text-xs text-gray-500">Enter date as dd-mm-yyyy or dd-mmm-yyyy (e.g., 03-Jun-2025). You can paste.</p>
                            </div>

                            <!-- PO Value -->
                            <div>
                                <label for="po_value" class="block text-sm font-medium text-gray-700 mb-2">PO Value</label>
                                <input type="number" id="po_value" name="po_value" step="0.01"
                                       value="<?= htmlspecialchars($po['po_value']) ?>"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-red-500">
                            </div>

                            <!-- Billing Frequency -->
                            <div>
                                <label for="billing_frequency" class="block text-sm font-medium text-gray-700 mb-2">Billing Frequency</label>
                                <select id="billing_frequency" name="billing_frequency"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-red-500">
                                    <option value="">Select frequency</option>
                                    <option value="Monthly" <?= $po['billing_frequency'] === 'Monthly' ? 'selected' : '' ?>>Monthly</option>
                                    <option value="Quarterly" <?= $po['billing_frequency'] === 'Quarterly' ? 'selected' : '' ?>>Quarterly</option>
                                    <option value="Half-yearly" <?= $po['billing_frequency'] === 'Half-yearly' ? 'selected' : '' ?>>Half-yearly</option>
                                    <option value="Yearly" <?= $po['billing_frequency'] === 'Yearly' ? 'selected' : '' ?>>Yearly</option>
                                    <option value="One-time" <?= $po['billing_frequency'] === 'One-time' ? 'selected' : '' ?>>One-time</option>
                                </select>
                            </div>

                            <!-- Target GM -->
                            <div>
                                <label for="target_gm" class="block text-sm font-medium text-gray-700 mb-2">Target GM (%)</label>
                                <input type="number" id="target_gm" name="target_gm" step="0.01" min="0" max="100"
                                       value="<?= htmlspecialchars(is_numeric($po['target_gm']) ? $po['target_gm'] * 100 : $po['target_gm']) ?>"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-red-500">
                            </div>

                            <!-- PO Status -->
                            <div>
                                <label for="po_status" class="block text-sm font-medium text-gray-700 mb-2">PO Status</label>
                                <select id="po_status" name="po_status"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-red-500">
                                    <option value="Active" <?= $po['po_status'] === 'Active' ? 'selected' : '' ?>>Active</option>
                                    <option value="Inactive" <?= $po['po_status'] === 'Inactive' ? 'selected' : '' ?>>Inactive</option>
                                    <option value="Completed" <?= $po['po_status'] === 'Completed' ? 'selected' : '' ?>>Completed</option>
                                    <option value="Cancelled" <?= $po['po_status'] === 'Cancelled' ? 'selected' : '' ?>>Cancelled</option>
                                </select>
                            </div>

                            <!-- Vendor Name -->
                            <div>
                                <label for="vendor_name" class="block text-sm font-medium text-gray-700 mb-2">Vendor Name</label>
                                <input type="text" id="vendor_name" name="vendor_name"
                                       value="<?= htmlspecialchars($po['vendor_name']) ?>"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-red-500">
                                <p class="mt-1 text-xs text-gray-500">If left blank, system may auto-derive from invoices/outsourcing data.</p>
                            </div>

                            <!-- Customer Name -->
                            <div>
                                <label for="customer_name" class="block text-sm font-medium text-gray-700 mb-2">Customer Name</label>
                                <input type="text" id="customer_name" name="customer_name"
                                       value="<?= htmlspecialchars($po['customer_name'] ?? '') ?>"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-red-500">
                            </div>

                            <!-- Remarks -->
                            <div class="md:col-span-2">
                                <label for="remarks" class="block text-sm font-medium text-gray-700 mb-2">Remarks</label>
                                <textarea id="remarks" name="remarks" rows="3"
                                          class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-red-500"><?= htmlspecialchars($po['remarks']) ?></textarea>
                            </div>
                        </div>

                        <!-- Form Actions -->
                        <div class="mt-8 flex justify-end gap-4">
                            <a href="list.php" class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-red-500">
                                Cancel
                            </a>
                            <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded-md text-sm font-medium hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500">
                                <span class="material-symbols-outlined mr-2 text-sm">save</span>
                                Update Purchase Order
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </main>
    </div>
</body>
</html>
<script>
(function() {
    const toIso = (str) => {
        if (!str) return null; const s=String(str).trim();
        let m=/^(\d{1,2})[-\/](\d{1,2})[-\/]?(\d{4})$/i.exec(s);
        if(m){const d=m[1].padStart(2,'0'); const mo=m[2].padStart(2,'0'); const y=m[3]; if(+mo>=1&&+mo<=12&&+d>=1&&+d<=31) return `${y}-${mo}-${d}`; return null;}
        m=/^(\d{1,2})[-\s]([A-Za-z]{3,})[-\s](\d{4})$/i.exec(s);
        if(m){const d=m[1].padStart(2,'0'); const mon=m[2].slice(0,3).toLowerCase(); const y=m[3]; const map={jan:'01',feb:'02',mar:'03',apr:'04',may:'05',jun:'06',jul:'07',aug:'08',sep:'09',oct:'10',nov:'11',dec:'12'}; const mo=map[mon]; if(mo&&+d>=1&&+d<=31) return `${y}-${mo}-${d}`; return null;}
        return null;
    };
    const wire=(input)=>{
        const convert=()=>{const v=input.value; const iso=toIso(v); if(iso) input.value=iso;};
        input.addEventListener('blur', convert);
        input.addEventListener('paste', (e)=>{const text=(e.clipboardData||window.clipboardData).getData('text'); const iso=toIso(text); if(iso){e.preventDefault(); input.value=iso;}});
    };
    document.querySelectorAll('input[type="date"][data-accept-ddmmyyyy]').forEach(wire);
})();
</script>