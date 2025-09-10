<?php
session_start();
if (!isset($_SESSION['username'])) {
  header('Location: ../1Login_signuppage/login.php');
  exit();
}
include '../db.php';

$success = '';
$error = '';

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

    $stmt = $conn->prepare("INSERT INTO invoices (po_id, cantik_invoice_no, cantik_invoice_date, taxable_value, vendor_invoice_no, payment_receipt_date, payment_advise_no, vendor_name, receivable, project_description) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, (SELECT project_description FROM purchase_orders WHERE po_id = ?))");
    $stmt->bind_param("isssssssis", $po_id, $inv_no, $inv_date, $taxable, $vendor_inv, $payment_receipt_date, $payment_advise_no, $vendor_name, $taxable, $po_id);
    
    if ($stmt->execute()) {
      $success = "Invoice created successfully!";
      $_POST = array(); // Clear form
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
  <title>Add Invoice - Cantik Homemade</title>
  <meta name="description" content="Create a new invoice">
</head>
<body>
  <div class="container">
    <?php include '../shared/nav.php'; ?>
    
    <main>
      <!-- Page Header -->
      <div class="page-header">
        <div style="display: flex; align-items: center; gap: 1rem;">
          <a href="list.php" class="btn btn-outline">
            <i class="fas fa-arrow-left"></i>
            Back to Invoice List
          </a>
          <div>
            <h1>Add New Invoice</h1>
            <p>Create a new invoice</p>
          </div>
        </div>
      </div>

      <!-- Form -->
      <form method="post">
        <!-- Invoice Information -->
        <div class="card">
          <div class="card-header">
            <h2 class="card-title">Invoice Information</h2>
            <p class="card-description">Basic invoice details and PO reference</p>
          </div>
          <div class="card-content">
            <?php if ($success): ?>
              <div class="alert success">
                <i class="fas fa-check-circle"></i>
                <?= htmlspecialchars($success) ?>
              </div>
            <?php endif; ?>
            
            <?php if ($error): ?>
              <div class="alert danger">
                <i class="fas fa-exclamation-circle"></i>
                <?= htmlspecialchars($error) ?>
              </div>
            <?php endif; ?>

            <div class="form-group">
              <div class="form-field">
                <label for="po_number">PO Number *</label>
                <input type="text" name="po_number" id="po_number" 
                       value="<?= htmlspecialchars($_POST['po_number'] ?? '') ?>" 
                       placeholder="Enter PO number" required>
              </div>
              <div class="form-field">
                <label for="cantik_invoice_no">Cantik Invoice No *</label>
                <input type="text" name="cantik_invoice_no" id="cantik_invoice_no" 
                       value="<?= htmlspecialchars($_POST['cantik_invoice_no'] ?? '') ?>" 
                       placeholder="Enter Cantik invoice number" required>
              </div>
            </div>
            
            <div class="form-group">
              <div class="form-field">
                <label for="cantik_invoice_date">Invoice Date</label>
                <input type="date" name="cantik_invoice_date" id="cantik_invoice_date" 
                       value="<?= htmlspecialchars($_POST['cantik_invoice_date'] ?? '') ?>">
              </div>
              <div class="form-field">
                <label for="taxable_value">Taxable Value (₹) *</label>
                <input type="number" step="0.01" name="taxable_value" id="taxable_value" 
                       value="<?= htmlspecialchars($_POST['taxable_value'] ?? '') ?>" 
                       placeholder="Enter taxable value" required>
              </div>
            </div>

            <div class="form-group">
              <div class="form-field">
                <label for="vendor_invoice_no">Vendor Invoice No</label>
                <input type="text" name="vendor_invoice_no" id="vendor_invoice_no" 
                       value="<?= htmlspecialchars($_POST['vendor_invoice_no'] ?? '') ?>" 
                       placeholder="Enter vendor invoice number">
              </div>
              <div class="form-field">
                <label for="vendor_name">Vendor Name</label>
                <input type="text" name="vendor_name" id="vendor_name" 
                       value="<?= htmlspecialchars($_POST['vendor_name'] ?? '') ?>" 
                       placeholder="Enter vendor name">
              </div>
            </div>

            <div class="form-group">
              <div class="form-field">
                <label for="payment_receipt_date">Payment Receipt Date</label>
                <input type="date" name="payment_receipt_date" id="payment_receipt_date" 
                       value="<?= htmlspecialchars($_POST['payment_receipt_date'] ?? '') ?>">
              </div>
              <div class="form-field">
                <label for="payment_advise_no">Payment Advise No</label>
                <input type="text" name="payment_advise_no" id="payment_advise_no" 
                       value="<?= htmlspecialchars($_POST['payment_advise_no'] ?? '') ?>" 
                       placeholder="Enter payment advise number">
              </div>
            </div>
          </div>
        </div>

        <!-- Form Actions -->
        <div class="card">
          <div class="card-content">
            <div class="actions">
              <a href="list.php" class="btn btn-outline">Cancel</a>
              <button type="submit" class="btn btn-primary">
                <i class="fas fa-save"></i>
                Create Invoice
              </button>
            </div>
          </div>
        </div>
      </form>
    </main>
  </div>

  <script>
    // Auto-clear success message after 3 seconds
    document.addEventListener('DOMContentLoaded', function() {
      const successAlert = document.querySelector('.alert.success');
      if (successAlert) {
        setTimeout(() => {
          successAlert.style.opacity = '0';
          setTimeout(() => successAlert.remove(), 300);
        }, 3000);
      }
    });

    // Form validation
    document.querySelector('form').addEventListener('submit', function(e) {
      const requiredFields = this.querySelectorAll('input[required]');
      let isValid = true;
      
      requiredFields.forEach(field => {
        if (!field.value.trim()) {
          field.style.borderColor = 'var(--destructive)';
          isValid = false;
        } else {
          field.style.borderColor = 'var(--border)';
        }
      });
      
      if (!isValid) {
        e.preventDefault();
        alert('Please fill in all required fields.');
      }
    });
  </script>
</body>
</html>