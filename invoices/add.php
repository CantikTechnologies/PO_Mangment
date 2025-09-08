<?php
session_start();
if (!isset($_SESSION['username'])) {
  header('Location: ../1Login_signuppage/login.php');
  exit();
}
include '../db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $po_number = $_POST['po_number'];

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
    $inv_no = $_POST['cantik_invoice_no'];
    $inv_date = $_POST['cantik_invoice_date'] ?: null;
    $taxable = $_POST['taxable_value'] ?: 0;
    $vendor_inv = $_POST['vendor_invoice_no'];
    $payment_receipt_date = $_POST['payment_receipt_date'] ?: null;
    $payment_advise_no = $_POST['payment_advise_no'];
    $vendor_name = $_POST['vendor_name'];

    $stmt = $conn->prepare("INSERT INTO invoices (po_id, cantik_invoice_no, cantik_invoice_date, taxable_value, vendor_invoice_no, payment_receipt_date, payment_advise_no, vendor_name) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("issdssss", $po_id, $inv_no, $inv_date, $taxable, $vendor_inv, $payment_receipt_date, $payment_advise_no, $vendor_name);

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
  <title>Add Invoice</title>
  <link rel="stylesheet" href="../assets/style.css?v=<?php echo time(); ?>">
</head>
<body>
  <div class="container form-page">
    <?php include '../shared/nav.php'; ?>
    <main>
      <div class="page-header">
        <h1>Add New Invoice</h1>
      </div>
      <div class="card">
        <?php if (isset($error)): ?>
          <div class="alert danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        <form method="post">
          <div class="form-group">
            <label>Select PO Number
              <select name="po_number" id="po_select" required onchange="fetchPODetails()">
                <option value="">-- Select PO Number --</option>
                <?php
                $pos = $conn->query("SELECT po_id, po_number, project_description, vendor_name, cost_center FROM purchase_orders ORDER BY po_number");
                if ($pos && $pos->num_rows > 0) {
                  while ($po = $pos->fetch_assoc()) {
                    echo '<option value="' . htmlspecialchars($po['po_number']) . '" data-po-id="' . $po['po_id'] . '" data-project="' . htmlspecialchars($po['project_description']) . '" data-vendor="' . htmlspecialchars($po['vendor_name']) . '" data-cost-center="' . htmlspecialchars($po['cost_center']) . '">' . htmlspecialchars($po['po_number']) . ' - ' . htmlspecialchars($po['project_description']) . '</option>';
                  }
                }
                ?>
              </select>
            </label>
            <label>Cantik Invoice No<input name="cantik_invoice_no" required></label>
            <label>Cantik Invoice Date<input type="date" name="cantik_invoice_date"></label>
          </div>
          <div class="form-group">
            <label>Taxable Value<input type="number" step="0.01" name="taxable_value" required></label>
            <label>Vendor Invoice No<input name="vendor_invoice_no"></label>
          </div>
          <hr>
          <div class="form-group">
            <label>Payment Receipt Date<input type="date" name="payment_receipt_date"></label>
            <label>Payment Advise No<input name="payment_advise_no"></label>
            <label>Vendor Name<input name="vendor_name" id="vendor_name" readonly></label>
          </div>
          <div class="form-group">
            <label>Project Description<input name="project_description" id="project_description" readonly></label>
            <label>Cost Center<input name="cost_center" id="cost_center" readonly></label>
          </div>

          <div class="actions">
            <button type="submit">Save Invoice</button>
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
