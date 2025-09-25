<?php
// Use universal includes
include 'src/shared/includes.php';

// Check authentication
if (!isset($_SESSION['username'])) {
  header('Location: login.php');
  exit();
}
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

// Formatting functions are now included from shared/formatting.php

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
    <title>Dashboard - Cantik</title>
    <link crossorigin="" href="https://fonts.gstatic.com/" rel="preconnect"/>
    <link as="style" href="https://fonts.googleapis.com/css2?display=swap&amp;family=Noto+Sans%3Awght%40400%3B500%3B700%3B900&amp;family=Public+Sans%3Awght%40400%3B500%3B600%3B700%3B800" onload="this.rel='stylesheet'" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet"/>
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
</head>
<body class="bg-gradient-to-br from-rose-100 via-sky-100 to-indigo-100 text-gray-900" style='font-family: "Public Sans", "Noto Sans", sans-serif;'>
    <div class="relative flex size-full min-h-screen flex-col overflow-x-hidden">
        <?php include 'src/shared/nav.php'; ?>   
        
        <main class="flex-1 px-4 sm:px-6 lg:px-8 py-8">
            <div class="max-w-7xl mx-auto">
                <!-- Hero / Welcome Section -->
                <div class="mb-10">
                    <div class="relative overflow-hidden rounded-2xl bg-white border border-gray-300 shadow-sm">
                        <div class="px-6 sm:px-8 py-6">
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 items-center">
                                <!-- Title + Subtitle -->
                                <div class="md:col-span-2 min-w-0">
                                    <div class="inline-flex items-center gap-2 text-[11px] font-semibold uppercase tracking-wider text-gray-600">
                                        <span class="material-symbols-outlined text-sm">dashboard</span>
                                        Dashboard Overview
                                    </div>
                                    <h1 class="mt-1 text-2xl sm:text-3xl font-bold tracking-tight text-gray-900 truncate">
                                        Welcome back, <?= htmlspecialchars($user['first_name'] ?: $user['username']) ?>
                                    </h1>
                                    <p class="mt-1 text-sm text-gray-600">Quick snapshot of your POs, invoices, and outsourcing.</p>
                                </div>
                                <!-- Compact Actions + Meta -->
                                <div class="justify-self-start md:justify-self-end w-full md:w-auto">
                                    <div class="flex items-center gap-2">
                                        <?php if (hasPermission('add_po_details')): ?>
                                        <a href="src/Modules/po_details/add.php" class="inline-flex items-center justify-center w-9 h-9 rounded-lg border border-blue-300 text-blue-700 hover:bg-blue-50 hover:border-blue-400 transition-colors" title="New PO">
                                            <span class="material-symbols-outlined text-base">add</span>
                                        </a>
                                        <?php endif; ?>
                                        <?php if (hasPermission('add_invoices')): ?>
                                        <a href="src/Modules/invoices/add.php" class="inline-flex items-center justify-center w-9 h-9 rounded-lg border border-green-300 text-green-700 hover:bg-green-50 hover:border-green-400 transition-colors" title="New Invoice">
                                            <span class="material-symbols-outlined text-base">receipt_long</span>
                                        </a>
                                        <?php endif; ?>
                                        <?php if (hasPermission('add_outsourcing')): ?>
                                        <a href="src/Modules/outsourcing/add.php" class="inline-flex items-center justify-center w-9 h-9 rounded-lg border border-purple-300 text-purple-700 hover:bg-purple-50 hover:border-purple-400 transition-colors" title="New Outsourcing">
                                            <span class="material-symbols-outlined text-base">business_center</span>
                                        </a>
                                        <?php endif; ?>
                                    </div>
                                    <div class="mt-3 flex flex-wrap items-center gap-2">
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-xs bg-gray-100 text-gray-700 border border-gray-200">
                                            <span class="material-symbols-outlined text-xs">schedule</span>
                                            <?= $user['last_login'] ? date('M j, Y g:i A', strtotime($user['last_login'])) : 'First time' ?>
                                        </span>
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-xs bg-gray-100 text-gray-700 border border-gray-200">
                                            <span class="material-symbols-outlined text-xs">person</span>
                                            <?= htmlspecialchars($user['department'] ?? 'User') ?>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Stats Section (grouped in one container) -->
                <div class="bg-white rounded-2xl border border-gray-300 shadow-md p-6 mb-10">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-lg font-semibold text-gray-900 inline-flex items-center gap-2">
                            <span class="material-symbols-outlined text-rose-600">insights</span>
                            Overview
                        </h2>
                        <div class="text-xs text-gray-500">Key metrics</div>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                    <!-- Purchase Orders Card -->
                    <div class="group relative bg-white rounded-xl shadow-sm border border-gray-300 p-6 transition-all hover:shadow-lg hover:-translate-y-0.5 hover:border-blue-400 border-l-4 border-l-blue-500">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-600">Purchase Orders</p>
                                <p class="mt-1 text-3xl font-extrabold text-gray-900"><?= number_format($totalPOs) ?></p>
                                <p class="text-sm text-gray-500">Total Value: <?= formatCurrency($totalPOValue) ?></p>
                            </div>
                            <div class="w-12 h-12 bg-blue-50 rounded-lg flex items-center justify-center ring-2 ring-inset ring-blue-300 group-hover:ring-blue-400 transition-colors">
                                <span class="material-symbols-outlined text-2xl text-blue-600">description</span>
                            </div>
                        </div>
                        <div class="mt-4">
                            <a href="src/Modules/po_details/list.php" class="inline-flex items-center gap-1 text-sm text-blue-700 hover:text-blue-900 font-medium transition-colors hover:bg-blue-50 px-2 py-1 rounded">
                                View all POs <span class="material-symbols-outlined text-sm group-hover:translate-x-0.5 transition-transform">arrow_forward</span>
                            </a>
                        </div>
                    </div>

                    <!-- Invoices Card -->
                    <div class="group relative bg-white rounded-xl shadow-sm border border-gray-300 p-6 transition-all hover:shadow-lg hover:-translate-y-0.5 hover:border-green-400 border-l-4 border-l-green-500">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-600">Invoices</p>
                                <p class="mt-1 text-3xl font-extrabold text-gray-900"><?= number_format($totalInvoices) ?></p>
                                <p class="text-sm text-gray-500">Receivable: <?= formatCurrency($totalReceivable) ?></p>
                            </div>
                            <div class="w-12 h-12 bg-green-50 rounded-lg flex items-center justify-center ring-2 ring-inset ring-green-300 group-hover:ring-green-400 transition-colors">
                                <span class="material-symbols-outlined text-2xl text-green-600">receipt</span>
                            </div>
                        </div>
                        <div class="mt-4">
                            <a href="src/Modules/invoices/list.php" class="inline-flex items-center gap-1 text-sm text-green-700 hover:text-green-900 font-medium transition-colors hover:bg-green-50 px-2 py-1 rounded">
                                View all invoices <span class="material-symbols-outlined text-sm group-hover:translate-x-0.5 transition-transform">arrow_forward</span>
                            </a>
                        </div>
                    </div>

                    <!-- Outsourcing Card -->
                    <div class="group relative bg-white rounded-xl shadow-sm border border-gray-300 p-6 transition-all hover:shadow-lg hover:-translate-y-0.5 hover:border-purple-400 border-l-4 border-l-purple-500">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-600">Outsourcing</p>
                                <p class="mt-1 text-3xl font-extrabold text-gray-900"><?= number_format($totalOutsourcing) ?></p>
                                <p class="text-sm text-gray-500">Pending: <?= formatCurrency($totalPendingOutsourcing) ?></p>
                            </div>
                            <div class="w-12 h-12 bg-purple-50 rounded-lg flex items-center justify-center ring-2 ring-inset ring-purple-300 group-hover:ring-purple-400 transition-colors">
                                <span class="material-symbols-outlined text-2xl text-purple-600">business_center</span>
                            </div>
                        </div>
                        <div class="mt-4">
                            <a href="src/Modules/outsourcing/list.php" class="inline-flex items-center gap-1 text-sm text-purple-700 hover:text-purple-900 font-medium transition-colors hover:bg-purple-50 px-2 py-1 rounded">
                                View all records <span class="material-symbols-outlined text-sm group-hover:translate-x-0.5 transition-transform">arrow_forward</span>
                            </a>
        </div>
                    </div>

                    <!-- Reports Card -->
                    <div class="group relative bg-white rounded-xl shadow-sm border border-gray-300 p-6 transition-all hover:shadow-lg hover:-translate-y-0.5 hover:border-orange-400 border-l-4 border-l-orange-500">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-600">Reports</p>
                                <p class="text-2xl font-bold text-gray-900">SO Form</p>
                                <p class="text-sm text-gray-500">Summary & Analytics</p>
                            </div>
                            <div class="w-12 h-12 bg-orange-50 rounded-lg flex items-center justify-center ring-2 ring-inset ring-orange-300 group-hover:ring-orange-400 transition-colors">
                                <span class="material-symbols-outlined text-2xl text-orange-600">assessment</span>
                            </div>
                        </div>
                        <div class="mt-4">
                            <a href="so_form.php" class="inline-flex items-center gap-1 text-sm text-orange-700 hover:text-orange-900 font-medium transition-colors hover:bg-orange-50 px-2 py-1 rounded">View reports <span class="material-symbols-outlined text-sm group-hover:translate-x-0.5 transition-transform">arrow_forward</span></a>
                        </div>
                    </div>
                    </div>
        </div>

                <!-- Quick Actions -->
                <div class="bg-white rounded-2xl shadow-md border border-gray-300 mb-10">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h2 class="text-lg font-semibold text-gray-900 inline-flex items-center gap-2">
                            <span class="material-symbols-outlined text-indigo-600">bolt</span>
                            Quick Actions
                        </h2>
                    </div>
                    <div class="p-6">
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                            <?php if (hasPermission('add_po_details')): ?>
                            <a href="src/Modules/po_details/add.php" class="flex items-center gap-3 p-4 rounded-lg border border-blue-300 hover:bg-blue-50 hover:border-blue-400 transition-colors shadow-sm">
                                <span class="material-symbols-outlined text-2xl text-blue-600">add</span>
            <div>
                                    <p class="font-medium text-gray-900">New PO</p>
                                    <p class="text-sm text-gray-500">Create purchase order</p>
                      </div>
                    </a>
                <?php endif; ?>
                            
                            <?php if (hasPermission('add_invoices')): ?>
                            <a href="src/Modules/invoices/add.php" class="flex items-center gap-3 p-4 rounded-lg border border-green-300 hover:bg-green-50 hover:border-green-400 transition-colors shadow-sm">
                                <span class="material-symbols-outlined text-2xl text-green-600">add</span>
                                <div>
                                    <p class="font-medium text-gray-900">New Invoice</p>
                                    <p class="text-sm text-gray-500">Create invoice</p>
            </div>
                            </a>
                            <?php endif; ?>
                            
                            <?php if (hasPermission('add_outsourcing')): ?>
                            <a href="src/Modules/outsourcing/add.php" class="flex items-center gap-3 p-4 rounded-lg border border-purple-300 hover:bg-purple-50 hover:border-purple-400 transition-colors shadow-sm">
                                <span class="material-symbols-outlined text-2xl text-purple-600">add</span>
            <div>
                                    <p class="font-medium text-gray-900">New Outsourcing</p>
                                    <p class="text-sm text-gray-500">Add outsourcing record</p>
                      </div>
                    </a>
                <?php endif; ?>
                            
                            <a href="so_form.php" class="flex items-center gap-3 p-4 rounded-lg border border-orange-300 hover:bg-orange-50 hover:border-orange-400 transition-colors shadow-sm">
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
                    <div class="bg-white rounded-2xl shadow-md border border-gray-300">
                        <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <span class="material-symbols-outlined text-blue-600">description</span>
                                <h3 class="text-lg font-semibold text-gray-900">Recent Purchase Orders</h3>
                            </div>
                            <a href="src/Modules/po_details/list.php" class="inline-flex items-center gap-1 text-sm text-blue-700 hover:text-blue-900 font-medium">
                                View all <span class="material-symbols-outlined text-sm">arrow_forward</span>
                            </a>
                        </div>
                        <div class="p-0">
                            <?php if ($recentPOs && $recentPOs->num_rows > 0): ?>
                                <div class="divide-y divide-gray-100">
                                    <?php while ($po = $recentPOs->fetch_assoc()): ?>
                                    <div class="group flex items-center justify-between gap-4 px-6 py-4 hover:bg-gray-50 transition-colors">
                                        <div class="min-w-0">
                                            <p class="font-medium text-gray-900 truncate"><?= htmlspecialchars($po['po_number']) ?></p>
                                            <p class="text-sm text-gray-500 truncate"><?= htmlspecialchars($po['project_description']) ?></p>
                                            <p class="text-sm text-gray-500"><?= formatCurrency($po['po_value']) ?></p>
                                        </div>
                                        <div class="flex items-center gap-3">
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?= $po['po_status'] === 'Active' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' ?>">
                                                <?= htmlspecialchars($po['po_status']) ?>
                                            </span>
                                            <span class="material-symbols-outlined text-gray-400 group-hover:text-gray-600">chevron_right</span>
                                        </div>
                                    </div>
                                    <?php endwhile; ?>
                                </div>
                            <?php else: ?>
                                <div class="p-8 text-center">
                                    <p class="text-gray-500">No recent purchase orders</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Recent Invoices -->
                    <div class="bg-white rounded-2xl shadow-md border border-gray-300">
                        <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <span class="material-symbols-outlined text-green-600">receipt</span>
                                <h3 class="text-lg font-semibold text-gray-900">Recent Invoices</h3>
                            </div>
                            <a href="src/Modules/invoices/list.php" class="inline-flex items-center gap-1 text-sm text-green-700 hover:text-green-900 font-medium">
                                View all <span class="material-symbols-outlined text-sm">arrow_forward</span>
                            </a>
                        </div>
                        <div class="p-0">
                            <?php if ($recentInvoices && $recentInvoices->num_rows > 0): ?>
                                <div class="divide-y divide-gray-100">
                                    <?php while ($invoice = $recentInvoices->fetch_assoc()): ?>
                                    <div class="group flex items-center justify-between gap-4 px-6 py-4 hover:bg-gray-50 transition-colors">
                                        <div class="min-w-0">
                                            <p class="font-medium text-gray-900 truncate"><?= htmlspecialchars($invoice['cantik_invoice_no']) ?></p>
                                            <p class="text-sm text-gray-500">Date: <?= formatDate($invoice['cantik_invoice_date']) ?></p>
                                            <p class="text-sm text-gray-500"><?= formatCurrency($invoice['receivable']) ?></p>
                                        </div>
                                        <span class="material-symbols-outlined text-gray-400 group-hover:text-gray-600">chevron_right</span>
                                    </div>
                                    <?php endwhile; ?>
                                </div>
                            <?php else: ?>
                                <div class="p-8 text-center">
                                    <p class="text-gray-500">No recent invoices</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Recent Outsourcing -->
                    <div class="bg-white rounded-2xl shadow-md border border-gray-300">
                        <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <span class="material-symbols-outlined text-purple-600">business_center</span>
                                <h3 class="text-lg font-semibold text-gray-900">Recent Outsourcing</h3>
                            </div>
                            <a href="src/Modules/outsourcing/list.php" class="inline-flex items-center gap-1 text-sm text-purple-700 hover:text-purple-900 font-medium">
                                View all <span class="material-symbols-outlined text-sm">arrow_forward</span>
                            </a>
                        </div>
                        <div class="p-0">
                            <?php if ($recentOutsourcing && $recentOutsourcing->num_rows > 0): ?>
                                <div class="divide-y divide-gray-100">
                                    <?php while ($out = $recentOutsourcing->fetch_assoc()): ?>
                                    <div class="group flex items-center justify-between gap-4 px-6 py-4 hover:bg-gray-50 transition-colors">
                                        <div class="min-w-0">
                                            <p class="font-medium text-gray-900 truncate"><?= htmlspecialchars($out['cantik_po_no']) ?></p>
                                            <p class="text-sm text-gray-500 truncate"><?= htmlspecialchars($out['project_details']) ?></p>
                                            <p class="text-sm text-gray-500 truncate"><?= htmlspecialchars($out['vendor_name']) ?></p>
                                            <p class="text-sm text-gray-500">Pending: <?= formatCurrency($out['pending_payment']) ?></p>
                                        </div>
                                        <span class="material-symbols-outlined text-gray-400 group-hover:text-gray-600">chevron_right</span>
                                    </div>
                                    <?php endwhile; ?>
                                </div>
                            <?php else: ?>
                                <div class="p-8 text-center">
                                    <p class="text-gray-500">No recent outsourcing records</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </main>
  </div>

<script src="assets/indian-numbering.js"></script>
</body>
</html>
