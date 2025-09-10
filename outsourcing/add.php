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
  $vendor = $conn->real_escape_string($_POST['vendor_name']);
  $work_desc = $conn->real_escape_string($_POST['work_description']);
  $start_date = $_POST['start_date'] ?: null;
  $end_date = $_POST['end_date'] ?: null;
  $total_amount = $_POST['total_amount'] ?: 0;
  $pending_payment = $_POST['pending_payment'] ?: 0;

  $sql = "INSERT INTO outsourcing_details (project_description, vendor_name, work_description, start_date, end_date, total_amount, pending_payment) VALUES ('{$project}', '{$vendor}', '{$work_desc}', " . 
          ($start_date ? "'{$start_date}'" : "NULL") . ", " . 
          ($end_date ? "'{$end_date}'" : "NULL") . ", {$total_amount}, {$pending_payment})";

  if ($conn->query($sql)) {
    $success = "Outsourcing record created successfully!";
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
  <title>Add Outsourcing Record - Cantik Homemade</title>
  <meta name="description" content="Create a new outsourcing record">
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
            Back to Outsourcing List
          </a>
          <div>
            <h1>Add New Outsourcing Record</h1>
            <p>Create a new outsourcing record</p>
          </div>
        </div>
      </div>

      <!-- Form -->
      <form method="post">
        <!-- Project Information -->
        <div class="card">
          <div class="card-header">
            <h2 class="card-title">Project Information</h2>
            <p class="card-description">Basic project and vendor details</p>
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
                <label for="vendor_name">Vendor Name *</label>
                <input type="text" name="vendor_name" id="vendor_name" 
                       value="<?= htmlspecialchars($_POST['vendor_name'] ?? '') ?>" 
                       placeholder="Enter vendor name" required>
              </div>
            </div>
            
            <div class="form-field">
              <label for="work_description">Work Description *</label>
              <textarea name="work_description" id="work_description" rows="3" 
                        placeholder="Enter work description" required><?= htmlspecialchars($_POST['work_description'] ?? '') ?></textarea>
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

        <!-- Financial Details -->
        <div class="card">
          <div class="card-header">
            <h2 class="card-title">Financial Details</h2>
            <p class="card-description">Amount and payment information</p>
          </div>
          <div class="card-content">
            <div class="form-group">
              <div class="form-field">
                <label for="total_amount">Total Amount (₹)</label>
                <input type="number" step="0.01" name="total_amount" id="total_amount" 
                       value="<?= htmlspecialchars($_POST['total_amount'] ?? '') ?>" 
                       placeholder="Enter total amount">
              </div>
              <div class="form-field">
                <label for="pending_payment">Pending Payment (₹)</label>
                <input type="number" step="0.01" name="pending_payment" id="pending_payment" 
                       value="<?= htmlspecialchars($_POST['pending_payment'] ?? '') ?>" 
                       placeholder="Enter pending payment amount">
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
                Create Record
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
      const requiredFields = this.querySelectorAll('input[required], textarea[required]');
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