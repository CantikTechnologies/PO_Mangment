<?php
session_start();
if (!isset($_SESSION['username'])) {
  header('Location: 1Login_signuppage/login.php');
  exit();
}
include 'db.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Cantik Homemade - PO Management Dashboard</title>
  <meta name="description" content="Professional Purchase Order Management System for Cantik Homemade - Track POs, invoices, and outsourcing efficiently">
</head>
<body>
  <div class="container">
    <?php include 'shared/nav.php'; ?>
    
    <?php
    // Dashboard stats
    $totalPOs = 0; $totalInvoices = 0; $totalOutsourcing = 0;
    $totalPOValue = 0.0; $totalReceivable = 0.0; $totalPendingOutsourcing = 0.0;

    // Total POs and value
    if ($res = $conn->query("SELECT COUNT(*) AS cnt, COALESCE(SUM(po_value),0) AS total_value FROM purchase_orders")) {
      $row = $res->fetch_assoc();
      $totalPOs = (int)$row['cnt'];
      $totalPOValue = (float)$row['total_value'];
      $res->free();
    }

    // Total invoices and receivable sum
    if ($res = $conn->query("SELECT COUNT(*) AS cnt, COALESCE(SUM(receivable),0) AS total_recv FROM invoices")) {
      $row = $res->fetch_assoc();
      $totalInvoices = (int)$row['cnt'];
      $totalReceivable = (float)$row['total_recv'];
      $res->free();
    }

    // Total outsourcing and pending sum
    if ($res = $conn->query("SELECT COUNT(*) AS cnt, COALESCE(SUM(pending_payment),0) AS total_pending FROM outsourcing_details")) {
      $row = $res->fetch_assoc();
      $totalOutsourcing = (int)$row['cnt'];
      $totalPendingOutsourcing = (float)$row['total_pending'];
      $res->free();
    }

    function formatCurrency($amount) {
      return '₹ ' . number_format($amount, 0, '.', ',');
    }
    ?>

    <main>
      <!-- Page Header -->
      <div class="page-header">
        <div>
          <h1>Dashboard</h1>
          <p>Overview of your purchase orders, invoices, and business metrics</p>
        </div>
      </div>

      <!-- Stats Grid -->
      <div class="stats-grid">
        <a href="po_details/list.php" class="stat-card">
          <div class="stat-header">
            <div class="stat-label">Purchase Orders</div>
            <i class="fas fa-file-text stat-icon"></i>
          </div>
          <div class="stat-value"><?php echo number_format($totalPOs); ?></div>
          <div class="stat-sub">Total Value: <?php echo formatCurrency($totalPOValue); ?></div>
          <div class="stat-change">
            <i class="fas fa-trending-up"></i>
            +12% from last month
          </div>
        </a>

        <a href="invoices/list.php" class="stat-card accent">
          <div class="stat-header">
            <div class="stat-label">Invoices</div>
            <i class="fas fa-receipt stat-icon" style="color: var(--accent);"></i>
          </div>
          <div class="stat-value"><?php echo number_format($totalInvoices); ?></div>
          <div class="stat-sub">Total Receivable: <?php echo formatCurrency($totalReceivable); ?></div>
          <div class="stat-change">
            <i class="fas fa-trending-up"></i>
            +8% from last month
          </div>
        </a>

        <a href="outsourcing/list.php" class="stat-card warning">
          <div class="stat-header">
            <div class="stat-label">Outsourcing</div>
            <i class="fas fa-users stat-icon" style="color: var(--warning);"></i>
          </div>
          <div class="stat-value"><?php echo number_format($totalOutsourcing); ?></div>
          <div class="stat-sub">Pending Payments: <?php echo formatCurrency($totalPendingOutsourcing); ?></div>
          <div class="stat-change" style="color: var(--warning);">
            <i class="fas fa-exclamation-circle"></i>
            3 pending reviews
          </div>
        </a>

        <a href="so_form.php" class="stat-card muted">
          <div class="stat-header">
            <div class="stat-label">SO Form</div>
            <i class="fas fa-cog stat-icon" style="color: var(--muted-foreground);"></i>
          </div>
          <div class="stat-value">Report</div>
          <div class="stat-sub">Aggregated overview</div>
          <div class="stat-change" style="color: var(--muted-foreground);">
            <i class="fas fa-clock"></i>
            Last updated today
          </div>
        </a>
      </div>

      <!-- Quick Actions -->
      <div class="card quick-actions">
        <div class="card-header">
          <h2 class="card-title">
            <i class="fas fa-plus"></i>
            Quick Actions
          </h2>
          <p class="card-description">Quickly add new records or access important features</p>
        </div>
        <div class="card-content">
          <div class="quick-grid">
            <a href="po_details/add.php" class="btn btn-primary">
              <i class="fas fa-plus"></i>
              New PO
            </a>
            <a href="invoices/add.php" class="btn btn-outline">
              <i class="fas fa-plus"></i>
              New Invoice
            </a>
            <a href="outsourcing/add.php" class="btn btn-outline">
              <i class="fas fa-plus"></i>
              New Outsourcing
            </a>
            <a href="so_form.php" class="btn btn-secondary">
              <i class="fas fa-cog"></i>
              Open SO Form
            </a>
          </div>
        </div>
      </div>

      <!-- Recent Activity -->
      <div class="card">
        <div class="card-header">
          <h2 class="card-title">Recent Activity</h2>
          <p class="card-description">Latest purchase orders and invoices</p>
        </div>
        <div class="card-content">
          <div class="grid-responsive">
            <!-- Recent POs -->
            <div>
              <h3 class="font-semibold mt-0 mb-4">
                <i class="fas fa-file-text"></i>
                Latest Purchase Orders
              </h3>
              <ul class="list-clean">
                <?php
                $recentPOs = $conn->query("SELECT po_id, po_number, project_description, po_status FROM purchase_orders ORDER BY po_id DESC LIMIT 5");
                if ($recentPOs && $recentPOs->num_rows): 
                  while($r = $recentPOs->fetch_assoc()): ?>
                    <li class="item-row">
                      <a href="po_details/edit.php?id=<?php echo (int)$r['po_id']; ?>" style="text-decoration:none;color:inherit">
                        <div style="display: flex; justify-content: space-between; align-items: center;">
                          <div>
                            <div class="font-medium"><?php echo htmlspecialchars($r['po_number']); ?></div>
                            <div class="muted-ellipsis"><?php echo htmlspecialchars($r['project_description']); ?></div>
                          </div>
                          <div class="badge badge-<?php echo strtolower($r['po_status']) === 'active' ? 'accent' : (strtolower($r['po_status']) === 'completed' ? 'primary' : 'warning'); ?>">
                            <?php echo htmlspecialchars($r['po_status']); ?>
                          </div>
                        </div>
                      </a>
                    </li>
                  <?php endwhile; 
                else: ?>
                  <li class="muted-text">No recent POs.</li>
                <?php endif; ?>
              </ul>
            </div>

            <!-- Recent Invoices -->
            <div>
              <h3 class="font-semibold mt-0 mb-4">
                <i class="fas fa-receipt"></i>
                Latest Invoices
              </h3>
              <ul class="list-clean">
                <?php
                $recentInv = $conn->query("SELECT invoice_id, cantik_invoice_no, cantik_invoice_date, receivable FROM invoices ORDER BY invoice_id DESC LIMIT 5");
                if ($recentInv && $recentInv->num_rows): 
                  while($r = $recentInv->fetch_assoc()): ?>
                    <li class="item-row">
                      <a href="invoices/edit.php?id=<?php echo (int)$r['invoice_id']; ?>" style="text-decoration:none;color:inherit">
                        <div style="display: flex; justify-content: space-between; align-items: center;">
                          <div>
                            <div class="font-medium"><?php echo htmlspecialchars($r['cantik_invoice_no']); ?></div>
                            <div class="muted-text"><?php echo htmlspecialchars($r['cantik_invoice_date']); ?></div>
                          </div>
                          <div class="font-medium">
                            <?php echo formatCurrency($r['receivable']); ?>
                          </div>
                        </div>
                      </a>
                    </li>
                  <?php endwhile; 
                else: ?>
                  <li class="muted-text">No recent invoices.</li>
                <?php endif; ?>
              </ul>
            </div>
          </div>
        </div>
      </div>
    </main>
  </div>
</body>
</html>