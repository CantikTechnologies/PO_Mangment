<?php
session_start();
if (!isset($_SESSION['username'])) {
    header('Location: ../../../login.php');
    exit();
}
include '../../../config/db.php';
include '../../../config/auth.php';
requirePermission('view_invoices');

$id = intval($_GET['id'] ?? 0);
if (!$id) {
    header('Location: list.php');
    exit();
}

// Get invoice data
$sql = "SELECT * FROM billing_details WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$invoice = $result->fetch_assoc();
$stmt->close();

if (!$invoice) {
    header('Location: list.php');
    exit();
}

// Convert Excel dates to readable format
function excelToDate($excel_date) {
    if (empty($excel_date)) return '';
    $unix_date = ($excel_date - 25569) * 86400;
    return date('d-m-Y', $unix_date);
}

// Get related PO details
$po_sql = "SELECT * FROM po_details WHERE po_number = ?";
$po_stmt = $conn->prepare($po_sql);
$po_stmt->bind_param("s", $invoice['customer_po']);
$po_stmt->execute();
$po_result = $po_stmt->get_result();
$po = $po_result->fetch_assoc();
$po_stmt->close();

function formatCurrency($amount) {
    return '₹ ' . number_format($amount, 0, '.', ',');
}

function getBadgeClass($status) {
    switch (strtolower($status)) {
        case 'paid': return 'bg-green-100 text-green-800';
        case 'pending': return 'bg-yellow-100 text-yellow-800';
        case 'overdue': return 'bg-red-100 text-red-800';
        default: return 'bg-gray-100 text-gray-800';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>View Invoice - Cantik Homemade</title>
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
                            <h1 class="text-3xl font-bold text-gray-900">Invoice Details</h1>
                            <p class="text-gray-600 mt-2">Invoice #<?= htmlspecialchars($invoice['cantik_invoice_no']) ?></p>
                        </div>
                        <div class="flex gap-3">
                            <?php if (hasPermission('edit_invoices')): ?>
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
                    <!-- Invoice Details Card -->
                    <div class="lg:col-span-2">
                        <div class="bg-white rounded-lg shadow-sm border border-gray-200">
                            <div class="px-6 py-4 border-b border-gray-200">
                                <h2 class="text-lg font-semibold text-gray-900">Invoice Information</h2>
                            </div>
                            <div class="p-6">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <!-- Invoice Number -->
                                    <div>
                                        <label class="block text-sm font-medium text-gray-500 mb-1">Cantik Invoice Number</label>
                                        <p class="text-lg font-semibold text-gray-900"><?= htmlspecialchars($invoice['cantik_invoice_no']) ?></p>
                                    </div>

                                    <!-- Invoice Date -->
                                    <div>
                                        <label class="block text-sm font-medium text-gray-500 mb-1">Invoice Date</label>
                                        <p class="text-gray-900"><?= excelToDate($invoice['cantik_invoice_date']) ?></p>
                                    </div>

                                    <!-- Project Details -->
                                    <div class="md:col-span-2">
                                        <label class="block text-sm font-medium text-gray-500 mb-1">Project Details</label>
                                        <p class="text-gray-900"><?= htmlspecialchars($invoice['project_details']) ?></p>
                                    </div>

                                    <!-- Cost Center -->
                                    <div>
                                        <label class="block text-sm font-medium text-gray-500 mb-1">Cost Center</label>
                                        <p class="text-gray-900"><?= htmlspecialchars($invoice['cost_center']) ?></p>
                                    </div>

                                    <!-- Customer PO -->
                                    <div>
                                        <label class="block text-sm font-medium text-gray-500 mb-1">Customer PO</label>
                                        <p class="text-gray-900"><?= htmlspecialchars($invoice['customer_po']) ?></p>
                                    </div>

                                    <!-- Vendor Name -->
                                    <div class="md:col-span-2">
                                        <label class="block text-sm font-medium text-gray-500 mb-1">Vendor Name</label>
                                        <p class="text-gray-900"><?= htmlspecialchars($invoice['vendor_name'] ?: 'No vendor assigned') ?></p>
                                    </div>

                                    <!-- Against Vendor Invoice Number -->
                                    <div>
                                        <label class="block text-sm font-medium text-gray-500 mb-1">Against Vendor Invoice Number</label>
                                        <p class="text-gray-900"><?= htmlspecialchars($invoice['against_vendor_inv_number'] ?: 'Not specified') ?></p>
                                    </div>

                                    <!-- Payment Advise Number -->
                                    <div>
                                        <label class="block text-sm font-medium text-gray-500 mb-1">Payment Advise Number</label>
                                        <p class="text-gray-900"><?= htmlspecialchars($invoice['payment_advise_no'] ?: 'Not specified') ?></p>
                                    </div>

                                    <!-- Payment Receipt Date -->
                                    <div>
                                        <label class="block text-sm font-medium text-gray-500 mb-1">Payment Receipt Date</label>
                                        <p class="text-gray-900"><?= excelToDate($invoice['payment_receipt_date']) ?: 'Not received' ?></p>
                                    </div>

                                    <!-- Remaining Balance -->
                                    <div>
                                        <label class="block text-sm font-medium text-gray-500 mb-1">Remaining Balance in PO</label>
                                        <p class="text-gray-900"><?= formatCurrency($invoice['remaining_balance_in_po']) ?></p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Financial Summary Card -->
                    <div class="lg:col-span-1">
                        <div class="bg-white rounded-lg shadow-sm border border-gray-200">
                            <div class="px-6 py-4 border-b border-gray-200">
                                <h2 class="text-lg font-semibold text-gray-900">Financial Summary</h2>
                            </div>
                            <div class="p-6">
                                <div class="space-y-4">
                                    <div class="flex justify-between items-center">
                                        <span class="text-sm text-gray-500">Taxable Value</span>
                                        <span class="font-semibold text-gray-900"><?= formatCurrency($invoice['cantik_inv_value_taxable']) ?></span>
                                    </div>
                                    <div class="flex justify-between items-center">
                                        <span class="text-sm text-gray-500">TDS (2%)</span>
                                        <span class="font-semibold text-gray-900"><?= formatCurrency($invoice['tds']) ?></span>
                                    </div>
                                    <div class="border-t pt-4">
                                        <div class="flex justify-between items-center">
                                            <span class="text-sm text-gray-500">Receivable</span>
                                            <span class="text-lg font-bold text-green-600"><?= formatCurrency($invoice['receivable']) ?></span>
                                        </div>
                                    </div>
                                    <div class="flex justify-between items-center">
                                        <span class="text-sm text-gray-500">Remaining Balance</span>
                                        <span class="font-semibold text-gray-900"><?= formatCurrency($invoice['remaining_balance_in_po']) ?></span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Related PO Information -->
                        <?php if ($po): ?>
                        <div class="mt-6 bg-white rounded-lg shadow-sm border border-gray-200">
                            <div class="px-6 py-4 border-b border-gray-200">
                                <h2 class="text-lg font-semibold text-gray-900">Related PO</h2>
                            </div>
                            <div class="p-6">
                                <div class="space-y-3">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-500 mb-1">PO Number</label>
                                        <p class="text-gray-900"><?= htmlspecialchars($po['po_number']) ?></p>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-500 mb-1">PO Value</label>
                                        <p class="text-gray-900"><?= formatCurrency($po['po_value']) ?></p>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-500 mb-1">Status</label>
                                        <span class="inline-flex px-2 py-1 text-xs font-medium rounded-full <?= getBadgeClass($po['po_status']) ?>">
                                            <?= htmlspecialchars($po['po_status']) ?>
                                        </span>
                                    </div>
                                    <div class="pt-3 border-t">
                                        <a href="../po_details/view.php?id=<?= $po['id'] ?>" class="w-full inline-flex items-center justify-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-red-500">
                                            <span class="material-symbols-outlined mr-2 text-sm">visibility</span>
                                            View PO Details
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>

                        <!-- Quick Actions -->
                        <div class="mt-6 bg-white rounded-lg shadow-sm border border-gray-200">
                            <div class="px-6 py-4 border-b border-gray-200">
                                <h2 class="text-lg font-semibold text-gray-900">Quick Actions</h2>
                            </div>
                            <div class="p-6">
                                <div class="space-y-3">
                                    <?php if (hasPermission('edit_invoices')): ?>
                                    <a href="edit.php?id=<?= $id ?>" class="w-full inline-flex items-center justify-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-red-500">
                                        <span class="material-symbols-outlined mr-2 text-sm">edit</span>
                                        Edit Invoice
                                    </a>
                                    <?php endif; ?>
                                    
                                    <button onclick="window.print()" class="w-full inline-flex items-center justify-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-red-500">
                                        <span class="material-symbols-outlined mr-2 text-sm">print</span>
                                        Print Invoice
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Calculation Breakdown -->
                <div class="mt-8">
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200">
                        <div class="px-6 py-4 border-b border-gray-200">
                            <h2 class="text-lg font-semibold text-gray-900">Calculation Breakdown</h2>
                        </div>
                        <div class="p-6">
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                <div class="text-center p-4 bg-gray-50 rounded-lg">
                                    <h3 class="text-sm font-medium text-gray-500 mb-2">Taxable Value</h3>
                                    <p class="text-2xl font-bold text-gray-900"><?= formatCurrency($invoice['cantik_inv_value_taxable']) ?></p>
                                    <p class="text-xs text-gray-500 mt-1">Base amount before taxes</p>
                                </div>
                                <div class="text-center p-4 bg-blue-50 rounded-lg">
                                    <h3 class="text-sm font-medium text-blue-600 mb-2">GST (18%)</h3>
                                    <p class="text-2xl font-bold text-blue-600"><?= formatCurrency($invoice['cantik_inv_value_taxable'] * 0.18) ?></p>
                                    <p class="text-xs text-blue-500 mt-1">18% of taxable value</p>
                                </div>
                                <div class="text-center p-4 bg-red-50 rounded-lg">
                                    <h3 class="text-sm font-medium text-red-600 mb-2">TDS (2%)</h3>
                                    <p class="text-2xl font-bold text-red-600"><?= formatCurrency($invoice['tds']) ?></p>
                                    <p class="text-xs text-red-500 mt-1">2% deduction</p>
                                </div>
                            </div>
                            <div class="mt-6 text-center p-4 bg-green-50 rounded-lg">
                                <h3 class="text-sm font-medium text-green-600 mb-2">Net Receivable</h3>
                                <p class="text-3xl font-bold text-green-600"><?= formatCurrency($invoice['receivable']) ?></p>
                                <p class="text-sm text-green-500 mt-1">Taxable + GST - TDS</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</body>
</html>
