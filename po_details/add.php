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
  $project = $conn->real_escape_string($_POST['project_description']);
  $cost_center = $conn->real_escape_string($_POST['cost_center']);
  $sow = $conn->real_escape_string($_POST['sow_number']);
  $start = $_POST['start_date'] ?: null;
  $end = $_POST['end_date'] ?: null;
  $po_number = $conn->real_escape_string($_POST['po_number']);
  $po_date = $_POST['po_date'] ?: null;
  $po_value = $_POST['po_value'] ?: 0;
  $billing = $conn->real_escape_string($_POST['billing_frequency']);
  $target_gm = $_POST['target_gm'] ?: null;
  $status = $conn->real_escape_string($_POST['po_status']);
  $remarks = $conn->real_escape_string($_POST['remarks']);
  $vendor = $conn->real_escape_string($_POST['vendor_name']);

  $sql = "INSERT INTO purchase_orders (project_description, cost_center, sow_number, start_date, end_date, po_number, po_date, po_value, billing_frequency, target_gm, po_status, remarks, vendor_name) VALUES ('{$project}', '{$cost_center}', '{$sow}', " . 
          ($start ? "'{$start}'" : "NULL") . ", " . 
          ($end ? "'{$end}'" : "NULL") . ", '{$po_number}', " . 
          ($po_date ? "'{$po_date}'" : "NULL") . ", {$po_value}, '{$billing}', " . 
          ($target_gm ? "{$target_gm}" : "NULL") . ", '{$status}', '{$remarks}', '{$vendor}')";

  if ($conn->query($sql)) {
    $success = "Purchase order created successfully!";
    // Clear form data after successful submission
    $_POST = array();
  } else {
    $error = "Error: " . $conn->error;
  }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Add Purchase Order - Cantik Homemade</title>
  <meta name="description" content="Create a new purchase order">
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
            Back to PO List
          </a>
          <div>
            <h1>Add New Purchase Order</h1>
            <p>Create a new purchase order</p>
          </div>
        </div>
      </div>

      <!-- Form -->
      <form method="post">
        <!-- Project Information -->
        <div class="card">
          <div class="card-header">
            <h2 class="card-title">Project Information</h2>
            <p class="card-description">Basic project details and description</p>
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
                <label for="project_description">Project Description *</label>
                <input type="text" name="project_description" id="project_description" 
                       value="<?= htmlspecialchars($_POST['project_description'] ?? '') ?>" 
                       placeholder="Enter project description" required>
              </div>
              <div class="form-field">
                <label for="cost_center">Cost Center *</label>
                <input type="text" name="cost_center" id="cost_center" 
                       value="<?= htmlspecialchars($_POST['cost_center'] ?? '') ?>" 
                       placeholder="Enter cost center" required>
              </div>
            </div>
            
            <div class="form-group">
              <div class="form-field">
                <label for="sow_number">SOW Number</label>
                <input type="text" name="sow_number" id="sow_number" 
                       value="<?= htmlspecialchars($_POST['sow_number'] ?? '') ?>" 
                       placeholder="Enter SOW number">
              </div>
              <div class="form-field">
                <label for="vendor_name">Vendor Name *</label>
                <input type="text" name="vendor_name" id="vendor_name" 
                       value="<?= htmlspecialchars($_POST['vendor_name'] ?? '') ?>" 
                       placeholder="Enter vendor name" required>
              </div>
            </div>

            <div class="form-group">
              <div class="form-field">
                <label for="start_date">Start Date</label>
                <input type="date" name="start_date" id="start_date" 
                       value="<?= htmlspecialchars($_POST['start_date'] ?? '') ?>">
              </div>
              <div class="form-field">
                <label for="end_date">End Date</label>
                <input type="date" name="end_date" id="end_date" 
                       value="<?= htmlspecialchars($_POST['end_date'] ?? '') ?>">
              </div>
            </div>
          </div>
        </div>

        <!-- Purchase Order Details -->
        <div class="card">
          <div class="card-header">
            <h2 class="card-title">Purchase Order Details</h2>
            <p class="card-description">PO-specific information and financial details</p>
          </div>
          <div class="card-content">
            <div class="form-group">
              <div class="form-field">
                <label for="po_number">PO Number *</label>
                <input type="text" name="po_number" id="po_number" 
                       value="<?= htmlspecialchars($_POST['po_number'] ?? '') ?>" 
                       placeholder="Enter PO number" required>
              </div>
              <div class="form-field">
                <label for="po_date">PO Date</label>
                <input type="date" name="po_date" id="po_date" 
                       value="<?= htmlspecialchars($_POST['po_date'] ?? '') ?>">
              </div>
            </div>

            <div class="form-group">
              <div class="form-field">
                <label for="po_value">PO Value (₹)</label>
                <input type="number" step="0.01" name="po_value" id="po_value" 
                       value="<?= htmlspecialchars($_POST['po_value'] ?? '') ?>" 
                       placeholder="Enter PO value">
              </div>
              <div class="form-field">
                <label for="target_gm">Target GM (%)</label>
                <input type="number" step="0.01" name="target_gm" id="target_gm" 
                       value="<?= htmlspecialchars($_POST['target_gm'] ?? '') ?>" 
                       placeholder="Enter target GM percentage">
              </div>
            </div>

            <div class="form-group">
              <div class="form-field">
                <label for="billing_frequency">Billing Frequency</label>
                <select name="billing_frequency" id="billing_frequency">
                  <option value="Monthly" <?= ($_POST['billing_frequency'] ?? '') == 'Monthly' ? 'selected' : '' ?>>Monthly</option>
                  <option value="Quarterly" <?= ($_POST['billing_frequency'] ?? '') == 'Quarterly' ? 'selected' : '' ?>>Quarterly</option>
                  <option value="Yearly" <?= ($_POST['billing_frequency'] ?? '') == 'Yearly' ? 'selected' : '' ?>>Yearly</option>
                  <option value="Other" <?= ($_POST['billing_frequency'] ?? '') == 'Other' ? 'selected' : '' ?>>Other</option>
                </select>
              </div>
              <div class="form-field">
                <label for="po_status">Status</label>
                <select name="po_status" id="po_status">
                  <option value="Active" <?= ($_POST['po_status'] ?? 'Active') == 'Active' ? 'selected' : '' ?>>Active</option>
                  <option value="Pending" <?= ($_POST['po_status'] ?? '') == 'Pending' ? 'selected' : '' ?>>Pending</option>
                  <option value="Completed" <?= ($_POST['po_status'] ?? '') == 'Completed' ? 'selected' : '' ?>>Completed</option>
                  <option value="Cancelled" <?= ($_POST['po_status'] ?? '') == 'Cancelled' ? 'selected' : '' ?>>Cancelled</option>
                </select>
              </div>
            </div>

            <div class="form-field">
              <label for="remarks">Remarks</label>
              <textarea name="remarks" id="remarks" rows="3" 
                        placeholder="Enter any additional remarks or notes"><?= htmlspecialchars($_POST['remarks'] ?? '') ?></textarea>
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
                Create PO
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