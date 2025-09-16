<?php
session_start();
if (!isset($_SESSION['username'])) {
    header('Location: ../../../login.php');
    exit();
}
include '../../../config/db.php';
include '../../../config/auth.php';
requirePermission('view_po_details');

$id = intval($_GET['id'] ?? 0);
if (!$id) {
    header('Location: list.php');
    exit();
}

// Get PO data
$sql = "SELECT pd.*, 
        (SELECT od.vendor_name FROM outsourcing_detail od WHERE od.customer_po = pd.po_number ORDER BY od.id DESC LIMIT 1) AS vendor_from_outsourcing,
        (SELECT bd.vendor_name FROM billing_details bd WHERE bd.customer_po = pd.po_number ORDER BY bd.id DESC LIMIT 1) AS vendor_from_billing
        FROM po_details pd WHERE pd.id = ?";
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

// Convert Excel dates to readable format
function excelToDate($excel_date) {
    if (empty($excel_date)) return '';
    $unix_date = ($excel_date - 25569) * 86400;
    return date('d-m-Y', $unix_date);
}

// Get related invoices
$invoice_sql = "SELECT * FROM billing_details WHERE customer_po = ? ORDER BY id DESC";
$invoice_stmt = $conn->prepare($invoice_sql);
$invoice_stmt->bind_param("s", $po['po_number']);
$invoice_stmt->execute();
$invoices = $invoice_stmt->get_result();
$invoice_stmt->close();

// Get related outsourcing records
$outsourcing_sql = "SELECT * FROM outsourcing_detail WHERE customer_po = ? ORDER BY id DESC";
$outsourcing_stmt = $conn->prepare($outsourcing_sql);
$outsourcing_stmt->bind_param("s", $po['po_number']);
$outsourcing_stmt->execute();
$outsourcing_records = $outsourcing_stmt->get_result();
$outsourcing_stmt->close();

function formatCurrency($amount) {
    return '₹ ' . number_format($amount, 0, '.', ',');
}

function getBadgeClass($status) {
    switch (strtolower($status)) {
        case 'active': return 'bg-green-100 text-green-800';
        case 'completed': return 'bg-blue-100 text-blue-800';
        case 'pending': return 'bg-yellow-100 text-yellow-800';
        case 'cancelled': return 'bg-red-100 text-red-800';
        default: return 'bg-gray-100 text-gray-800';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>View Purchase Order - Cantik Homemade</title>
    <link crossorigin="" href="https://fonts.gstatic.com/" rel="preconnect"/>
    <link as="style" href="https://fonts.googleapis.com/css2?display=swap&amp;family=Noto+Sans%3Awght%40400%3B500%3B700%3B900&amp;family=Public+Sans%3Awght%40400%3B500%3B600%3B700%3B800" onload="this.rel='stylesheet'" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet"/>
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
</head>
<body class="bg-gray-50 text-gray-900" style='font-family: "Public Sans", "Noto Sans", sans-serif;'>
    <div class="relative flex size-full min-h-screen flex-col overflow-x-hidden">
        <?php include '../../shared/nav.php'; ?>
        
        <main class="flex-1 px-4 sm:px-6 lg:px-8 py-8">
            <div class="max-w-6xl mx-auto">
                <!-- Page Header -->
                <div class="mb-8">
                    <div class="flex items-center justify-between">
                        <div>
                            <h1 class="text-3xl font-bold text-gray-900">Purchase Order Details</h1>
                            <p class="text-gray-600 mt-2">PO #<?= htmlspecialchars($po['po_number']) ?></p>
                        </div>
                        <div class="flex gap-3">
                            <?php if (hasPermission('edit_po_details')): ?>
                            <a href="edit.php?id=<?= $id ?>" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-red-500">
                                <span class="material-symbols-outlined mr-2 text-sm">edit</span>
                                Edit
                            </a>
                            <?php endif; ?>
                            <a href="list.php" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-red-500">
                                <span class="material-symbols-outlined mr-2 text-sm">arrow_back</span>
                                Back to List
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Main Content -->
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                    <!-- PO Details Card -->
                    <div class="lg:col-span-2">
                        <div class="bg-white rounded-lg shadow-sm border border-gray-200">
                            <div class="px-6 py-4 border-b border-gray-200">
                                <h2 class="text-lg font-semibold text-gray-900">Purchase Order Information</h2>
                            </div>
                            <div class="p-6">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <!-- PO Number -->
                                    <div>
                                        <label class="block text-sm font-medium text-gray-500 mb-1">PO Number</label>
                                        <p class="text-lg font-semibold text-gray-900"><?= htmlspecialchars($po['po_number']) ?></p>
                                    </div>

                                    <!-- Status -->
                                    <div>
                                        <label class="block text-sm font-medium text-gray-500 mb-1">Status</label>
                                        <span class="inline-flex px-2 py-1 text-xs font-medium rounded-full <?= getBadgeClass($po['po_status']) ?>">
                                            <?= htmlspecialchars($po['po_status']) ?>
                                        </span>
                                    </div>

                                    <!-- Project Description -->
                                    <div class="md:col-span-2">
                                        <label class="block text-sm font-medium text-gray-500 mb-1">Project Description</label>
                                        <p class="text-gray-900"><?= htmlspecialchars($po['project_description']) ?></p>
                                    </div>

                                    <!-- Cost Center -->
                                    <div>
                                        <label class="block text-sm font-medium text-gray-500 mb-1">Cost Center</label>
                                        <p class="text-gray-900"><?= htmlspecialchars($po['cost_center']) ?></p>
                                    </div>

                                    <!-- SOW Number -->
                                    <div>
                                        <label class="block text-sm font-medium text-gray-500 mb-1">SOW Number</label>
                                        <p class="text-gray-900"><?= htmlspecialchars($po['sow_number']) ?></p>
                                    </div>

                                    <!-- Vendor Name -->
                                    <div class="md:col-span-2">
                                        <label class="block text-sm font-medium text-gray-500 mb-1">Vendor Name</label>
                                        <?php $derived_vendor = $po['vendor_name'] ?: ($po['vendor_from_outsourcing'] ?: $po['vendor_from_billing']); ?>
                                        <p class="text-gray-900"><?= htmlspecialchars($derived_vendor ?: 'No vendor assigned') ?></p>
                                    </div>

                                    <!-- Dates -->
                                    <div>
                                        <label class="block text-sm font-medium text-gray-500 mb-1">Start Date</label>
                                        <p class="text-gray-900"><?= excelToDate($po['start_date']) ?></p>
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-500 mb-1">End Date</label>
                                        <p class="text-gray-900"><?= excelToDate($po['end_date']) ?></p>
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-500 mb-1">PO Date</label>
                                        <p class="text-gray-900"><?= excelToDate($po['po_date']) ?></p>
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-500 mb-1">Billing Frequency</label>
                                        <p class="text-gray-900"><?= htmlspecialchars($po['billing_frequency']) ?></p>
                                    </div>

                                    <!-- Financial Information -->
                                    <div>
                                        <label class="block text-sm font-medium text-gray-500 mb-1">PO Value</label>
                                        <p class="text-xl font-bold text-gray-900"><?= formatCurrency($po['po_value']) ?></p>
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-500 mb-1">Pending Amount</label>
                                        <p class="text-lg font-semibold text-gray-900"><?= formatCurrency($po['pending_amount']) ?></p>
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-500 mb-1">Target GM</label>
                                        <p class="text-gray-900"><?= is_numeric($po['target_gm']) ? (($po['target_gm'] < 1) ? rtrim(rtrim(number_format((float)$po['target_gm'] * 100, 1), '0'), '.') . '%' : rtrim(rtrim(number_format((float)$po['target_gm'], 1), '0'), '.') . '%') : '0%' ?></p>
                                    </div>

                                    <!-- Remarks -->
                                    <?php if (!empty($po['remarks'])): ?>
                                    <div class="md:col-span-2">
                                        <label class="block text-sm font-medium text-gray-500 mb-1">Remarks</label>
                                        <p class="text-gray-900"><?= htmlspecialchars($po['remarks']) ?></p>
                                    </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Summary Card -->
                    <div class="lg:col-span-1">
                        <div class="bg-white rounded-lg shadow-sm border border-gray-200">
                            <div class="px-6 py-4 border-b border-gray-200">
                                <h2 class="text-lg font-semibold text-gray-900">Summary</h2>
                            </div>
                            <div class="p-6">
                                <div class="space-y-4">
                                    <div class="flex justify-between items-center">
                                        <span class="text-sm text-gray-500">Total PO Value</span>
                                        <span class="font-semibold text-gray-900"><?= formatCurrency($po['po_value']) ?></span>
                                    </div>
                                    <div class="flex justify-between items-center">
                                        <span class="text-sm text-gray-500">Pending Amount</span>
                                        <span class="font-semibold text-gray-900"><?= formatCurrency($po['pending_amount']) ?></span>
                                    </div>
                                    <div class="flex justify-between items-center">
                                        <span class="text-sm text-gray-500">Target GM</span>
                                        <span class="font-semibold text-gray-900"><?= is_numeric($po['target_gm']) ? (($po['target_gm'] < 1) ? rtrim(rtrim(number_format((float)$po['target_gm'] * 100, 1), '0'), '.') . '%' : rtrim(rtrim(number_format((float)$po['target_gm'], 1), '0'), '.') . '%') : '0%' ?></span>
                                    </div>
                                    <div class="border-t pt-4">
                                        <div class="flex justify-between items-center">
                                            <span class="text-sm text-gray-500">Status</span>
                                            <span class="inline-flex px-2 py-1 text-xs font-medium rounded-full <?= getBadgeClass($po['po_status']) ?>">
                                                <?= htmlspecialchars($po['po_status']) ?>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Quick Actions -->
                        <div class="mt-6 bg-white rounded-lg shadow-sm border border-gray-200">
                            <div class="px-6 py-4 border-b border-gray-200">
                                <h2 class="text-lg font-semibold text-gray-900">Quick Actions</h2>
                            </div>
                            <div class="p-6">
                                <div class="space-y-3">
                                    <?php if (hasPermission('edit_po_details')): ?>
                                    <a href="edit.php?id=<?= $id ?>" class="w-full inline-flex items-center justify-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-red-500">
                                        <span class="material-symbols-outlined mr-2 text-sm">edit</span>
                                        Edit PO
                                    </a>
                                    <?php endif; ?>
                                    
                                    <?php if (hasPermission('add_invoices')): ?>
                                    <a href="../invoices/add.php?po_number=<?= urlencode($po['po_number']) ?>" class="w-full inline-flex items-center justify-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-red-500">
                                        <span class="material-symbols-outlined mr-2 text-sm">receipt</span>
                                        Add Invoice
                                    </a>
                                    <?php endif; ?>
                                    
                                    <?php if (hasPermission('add_outsourcing')): ?>
                                    <a href="../outsourcing/add.php?po_number=<?= urlencode($po['po_number']) ?>" class="w-full inline-flex items-center justify-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-red-500">
                                        <span class="material-symbols-outlined mr-2 text-sm">business</span>
                                        Add Outsourcing
                                    </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Related Records -->
                <div class="mt-8 grid grid-cols-1 lg:grid-cols-2 gap-8">
                    <!-- Invoices -->
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200">
                        <div class="px-6 py-4 border-b border-gray-200">
                            <div class="flex items-center justify-between">
                                <h2 class="text-lg font-semibold text-gray-900">Related Invoices</h2>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                    <?= $invoices->num_rows ?> invoice<?= $invoices->num_rows !== 1 ? 's' : '' ?>
                                </span>
                            </div>
                        </div>
                        <div class="p-6">
                            <?php if ($invoices->num_rows > 0): ?>
                                <div class="space-y-4">
                                    <?php while ($invoice = $invoices->fetch_assoc()): ?>
                                    <div class="border border-gray-200 rounded-lg p-4">
                                        <div class="flex justify-between items-start">
                                            <div>
                                                <h3 class="font-medium text-gray-900"><?= htmlspecialchars($invoice['cantik_invoice_no']) ?></h3>
                                                <p class="text-sm text-gray-500"><?= excelToDate($invoice['cantik_invoice_date']) ?></p>
                                                <p class="text-sm text-gray-500"><?= htmlspecialchars($invoice['vendor_name'] ?: 'No vendor') ?></p>
                                            </div>
                                            <div class="text-right">
                                                <p class="font-semibold text-gray-900"><?= formatCurrency($invoice['cantik_inv_value_taxable']) ?></p>
                                                <p class="text-sm text-gray-500">Receivable: <?= formatCurrency($invoice['receivable']) ?></p>
                                            </div>
                                        </div>
                                    </div>
                                    <?php endwhile; ?>
                                </div>
                            <?php else: ?>
                                <p class="text-gray-500 text-center py-4">No invoices found for this PO</p>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Outsourcing Records -->
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200">
                        <div class="px-6 py-4 border-b border-gray-200">
                            <div class="flex items-center justify-between">
                                <h2 class="text-lg font-semibold text-gray-900">Outsourcing Records</h2>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                    <?= $outsourcing_records->num_rows ?> record<?= $outsourcing_records->num_rows !== 1 ? 's' : '' ?>
                                </span>
                            </div>
                        </div>
                        <div class="p-6">
                            <?php if ($outsourcing_records->num_rows > 0): ?>
                                <div class="space-y-4">
                                    <?php while ($outsourcing = $outsourcing_records->fetch_assoc()): ?>
                                    <div class="border border-gray-200 rounded-lg p-4">
                                        <div class="flex justify-between items-start">
                                            <div>
                                                <h3 class="font-medium text-gray-900"><?= htmlspecialchars($outsourcing['cantik_po_no']) ?></h3>
                                                <p class="text-sm text-gray-500"><?= excelToDate($outsourcing['cantik_po_date']) ?></p>
                                                <p class="text-sm text-gray-500"><?= htmlspecialchars($outsourcing['vendor_name']) ?></p>
                                            </div>
                                            <div class="text-right">
                                                <p class="font-semibold text-gray-900"><?= formatCurrency($outsourcing['cantik_po_value']) ?></p>
                                                <p class="text-sm text-gray-500">Pending: <?= formatCurrency($outsourcing['pending_payment']) ?></p>
                                            </div>
                                        </div>
                                    </div>
                                    <?php endwhile; ?>
                                </div>
                            <?php else: ?>
                                <p class="text-gray-500 text-center py-4">No outsourcing records found for this PO</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</body>
</html>
