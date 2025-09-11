<?php
session_start();
if (!isset($_SESSION['username'])) {
  header('Location: login.php');
  exit();
}
include 'config/db.php';
include 'config/auth.php';
requirePermission('view_dashboard');

// Get current user
$user = getCurrentUser();

    // Dashboard stats
$totalPOs = 0;
$totalInvoices = 0;
$totalOutsourcing = 0;
$totalPOValue = 0.0;
$totalReceivable = 0.0;
$totalPendingOutsourcing = 0.0;

    // Total POs and value
if ($res = $conn->query("SELECT COUNT(*) AS cnt, COALESCE(SUM(po_value),0) AS total_value FROM po_details")) {
      $row = $res->fetch_assoc();
      $totalPOs = (int)$row['cnt'];
      $totalPOValue = (float)$row['total_value'];
      $res->free();
    }

    // Total invoices and receivable sum
if ($res = $conn->query("SELECT COUNT(*) AS cnt, COALESCE(SUM(receivable),0) AS total_recv FROM billing_details")) {
      $row = $res->fetch_assoc();
      $totalInvoices = (int)$row['cnt'];
      $totalReceivable = (float)$row['total_recv'];
      $res->free();
    }

    // Total outsourcing and pending sum
if ($res = $conn->query("SELECT COUNT(*) AS cnt, COALESCE(SUM(pending_payment),0) AS total_pending FROM outsourcing_detail")) {
      $row = $res->fetch_assoc();
      $totalOutsourcing = (int)$row['cnt'];
      $totalPendingOutsourcing = (float)$row['total_pending'];
      $res->free();
    }

function formatCurrency($amount) {
    return '₹ ' . number_format($amount, 0, '.', ',');
}

// Get recent activity
$recentPOs = $conn->query("SELECT id, po_number, project_description, po_status, po_value FROM po_details ORDER BY created_at DESC LIMIT 5");
$recentInvoices = $conn->query("SELECT id, cantik_invoice_no, cantik_invoice_date, receivable FROM billing_details ORDER BY created_at DESC LIMIT 5");
$recentOutsourcing = $conn->query("SELECT id, cantik_po_no, project_details, vendor_name, pending_payment FROM outsourcing_detail ORDER BY created_at DESC LIMIT 5");

function formatDate($excel_date) {
    if (empty($excel_date)) return '-';
    $unix_date = ($excel_date - 25569) * 86400;
    return date('M j, Y', $unix_date);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Dashboard - Cantik Homemade</title>
    <link crossorigin="" href="https://fonts.gstatic.com/" rel="preconnect"/>
    <link as="style" href="https://fonts.googleapis.com/css2?display=swap&amp;family=Noto+Sans%3Awght%40400%3B500%3B700%3B900&amp;family=Public+Sans%3Awght%40400%3B500%3B600%3B700%3B800" onload="this.rel='stylesheet'" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet"/>
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
</head>
<body class="bg-gray-50 text-gray-900" style='font-family: "Public Sans", "Noto Sans", sans-serif;'>
    <div class="relative flex size-full min-h-screen flex-col overflow-x-hidden">
        <?php include 'src/shared/nav.php'; ?>   
        
        <main class="flex-1 px-4 sm:px-6 lg:px-8 py-8">
            <div class="max-w-7xl mx-auto">
                <!-- Welcome Section -->
                <div class="mb-8">
                    <div class="flex items-center justify-between">
                        <div>
                            <h1 class="text-3xl font-bold text-gray-900">Welcome back, <?= htmlspecialchars($user['first_name'] ?: $user['username']) ?>!</h1>
                            <p class="text-gray-600 mt-2">Here's what's happening with your business today.</p>
                        </div>
                        <div class="flex items-center gap-4">
                            <div class="text-right">
                                <p class="text-sm text-gray-500">Last login</p>
                                <p class="text-sm font-medium text-gray-900">
                                    <?= $user['last_login'] ? date('M j, Y g:i A', strtotime($user['last_login'])) : 'First time' ?>
                                </p>
                            </div>
                            <div class="flex items-center gap-2">
                                <?php if (isset($user['profile_picture']) && $user['profile_picture'] && file_exists($user['profile_picture'])): ?>
                                    <img src="<?= htmlspecialchars($user['profile_picture']) ?>" 
                                         alt="Profile" 
                                         class="w-12 h-12 rounded-full object-cover border-2 border-gray-200">
                                <?php else: ?>
                                    <div class="w-12 h-12 rounded-full bg-gray-200 flex items-center justify-center border-2 border-gray-200">
                                        <span class="material-symbols-outlined text-2xl text-gray-400">person</span>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Stats Grid -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                    <!-- Purchase Orders Card -->
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 hover:shadow-md transition-shadow">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-600">Purchase Orders</p>
                                <p class="text-2xl font-bold text-gray-900"><?= number_format($totalPOs) ?></p>
                                <p class="text-sm text-gray-500">Total Value: <?= formatCurrency($totalPOValue) ?></p>
                            </div>
                            <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                                <span class="material-symbols-outlined text-2xl text-blue-600">description</span>
                            </div>
                        </div>
                        <div class="mt-4">
                            <a href="src/Modules/po_details/list.php" class="text-sm text-blue-600 hover:text-blue-800 font-medium">View all POs →</a>
                        </div>
                    </div>

                    <!-- Invoices Card -->
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 hover:shadow-md transition-shadow">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-600">Invoices</p>
                                <p class="text-2xl font-bold text-gray-900"><?= number_format($totalInvoices) ?></p>
                                <p class="text-sm text-gray-500">Receivable: <?= formatCurrency($totalReceivable) ?></p>
                            </div>
                            <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                                <span class="material-symbols-outlined text-2xl text-green-600">receipt</span>
                            </div>
                        </div>
                        <div class="mt-4">
                            <a href="src/Modules/invoices/list.php" class="text-sm text-green-600 hover:text-green-800 font-medium">View all invoices →</a>
                        </div>
                    </div>

                    <!-- Outsourcing Card -->
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 hover:shadow-md transition-shadow">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-600">Outsourcing</p>
                                <p class="text-2xl font-bold text-gray-900"><?= number_format($totalOutsourcing) ?></p>
                                <p class="text-sm text-gray-500">Pending: <?= formatCurrency($totalPendingOutsourcing) ?></p>
                            </div>
                            <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center">
                                <span class="material-symbols-outlined text-2xl text-purple-600">business_center</span>
                            </div>
                        </div>
                        <div class="mt-4">
                            <a href="src/Modules/outsourcing/list.php" class="text-sm text-purple-600 hover:text-purple-800 font-medium">View all records →</a>
        </div>
        </div>

                    <!-- Reports Card -->
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 hover:shadow-md transition-shadow">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-600">Reports</p>
                                <p class="text-2xl font-bold text-gray-900">SO Form</p>
                                <p class="text-sm text-gray-500">Summary & Analytics</p>
                            </div>
                            <div class="w-12 h-12 bg-orange-100 rounded-lg flex items-center justify-center">
                                <span class="material-symbols-outlined text-2xl text-orange-600">assessment</span>
                            </div>
                        </div>
                        <div class="mt-4">
                            <a href="so_form.php" class="text-sm text-orange-600 hover:text-orange-800 font-medium">View reports →</a>
                        </div>
          </div>
        </div>

                <!-- Quick Actions -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 mb-8">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h2 class="text-lg font-semibold text-gray-900">Quick Actions</h2>
                    </div>
                    <div class="p-6">
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                            <?php if (hasPermission('add_po_details')): ?>
                            <a href="src/Modules/po_details/add.php" class="flex items-center gap-3 p-4 rounded-lg border border-gray-200 hover:bg-gray-50 transition-colors">
                                <span class="material-symbols-outlined text-2xl text-blue-600">add</span>
            <div>
                                    <p class="font-medium text-gray-900">New PO</p>
                                    <p class="text-sm text-gray-500">Create purchase order</p>
                      </div>
                    </a>
                <?php endif; ?>
                            
                            <?php if (hasPermission('add_invoices')): ?>
                            <a href="src/Modules/invoices/add.php" class="flex items-center gap-3 p-4 rounded-lg border border-gray-200 hover:bg-gray-50 transition-colors">
                                <span class="material-symbols-outlined text-2xl text-green-600">add</span>
                                <div>
                                    <p class="font-medium text-gray-900">New Invoice</p>
                                    <p class="text-sm text-gray-500">Create invoice</p>
            </div>
                            </a>
                            <?php endif; ?>
                            
                            <?php if (hasPermission('add_outsourcing')): ?>
                            <a href="src/Modules/outsourcing/add.php" class="flex items-center gap-3 p-4 rounded-lg border border-gray-200 hover:bg-gray-50 transition-colors">
                                <span class="material-symbols-outlined text-2xl text-purple-600">add</span>
            <div>
                                    <p class="font-medium text-gray-900">New Outsourcing</p>
                                    <p class="text-sm text-gray-500">Add outsourcing record</p>
                      </div>
                    </a>
                <?php endif; ?>
                            
                            <a href="so_form.php" class="flex items-center gap-3 p-4 rounded-lg border border-gray-200 hover:bg-gray-50 transition-colors">
                                <span class="material-symbols-outlined text-2xl text-orange-600">assessment</span>
                                <div>
                                    <p class="font-medium text-gray-900">View Reports</p>
                                    <p class="text-sm text-gray-500">Summary & analytics</p>
                                </div>
                            </a>
            </div>
          </div>
        </div>

                <!-- Recent Activity -->
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <!-- Recent POs -->
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200">
                        <div class="px-6 py-4 border-b border-gray-200">
                            <h3 class="text-lg font-semibold text-gray-900">Recent Purchase Orders</h3>
                        </div>
                        <div class="p-6">
                            <?php if ($recentPOs && $recentPOs->num_rows > 0): ?>
                                <div class="space-y-4">
                                    <?php while ($po = $recentPOs->fetch_assoc()): ?>
                                    <div class="flex items-center justify-between p-3 rounded-lg bg-gray-50 hover:bg-gray-100 transition-colors">
                                        <div>
                                            <p class="font-medium text-gray-900"><?= htmlspecialchars($po['po_number']) ?></p>
                                            <p class="text-sm text-gray-500"><?= htmlspecialchars($po['project_description']) ?></p>
                                            <p class="text-sm text-gray-500"><?= formatCurrency($po['po_value']) ?></p>
                                        </div>
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?= $po['po_status'] === 'Active' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' ?>">
                                            <?= htmlspecialchars($po['po_status']) ?>
                                        </span>
                                    </div>
                                    <?php endwhile; ?>
                                </div>
                            <?php else: ?>
                                <p class="text-gray-500 text-center py-4">No recent purchase orders</p>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Recent Invoices -->
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200">
                        <div class="px-6 py-4 border-b border-gray-200">
                            <h3 class="text-lg font-semibold text-gray-900">Recent Invoices</h3>
                        </div>
                        <div class="p-6">
                            <?php if ($recentInvoices && $recentInvoices->num_rows > 0): ?>
                                <div class="space-y-4">
                                    <?php while ($invoice = $recentInvoices->fetch_assoc()): ?>
                                    <div class="flex items-center justify-between p-3 rounded-lg bg-gray-50 hover:bg-gray-100 transition-colors">
                                        <div>
                                            <p class="font-medium text-gray-900"><?= htmlspecialchars($invoice['cantik_invoice_no']) ?></p>
                                            <p class="text-sm text-gray-500">Date: <?= formatDate($invoice['cantik_invoice_date']) ?></p>
                                            <p class="text-sm text-gray-500"><?= formatCurrency($invoice['receivable']) ?></p>
                                        </div>
                                        <span class="material-symbols-outlined text-green-600">receipt</span>
                                    </div>
                                    <?php endwhile; ?>
                                </div>
                            <?php else: ?>
                                <p class="text-gray-500 text-center py-4">No recent invoices</p>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Recent Outsourcing -->
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200">
                        <div class="px-6 py-4 border-b border-gray-200">
                            <h3 class="text-lg font-semibold text-gray-900">Recent Outsourcing</h3>
                        </div>
                        <div class="p-6">
                            <?php if ($recentOutsourcing && $recentOutsourcing->num_rows > 0): ?>
                                <div class="space-y-4">
                                    <?php while ($out = $recentOutsourcing->fetch_assoc()): ?>
                                    <div class="flex items-center justify-between p-3 rounded-lg bg-gray-50 hover:bg-gray-100 transition-colors">
                                        <div>
                                            <p class="font-medium text-gray-900"><?= htmlspecialchars($out['cantik_po_no']) ?></p>
                                            <p class="text-sm text-gray-500"><?= htmlspecialchars($out['project_details']) ?></p>
                                            <p class="text-sm text-gray-500"><?= htmlspecialchars($out['vendor_name']) ?></p>
                                            <p class="text-sm text-gray-500">Pending: <?= formatCurrency($out['pending_payment']) ?></p>
                                        </div>
                                        <span class="material-symbols-outlined text-purple-600">business_center</span>
                                    </div>
                                    <?php endwhile; ?>
                                </div>
                            <?php else: ?>
                                <p class="text-gray-500 text-center py-4">No recent outsourcing records</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </main>
  </div>
</body>
</html>
