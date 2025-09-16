<?php
session_start();
if (!isset($_SESSION['username'])) {
  header('Location: ../../../login.php');
  exit();
}
include '../../../config/db.php';
include '../../../config/auth.php';
requirePermission('edit_invoices');

$id = intval($_GET['id'] ?? 0);
if (!$id) {
  header('Location: list.php');
    exit();
}

$success = '';
$error = '';

// Get existing invoice data
$sql = "SELECT * FROM billing_details WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$invoice = $result->fetch_assoc();
$stmt->close();

if (!$invoice) {
    header('Location: list.php');
    exit();
}

// Convert Excel dates to readable format
function excelToDate($excel_date) {
    if (empty($excel_date)) return '';
    $unix_date = ($excel_date - 25569) * 86400;
    return date('Y-m-d', $unix_date);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $project_details = trim($_POST['project_details'] ?? '');
    $cost_center = trim($_POST['cost_center'] ?? '');
    $customer_po = trim($_POST['customer_po'] ?? '');
    $remaining_balance = $_POST['remaining_balance_in_po'] ?: 0;
    $cantik_invoice_no = trim($_POST['cantik_invoice_no'] ?? '');
    $cantik_invoice_date = $_POST['cantik_invoice_date'] ?: null;
    $cantik_inv_value_taxable = $_POST['cantik_inv_value_taxable'] ?: 0;
    $tds = $_POST['tds'] ?: 0;
    $receivable = $_POST['receivable'] ?: 0;
    $against_vendor_inv_number = trim($_POST['against_vendor_inv_number'] ?? '');
    $payment_receipt_date = $_POST['payment_receipt_date'] ?: null;
    $payment_advise_no = trim($_POST['payment_advise_no'] ?? '');
    $vendor_name = trim($_POST['vendor_name'] ?? '');

    // Convert dates to Excel format if provided (cast to integers)
    $cantik_invoice_date_excel = $cantik_invoice_date ? (int)floor((strtotime($cantik_invoice_date) / 86400) + 25569) : null;
    $payment_receipt_date_excel = $payment_receipt_date ? (int)floor((strtotime($payment_receipt_date) / 86400) + 25569) : null;

    $sql = "UPDATE billing_details SET project_details = ?, cost_center = ?, customer_po = ?, remaining_balance_in_po = ?, cantik_invoice_no = ?, cantik_invoice_date = ?, cantik_inv_value_taxable = ?, tds = ?, receivable = ?, against_vendor_inv_number = ?, payment_receipt_date = ?, payment_advise_no = ?, vendor_name = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    
    if ($stmt) {
        // Types: s s s d s i d d d s i s s i (14 params)
        $stmt->bind_param("sssdsidddsissi", $project_details, $cost_center, $customer_po, $remaining_balance, $cantik_invoice_no, $cantik_invoice_date_excel, $cantik_inv_value_taxable, $tds, $receivable, $against_vendor_inv_number, $payment_receipt_date_excel, $payment_advise_no, $vendor_name, $id);

  if ($stmt->execute()) {
            $success = "Invoice updated successfully!";
            $auth->logAction('update_invoice', 'billing_details', $id);
            // Refresh invoice data
            $sql = "SELECT * FROM billing_details WHERE id = ?";
            $stmt2 = $conn->prepare($sql);
            if ($stmt2) {
                $stmt2->bind_param("i", $id);
                $stmt2->execute();
                $result = $stmt2->get_result();
                $invoice = $result->fetch_assoc();
                $stmt2->close();
            }
  } else {
    $error = "Error: " . $stmt->error;
  }
  if ($stmt) { $stmt->close(); }
    } else {
        $error = "Error preparing statement: " . $conn->error;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Edit Invoice - Cantik Homemade</title>
    <link crossorigin="" href="https://fonts.gstatic.com/" rel="preconnect"/>
    <link as="style" href="https://fonts.googleapis.com/css2?display=swap&amp;family=Noto+Sans%3Awght%40400%3B500%3B700%3B900&amp;family=Public+Sans%3Awght%40400%3B500%3B600%3B700%3B800" onload="this.rel='stylesheet'" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet"/>
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
</head>
<body class="bg-gray-50 text-gray-900" style='font-family: "Public Sans", "Noto Sans", sans-serif;'>
    <div class="relative flex size-full min-h-screen flex-col overflow-x-hidden">
        <?php include '../../shared/nav.php'; ?>
        
        <main class="flex-1 px-4 sm:px-6 lg:px-8 py-8">
            <div class="max-w-4xl mx-auto">
                <!-- Page Header -->
                <div class="mb-8">
                    <div class="flex items-center justify-between">
                        <div>
                            <h1 class="text-3xl font-bold text-gray-900">Edit Invoice</h1>
                            <p class="text-gray-600 mt-2">Update invoice #<?= htmlspecialchars($invoice['cantik_invoice_no']) ?></p>
                        </div>
                        <a href="list.php" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-red-500">
                            <span class="material-symbols-outlined mr-2 text-sm">arrow_back</span>
                            Back to List
                        </a>
                    </div>
                </div>

                <?php if ($success): ?>
                    <div class="mb-6 p-4 bg-green-50 border border-green-200 rounded-lg">
                        <p class="text-green-800"><?= htmlspecialchars($success) ?></p>
                    </div>
                <?php endif; ?>

                <?php if ($error): ?>
                    <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-lg">
                        <p class="text-red-800"><?= htmlspecialchars($error) ?></p>
      </div>
        <?php endif; ?>

                <!-- Form -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h2 class="text-lg font-semibold text-gray-900">Invoice Details</h2>
                    </div>
                    <form method="POST" class="p-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Project Details -->
                            <div class="md:col-span-2">
                                <label for="project_details" class="block text-sm font-medium text-gray-700 mb-2">Project Details</label>
                                <input type="text" id="project_details" name="project_details" readonly
                                       value="<?= htmlspecialchars($invoice['project_details']) ?>"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md bg-gray-100 cursor-not-allowed">
                            </div>

                            <!-- Cost Center -->
                            <div>
                                <label for="cost_center" class="block text-sm font-medium text-gray-700 mb-2">Cost Center</label>
                                <input type="text" id="cost_center" name="cost_center" readonly
                                       value="<?= htmlspecialchars($invoice['cost_center']) ?>"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md bg-gray-100 cursor-not-allowed">
                            </div>

                            <!-- Customer PO -->
                            <div>
                                <label for="customer_po" class="block text-sm font-medium text-gray-700 mb-2">Customer PO</label>
                                <input type="text" id="customer_po" name="customer_po" readonly
                                       value="<?= htmlspecialchars($invoice['customer_po']) ?>"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md bg-gray-100 cursor-not-allowed">
                            </div>

                            <!-- Remaining Balance in PO -->
                            <div>
                                <label for="remaining_balance_in_po" class="block text-sm font-medium text-gray-700 mb-2">Remaining Balance in PO</label>
                                <input type="number" id="remaining_balance_in_po" name="remaining_balance_in_po" step="0.01"
                                       value="<?= htmlspecialchars($invoice['remaining_balance_in_po']) ?>"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-red-500">
                            </div>

                            <!-- Cantik Invoice Number -->
                            <div>
                                <label for="cantik_invoice_no" class="block text-sm font-medium text-gray-700 mb-2">Cantik Invoice Number</label>
                                <input type="text" id="cantik_invoice_no" name="cantik_invoice_no"
                                       value="<?= htmlspecialchars($invoice['cantik_invoice_no']) ?>"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-red-500">
                            </div>

                            <!-- Cantik Invoice Date -->
                            <div>
                                <label for="cantik_invoice_date" class="block text-sm font-medium text-gray-700 mb-2">Cantik Invoice Date</label>
                                <input type="date" id="cantik_invoice_date" name="cantik_invoice_date"
                                       value="<?= excelToDate($invoice['cantik_invoice_date']) ?>"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-red-500">
                            </div>

                            <!-- Cantik Invoice Value (Taxable) -->
                            <div>
                                <label for="cantik_inv_value_taxable" class="block text-sm font-medium text-gray-700 mb-2">Cantik Invoice Value (Taxable)</label>
                                <input type="number" id="cantik_inv_value_taxable" name="cantik_inv_value_taxable" step="0.01"
                                       value="<?= htmlspecialchars($invoice['cantik_inv_value_taxable']) ?>"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-red-500">
                            </div>

                            <!-- TDS -->
                            <div>
                                <label for="tds" class="block text-sm font-medium text-gray-700 mb-2">TDS</label>
                                <input type="number" id="tds" name="tds" step="0.01" readonly
                                       value="<?= htmlspecialchars($invoice['tds']) ?>"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md bg-gray-100 cursor-not-allowed">
                            </div>

                            <!-- Receivable -->
                            <div>
                                <label for="receivable" class="block text-sm font-medium text-gray-700 mb-2">Receivable</label>
                                <input type="number" id="receivable" name="receivable" step="0.01" readonly
                                       value="<?= htmlspecialchars($invoice['receivable']) ?>"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md bg-gray-100 cursor-not-allowed">
                            </div>

                            <!-- Against Vendor Invoice Number -->
                            <div>
                                <label for="against_vendor_inv_number" class="block text-sm font-medium text-gray-700 mb-2">Against Vendor Invoice Number</label>
                                <input type="text" id="against_vendor_inv_number" name="against_vendor_inv_number"
                                       value="<?= htmlspecialchars($invoice['against_vendor_inv_number']) ?>"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-red-500">
                            </div>

                            <!-- Payment Receipt Date -->
                            <div>
                                <label for="payment_receipt_date" class="block text-sm font-medium text-gray-700 mb-2">Payment Receipt Date</label>
                                <input type="date" id="payment_receipt_date" name="payment_receipt_date"
                                       value="<?= excelToDate($invoice['payment_receipt_date']) ?>"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-red-500">
                            </div>

                            <!-- Payment Advise Number -->
                            <div>
                                <label for="payment_advise_no" class="block text-sm font-medium text-gray-700 mb-2">Payment Advise Number</label>
                                <input type="text" id="payment_advise_no" name="payment_advise_no"
                                       value="<?= htmlspecialchars($invoice['payment_advise_no']) ?>"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-red-500">
          </div>

                            <!-- Vendor Name -->
                            <div>
                                <label for="vendor_name" class="block text-sm font-medium text-gray-700 mb-2">Vendor Name</label>
                                <input type="text" id="vendor_name" name="vendor_name" readonly
                                       value="<?= htmlspecialchars($invoice['vendor_name']) ?>"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md bg-gray-100 cursor-not-allowed">
          </div>
          </div>

                        <!-- Form Actions -->
                        <div class="mt-8 flex justify-end gap-4">
                            <a href="list.php" class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-red-500">
                                Cancel
                            </a>
                            <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded-md text-sm font-medium hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500">
                                <span class="material-symbols-outlined mr-2 text-sm">save</span>
                                Update Invoice
                            </button>
          </div>
        </form>
                </div>
      </div>
    </main>
  </div>
</body>
</html>
