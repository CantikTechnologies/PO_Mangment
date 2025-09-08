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
  <title>PO Management Dashboard</title>
  <link rel="stylesheet" href="assets/style.css?v=<?php echo time(); ?>">
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
    ?>

    <main>
      <section class="dashboard">
        <div class="page-header">
          <h1>Dashboard</h1>
        </div>
        <div class="stats-grid">
          <a class="stat-card" href="po_details/list.php">
            <div class="stat-label">Purchase Orders</div>
            <div class="stat-value"><?php echo number_format($totalPOs); ?></div>
            <div class="stat-sub">Total PO Value: ₹ <?php echo number_format($totalPOValue, 2); ?></div>
          </a>
          <a class="stat-card" href="invoices/list.php">
            <div class="stat-label">Invoices</div>
            <div class="stat-value"><?php echo number_format($totalInvoices); ?></div>
            <div class="stat-sub">Total Receivable: ₹ <?php echo number_format($totalReceivable, 2); ?></div>
          </a>
          <a class="stat-card" href="outsourcing/list.php">
            <div class="stat-label">Outsourcing</div>
            <div class="stat-value"><?php echo number_format($totalOutsourcing); ?></div>
            <div class="stat-sub">Pending Payments: ₹ <?php echo number_format($totalPendingOutsourcing, 2); ?></div>
          </a>
          <a class="stat-card" href="so_form.php">
            <div class="stat-label">SO Form</div>
            <div class="stat-value">Report</div>
            <div class="stat-sub">Aggregated overview</div>
          </a>
        </div>

        <div class="quick-actions card">
          <h2>Quick Actions</h2>
          <div class="quick-grid">
            <a class="btn" href="po_details/add.php">+ New PO</a>
            <a class="btn" href="invoices/add.php">+ New Invoice</a>
            <a class="btn" href="outsourcing/add.php">+ New Outsourcing</a>
            <a class="btn muted" href="so_form.php">Open SO Form</a>
          </div>
        </div>

        <?php
        // Recent activity
        $recentPOs = $conn->query("SELECT po_id, po_number, project_description FROM purchase_orders ORDER BY po_id DESC LIMIT 5");
        $recentInv = $conn->query("SELECT invoice_id, cantik_invoice_no, cantik_invoice_date FROM invoices ORDER BY invoice_id DESC LIMIT 5");
        ?>
        <div class="card">
          <h2>Recent Activity</h2>
          <div class="grid-responsive">
            <div>
              <h3 style="margin-top:0">Latest POs</h3>
              <ul class="list-clean">
                <?php if ($recentPOs && $recentPOs->num_rows): while($r=$recentPOs->fetch_assoc()): ?>
                  <li class="item-row">
                    <a href="po_details/edit.php?id=<?php echo (int)$r['po_id']; ?>" style="text-decoration:none;color:inherit">
                      <strong><?php echo htmlspecialchars($r['po_number']); ?></strong>
                      <div class="muted-ellipsis">
                        <?php echo htmlspecialchars($r['project_description']); ?>
                      </div>
                    </a>
                  </li>
                <?php endwhile; else: ?>
                  <li>No recent POs.</li>
                <?php endif; ?>
              </ul>
            </div>
            <div>
              <h3 style="margin-top:0">Latest Invoices</h3>
              <ul class="list-clean">
                <?php if ($recentInv && $recentInv->num_rows): while($r=$recentInv->fetch_assoc()): ?>
                  <li class="item-row">
                    <a href="invoices/edit.php?id=<?php echo (int)$r['invoice_id']; ?>" style="text-decoration:none;color:inherit">
                      <strong><?php echo htmlspecialchars($r['cantik_invoice_no']); ?></strong>
                      <div class="muted-text">
                        <?php echo htmlspecialchars($r['cantik_invoice_date']); ?>
                      </div>
                    </a>
                  </li>
                <?php endwhile; else: ?>
                  <li>No recent invoices.</li>
                <?php endif; ?>
              </ul>
            </div>
          </div>
        </div>

        <div class="card">
          <h2>Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?></h2>
          <p>Use the navigation above to manage Purchase Orders, Invoices and Outsourcing records. The SO Form provides a consolidated report across modules.</p>
        </div>
      </section>
    </main>

    <footer>
      
    </footer>
  </div>
<script src="assets/script.js"></script>
</body>
</html>
