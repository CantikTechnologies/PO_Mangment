
<?php
// Invoices bulk upload
header('Content-Type: application/json');
session_start();
include '../../../config/db.php';
include '../../../config/auth.php';
if (!hasPermission('add_invoices')) {
  echo json_encode(['success'=>false,'errors'=>[['row'=>0,'message'=>'Insufficient permissions']]]);
  exit;
}

function parseDateToSerial($value) {
  if ($value === null) return null;
  $t = trim((string)$value);
  if ($t === '') return null;
  $placeholders = ['-', ' - ', '--', '—', '–', '(0)', '0', 'N/A', 'n/a', 'NA', 'na', 'N.A.', 'n.a.', 'Nil', 'nil', 'NIL'];
  if (in_array($t, $placeholders, true)) return null;
  if (is_numeric($value)) return (int)$value;
  // Support dd/mm/yyyy or dd-mm-yyyy (2 or 4 digit years)
  $s2 = str_replace(['.', ' '], ['', ''], $t);
  if (preg_match('/^(\d{1,2})[\/-](\d{1,2})[\/-](\d{2,4})$/', $s2, $m)) {
    $d=(int)$m[1]; $mo=(int)$m[2]; $y=(int)$m[3];
    if ($y < 100) { $y += ($y >= 70 ? 1900 : 2000); }
    if ($mo>=1 && $mo<=12 && $d>=1 && $d<=31) {
      $ts = gmmktime(0,0,0,$mo,$d,$y);
      return (int)floor($ts/86400) + 25569;
    }
  }
  // Support d-MMM-yyyy with -, /, or space separators
  if (preg_match('/^(\d{1,2})[\-\/\s]([A-Za-z]{3,})[\-\/\s](\d{4})$/', $t, $m)) {
    $d=(int)$m[1]; $mon=strtolower(substr($m[2],0,3)); $y=(int)$m[3];
    $map=['jan'=>1,'feb'=>2,'mar'=>3,'apr'=>4,'may'=>5,'jun'=>6,'jul'=>7,'aug'=>8,'sep'=>9,'oct'=>10,'nov'=>11,'dec'=>12];
    if (isset($map[$mon]) && $d>=1 && $d<=31) {
      $ts = gmmktime(0,0,0,$map[$mon],$d,$y);
      return (int)floor($ts/86400) + 25569;
    }
  }
  // Fallback
  $normalized = str_replace(['\\', '.'], ['/', '/'], $t);
  $ts = strtotime($normalized);
  if ($ts === false) return null;
  return (int)floor($ts/86400) + 25569;
}

if (!isset($_FILES['csvFile']) || $_FILES['csvFile']['error'] !== UPLOAD_ERR_OK) {
  echo json_encode(['success'=>false,'errors'=>[['row'=>0,'message'=>'No file uploaded']]]);
  exit;
}

$file = $_FILES['csvFile']['tmp_name'];
$lines = file($file, FILE_IGNORE_NEW_LINES);
if ($lines === false || count($lines) === 0) {
  echo json_encode(['success'=>false,'errors'=>[['row'=>0,'message'=>'Empty file']]]);
  exit;
}
$first = str_replace("\xEF\xBB\xBF", '', $lines[0]);
$delimiter = substr_count($first, "\t") > substr_count($first, ',') ? "\t" : ',';
$headersRaw = str_getcsv($first, $delimiter);
$headersRaw = array_map('trim', $headersRaw);

// Canonicalize headers with aliases to avoid missing header errors
function canon($s){ $s=strtolower(trim((string)$s)); $s=preg_replace('/[^a-z0-9]+/','',$s); return $s; }
$alias=[
  'projectdetails'=>'project_details','projectname'=>'project_details','project'=>'project_details',
  'costcentre'=>'cost_center','costcenter'=>'cost_center',
  'customerpo'=>'customer_po','customerpono'=>'customer_po',
  'cantikinvoiceno'=>'cantik_invoice_no','invoiceno'=>'cantik_invoice_no','invoice'=>'cantik_invoice_no',
  'cantikinvoicedate'=>'cantik_invoice_date','invoicedate'=>'cantik_invoice_date',
  'cantikinvoicetaxablevalue'=>'cantik_inv_value_taxable','taxablevalue'=>'cantik_inv_value_taxable','amount'=>'cantik_inv_value_taxable',
  'againstvendorinvoicenumber'=>'against_vendor_inv_number','vendorinvoiceno'=>'against_vendor_inv_number',
  'paymentreceiptdate'=>'payment_receipt_date','paymentdate'=>'payment_receipt_date',
  'paymentadviseno'=>'payment_advise_no','paymentadvisenumber'=>'payment_advise_no',
  'vendorname'=>'vendor_name','vendor'=>'vendor_name'
];
$headers=[]; foreach($headersRaw as $h){ $k=canon($h); $headers[] = $alias[$k] ?? $h; }

$required = ['cost_center','customer_po','cantik_invoice_no','cantik_invoice_date','cantik_inv_value_taxable'];
$optional = ['against_vendor_inv_number','payment_receipt_date','payment_advise_no','vendor_name'];
$all = array_merge($required, $optional);

foreach ($required as $h) {
  if (!in_array($h, $headers)) {
    echo json_encode(['success'=>false,'errors'=>[['row'=>0,'message'=>'Missing header: '.$h]]]);
    exit;
  }
}

$inserted=0;$skipped=0;$errors=[];$rowNumber=1;
$seenInvoiceNos = [];

// Helpers for cleaning values similar to PO upload
function isEmptyLike($value){
  if ($value===null) return true;
  $t=trim((string)$value);
  if ($t==='') return true;
  $p=['-',' - ','--','—','–','(0)','0','N/A','n/a','NA','na','N.A.','n.a.','Nil','nil','NIL'];
  return in_array($t,$p,true);
}
function cleanAmount($value){
  if ($value===null) return '';
  $s=trim((string)$value);
  if ($s===''||$s==='-'||strtoupper($s)==='N/A') return '';
  $s=preg_replace('/[₹$,\s]/u','',$s);
  $neg=false;
  if (preg_match('/^\((.*)\)$/',$s,$m)){ $neg=true; $s=$m[1]; }
  $s=preg_replace('/[^0-9.\-]/','',$s);
  if ($s===''||!is_numeric($s)) return '';
  $n=(float)$s; if ($neg) $n=-$n; return (string)$n;
}
$dryRun = isset($_POST['dry_run']) && $_POST['dry_run']=='1';
$conn->begin_transaction();
for ($i=1;$i<count($lines);$i++) {
  $rowNumber++;
  $line = trim($lines[$i] ?? '');
  if ($line==='') continue;
  $cols = str_getcsv($line, $delimiter);
  if (count($cols) < count($headers)) $cols = array_pad($cols, count($headers), '');
  $data = array_combine($headers, $cols);

  // Enrich/clean before validation
  // 1) Project details: try derive from po_details if empty
  if (!isset($data['project_details']) || trim($data['project_details'])==='') {
    $pd = '';
    if (isset($data['customer_po']) && trim($data['customer_po'])!=='') {
      $po = trim($data['customer_po']);
      $st = $conn->prepare('SELECT project_description FROM po_details WHERE po_number = ? LIMIT 1');
      if ($st){ $st->bind_param('s',$po); $st->execute(); $res=$st->get_result(); if ($row=$res->fetch_assoc()){ $pd = $row['project_description'] ?? ''; } $st->close(); }
    }
    if ($pd!=='') { $data['project_details']=$pd; }
  }
  // 2) Clean taxable amount
  $cleanTaxable = cleanAmount($data['cantik_inv_value_taxable'] ?? '');
  // 3) Normalize date
  $cantikDate = parseDateToSerial($data['cantik_invoice_date'] ?? null);
  $paymentDate = isset($data['payment_receipt_date']) ? parseDateToSerial($data['payment_receipt_date']) : null;

  // Basic validation after enrichment
  $missing=[]; foreach ($required as $h){ if (!isset($data[$h]) || trim($data[$h])==='') $missing[]=$h; }
  if (!empty($missing)) { $errors[]=['row'=>$rowNumber,'message'=>'Missing fields: '.implode(', ',$missing)]; continue; }
  if ($cleanTaxable === '' || !is_numeric($cleanTaxable)) { $errors[]=['row'=>$rowNumber,'message'=>'cantik_inv_value_taxable must be numeric']; continue; }
  if ($cantikDate===null) { $errors[]=['row'=>$rowNumber,'message'=>'Invalid cantik_invoice_date']; continue; }

  // 4) Enforce unique cantik_invoice_no (within file and database)
  $invoiceNo = trim($data['cantik_invoice_no']);
  $invoiceKey = strtolower($invoiceNo);
  if (isset($seenInvoiceNos[$invoiceKey])) {
    $errors[] = ['row'=>$rowNumber,'message'=>'Duplicate cantik_invoice_no in uploaded file'];
    $skipped++; continue;
  }
  $seenInvoiceNos[$invoiceKey] = true;
  $du = $conn->prepare('SELECT id FROM billing_details WHERE cantik_invoice_no = ? LIMIT 1');
  if ($du) { $du->bind_param('s', $invoiceNo); $du->execute(); $dures=$du->get_result(); if ($dures && $dures->num_rows>0) { $errors[]=['row'=>$rowNumber,'message'=>'cantik_invoice_no already exists']; $skipped++; $du->close(); continue; } $du->close(); }

  // Insert
  if ($dryRun) { $inserted++; continue; }
  $stmt = $conn->prepare("INSERT INTO billing_details (
    project_details,cost_center,customer_po,remaining_balance_in_po,cantik_invoice_no,cantik_invoice_date,
    cantik_inv_value_taxable,against_vendor_inv_number,payment_receipt_date,payment_advise_no,vendor_name
  ) VALUES (?,?,?,?,?,?,?,?,?,?,?)");
  $zero = 0.00;
  // Prepare variables for bind_param (must be variables, not expressions)
  $projectDetails = $data['project_details'];
  $costCenter = $data['cost_center'];
  $customerPo = $data['customer_po'];
  $remainingBalance = $zero;
  $cantikInvoiceNo = $data['cantik_invoice_no'];
  $cantikInvoiceDate = $cantikDate; // int serial
  $taxableAmount = (float)$cleanTaxable;
  $againstVendorInvNumber = isset($data['against_vendor_inv_number']) && trim($data['against_vendor_inv_number']) !== '' ? $data['against_vendor_inv_number'] : null;
  $paymentReceiptDate = $paymentDate; // int serial or null
  $paymentAdviseNo = isset($data['payment_advise_no']) && trim($data['payment_advise_no']) !== '' ? $data['payment_advise_no'] : null;
  $vendorName = isset($data['vendor_name']) && trim($data['vendor_name']) !== '' ? $data['vendor_name'] : null;

  $stmt->bind_param(
    'sssdsidisis',
    $projectDetails, $costCenter, $customerPo, $remainingBalance,
    $cantikInvoiceNo, $cantikInvoiceDate, $taxableAmount,
    $againstVendorInvNumber, $paymentReceiptDate, $paymentAdviseNo,
    $vendorName
  );
  if ($stmt->execute()) { $inserted++; } else { $errors[]=['row'=>$rowNumber,'message'=>'DB: '.$stmt->error]; }
}

if ($dryRun) { $conn->rollback(); }
else { if ($inserted>0) { $conn->commit(); } else { $conn->rollback(); } }
echo json_encode(['success'=> ($dryRun ? true : $inserted>0), 'inserted'=>$inserted, 'skipped'=>$skipped, 'errors'=>$errors, 'dry_run'=>$dryRun]);
?>


