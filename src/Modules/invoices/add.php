<?php
// Use universal includes
include '../../shared/includes.php';

// Check authentication
if (!isset($_SESSION['username'])) {
  header('Location: ' . getLoginUrl());
  exit();
}
requirePermission('add_invoices');

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
    $remaining_balance = $_POST['remaining_balance_in_po'] ?: 0;
    $cantik_invoice_no = trim($_POST['cantik_invoice_no'] ?? '');
    $cantik_invoice_date = $_POST['cantik_invoice_date'] ?: null;
    $cantik_inv_value_taxable = $_POST['cantik_inv_value_taxable'] ?: 0;
    // Compute TDS using user-selected rate (2% default) and Receivable = (Taxable * 1.18) - TDS
    $tds_rate = isset($_POST['tds_rate']) ? (float)$_POST['tds_rate'] : 2.0; // 2 or 10
    $tds = round(((float)$cantik_inv_value_taxable) * ($tds_rate / 100.0), 2);
    $receivable = round((((float)$cantik_inv_value_taxable) * 1.18) - $tds, 2);
    $against_vendor_inv_number = trim($_POST['against_vendor_inv_number'] ?? '');
    $payment_receipt_date = $_POST['payment_receipt_date'] ?: null;
    $payment_advise_no = trim($_POST['payment_advise_no'] ?? '');
    $vendor_name = trim($_POST['vendor_name'] ?? '');

    // Convert dates to Excel format if provided
    $cantik_invoice_date_excel = $cantik_invoice_date ? (strtotime($cantik_invoice_date) / 86400) + 25569 : null;
    $payment_receipt_date_excel = $payment_receipt_date ? (strtotime($payment_receipt_date) / 86400) + 25569 : null;

    $sql = "INSERT INTO billing_details (project_details, cost_center, customer_po, remaining_balance_in_po, cantik_invoice_no, cantik_invoice_date, cantik_inv_value_taxable, tds, receivable, against_vendor_inv_number, payment_receipt_date, payment_advise_no, vendor_name) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    
    if ($stmt) {
        $stmt->bind_param("sssdsidddsiss", $project_details, $cost_center, $customer_po, $remaining_balance, $cantik_invoice_no, $cantik_invoice_date_excel, $cantik_inv_value_taxable, $tds, $receivable, $against_vendor_inv_number, $payment_receipt_date_excel, $payment_advise_no, $vendor_name);

    if ($stmt->execute()) {
            $success = "Invoice created successfully!";
            $auth->logAction('create_invoice', 'billing_details', $conn->insert_id);
            $_POST = array(); // Clear form data
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
    <title>Add Invoice - Cantik Homemade</title>
    <link crossorigin="" href="https://fonts.gstatic.com/" rel="preconnect"/>
    <link as="style" href="https://fonts.googleapis.com/css2?display=swap&amp;family=Noto+Sans%3Awght%40400%3B500%3B700%3B900&amp;family=Public+Sans%3Awght%40400%3B500%3B600%3B700%3B800" onload="this.rel='stylesheet'" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet"/>
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
</head>
<body class="bg-gray-50 text-gray-900" style='font-family: "Public Sans", "Noto Sans", sans-serif;'>
    <div class="relative flex size-full min-h-screen flex-col overflow-x-hidden">
    <?php include getSharedIncludePath('nav.php'); ?>
        
        <main class="flex-1 px-4 sm:px-6 lg:px-8 py-8">
            <div class="max-w-4xl mx-auto">
                <!-- Page Header -->
                <div class="mb-8">
                    <div class="flex items-center justify-between">
                        <div>
                            <h1 class="text-3xl font-bold text-gray-900">Add Invoice</h1>
                            <p class="text-gray-600 mt-2">Create a new invoice record</p>
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
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Project Details -->
                            <div class="md:col-span-2">
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

                            <!-- Remaining Balance in PO -->
                            <div>
                                <label for="remaining_balance_in_po" class="block text-sm font-medium text-gray-700 mb-2">Remaining Balance in PO</label>
                                <input type="number" id="remaining_balance_in_po" name="remaining_balance_in_po" step="0.01" readonly
                                       value="<?= htmlspecialchars($_POST['remaining_balance_in_po'] ?? '') ?>"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md bg-gray-100 cursor-not-allowed">
                                <p class="mt-1 text-xs text-gray-500">Auto-updates from PO Value and decreases as taxable increases.</p>
                            </div>

                            <!-- Cantik Invoice Number -->
                            <div>
                                <label for="cantik_invoice_no" class="block text-sm font-medium text-gray-700 mb-2">Cantik Invoice Number</label>
                                <input type="text" id="cantik_invoice_no" name="cantik_invoice_no"
                                       value="<?= htmlspecialchars($_POST['cantik_invoice_no'] ?? '') ?>"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-red-500">
                            </div>

                            <!-- Cantik Invoice Date -->
                            <div>
                                <label for="cantik_invoice_date" class="block text-sm font-medium text-gray-700 mb-2">Cantik Invoice Date</label>
                                <input type="date" id="cantik_invoice_date" name="cantik_invoice_date" data-accept-ddmmyyyy placeholder="dd-mmm-yyyy"
                                       value="<?= htmlspecialchars($_POST['cantik_invoice_date'] ?? '') ?>"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-red-500">
                                <p class="mt-1 text-xs text-gray-500">Enter date as dd-mm-yyyy or dd-mmm-yyyy (e.g., 03-Jun-2025). You can paste.</p>
                            </div>

                            <!-- Cantik Invoice Value (Taxable) -->
                            <div>
                                <label for="cantik_inv_value_taxable" class="block text-sm font-medium text-gray-700 mb-2">Cantik Invoice Value (Taxable)</label>
                                <input type="number" id="cantik_inv_value_taxable" name="cantik_inv_value_taxable" step="0.01"
                                       value="<?= htmlspecialchars($_POST['cantik_inv_value_taxable'] ?? '') ?>"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-red-500">
                            </div>

                            <!-- TDS -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">TDS</label>
                                <div class="flex items-center gap-3">
                                  <label for="tds_rate" class="text-sm text-gray-600">Rate</label>
                                  <select id="tds_rate" name="tds_rate" class="w-24 px-2 py-2 border border-gray-300 rounded-md text-sm bg-white focus:outline-none focus:ring-2 focus:ring-red-500">
                                    <option value="2" <?= (($_POST['tds_rate'] ?? '2') == '2') ? 'selected' : '' ?>>2%</option>
                                    <option value="10" <?= (($_POST['tds_rate'] ?? '') == '10') ? 'selected' : '' ?>>10%</option>
                                  </select>
                                  <input type="number" id="tds" name="tds" step="0.01" readonly
                                         value="<?= htmlspecialchars($_POST['tds'] ?? '') ?>"
                                         class="w-full px-3 py-2 border border-gray-300 rounded-md bg-gray-100 cursor-not-allowed">
                                </div>
                                <p class="mt-1 text-xs text-gray-500">Select TDS rate; value auto-calculates from Taxable.</p>
                            </div>

                            <!-- Receivable -->
                            <div>
                                <label for="receivable" class="block text-sm font-medium text-gray-700 mb-2">Net Receivable</label>
                                <input type="number" id="receivable" name="receivable" step="0.01" readonly
                                       value="<?= htmlspecialchars($_POST['receivable'] ?? '') ?>"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md bg-gray-100 cursor-not-allowed">
                            </div>

                            <!-- Against Vendor Invoice Number -->
                            <div>
                                <label for="against_vendor_inv_number" class="block text-sm font-medium text-gray-700 mb-2">Against Vendor Invoice Number</label>
                                <input type="text" id="against_vendor_inv_number" name="against_vendor_inv_number"
                                       value="<?= htmlspecialchars($_POST['against_vendor_inv_number'] ?? '') ?>"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-red-500">
                            </div>

                            <!-- Payment Receipt Date -->
                            <div>
                                <label for="payment_receipt_date" class="block text-sm font-medium text-gray-700 mb-2">Payment Receipt Date</label>
                                <input type="date" id="payment_receipt_date" name="payment_receipt_date" data-accept-ddmmyyyy placeholder="dd-mmm-yyyy"
                                       value="<?= htmlspecialchars($_POST['payment_receipt_date'] ?? '') ?>"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-red-500">
                                <p class="mt-1 text-xs text-gray-500">Enter date as dd-mm-yyyy or dd-mmm-yyyy (e.g., 03-Jun-2025). You can paste.</p>
          </div>

                            <!-- Payment Advise Number -->
                            <div>
                                <label for="payment_advise_no" class="block text-sm font-medium text-gray-700 mb-2">Payment Advise Number</label>
                                <input type="text" id="payment_advise_no" name="payment_advise_no"
                                       value="<?= htmlspecialchars($_POST['payment_advise_no'] ?? '') ?>"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-red-500">
          </div>

                            <!-- Vendor Name -->
                            <div>
                                <label for="vendor_name" class="block text-sm font-medium text-gray-700 mb-2">Vendor Name</label>
                                <input type="text" id="vendor_name" name="vendor_name" readonly
                                       value="<?= htmlspecialchars($_POST['vendor_name'] ?? '') ?>"
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
                                Create Invoice
                            </button>
          </div>
        </form>
                </div>
      </div>
    </main>
  </div>
  <script>
        // Allow dd-mm-yyyy or d-MMM-yyyy paste/typing for inputs with data-accept-ddmmyyyy
        (function() {
            const toIso = (str) => {
                if (!str) return null;
                const s = String(str).trim();
                // dd-mm-yyyy or dd/mm/yyyy
                let m = /^(\d{1,2})[-\/](\d{1,2})[-\/]?(\d{4})$/i.exec(s);
                if (m) {
                    const d = m[1].padStart(2,'0');
                    const mo = m[2].padStart(2,'0');
                    const y = m[3];
                    if (+mo >= 1 && +mo <= 12 && +d >= 1 && +d <= 31) return `${y}-${mo}-${d}`;
                    return null;
                }
                // d-MMM-yyyy
                m = /^(\d{1,2})[-\s]([A-Za-z]{3,})[-\s](\d{4})$/i.exec(s);
                if (m) {
                    const d = m[1].padStart(2,'0');
                    const monTxt = m[2].slice(0,3).toLowerCase();
                    const y = m[3];
                    const map = {jan:'01',feb:'02',mar:'03',apr:'04',may:'05',jun:'06',jul:'07',aug:'08',sep:'09',oct:'10',nov:'11',dec:'12'};
                    const mo = map[monTxt];
                    if (mo && +d >= 1 && +d <= 31) return `${y}-${mo}-${d}`;
                    return null;
                }
                return null;
            };
            const wire = (input) => {
                const convert = () => {
                    const v = input.value;
                    const iso = toIso(v);
                    if (iso) input.value = iso;
                };
                input.addEventListener('blur', convert);
                input.addEventListener('paste', (e) => {
                    const text = (e.clipboardData || window.clipboardData).getData('text');
                    const iso = toIso(text);
                    if (iso) {
                        e.preventDefault();
                        input.value = iso;
                    }
                });
            };
            document.querySelectorAll('input[type="date"][data-accept-ddmmyyyy]').forEach(wire);
        })();
        // Live financial calculations and remaining balance update
        let basePendingInPo = 0;
        function recalcInvoiceFinancials() {
            const taxableEl = document.getElementById('cantik_inv_value_taxable');
            const tdsEl = document.getElementById('tds');
            const recvEl = document.getElementById('receivable');
            const remainEl = document.getElementById('remaining_balance_in_po');
            const tdsRateEl = document.getElementById('tds_rate');
            if (!taxableEl || !tdsEl || !recvEl || !remainEl) return;
            const taxable = parseFloat(taxableEl.value || '0') || 0;
            const rate = parseFloat((tdsRateEl && tdsRateEl.value) ? tdsRateEl.value : '2') || 2;
            const tds = +(taxable * (rate/100)).toFixed(2);
            const receivable = +((taxable * 1.18) - tds).toFixed(2);
            if (!isNaN(tds)) tdsEl.value = tds;
            if (!isNaN(receivable)) recvEl.value = receivable;
            const newRemaining = Math.max(0, +(basePendingInPo - taxable).toFixed(2));
            if (!isNaN(newRemaining)) remainEl.value = newRemaining;
        }
        const taxableInput = document.getElementById('cantik_inv_value_taxable');
        if (taxableInput) {
            ['input','change','blur'].forEach(evt => taxableInput.addEventListener(evt, recalcInvoiceFinancials));
        }
        const tdsRateInput = document.getElementById('tds_rate');
        if (tdsRateInput) {
            ['change','input','blur'].forEach(evt => tdsRateInput.addEventListener(evt, recalcInvoiceFinancials));
        }
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
                    // Prefill fields
                    const setVal = (id, val) => { const el = document.getElementById(id); if (el && (el.value === '' || el.value === undefined)) el.value = val ?? ''; };
                    document.getElementById('project_details').value = po.project_description ?? '';
                    document.getElementById('cost_center').value = po.cost_center ?? '';
                    document.getElementById('customer_po').value = po.po_number ?? '';
                    setVal('vendor_name', po.vendor_name ?? '');
                    // Seed remaining balance with PO Value as requested
                    basePendingInPo = parseFloat(po.po_value ?? '0') || 0;
                    const remainEl = document.getElementById('remaining_balance_in_po');
                    if (remainEl && (remainEl.value === '' || remainEl.value === undefined)) {
                        remainEl.value = basePendingInPo;
                    }
                    // Recalculate with any current taxable value
                    recalcInvoiceFinancials();
                } catch (e) {
                    alert('Unable to fetch PO details.');
                }
            });
    }
  </script>
</body>
</html>
