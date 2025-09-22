<?php
// Use universal includes
include '../../shared/includes.php';

// Check authentication
if (!isset($_SESSION['username'])) {
  header('Location: ' . getLoginUrl());
  exit();
}
requirePermission('add_outsourcing');

$po_list = [];
if ($res = $conn->query("SELECT id, po_number, project_description FROM po_details ORDER BY updated_at DESC LIMIT 100")) {
    while ($row = $res->fetch_assoc()) { $po_list[] = $row; }
    $res->free();
}

$success = '';
$error = '';

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
    $tds_rate = isset($_POST['tds_rate']) && is_numeric($_POST['tds_rate']) ? (float)$_POST['tds_rate'] : 2.0; // percent
    if ($tds_rate < 0) { $tds_rate = 0.0; }
    // Compute TDS @selected% on vendor invoice and Net Payable = (Value * 1.18) - TDS
    $tds_ded = round(((float)$vendor_inv_value) * ($tds_rate/100.0), 2);
    $net_payble = round((((float)$vendor_inv_value) * 1.18) - $tds_ded, 2);
    $payment_status_from_ntt = trim($_POST['payment_status_from_ntt'] ?? '');
    $payment_value = $_POST['payment_value'] ?: 0;
    $payment_date = $_POST['payment_date'] ?: null;
    // Pending Payment = Net Payable - payment_value (if any)
    $pending_payment = round(((float)$net_payble) - ((float)$payment_value ?: 0), 2);
    $remarks = trim($_POST['remarks'] ?? '');

    // Convert dates to Excel format if provided
    $cantik_po_date_excel = $cantik_po_date ? (strtotime($cantik_po_date) / 86400) + 25569 : null;
    $vendor_inv_date_excel = $vendor_inv_date ? (strtotime($vendor_inv_date) / 86400) + 25569 : null;
    $payment_date_excel = $payment_date ? (strtotime($payment_date) / 86400) + 25569 : null;

    $sql = "INSERT INTO outsourcing_detail (project_details, cost_center, customer_po, vendor_name, cantik_po_no, cantik_po_date, cantik_po_value, remaining_bal_in_po, vendor_invoice_frequency, vendor_inv_number, vendor_inv_date, vendor_inv_value, tds_ded, net_payble, payment_status_from_ntt, payment_value, payment_date, pending_payment, remarks) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    
    if ($stmt) {
        $stmt->bind_param("sssssiddssidddsdids", $project_details, $cost_center, $customer_po, $vendor_name, $cantik_po_no, $cantik_po_date_excel, $cantik_po_value, $remaining_bal_in_po, $vendor_invoice_frequency, $vendor_inv_number, $vendor_inv_date_excel, $vendor_inv_value, $tds_ded, $net_payble, $payment_status_from_ntt, $payment_value, $payment_date_excel, $pending_payment, $remarks);

    if ($stmt->execute()) {
            $success = "Outsourcing record created successfully!";
            $auth->logAction('create_outsourcing', 'outsourcing_detail', $conn->insert_id);
            // Redirect to list page after successful creation
            header('Location: list.php?success=created');
            exit();
    } else {
      $error = "Error: " . $stmt->error;
    }
    $stmt->close();
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
    <title>Add Outsourcing Record - Cantik</title>
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
                            <h1 class="text-3xl font-bold text-gray-900">Add Outsourcing Record</h1>
                            <p class="text-gray-600 mt-2">Create a new outsourcing record</p>
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
                        <!-- Link to PO -->
                        <div class="mb-6 p-4 rounded-md bg-rose-50 border border-rose-100">
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 items-end">
                                <div class="md:col-span-2">
                                    <label for="linked_po_number" class="block text-sm font-medium text-gray-700 mb-2">Link to PO (optional)</label>
                                    <input list="po_numbers" id="linked_po_number" placeholder="Start typing PO Number" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-red-500">
                                    <datalist id="po_numbers">
                                        <?php foreach ($po_list as $po): ?>
                                            <option value="<?= htmlspecialchars($po['po_number']) ?>"><?= htmlspecialchars($po['project_description']) ?></option>
                                        <?php endforeach; ?>
                                    </datalist>
                                    <p class="mt-1 text-xs text-gray-500">Selecting a PO will prefill project, cost center, customer PO and vendor.</p>
                                </div>
                                <div>
                                    <button type="button" id="fetchPoBtn" class="w-full inline-flex items-center justify-center px-4 py-2 bg-red-600 text-white rounded-md text-sm font-medium hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500">
                                        <span class="material-symbols-outlined mr-2 text-sm">sync</span>
                                        Fetch PO
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                            <!-- Project Details -->
                            <div class="md:col-span-2 lg:col-span-3">
                                <label for="project_details" class="block text-sm font-medium text-gray-700 mb-2">Project Details</label>
                                <input type="text" id="project_details" name="project_details" readonly
                                       value="<?= htmlspecialchars($_POST['project_details'] ?? '') ?>"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md bg-gray-100 cursor-not-allowed">
                            </div>

                            <!-- Cost Center -->
                            <div>
                                <label for="cost_center" class="block text-sm font-medium text-gray-700 mb-2">Cost Center</label>
                                <input type="text" id="cost_center" name="cost_center" readonly
                                       value="<?= htmlspecialchars($_POST['cost_center'] ?? '') ?>"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md bg-gray-100 cursor-not-allowed">
                            </div>

                            <!-- Customer PO -->
                            <div>
                                <label for="customer_po" class="block text-sm font-medium text-gray-700 mb-2">Customer PO</label>
                                <input type="text" id="customer_po" name="customer_po" readonly
                                       value="<?= htmlspecialchars($_POST['customer_po'] ?? '') ?>"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md bg-gray-100 cursor-not-allowed">
                            </div>

                            <!-- Vendor Name -->
                            <div>
                                <label for="vendor_name" class="block text-sm font-medium text-gray-700 mb-2">Vendor Name</label>
                                <input type="text" id="vendor_name" name="vendor_name"
                                       value="<?= htmlspecialchars($_POST['vendor_name'] ?? '') ?>"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-red-500">
                            </div>

                            <!-- Cantik PO Number -->
                            <div>
                                <label for="cantik_po_no" class="block text-sm font-medium text-gray-700 mb-2">Cantik PO Number</label>
                                <input type="text" id="cantik_po_no" name="cantik_po_no"
                                       value="<?= htmlspecialchars($_POST['cantik_po_no'] ?? '') ?>"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-red-500">
                            </div>

                            <!-- Cantik PO Date -->
                            <div>
                                <label for="cantik_po_date" class="block text-sm font-medium text-gray-700 mb-2">Cantik PO Date</label>
                                <input type="text" id="cantik_po_date" name="cantik_po_date" data-accept-ddmmyyyy placeholder="dd-mmm-yyyy"
                                       onfocus="this.type='date'" onblur="if(!this.value) this.type='text'"
                                       value="<?= htmlspecialchars($_POST['cantik_po_date'] ?? '') ?>"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-red-500">
                                <p class="mt-1 text-xs text-gray-500">Enter date as dd-mm-yyyy or dd-mmm-yyyy (e.g., 03-Jun-2025). You can paste.</p>
                            </div>

                            <!-- Cantik PO Value -->
                            <div>
                                <label for="cantik_po_value" class="block text-sm font-medium text-gray-700 mb-2">Cantik PO Value</label>
                                <input type="number" id="cantik_po_value" name="cantik_po_value" step="0.01"
                                       value="<?= htmlspecialchars($_POST['cantik_po_value'] ?? '') ?>"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-red-500">
                            </div>

                            <!-- Remaining Balance in PO -->
                            <div>
                                <label for="remaining_bal_in_po" class="block text-sm font-medium text-gray-700 mb-2">Remaining Balance in PO</label>
                                <input type="number" id="remaining_bal_in_po" name="remaining_bal_in_po" step="0.01"
                                       value="<?= htmlspecialchars($_POST['remaining_bal_in_po'] ?? '') ?>"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-red-500">
                            </div>

                            <!-- Vendor Invoice Frequency -->
                            <div>
                                <label for="vendor_invoice_frequency" class="block text-sm font-medium text-gray-700 mb-2">Vendor Invoice Frequency</label>
                                <select id="vendor_invoice_frequency" name="vendor_invoice_frequency"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-red-500">
                                    <option value="">Select frequency</option>
                                    <option value="Monthly" <?= ($_POST['vendor_invoice_frequency'] ?? '') === 'Monthly' ? 'selected' : '' ?>>Monthly</option>
                                    <option value="Quarterly" <?= ($_POST['vendor_invoice_frequency'] ?? '') === 'Quarterly' ? 'selected' : '' ?>>Quarterly</option>
                                    <option value="Half-yearly" <?= ($_POST['vendor_invoice_frequency'] ?? '') === 'Half-yearly' ? 'selected' : '' ?>>Half-yearly</option>
                                    <option value="Yearly" <?= ($_POST['vendor_invoice_frequency'] ?? '') === 'Yearly' ? 'selected' : '' ?>>Yearly</option>
                                    <option value="One-time" <?= ($_POST['vendor_invoice_frequency'] ?? '') === 'One-time' ? 'selected' : '' ?>>One-time</option>
              </select>
                            </div>

                            <!-- Vendor Invoice Number -->
                            <div>
                                <label for="vendor_inv_number" class="block text-sm font-medium text-gray-700 mb-2">Vendor Invoice Number</label>
                                <input type="text" id="vendor_inv_number" name="vendor_inv_number"
                                       value="<?= htmlspecialchars($_POST['vendor_inv_number'] ?? '') ?>"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-red-500">
                            </div>

                            <!-- Vendor Invoice Date -->
                            <div>
                                <label for="vendor_inv_date" class="block text-sm font-medium text-gray-700 mb-2">Vendor Invoice Date</label>
                                <input type="text" id="vendor_inv_date" name="vendor_inv_date" data-accept-ddmmyyyy placeholder="dd-mmm-yyyy"
                                       value="<?= htmlspecialchars($_POST['vendor_inv_date'] ?? '') ?>"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-red-500"
                                       onfocus="this.type='date'" onblur="if(!this.value) this.type='text'">
                                <p class="mt-1 text-xs text-gray-500">Enter date as dd-mm-yyyy or dd-mmm-yyyy (e.g., 03-Jun-2025). You can paste.</p>
          </div>

                            <!-- Vendor Invoice Value -->
                            <div>
                                <label for="vendor_inv_value" class="block text-sm font-medium text-gray-700 mb-2">Vendor Invoice Value</label>
                                <input type="number" id="vendor_inv_value" name="vendor_inv_value" step="0.01"
                                       value="<?= htmlspecialchars($_POST['vendor_inv_value'] ?? '') ?>"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-red-500">
                            </div>

                            <!-- TDS Rate -->
                            <div>
                                <label for="tds_rate" class="block text-sm font-medium text-gray-700 mb-2">TDS Rate</label>
                                <select id="tds_rate" name="tds_rate" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-red-500">
                                    <?php $rateSel = isset($_POST['tds_rate']) ? (string)$_POST['tds_rate'] : '2'; ?>
                                    <option value="2" <?= $rateSel==='2' ? 'selected' : '' ?>>2%</option>
                                    <option value="10" <?= $rateSel==='10' ? 'selected' : '' ?>>10%</option>
                                </select>
                                <p class="mt-1 text-xs text-gray-500">Select TDS rate; value auto-calculates from Taxable.</p>
                            </div>
                            <!-- TDS Deducted -->
                            <div>
                                <label for="tds_ded" class="block text-sm font-medium text-gray-700 mb-2">TDS Deducted</label>
                                <input type="number" id="tds_ded" name="tds_ded" step="0.01" readonly
                                       value="<?= htmlspecialchars($_POST['tds_ded'] ?? '') ?>"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md bg-gray-100 cursor-not-allowed">
          </div>

                            <!-- Net Payable -->
                            <div>
                                <label for="net_payble" class="block text-sm font-medium text-gray-700 mb-2">Net Payable</label>
                                <input type="number" id="net_payble" name="net_payble" step="0.01" readonly
                                       value="<?= htmlspecialchars($_POST['net_payble'] ?? '') ?>"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md bg-gray-100 cursor-not-allowed">
          </div>

                            <!-- Payment Status from NTT -->
                            <div>
                                <label for="payment_status_from_ntt" class="block text-sm font-medium text-gray-700 mb-2">Payment Status from NTT</label>
                                <select id="payment_status_from_ntt" name="payment_status_from_ntt"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-red-500">
                                    <option value="">Select status</option>
                                    <option value="Pending" <?= ($_POST['payment_status_from_ntt'] ?? '') === 'Pending' ? 'selected' : '' ?>>Pending</option>
                                    <option value="Paid" <?= ($_POST['payment_status_from_ntt'] ?? '') === 'Paid' ? 'selected' : '' ?>>Paid</option>
                                    <option value="Partial" <?= ($_POST['payment_status_from_ntt'] ?? '') === 'Partial' ? 'selected' : '' ?>>Partial</option>
                                    <option value="Overdue" <?= ($_POST['payment_status_from_ntt'] ?? '') === 'Overdue' ? 'selected' : '' ?>>Overdue</option>
              </select>
                            </div>

                            <!-- Payment Value -->
                            <div>
                                <label for="payment_value" class="block text-sm font-medium text-gray-700 mb-2">Payment Value</label>
                                <input type="number" id="payment_value" name="payment_value" step="0.01"
                                       value="<?= htmlspecialchars($_POST['payment_value'] ?? '') ?>"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-red-500">
                            </div>

                            <!-- Payment Date -->
                            <div>
                                <label for="payment_date" class="block text-sm font-medium text-gray-700 mb-2">Payment Date</label>
                                <input type="text" id="payment_date" name="payment_date" data-accept-ddmmyyyy placeholder="dd-mmm-yyyy"
                                       onfocus="this.type='date'" onblur="if(!this.value) this.type='text'"
                                       value="<?= htmlspecialchars($_POST['payment_date'] ?? '') ?>"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-red-500">
                                <p class="mt-1 text-xs text-gray-500">Enter date as dd-mm-yyyy or dd-mmm-yyyy (e.g., 03-Jun-2025). You can paste.</p>
                            </div>

                            <!-- Pending Payment -->
                            <div>
                                <label for="pending_payment" class="block text-sm font-medium text-gray-700 mb-2">Pending Payment</label>
                                <input type="number" id="pending_payment" name="pending_payment" step="0.01" readonly
                                       value="<?= htmlspecialchars($_POST['pending_payment'] ?? '') ?>"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md bg-gray-100 cursor-not-allowed">
                                <p class="mt-1 text-xs text-gray-500">Calculated as Net Payable − Payment Value.</p>
                            </div>

                            <!-- Remarks -->
                            <div class="md:col-span-2 lg:col-span-3">
                                <label for="remarks" class="block text-sm font-medium text-gray-700 mb-2">Remarks</label>
                                <textarea id="remarks" name="remarks" rows="3"
                                          class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-red-500"><?= htmlspecialchars($_POST['remarks'] ?? '') ?></textarea>
          </div>
          </div>

                        <!-- Form Actions -->
                        <div class="mt-8 flex justify-end gap-4">
                            <a href="list.php" class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-red-500">
                                Cancel
                            </a>
                            <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded-md text-sm font-medium hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500">
                                <span class="material-symbols-outlined mr-2 text-sm">save</span>
                                Create Outsourcing Record
                            </button>
          </div>
        </form>
                </div>
      </div>
    </main>
  </div>
  <script>
        const fetchBtn = document.getElementById('fetchPoBtn');
        if (fetchBtn) {
            fetchBtn.addEventListener('click', async () => {
                const poNum = document.getElementById('linked_po_number').value.trim();
                if (!poNum) return;
                try {
                    const res = await fetch(`../po_details/get_po.php?po_number=${encodeURIComponent(poNum)}`);
                    if (!res.ok) throw new Error('Failed to fetch PO');
                    const json = await res.json();
                    const po = json.data || {};
                    document.getElementById('project_details').value = po.project_description ?? '';
                    document.getElementById('cost_center').value = po.cost_center ?? '';
                    document.getElementById('customer_po').value = po.po_number ?? '';
                    const setVal = (id, val) => { const el = document.getElementById(id); if (el && (el.value === '' || el.value === undefined)) el.value = val ?? ''; };
                    setVal('vendor_name', po.vendor_name ?? '');
                    setVal('remaining_bal_in_po', po.pending_amount ?? '');
                } catch (e) {
                    alert('Unable to fetch PO details.');
                }
            });
    }
  </script>
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
    const wire = (input) => {
        const convert = () => { const v=input.value; const iso=toIso(v); if(iso) input.value=iso; };
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
// Live calculations for outsourcing: TDS (2%), Net Payable, Pending Payment
(function() {
    const byId = (id) => document.getElementById(id);
    const invEl = byId('vendor_inv_value');
    const tdsEl = byId('tds_ded');
    const netEl = byId('net_payble');
    const rateEl = byId('tds_rate');
    const payValEl = byId('payment_value');
    const pendingEl = byId('pending_payment');
    const cantikPoValEl = byId('cantik_po_value');
    const customerPoEl = byId('customer_po');
    const remainingBalEl = byId('remaining_bal_in_po');
    let bookedTillDate = 0;

    function recalc() {
        if (!invEl || !tdsEl || !netEl || !pendingEl) return;
        const inv = parseFloat(invEl.value || '0') || 0;
        const rate = parseFloat(rateEl && rateEl.value ? rateEl.value : '2') || 2;
        const tds = +(inv * (rate/100)).toFixed(2);
        const net = +((inv * 1.18) - tds).toFixed(2); // GST 18%
        const pay = parseFloat((payValEl && payValEl.value) ? payValEl.value : '0') || 0;
        const pending = Math.max(0, +(net - pay).toFixed(2));

        tdsEl.value = isFinite(tds) ? tds : '';
        netEl.value = isFinite(net) ? net : '';
        pendingEl.value = isFinite(pending) ? pending : '';

        // Remaining balance in PO = Cantik PO Value - Vendor invoices booked till date
        const cantikPoVal = parseFloat(cantikPoValEl && cantikPoValEl.value ? cantikPoValEl.value : '0') || 0;
        const remaining = Math.max(0, +(cantikPoVal - bookedTillDate).toFixed(2));
        if (remainingBalEl) remainingBalEl.value = isFinite(remaining) ? remaining : '';
    }

    if (invEl) ['input','change','blur'].forEach(e=>invEl.addEventListener(e, recalc));
    if (rateEl) ['change'].forEach(e=>rateEl.addEventListener(e, recalc));
    if (payValEl) ['input','change','blur'].forEach(e=>payValEl.addEventListener(e, recalc));
    // Fetch booked till date when customer_po is known
    async function fetchBookedTillDate() {
        if (!customerPoEl || !customerPoEl.value) return;
        try {
            const res = await fetch(`get_po_vendor_sum.php?po_number=${encodeURIComponent(customerPoEl.value)}`);
            if (!res.ok) return;
            const json = await res.json();
            if (json && json.success) {
                bookedTillDate = parseFloat(json.data.total_booked || 0) || 0;
                recalc();
            }
        } catch (_) {}
    }
    if (customerPoEl) {
        ['change','blur'].forEach(e=>customerPoEl.addEventListener(e, fetchBookedTillDate));
        // also trigger once on load
        fetchBookedTillDate();
    }

    // Initial calc
    recalc();
})();
</script>
