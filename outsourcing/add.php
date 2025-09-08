<?php
session_start();
if (!isset($_SESSION['username'])) {
  header('Location: ../1Login_signuppage/login.php');
  exit();
}
include '../db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $po_number = $_POST['customer_po_number'];

  $stmt = $conn->prepare("SELECT po_id FROM purchase_orders WHERE po_number = ? LIMIT 1");
  $stmt->bind_param("s", $po_number);
  $stmt->execute();
  $result = $stmt->get_result();
  $po = $result->fetch_assoc();
  $stmt->close();

  if (!$po) {
    $error = "PO Number not found.";
  } else {
    $po_id = $po['po_id'];
    $cantik_po_no = $_POST['cantik_po_no'];
    $cantik_po_date = $_POST['cantik_po_date'] ?: null;
    $cantik_po_value = $_POST['cantik_po_value'] ?: 0;
    $vendor_invoice_no = $_POST['vendor_invoice_no'];
    $vendor_invoice_date = $_POST['vendor_invoice_date'] ?: null;
    $vendor_invoice_value = $_POST['vendor_invoice_value'] ?: 0;
    $payment_date = $_POST['payment_date'] ?: null;
    $remarks = $_POST['remarks'];
    // Check if column payment_status_from_ntt exists
    $colExists = false;
    if ($resCol = $conn->query("SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'outsourcing_details' AND COLUMN_NAME = 'payment_status_from_ntt'")) {
      $colExists = $resCol->num_rows > 0;
      $resCol->free();
    }

    if ($colExists) {
      $payment_status_from_ntt = $_POST['payment_status_from_ntt'] ?? '';
      $stmt = $conn->prepare("INSERT INTO outsourcing_details (po_id, cantik_po_no, cantik_po_date, cantik_po_value, vendor_invoice_no, vendor_invoice_date, vendor_invoice_value, payment_status_from_ntt, payment_date, remarks) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
      $stmt->bind_param("issdsdssss", $po_id, $cantik_po_no, $cantik_po_date, $cantik_po_value, $vendor_invoice_no, $vendor_invoice_date, $vendor_invoice_value, $payment_status_from_ntt, $payment_date, $remarks);
    } else {
      $stmt = $conn->prepare("INSERT INTO outsourcing_details (po_id, cantik_po_no, cantik_po_date, cantik_po_value, vendor_invoice_no, vendor_invoice_date, vendor_invoice_value, payment_date, remarks) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
      $stmt->bind_param("issdsdsss", $po_id, $cantik_po_no, $cantik_po_date, $cantik_po_value, $vendor_invoice_no, $vendor_invoice_date, $vendor_invoice_value, $payment_date, $remarks);
    }

    if ($stmt->execute()) {
      header('Location: list.php');
      exit;
    } else {
      $error = "Error: " . $stmt->error;
    }
    $stmt->close();
  }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Add Outsourcing</title>
  <link rel="stylesheet" href="../assets/style.css?v=<?php echo time(); ?>">
</head>
<body>
  <div class="container form-page">
    <?php include '../shared/nav.php'; ?>
    <main>
      <div class="page-header">
        <h1>Add New Outsourcing Record</h1>
      </div>
      <div class="card">
        <?php if (isset($error)): ?>
          <div class="alert danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        <form method="post">
          <div class="form-group">
            <label>Select PO Number
              <select name="customer_po_number" id="po_select" required onchange="fetchPODetails()">
                <option value="">-- Select PO Number --</option>
                <?php
                $pos = $conn->query("SELECT po_id, po_number, project_description, vendor_name, cost_center, po_value, po_date FROM purchase_orders ORDER BY po_number");
                if ($pos && $pos->num_rows > 0) {
                  while ($po = $pos->fetch_assoc()) {
                    echo '<option value="' . htmlspecialchars($po['po_number']) . '" data-po-id="' . $po['po_id'] . '" data-project="' . htmlspecialchars($po['project_description']) . '" data-vendor="' . htmlspecialchars($po['vendor_name']) . '" data-cost-center="' . htmlspecialchars($po['cost_center']) . '" data-po-value="' . $po['po_value'] . '" data-po-date="' . $po['po_date'] . '">' . htmlspecialchars($po['po_number']) . ' - ' . htmlspecialchars($po['project_description']) . '</option>';
                  }
                }
                ?>
              </select>
            </label>
          </div>
          <div class="form-group">
            <label>Project Description<input name="project_description" id="project_description" readonly></label>
            <label>Vendor Name<input name="vendor_name" id="vendor_name" readonly></label>
            <label>Cost Center<input name="cost_center" id="cost_center" readonly></label>
          </div>
          <hr>
          <h4>Cantik PO Details</h4>
          <div class="form-group">
            <label>Cantik PO No<input name="cantik_po_no" required></label>
            <label>Cantik PO Date<input type="date" name="cantik_po_date"></label>
            <label>Cantik PO Value<input type="number" step="0.01" name="cantik_po_value" required></label>
          </div>
          <hr>
          <h4>Vendor Invoice Details</h4>
          <div class="form-group">
            <label>Vendor Invoice No<input name="vendor_invoice_no" required></label>
            <label>Vendor Invoice Date<input type="date" name="vendor_invoice_date"></label>
            <label>Vendor Invoice Value<input type="number" step="0.01" name="vendor_invoice_value" required></label>
          </div>
          <hr>
          <h4>Payment Details</h4>
          <div class="form-group">
            <label>Payment Status from NTT
              <select name="payment_status_from_ntt">
                <option value="">-- Select Status --</option>
                <option value="Paid">Paid</option>
                <option value="Pending">Pending</option>
                <option value="Overdue">Overdue</option>
                <option value="Cancelled">Cancelled</option>
              </select>
            </label>
            <label>Payment Date<input type="date" name="payment_date"></label>
          </div>
          <div class="form-group">
            <label>Remarks<textarea name="remarks"></textarea></label>
          </div>
          <div class="actions">
            <button type="submit">Save Record</button>
            <a href="list.php" class="btn muted">Cancel</a>
          </div>
        </form>
      </div>
    </main>
  </div>
  <script src="../assets/script.js"></script>
  <script>
    function fetchPODetails() {
      const select = document.getElementById('po_select');
      const selectedOption = select.options[select.selectedIndex];
      
      if (selectedOption.value) {
        document.getElementById('vendor_name').value = selectedOption.dataset.vendor || '';
        document.getElementById('project_description').value = selectedOption.dataset.project || '';
        document.getElementById('cost_center').value = selectedOption.dataset.costCenter || '';
      } else {
        document.getElementById('vendor_name').value = '';
        document.getElementById('project_description').value = '';
        document.getElementById('cost_center').value = '';
      }
    }
  </script>
</body>
</html>
