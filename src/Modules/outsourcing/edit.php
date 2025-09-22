<?php
// Use universal includes
include '../../shared/includes.php';

// Check authentication
if (!isset($_SESSION['username'])) {
  header('Location: ' . getLoginUrl());
  exit();
}
requirePermission('edit_outsourcing');

$id = intval($_GET['id'] ?? 0);
if (!$id) {
  header('Location: list.php');
    exit();
}

$success = '';
$error = '';

// Get existing outsourcing data
$sql = "SELECT * FROM outsourcing_detail WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$outsourcing = $result->fetch_assoc();
$stmt->close();

if (!$outsourcing) {
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
    $vendor_name = trim($_POST['vendor_name'] ?? '');
    $cantik_po_no = trim($_POST['cantik_po_no'] ?? '');
    $cantik_po_date = $_POST['cantik_po_date'] ?: null;
    $cantik_po_value = $_POST['cantik_po_value'] ?: 0;
    $remaining_bal_in_po = $_POST['remaining_bal_in_po'] ?: 0;
    $vendor_invoice_frequency = trim($_POST['vendor_invoice_frequency'] ?? '');
    $vendor_inv_number = trim($_POST['vendor_inv_number'] ?? '');
    $vendor_inv_date = $_POST['vendor_inv_date'] ?: null;
    $vendor_inv_value = $_POST['vendor_inv_value'] ?: 0;
    $tds_ded = $_POST['tds_ded'] ?: 0;
    $net_payble = $_POST['net_payble'] ?: 0;
    $payment_status_from_ntt = trim($_POST['payment_status_from_ntt'] ?? '');
    $payment_value = $_POST['payment_value'] ?: 0;
    $payment_date = $_POST['payment_date'] ?: null;
    $pending_payment = $_POST['pending_payment'] ?: 0;
    $remarks = trim($_POST['remarks'] ?? '');

    // Convert dates to Excel format if provided (cast to integers)
    $cantik_po_date_excel = $cantik_po_date ? (int)floor((strtotime($cantik_po_date) / 86400) + 25569) : null;
    $vendor_inv_date_excel = $vendor_inv_date ? (int)floor((strtotime($vendor_inv_date) / 86400) + 25569) : null;
    $payment_date_excel = $payment_date ? (int)floor((strtotime($payment_date) / 86400) + 25569) : null;

    $sql = "UPDATE outsourcing_detail SET project_details = ?, cost_center = ?, customer_po = ?, vendor_name = ?, cantik_po_no = ?, cantik_po_date = ?, cantik_po_value = ?, remaining_bal_in_po = ?, vendor_invoice_frequency = ?, vendor_inv_number = ?, vendor_inv_date = ?, vendor_inv_value = ?, tds_ded = ?, net_payble = ?, payment_status_from_ntt = ?, payment_value = ?, payment_date = ?, pending_payment = ?, remarks = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    
    if ($stmt) {
        // Types (20): s s s s s i d d s s i d d d s d i d s i
        $stmt->bind_param("sssssiddssidddsdidsi", $project_details, $cost_center, $customer_po, $vendor_name, $cantik_po_no, $cantik_po_date_excel, $cantik_po_value, $remaining_bal_in_po, $vendor_invoice_frequency, $vendor_inv_number, $vendor_inv_date_excel, $vendor_inv_value, $tds_ded, $net_payble, $payment_status_from_ntt, $payment_value, $payment_date_excel, $pending_payment, $remarks, $id);

  if ($stmt->execute()) {
            $success = "Outsourcing record updated successfully!";
            $auth->logAction('update_outsourcing', 'outsourcing_detail', $id);
            // Redirect to list page after successful update
            header('Location: list.php?success=updated');
            exit();
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
    <title>Edit Outsourcing Record - Cantik </title>
    <link crossorigin="" href="https://fonts.gstatic.com/" rel="preconnect"/>
    <link as="style" href="https://fonts.googleapis.com/css2?display=swap&amp;family=Noto+Sans%3Awght%40400%3B500%3B700%3B900&amp;family=Public+Sans%3Awght%40400%3B500%3B600%3B700%3B800" onload="this.rel='stylesheet'" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet"/>
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
</head>
<body class="bg-gray-50 text-gray-900" style='font-family: "Public Sans", "Noto Sans", sans-serif;'>
    <div class="relative flex size-full min-h-screen flex-col overflow-x-hidden">
    <?php include getSharedIncludePath('nav.php'); ?>
        
        <main class="flex-1 px-4 sm:px-6 lg:px-8 py-8">
            <div class="max-w-6xl mx-auto">
                <!-- Page Header -->
                <div class="mb-8">
                    <div class="flex items-center justify-between">
                        <div>
                            <h1 class="text-3xl font-bold text-gray-900">Edit Outsourcing Record</h1>
                            <p class="text-gray-600 mt-2">Update outsourcing record #<?= htmlspecialchars($outsourcing['cantik_po_no']) ?></p>
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
                        <h2 class="text-lg font-semibold text-gray-900">Outsourcing Details</h2>
                    </div>
                    <form method="POST" class="p-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                            <!-- Project Details -->
                            <div class="md:col-span-2 lg:col-span-3">
                                <label for="project_details" class="block text-sm font-medium text-gray-700 mb-2">Project Details</label>
                                <input type="text" id="project_details" name="project_details" readonly
                                       value="<?= htmlspecialchars($outsourcing['project_details']) ?>"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md bg-gray-100 cursor-not-allowed">
                            </div>

                            <!-- Cost Center -->
                            <div>
                                <label for="cost_center" class="block text-sm font-medium text-gray-700 mb-2">Cost Center</label>
                                <input type="text" id="cost_center" name="cost_center" readonly
                                       value="<?= htmlspecialchars($outsourcing['cost_center']) ?>"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md bg-gray-100 cursor-not-allowed">
                            </div>

                            <!-- Customer PO -->
                            <div>
                                <label for="customer_po" class="block text-sm font-medium text-gray-700 mb-2">Customer PO</label>
                                <input type="text" id="customer_po" name="customer_po" readonly
                                       value="<?= htmlspecialchars($outsourcing['customer_po']) ?>"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md bg-gray-100 cursor-not-allowed">
                            </div>

                            <!-- Vendor Name -->
                            <div>
                                <label for="vendor_name" class="block text-sm font-medium text-gray-700 mb-2">Vendor Name</label>
                                <input type="text" id="vendor_name" name="vendor_name" readonly
                                       value="<?= htmlspecialchars($outsourcing['vendor_name']) ?>"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md bg-gray-100 cursor-not-allowed">
                            </div>

                            <!-- Cantik PO Number -->
                            <div>
                                <label for="cantik_po_no" class="block text-sm font-medium text-gray-700 mb-2">Cantik PO Number</label>
                                <input type="text" id="cantik_po_no" name="cantik_po_no"
                                       value="<?= htmlspecialchars($outsourcing['cantik_po_no']) ?>"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-red-500">
                            </div>

                            <!-- Cantik PO Date -->
                            <div>
                                <label for="cantik_po_date" class="block text-sm font-medium text-gray-700 mb-2">Cantik PO Date</label>
                                <input type="text" id="cantik_po_date" name="cantik_po_date" data-accept-ddmmyyyy placeholder="dd-mmm-yyyy"
                                       onfocus="this.type='date'" onblur="if(!this.value) this.type='text'"
                                       value="<?= excelToDate($outsourcing['cantik_po_date']) ?>"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-red-500">
                                <p class="mt-1 text-xs text-gray-500">Enter date as dd-mm-yyyy or dd-mmm-yyyy (e.g., 03-Jun-2025). You can paste.</p>
                            </div>

                            <!-- Cantik PO Value -->
                            <div>
                                <label for="cantik_po_value" class="block text-sm font-medium text-gray-700 mb-2">Cantik PO Value</label>
                                <input type="number" id="cantik_po_value" name="cantik_po_value" step="0.01"
                                       value="<?= htmlspecialchars($outsourcing['cantik_po_value']) ?>"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-red-500">
                            </div>

                            <!-- Remaining Balance in PO -->
                            <div>
                                <label for="remaining_bal_in_po" class="block text-sm font-medium text-gray-700 mb-2">Remaining Balance in PO</label>
                                <input type="number" id="remaining_bal_in_po" name="remaining_bal_in_po" step="0.01"
                                       value="<?= htmlspecialchars($outsourcing['remaining_bal_in_po']) ?>"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-red-500">
                            </div>

                            <!-- Vendor Invoice Frequency -->
                            <div>
                                <label for="vendor_invoice_frequency" class="block text-sm font-medium text-gray-700 mb-2">Vendor Invoice Frequency</label>
                                <select id="vendor_invoice_frequency" name="vendor_invoice_frequency"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-red-500">
                                    <option value="">Select frequency</option>
                                    <option value="Monthly" <?= $outsourcing['vendor_invoice_frequency'] === 'Monthly' ? 'selected' : '' ?>>Monthly</option>
                                    <option value="Quarterly" <?= $outsourcing['vendor_invoice_frequency'] === 'Quarterly' ? 'selected' : '' ?>>Quarterly</option>
                                    <option value="Half-yearly" <?= $outsourcing['vendor_invoice_frequency'] === 'Half-yearly' ? 'selected' : '' ?>>Half-yearly</option>
                                    <option value="Yearly" <?= $outsourcing['vendor_invoice_frequency'] === 'Yearly' ? 'selected' : '' ?>>Yearly</option>
                                    <option value="One-time" <?= $outsourcing['vendor_invoice_frequency'] === 'One-time' ? 'selected' : '' ?>>One-time</option>
                                </select>
                            </div>

                            <!-- Vendor Invoice Number -->
                            <div>
                                <label for="vendor_inv_number" class="block text-sm font-medium text-gray-700 mb-2">Vendor Invoice Number</label>
                                <input type="text" id="vendor_inv_number" name="vendor_inv_number"
                                       value="<?= htmlspecialchars($outsourcing['vendor_inv_number']) ?>"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-red-500">
                            </div>

                            <!-- Vendor Invoice Date -->
                            <div>
                                <label for="vendor_inv_date" class="block text-sm font-medium text-gray-700 mb-2">Vendor Invoice Date</label>
                                <input type="text" id="vendor_inv_date" name="vendor_inv_date" data-accept-ddmmyyyy placeholder="dd-mmm-yyyy"
                                       onfocus="this.type='date'" onblur="if(!this.value) this.type='text'"
                                       value="<?= excelToDate($outsourcing['vendor_inv_date']) ?>"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-red-500">
                                <p class="mt-1 text-xs text-gray-500">Enter date as dd-mm-yyyy or dd-mmm-yyyy (e.g., 03-Jun-2025). You can paste.</p>
                            </div>

                            <!-- Vendor Invoice Value -->
                            <div>
                                <label for="vendor_inv_value" class="block text-sm font-medium text-gray-700 mb-2">Vendor Invoice Value</label>
                                <input type="number" id="vendor_inv_value" name="vendor_inv_value" step="0.01"
                                       value="<?= htmlspecialchars($outsourcing['vendor_inv_value']) ?>"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-red-500">
                            </div>

                            <!-- TDS Deducted -->
                            <div>
                                <label for="tds_ded" class="block text-sm font-medium text-gray-700 mb-2">TDS Deducted</label>
                                <input type="number" id="tds_ded" name="tds_ded" step="0.01" readonly
                                       value="<?= htmlspecialchars($outsourcing['tds_ded']) ?>"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md bg-gray-100 cursor-not-allowed">
          </div>

                            <!-- Net Payable -->
                            <div>
                                <label for="net_payble" class="block text-sm font-medium text-gray-700 mb-2">Net Payable</label>
                                <input type="number" id="net_payble" name="net_payble" step="0.01" readonly
                                       value="<?= htmlspecialchars($outsourcing['net_payble']) ?>"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md bg-gray-100 cursor-not-allowed">
          </div>

                            <!-- Payment Status from NTT -->
                            <div>
                                <label for="payment_status_from_ntt" class="block text-sm font-medium text-gray-700 mb-2">Payment Status from NTT</label>
                                <select id="payment_status_from_ntt" name="payment_status_from_ntt"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-red-500">
                                    <option value="">Select status</option>
                                    <option value="Pending" <?= $outsourcing['payment_status_from_ntt'] === 'Pending' ? 'selected' : '' ?>>Pending</option>
                                    <option value="Paid" <?= $outsourcing['payment_status_from_ntt'] === 'Paid' ? 'selected' : '' ?>>Paid</option>
                                    <option value="Partial" <?= $outsourcing['payment_status_from_ntt'] === 'Partial' ? 'selected' : '' ?>>Partial</option>
                                    <option value="Overdue" <?= $outsourcing['payment_status_from_ntt'] === 'Overdue' ? 'selected' : '' ?>>Overdue</option>
              </select>
                            </div>

                            <!-- Payment Value -->
                            <div>
                                <label for="payment_value" class="block text-sm font-medium text-gray-700 mb-2">Payment Value</label>
                                <input type="number" id="payment_value" name="payment_value" step="0.01"
                                       value="<?= htmlspecialchars($outsourcing['payment_value']) ?>"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-red-500">
                            </div>

                            <!-- Payment Date -->
                            <div>
                                <label for="payment_date" class="block text-sm font-medium text-gray-700 mb-2">Payment Date</label>
                                <input type="text" id="payment_date" name="payment_date" data-accept-ddmmyyyy placeholder="dd-mmm-yyyy"
                                       onfocus="this.type='date'" onblur="if(!this.value) this.type='text'"
                                       value="<?= excelToDate($outsourcing['payment_date']) ?>"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-red-500">
                                <p class="mt-1 text-xs text-gray-500">Enter date as dd-mm-yyyy or dd-mmm-yyyy (e.g., 03-Jun-2025). You can paste.</p>
                            </div>

                            <!-- Pending Payment -->
                            <div>
                                <label for="pending_payment" class="block text-sm font-medium text-gray-700 mb-2">Pending Payment</label>
                                <input type="number" id="pending_payment" name="pending_payment" step="0.01" readonly
                                       value="<?= htmlspecialchars($outsourcing['pending_payment']) ?>"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md bg-gray-100 cursor-not-allowed">
          </div>

                            <!-- Remarks -->
                            <div class="md:col-span-2 lg:col-span-3">
                                <label for="remarks" class="block text-sm font-medium text-gray-700 mb-2">Remarks</label>
                                <textarea id="remarks" name="remarks" rows="3"
                                          class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-red-500"><?= htmlspecialchars($outsourcing['remarks']) ?></textarea>
                            </div>
          </div>

                        <!-- Form Actions -->
                        <div class="mt-8 flex justify-end gap-4">
                            <a href="list.php" class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-red-500">
                                Cancel
                            </a>
                            <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded-md text-sm font-medium hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500">
                                <span class="material-symbols-outlined mr-2 text-sm">save</span>
                                Update Outsourcing Record
                            </button>
          </div>
        </form>
                </div>
      </div>
    </main>
  </div>
</body>
</html>
<script>
(function() {
    const toIso = (str) => {
        if (!str) return null; 
        const s = String(str).trim();
        
        // Handle yyyy-mm-dd format (already ISO)
        let m = /^(\d{4})-(\d{1,2})-(\d{1,2})$/i.exec(s);
        if (m) {
            const y = m[1];
            const mo = m[2].padStart(2,'0');
            const d = m[3].padStart(2,'0');
            if (+mo >= 1 && +mo <= 12 && +d >= 1 && +d <= 31) {
                return `${y}-${mo}-${d}`;
            }
            return null;
        }
        
        // Handle dd/mm/yyyy or dd-mm-yyyy formats
        m = /^(\d{1,2})[-\/](\d{1,2})[-\/]?(\d{4})$/i.exec(s);
        if (m) {
            const d = m[1].padStart(2,'0'); 
            const mo = m[2].padStart(2,'0'); 
            const y = m[3]; 
            if (+mo >= 1 && +mo <= 12 && +d >= 1 && +d <= 31) {
                return `${y}-${mo}-${d}`;
            }
            return null;
        }
        
        // Handle dd-mmm-yyyy or dd mmm yyyy formats
        m = /^(\d{1,2})[-\s]([A-Za-z]{3,})[-\s](\d{4})$/i.exec(s);
        if (m) {
            const d = m[1].padStart(2,'0'); 
            const mon = m[2].slice(0,3).toLowerCase(); 
            const y = m[3]; 
            const map = {
                jan:'01', feb:'02', mar:'03', apr:'04', may:'05', jun:'06',
                jul:'07', aug:'08', sep:'09', oct:'10', nov:'11', dec:'12'
            }; 
            const mo = map[mon]; 
            if (mo && +d >= 1 && +d <= 31) {
                return `${y}-${mo}-${d}`;
            }
            return null;
        }
        
        // Handle dd-mmm-yy format (2-digit year)
        m = /^(\d{1,2})[-\s]([A-Za-z]{3,})[-\s](\d{2})$/i.exec(s);
        if (m) {
            const d = m[1].padStart(2,'0'); 
            const mon = m[2].slice(0,3).toLowerCase(); 
            let y = m[3]; 
            // Convert 2-digit year to 4-digit (assuming 20xx for years 00-99)
            if (+y >= 0 && +y <= 99) {
                y = '20' + y.padStart(2,'0');
            }
            const map = {
                jan:'01', feb:'02', mar:'03', apr:'04', may:'05', jun:'06',
                jul:'07', aug:'08', sep:'09', oct:'10', nov:'11', dec:'12'
            }; 
            const mo = map[mon]; 
            if (mo && +d >= 1 && +d <= 31) {
                return `${y}-${mo}-${d}`;
            }
            return null;
        }
        
        // Handle mm/dd/yyyy format (US style)
        m = /^(\d{1,2})\/(\d{1,2})\/(\d{4})$/i.exec(s);
        if (m) {
            const mo = m[1].padStart(2,'0'); 
            const d = m[2].padStart(2,'0'); 
            const y = m[3]; 
            if (+mo >= 1 && +mo <= 12 && +d >= 1 && +d <= 31) {
                return `${y}-${mo}-${d}`;
            }
            return null;
        }
        
        return null;
    };
    const wire=(input)=>{
        const convert=()=>{const v=input.value; const iso=toIso(v); if(iso) input.value=iso;};
        input.addEventListener('blur', convert);
        input.addEventListener('paste', (e) => {
            // Get the pasted text immediately
            const pastedText = (e.clipboardData || window.clipboardData).getData('text');
            const iso = toIso(pastedText);
            if (iso) {
                e.preventDefault();
                input.value = iso;
                input.type = 'date';
            } else {
                // If not a valid date format, let the paste happen normally and check after
                setTimeout(() => {
                    const text = input.value;
                    const iso = toIso(text);
                    if (iso) {
                        input.value = iso;
                        input.type = 'date';
                    }
                }, 10);
            }
        });
        
        // Also handle input event for better paste support
        input.addEventListener('input', (e) => {
            const v = e.target.value;
            if (v.length >= 8) { // Minimum length for a date
                const iso = toIso(v);
                if (iso) {
                    input.value = iso;
                    input.type = 'date';
                }
            }
        });
    };
    document.querySelectorAll('input[data-accept-ddmmyyyy]').forEach(wire);
})();

// Automatic calculation for TDS, Net Payable, and Pending Payment
(function() {
    const vendorInvValueInput = document.getElementById('vendor_inv_value');
    const tdsDedInput = document.getElementById('tds_ded');
    const netPayableInput = document.getElementById('net_payble');
    const paymentValueInput = document.getElementById('payment_value');
    const pendingPaymentInput = document.getElementById('pending_payment');
    
    function calculateValues() {
        const vendorInvValue = parseFloat(vendorInvValueInput.value) || 0;
        const paymentValue = parseFloat(paymentValueInput.value) || 0;
        
        // TDS calculation (10% of vendor invoice value)
        const tdsRate = 0.10;
        const tdsAmount = vendorInvValue * tdsRate;
        
        // Net Payable = Vendor Invoice Value - TDS
        const netPayable = vendorInvValue - tdsAmount;
        
        // Pending Payment = Net Payable - Payment Value
        const pendingPayment = Math.max(netPayable - paymentValue, 0);
        
        tdsDedInput.value = tdsAmount.toFixed(2);
        netPayableInput.value = netPayable.toFixed(2);
        pendingPaymentInput.value = pendingPayment.toFixed(2);
    }
    
    if (vendorInvValueInput) {
        vendorInvValueInput.addEventListener('input', calculateValues);
        vendorInvValueInput.addEventListener('change', calculateValues);
    }
    
    if (paymentValueInput) {
        paymentValueInput.addEventListener('input', calculateValues);
        paymentValueInput.addEventListener('change', calculateValues);
    }
})();
</script>
