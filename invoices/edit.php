<?php
session_start();
if (!isset($_SESSION['username'])) {
  header('Location: ../1Login_signuppage/login.php');
  exit();
}
include '../db.php';

$id = intval($_GET['id'] ?? 0);
if (!$id) {
  header('Location: list.php');
  exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $inv_no = $_POST['cantik_invoice_no'];
  $inv_date = $_POST['cantik_invoice_date'] ?: null;
  $taxable = $_POST['taxable_value'] ?: 0;
  $vendor_inv = $_POST['vendor_invoice_no'];
  $payment_receipt_date = $_POST['payment_receipt_date'] ?: null;
  $payment_advise_no = $_POST['payment_advise_no'];
  $vendor_name = $_POST['vendor_name'];

  $stmt = $conn->prepare("UPDATE invoices SET cantik_invoice_no = ?, cantik_invoice_date = ?, taxable_value = ?, vendor_invoice_no = ?, payment_receipt_date = ?, payment_advise_no = ?, vendor_name = ? WHERE invoice_id = ?");
  $stmt->bind_param("ssdssssi", $inv_no, $inv_date, $taxable, $vendor_inv, $payment_receipt_date, $payment_advise_no, $vendor_name, $id);

  if ($stmt->execute()) {
    header('Location: list.php');
    exit;
  } else {
    $error = "Error: " . $stmt->error;
  }
  $stmt->close();
}

$stmt = $conn->prepare("SELECT inv.*, po.po_number FROM invoices inv JOIN purchase_orders po ON po.po_id = inv.po_id WHERE inv.invoice_id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
$stmt->close();

if (!$row) {
  header('Location: list.php');
  exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Edit Invoice</title>
  <link rel="stylesheet" href="../assets/style.css">
</head>
<body>
  <div class="container">
    <?php include '../shared/nav.php'; ?>
    <main>
      <div class="page-header">
        <h1>Edit Invoice #<?= htmlspecialchars($row['cantik_invoice_no']) ?></h1>
      </div>
      <div class="card">
        <?php if (isset($error)): ?>
          <div class="alert danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        <form method="post">
          <div class="form-group">
            <label>Customer PO Number<input name="po_number" value="<?= htmlspecialchars($row['po_number']) ?>" disabled></label>
            <label>Cantik Invoice No<input name="cantik_invoice_no" value="<?= htmlspecialchars($row['cantik_invoice_no']) ?>"></label>
            <label>Cantik Invoice Date<input type="date" name="cantik_invoice_date" value="<?= $row['cantik_invoice_date'] ?>"></label>
          </div>
          <div class="form-group">
            <label>Taxable Value<input type="number" step="0.01" name="taxable_value" value="<?= $row['taxable_value'] ?>"></label>
            <label>Vendor Invoice No<input name="vendor_invoice_no" value="<?= htmlspecialchars($row['vendor_invoice_no']) ?>"></label>
          </div>
          <hr>
          <div class="form-group">
            <label>Payment Receipt Date<input type="date" name="payment_receipt_date" value="<?= $row['payment_receipt_date'] ?>"></label>
            <label>Payment Advise No<input name="payment_advise_no" value="<?= htmlspecialchars($row['payment_advise_no']) ?>"></label>
            <label>Vendor Name<input name="vendor_name" value="<?= htmlspecialchars($row['vendor_name']) ?>"></label>
          </div>

          <div class="actions">
            <button type="submit">Update Invoice</button>
            <a href="list.php" class="btn muted">Cancel</a>
          </div>
        </form>
      </div>
    </main>
  </div>
  <script src="../assets/script.js"></script>
</body>
</html>
