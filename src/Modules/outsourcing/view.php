<?php
session_start();
if (!isset($_SESSION['username'])) {
    header('Location: ../../../login.php');
    exit();
}
include '../../../config/db.php';
include '../../../config/auth.php';
requirePermission('view_outsourcing');

$id = intval($_GET['id'] ?? 0);
if (!$id) {
    header('Location: list.php');
    exit();
}

// Get outsourcing data
$sql = "SELECT * FROM outsourcing_detail WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$outsourcing = $result->fetch_assoc();
$stmt->close();

if (!$outsourcing) {
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
$po_stmt->bind_param("s", $outsourcing['customer_po']);
$po_stmt->execute();
$po_result = $po_stmt->get_result();
$po = $po_result->fetch_assoc();
$po_stmt->close();

function formatCurrency($amount) {
    return '₹' . number_format((float)$amount, 2);
}

function getBadgeClass($status) {
    switch (strtolower($status)) {
        case 'paid': return 'bg-green-100 text-green-800';
        case 'pending': return 'bg-yellow-100 text-yellow-800';
        case 'partial': return 'bg-blue-100 text-blue-800';
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
    <title>View Outsourcing Record - Cantik Homemade</title>
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
                            <h1 class="text-3xl font-bold text-gray-900">Outsourcing Record Details</h1>
                            <p class="text-gray-600 mt-2">Record #<?= htmlspecialchars($outsourcing['cantik_po_no']) ?></p>
                        </div>
                        <div class="flex gap-3">
                            <?php if (hasPermission('edit_outsourcing')): ?>
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
                    <!-- Outsourcing Details Card -->
                    <div class="lg:col-span-2">
                        <div class="bg-white rounded-lg shadow-sm border border-gray-200">
                            <div class="px-6 py-4 border-b border-gray-200">
                                <h2 class="text-lg font-semibold text-gray-900">Outsourcing Information</h2>
                            </div>
                            <div class="p-6">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <!-- Project Details -->
                                    <div class="md:col-span-2">
                                        <label class="block text-sm font-medium text-gray-500 mb-1">Project Details</label>
                                        <p class="text-gray-900"><?= htmlspecialchars($outsourcing['project_details']) ?></p>
                                    </div>

                                    <!-- Cost Center -->
                                    <div>
                                        <label class="block text-sm font-medium text-gray-500 mb-1">Cost Center</label>
                                        <p class="text-gray-900"><?= htmlspecialchars($outsourcing['cost_center']) ?></p>
                                    </div>

                                    <!-- Customer PO -->
                                    <div>
                                        <label class="block text-sm font-medium text-gray-500 mb-1">Customer PO</label>
                                        <p class="text-gray-900"><?= htmlspecialchars($outsourcing['customer_po']) ?></p>
                                    </div>

                                    <!-- Vendor Name -->
                                    <div class="md:col-span-2">
                                        <label class="block text-sm font-medium text-gray-500 mb-1">Vendor Name</label>
                                        <p class="text-gray-900"><?= htmlspecialchars($outsourcing['vendor_name']) ?></p>
                                    </div>

                                    <!-- Cantik PO Number -->
                                    <div>
                                        <label class="block text-sm font-medium text-gray-500 mb-1">Cantik PO Number</label>
                                        <p class="text-lg font-semibold text-gray-900"><?= htmlspecialchars($outsourcing['cantik_po_no']) ?></p>
                                    </div>

                                    <!-- Cantik PO Date -->
                                    <div>
                                        <label class="block text-sm font-medium text-gray-500 mb-1">Cantik PO Date</label>
                                        <p class="text-gray-900"><?= excelToDate($outsourcing['cantik_po_date']) ?></p>
                                    </div>

                                    <!-- Vendor Invoice Number -->
                                    <div>
                                        <label class="block text-sm font-medium text-gray-500 mb-1">Vendor Invoice Number</label>
                                        <p class="text-gray-900"><?= htmlspecialchars($outsourcing['vendor_inv_number']) ?></p>
                                    </div>

                                    <!-- Vendor Invoice Date -->
                                    <div>
                                        <label class="block text-sm font-medium text-gray-500 mb-1">Vendor Invoice Date</label>
                                        <p class="text-gray-900"><?= excelToDate($outsourcing['vendor_inv_date']) ?></p>
                                    </div>

                                    <!-- Vendor Invoice Frequency -->
                                    <div>
                                        <label class="block text-sm font-medium text-gray-500 mb-1">Invoice Frequency</label>
                                        <p class="text-gray-900"><?= htmlspecialchars($outsourcing['vendor_invoice_frequency']) ?></p>
                                    </div>

                                    <!-- Payment Status -->
                                    <div>
                                        <label class="block text-sm font-medium text-gray-500 mb-1">Payment Status</label>
                                        <span class="inline-flex px-2 py-1 text-xs font-medium rounded-full <?= getBadgeClass($outsourcing['payment_status_from_ntt']) ?>">
                                            <?= htmlspecialchars($outsourcing['payment_status_from_ntt'] ?: 'Not specified') ?>
                                        </span>
                                    </div>

                                    <!-- Payment Date -->
                                    <div>
                                        <label class="block text-sm font-medium text-gray-500 mb-1">Payment Date</label>
                                        <p class="text-gray-900"><?= excelToDate($outsourcing['payment_date']) ?: 'Not paid' ?></p>
                                    </div>

                                    <!-- Remarks -->
                                    <?php if (!empty($outsourcing['remarks'])): ?>
                                    <div class="md:col-span-2">
                                        <label class="block text-sm font-medium text-gray-500 mb-1">Remarks</label>
                                        <p class="text-gray-900"><?= htmlspecialchars($outsourcing['remarks']) ?></p>
                                    </div>
                                    <?php endif; ?>
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
                                        <span class="text-sm text-gray-500">Cantik PO Value</span>
                                        <span class="font-semibold text-gray-900"><?= formatCurrency($outsourcing['cantik_po_value']) ?></span>
                                    </div>
                                    <div class="flex justify-between items-center">
                                        <span class="text-sm text-gray-500">Vendor Invoice Value</span>
                                        <span class="font-semibold text-gray-900"><?= formatCurrency($outsourcing['vendor_inv_value']) ?></span>
                                    </div>
                                    <div class="flex justify-between items-center">
                                        <span class="text-sm text-gray-500">TDS Deducted (2%)</span>
                                        <span class="font-semibold text-gray-900"><?= formatCurrency($outsourcing['tds_ded']) ?></span>
                                    </div>
                                    <div class="border-t pt-4">
                                        <div class="flex justify-between items-center">
                                            <span class="text-sm text-gray-500">Net Payable</span>
                                            <span class="text-lg font-bold text-blue-600"><?= formatCurrency($outsourcing['net_payble']) ?></span>
                                        </div>
                                    </div>
                                    <div class="flex justify-between items-center">
                                        <span class="text-sm text-gray-500">Payment Value</span>
                                        <span class="font-semibold text-gray-900"><?= formatCurrency($outsourcing['payment_value']) ?></span>
                                    </div>
                                    <div class="border-t pt-4">
                                        <div class="flex justify-between items-center">
                                            <?php 
                                              $net = (float)$outsourcing['net_payble'];
                                              $paid = ($outsourcing['payment_value'] === null || $outsourcing['payment_value'] === '') ? 0.0 : (float)$outsourcing['payment_value'];
                                              $status = strtolower(trim((string)($outsourcing['payment_status_from_ntt'] ?? '')));
                                              $pending = $net - $paid;
                                              if ($status === 'paid') { $pending = 0.0; }
                                              if ($paid <= 0) { $pending = $net; }
                                              if ($pending < 0) { $pending = 0.0; }
                                              $pendingClass = $pending > 0 ? 'text-red-600' : 'text-green-600';
                                            ?>
                                            <span class="text-sm text-gray-500">Pending Payment</span>
                                            <span class="text-lg font-bold <?= $pendingClass ?>"><?= formatCurrency($pending) ?></span>
                                        </div>
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
                                    <?php if (hasPermission('edit_outsourcing')): ?>
                                    <a href="edit.php?id=<?= $id ?>" class="w-full inline-flex items-center justify-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-red-500">
                                        <span class="material-symbols-outlined mr-2 text-sm">edit</span>
                                        Edit Record
                                    </a>
                                    <?php endif; ?>
                                    
                                    <button onclick="window.print()" class="w-full inline-flex items-center justify-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-red-500">
                                        <span class="material-symbols-outlined mr-2 text-sm">print</span>
                                        Print Record
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Payment Status & Calculation Breakdown -->
                <div class="mt-8 grid grid-cols-1 lg:grid-cols-2 gap-8">
                    <!-- Payment Status -->
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200">
                        <div class="px-6 py-4 border-b border-gray-200">
                            <h2 class="text-lg font-semibold text-gray-900">Payment Status</h2>
                        </div>
                        <div class="p-6">
                            <div class="space-y-4">
                                <div class="flex justify-between items-center">
                                    <span class="text-sm text-gray-500">Payment Status</span>
                                    <span class="inline-flex px-3 py-1 text-sm font-medium rounded-full <?= getBadgeClass($outsourcing['payment_status_from_ntt']) ?>">
                                        <?= htmlspecialchars($outsourcing['payment_status_from_ntt'] ?: 'Not specified') ?>
                                    </span>
                                </div>
                                <div class="flex justify-between items-center">
                                    <span class="text-sm text-gray-500">Payment Date</span>
                                    <span class="text-gray-900"><?= excelToDate($outsourcing['payment_date']) ?: 'Not paid' ?></span>
                                </div>
                                <div class="flex justify-between items-center">
                                    <span class="text-sm text-gray-500">Payment Value</span>
                                    <span class="font-semibold text-gray-900"><?= formatCurrency($outsourcing['payment_value']) ?></span>
                                </div>
                                <div class="border-t pt-4">
                                    <div class="flex justify-between items-center">
                                        <span class="text-sm text-gray-500">Outstanding Amount</span>
                                        <span class="text-lg font-bold text-red-600"><?= formatCurrency($outsourcing['pending_payment']) ?></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Calculation Breakdown -->
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200">
                        <div class="px-6 py-4 border-b border-gray-200">
                            <h2 class="text-lg font-semibold text-gray-900">Calculation Breakdown</h2>
                        </div>
                        <div class="p-6">
                            <div class="space-y-4">
                                <div class="text-center p-4 bg-gray-50 rounded-lg">
                                    <h3 class="text-sm font-medium text-gray-500 mb-2">Vendor Invoice Value</h3>
                                    <p class="text-xl font-bold text-gray-900"><?= formatCurrency($outsourcing['vendor_inv_value']) ?></p>
                                    <p class="text-xs text-gray-500 mt-1">Base invoice amount</p>
                                </div>
                                <div class="text-center p-4 bg-blue-50 rounded-lg">
                                    <h3 class="text-sm font-medium text-blue-600 mb-2">GST (18%)</h3>
                                    <p class="text-xl font-bold text-blue-600"><?= formatCurrency($outsourcing['vendor_inv_value'] * 0.18) ?></p>
                                    <p class="text-xs text-blue-500 mt-1">18% of invoice value</p>
                                </div>
                                <div class="text-center p-4 bg-red-50 rounded-lg">
                                    <h3 class="text-sm font-medium text-red-600 mb-2">TDS (2%)</h3>
                                    <p class="text-xl font-bold text-red-600"><?= formatCurrency($outsourcing['tds_ded']) ?></p>
                                    <p class="text-xs text-red-500 mt-1">2% deduction</p>
                                </div>
                                <div class="text-center p-4 bg-green-50 rounded-lg">
                                    <h3 class="text-sm font-medium text-green-600 mb-2">Net Payable</h3>
                                    <p class="text-2xl font-bold text-green-600"><?= formatCurrency($outsourcing['net_payble']) ?></p>
                                    <p class="text-sm text-green-500 mt-1">Invoice + GST - TDS</p>
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
