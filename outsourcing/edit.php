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
  $cantik_po_no = $_POST['cantik_po_no'];
  $cantik_po_date = $_POST['cantik_po_date'] ?: null;
  $cantik_po_value = $_POST['cantik_po_value'] ?: 0;
  $vendor_invoice_no = $_POST['vendor_invoice_no'];
  $vendor_invoice_date = $_POST['vendor_invoice_date'] ?: null;
  $vendor_invoice_value = $_POST['vendor_invoice_value'] ?: 0;
  $payment_status_from_ntt = $_POST['payment_status_from_ntt'] ?? '';
  $payment_value = $_POST['payment_value'] ?: null;
  $payment_date = $_POST['payment_date'] ?: null;
  $remarks = $_POST['remarks'];

  $stmt = $conn->prepare("UPDATE outsourcing_details SET cantik_po_no = ?, cantik_po_date = ?, cantik_po_value = ?, vendor_invoice_no = ?, vendor_invoice_date = ?, vendor_invoice_value = ?, payment_status_from_ntt = ?, payment_value = ?, payment_date = ?, remarks = ? WHERE outsourcing_id = ?");
  $stmt->bind_param("ssdssdssssi", $cantik_po_no, $cantik_po_date, $cantik_po_value, $vendor_invoice_no, $vendor_invoice_date, $vendor_invoice_value, $payment_status_from_ntt, $payment_value, $payment_date, $remarks, $id);

  if ($stmt->execute()) {
    header('Location: list.php');
    exit;
  } else {
    $error = "Error: " . $stmt->error;
  }
  $stmt->close();
}

$stmt = $conn->prepare("SELECT outd.*, po.po_number FROM outsourcing_details outd JOIN purchase_orders po ON po.po_id = outd.po_id WHERE outd.outsourcing_id = ?");
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
  <title>Edit Outsourcing</title>
  <link rel="stylesheet" href="../assets/style.css">
</head>
<body>
  <div class="container">
    <?php include '../shared/nav.php'; ?>
    <main>
      <div class="page-header">
        <h1>Edit Outsourcing Record #<?= htmlspecialchars($row['outsourcing_id']) ?></h1>
      </div>
      <div class="card">
        <?php if (isset($error)): ?>
          <div class="alert danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        <form method="post">

          <div class="form-group">
            <label>Customer PO Number<input value="<?= htmlspecialchars($row['po_number']) ?>" disabled></label>
          </div>
          <hr>

          <h4>Cantik PO Details</h4>
          <div class="form-group">
            <label>Cantik PO No<input name="cantik_po_no" value="<?= htmlspecialchars($row['cantik_po_no']) ?>" required></label>
            <label>Cantik PO Date<input type="date" name="cantik_po_date" value="<?= $row['cantik_po_date'] ?>"></label>
            <label>Cantik PO Value<input type="number" step="0.01" name="cantik_po_value" value="<?= $row['cantik_po_value'] ?>" required></label>
          </div>
          <hr>

          <h4>Vendor Invoice Details</h4>
          <div class="form-group">
            <label>Vendor Invoice No<input name="vendor_invoice_no" value="<?= htmlspecialchars($row['vendor_invoice_no']) ?>" required></label>
            <label>Vendor Invoice Date<input type="date" name="vendor_invoice_date" value="<?= $row['vendor_invoice_date'] ?>"></label>
            <label>Vendor Invoice Value<input type="number" step="0.01" name="vendor_invoice_value" value="<?= $row['vendor_invoice_value'] ?>" required></label>
          </div>
          <hr>

          <h4>Payment Details</h4>
          <div class="form-group">
            <label>Payment Status from NTT
              <select name="payment_status_from_ntt">
                <option value="">-- Select Status --</option>
                <option value="Paid" <?= ($row['payment_status_from_ntt'] ?? '') === 'Paid' ? 'selected' : '' ?>>Paid</option>
                <option value="Pending" <?= ($row['payment_status_from_ntt'] ?? '') === 'Pending' ? 'selected' : '' ?>>Pending</option>
                <option value="Overdue" <?= ($row['payment_status_from_ntt'] ?? '') === 'Overdue' ? 'selected' : '' ?>>Overdue</option>
                <option value="Cancelled" <?= ($row['payment_status_from_ntt'] ?? '') === 'Cancelled' ? 'selected' : '' ?>>Cancelled</option>
              </select>
            </label>
            <label>Payment Value<input type="number" step="0.01" name="payment_value" value="<?= $row['payment_value'] ?>"></label>
            <label>Payment Date<input type="date" name="payment_date" value="<?= $row['payment_date'] ?>"></label>
          </div>

          <div class="form-group">
            <label>Remarks<textarea name="remarks"><?= htmlspecialchars($row['remarks']) ?></textarea></label>
          </div>

          <div class="actions">
            <button type="submit">Update Record</button>
            <a href="list.php" class="btn muted">Cancel</a>
          </div>
        </form>
      </div>
    </main>
  </div>
  <script src="../assets/script.js"></script>
</body>
</html>

