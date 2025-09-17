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
  $placeholders = ['-', ' - ', '(0)', '0', 'N/A', 'n/a'];
  if (in_array(trim((string)$value), $placeholders, true)) return null;
  if (is_numeric($value)) return (int)$value;
  $trimmed = trim((string)$value);
  if ($trimmed === '') return null;
  $normalized = str_replace(['\\', '.'], ['/', '/'], $trimmed);
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

$required = ['project_details','cost_center','customer_po','cantik_invoice_no','cantik_invoice_date','cantik_inv_value_taxable'];
$optional = ['against_vendor_inv_number','payment_receipt_date','payment_advise_no','vendor_name'];
$all = array_merge($required, $optional);

foreach ($required as $h) {
  if (!in_array($h, $headers)) {
    echo json_encode(['success'=>false,'errors'=>[['row'=>0,'message'=>'Missing header: '.$h]]]);
    exit;
  }
}

$inserted=0;$skipped=0;$errors=[];$rowNumber=1;
$dryRun = isset($_POST['dry_run']) && $_POST['dry_run']=='1';
$conn->begin_transaction();
for ($i=1;$i<count($lines);$i++) {
  $rowNumber++;
  $line = trim($lines[$i] ?? '');
  if ($line==='') continue;
  $cols = str_getcsv($line, $delimiter);
  if (count($cols) < count($headers)) $cols = array_pad($cols, count($headers), '');
  $data = array_combine($headers, $cols);

  // Basic validation
  $missing=[]; foreach ($required as $h){ if (!isset($data[$h]) || trim($data[$h])==='') $missing[]=$h; }
  if (!empty($missing)) { $errors[]=['row'=>$rowNumber,'message'=>'Missing fields: '.implode(', ',$missing)]; continue; }
  if (!is_numeric($data['cantik_inv_value_taxable'])) { $errors[]=['row'=>$rowNumber,'message'=>'cantik_inv_value_taxable must be numeric']; continue; }

  $cantikDate = parseDateToSerial($data['cantik_invoice_date']);
  if ($cantikDate===null) { $errors[]=['row'=>$rowNumber,'message'=>'Invalid cantik_invoice_date']; continue; }
  $paymentDate = isset($data['payment_receipt_date']) ? parseDateToSerial($data['payment_receipt_date']) : null;

  // Insert
  if ($dryRun) { $inserted++; continue; }
  $stmt = $conn->prepare("INSERT INTO billing_details (
    project_details,cost_center,customer_po,remaining_balance_in_po,cantik_invoice_no,cantik_invoice_date,
    cantik_inv_value_taxable,against_vendor_inv_number,payment_receipt_date,payment_advise_no,vendor_name
  ) VALUES (?,?,?,?,?,?,?,?,?,?,?)");
  $zero = 0.00;
  $stmt->bind_param(
    'sssdsidisis',
    $data['project_details'], $data['cost_center'], $data['customer_po'], $zero,
    $data['cantik_invoice_no'], $cantikDate, $data['cantik_inv_value_taxable'],
    $data['against_vendor_inv_number'] ?: null, $paymentDate, $data['payment_advise_no'] ?: null,
    $data['vendor_name'] ?: null
  );
  if ($stmt->execute()) { $inserted++; } else { $errors[]=['row'=>$rowNumber,'message'=>'DB: '.$stmt->error]; }
}

if ($dryRun) { $conn->rollback(); }
else { if ($inserted>0) { $conn->commit(); } else { $conn->rollback(); } }
echo json_encode(['success'=> ($dryRun ? true : $inserted>0), 'inserted'=>$inserted, 'skipped'=>$skipped, 'errors'=>$errors, 'dry_run'=>$dryRun]);
?>


