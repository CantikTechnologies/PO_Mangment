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

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  // update
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

  $sql = "UPDATE purchase_orders SET project_description='{$project}', cost_center='{$cost_center}', sow_number='{$sow}', ";
  $sql .= $start ? "start_date='{$start}'," : "start_date=NULL,";
  $sql .= $end ? "end_date='{$end}'," : "end_date=NULL,";
  $sql .= "po_number='{$po_number}', po_date=" . ($po_date ? "'{$po_date}'" : "NULL") . ", po_value={$po_value}, billing_frequency='{$billing}', target_gm=" . ($target_gm ? "{$target_gm}" : "NULL") . ", po_status='{$status}', remarks='{$remarks}', vendor_name='{$vendor}' WHERE po_id={$id}";

  if ($conn->query($sql)) {
    $success = "Purchase order updated successfully!";
  } else {
    $error = "Error: " . $conn->error;
  }
}

$res = $conn->query("SELECT * FROM purchase_orders WHERE po_id={$id}");
$row = $res->fetch_assoc();
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
  <title>Edit Purchase Order - Cantik Homemade</title>
  <meta name="description" content="Edit purchase order details">
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
            <h1>Edit Purchase Order</h1>
            <p>Update purchase order details</p>
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
                       value="<?= htmlspecialchars($row['project_description'] ?? '') ?>" 
                       placeholder="Enter project description" required>
              </div>
              <div class="form-field">
                <label for="cost_center">Cost Center *</label>
                <input type="text" name="cost_center" id="cost_center" 
                       value="<?= htmlspecialchars($row['cost_center'] ?? '') ?>" 
                       placeholder="Enter cost center" required>
              </div>
            </div>
            
            <div class="form-group">
              <div class="form-field">
                <label for="sow_number">SOW Number</label>
                <input type="text" name="sow_number" id="sow_number" 
                       value="<?= htmlspecialchars($row['sow_number'] ?? '') ?>" 
                       placeholder="Enter SOW number">
              </div>
              <div class="form-field">
                <label for="vendor_name">Vendor Name *</label>
                <input type="text" name="vendor_name" id="vendor_name" 
                       value="<?= htmlspecialchars($row['vendor_name'] ?? '') ?>" 
                       placeholder="Enter vendor name" required>
              </div>
            </div>

            <div class="form-group">
              <div class="form-field">
                <label for="start_date">Start Date</label>
                <input type="date" name="start_date" id="start_date" 
                       value="<?= htmlspecialchars($row['start_date'] ?? '') ?>">
              </div>
              <div class="form-field">
                <label for="end_date">End Date</label>
                <input type="date" name="end_date" id="end_date" 
                       value="<?= htmlspecialchars($row['end_date'] ?? '') ?>">
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
                       value="<?= htmlspecialchars($row['po_number'] ?? '') ?>" 
                       placeholder="Enter PO number" required>
              </div>
              <div class="form-field">
                <label for="po_date">PO Date</label>
                <input type="date" name="po_date" id="po_date" 
                       value="<?= htmlspecialchars($row['po_date'] ?? '') ?>">
              </div>
            </div>

            <div class="form-group">
              <div class="form-field">
                <label for="po_value">PO Value (₹)</label>
                <input type="number" step="0.01" name="po_value" id="po_value" 
                       value="<?= htmlspecialchars($row['po_value'] ?? '') ?>" 
                       placeholder="Enter PO value">
              </div>
              <div class="form-field">
                <label for="target_gm">Target GM (%)</label>
                <input type="number" step="0.01" name="target_gm" id="target_gm" 
                       value="<?= htmlspecialchars($row['target_gm'] ?? '') ?>" 
                       placeholder="Enter target GM percentage">
              </div>
            </div>

            <div class="form-group">
              <div class="form-field">
                <label for="billing_frequency">Billing Frequency</label>
                <select name="billing_frequency" id="billing_frequency">
                  <option value="Monthly" <?= ($row['billing_frequency'] ?? '') == 'Monthly' ? 'selected' : '' ?>>Monthly</option>
                  <option value="Quarterly" <?= ($row['billing_frequency'] ?? '') == 'Quarterly' ? 'selected' : '' ?>>Quarterly</option>
                  <option value="Yearly" <?= ($row['billing_frequency'] ?? '') == 'Yearly' ? 'selected' : '' ?>>Yearly</option>
                  <option value="Other" <?= ($row['billing_frequency'] ?? '') == 'Other' ? 'selected' : '' ?>>Other</option>
                </select>
              </div>
              <div class="form-field">
                <label for="po_status">Status</label>
                <select name="po_status" id="po_status">
                  <option value="Active" <?= ($row['po_status'] ?? '') == 'Active' ? 'selected' : '' ?>>Active</option>
                  <option value="Pending" <?= ($row['po_status'] ?? '') == 'Pending' ? 'selected' : '' ?>>Pending</option>
                  <option value="Completed" <?= ($row['po_status'] ?? '') == 'Completed' ? 'selected' : '' ?>>Completed</option>
                  <option value="Cancelled" <?= ($row['po_status'] ?? '') == 'Cancelled' ? 'selected' : '' ?>>Cancelled</option>
                </select>
              </div>
            </div>

            <div class="form-field">
              <label for="remarks">Remarks</label>
              <textarea name="remarks" id="remarks" rows="3" 
                        placeholder="Enter any additional remarks or notes"><?= htmlspecialchars($row['remarks'] ?? '') ?></textarea>
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
                Update PO
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