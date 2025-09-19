<?php
// Outsourcing bulk upload
header('Content-Type: application/json');
session_start();
include '../../../config/db.php';
include '../../../config/auth.php';
if (!hasPermission('add_outsourcing')) {
  echo json_encode(['success'=>false,'errors'=>[['row'=>0,'message'=>'Insufficient permissions']]]);
  exit;
}

function parseDateToSerial($v){
  if ($v===null) return null; $t=trim((string)$v); if($t==='') return null;
  $ph=['-',' - ','--','—','–','(0)','0','N/A','n/a','NA','na','N.A.','n.a.','Nil','nil','NIL']; 
  if(in_array($t,$ph,true)) return null;
  if (is_numeric($t)) return (int)$t;
  
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
  $t=str_replace(['\\','.'],['/','/'],$t); $ts=strtotime($t); if($ts===false) return null; return (int)floor($ts/86400)+25569;
}

function cleanAmount($value) {
  if ($value === null) return '';
  $str = trim((string)$value);
  if ($str === '' || $str === '-' || strtoupper($str) === 'N/A') return '';
  // Remove currency symbols, commas, spaces, parentheses
  $str = preg_replace('/[₹$,\s]/u', '', $str);
  // Handle parentheses for negatives
  $isNegative = false;
  if (preg_match('/^\((.*)\)$/', $str, $m)) { $isNegative = true; $str = $m[1]; }
  // Keep only digits and dot
  $str = preg_replace('/[^0-9.\-]/', '', $str);
  if ($str === '' || !is_numeric($str)) return '';
  $num = (float)$str;
  if ($isNegative) $num = -$num;
  return (string)$num;
}

if (!isset($_FILES['csvFile']) || $_FILES['csvFile']['error']!==UPLOAD_ERR_OK){
  echo json_encode(['success'=>false,'errors'=>[['row'=>0,'message'=>'No file uploaded']]]); exit;
}
$lines=file($_FILES['csvFile']['tmp_name'], FILE_IGNORE_NEW_LINES); if($lines===false||!count($lines)){ echo json_encode(['success'=>false,'errors'=>[['row'=>0,'message'=>'Empty file']]]); exit; }
$first=str_replace("\xEF\xBB\xBF",'',$lines[0]); $delimiter= substr_count($first, "\t")>substr_count($first, ',')?"\t":',';
$headersRaw=str_getcsv($first,$delimiter); $headers=array_map('trim',$headersRaw);

// Canonicalize headers with aliases
function canon($s){ $s=strtolower(trim((string)$s)); $s=preg_replace('/[^a-z0-9]+/','',$s); return $s; }
$alias=[
  'projectdetails'=>'project_details','project'=>'project_details','projectname'=>'project_details','proj'=>'project_details',
  'costcentre'=>'cost_center','costcenter'=>'cost_center','costcentrecode'=>'cost_center','costcentercode'=>'cost_center',
  'customerpo'=>'customer_po','customerpono'=>'customer_po','po'=>'customer_po','customerponumber'=>'customer_po',
  'vendorname'=>'vendor_name','vendor'=>'vendor_name','supplier'=>'vendor_name',
  'cantikpono'=>'cantik_po_no','pono'=>'cantik_po_no','po_no'=>'cantik_po_no','ponumber'=>'cantik_po_no','po#'=>'cantik_po_no',
  'cantikpodate'=>'cantik_po_date','podate'=>'cantik_po_date','po_date'=>'cantik_po_date','poissuedate'=>'cantik_po_date',
  'cantikpovalue'=>'cantik_po_value','povalue'=>'cantik_po_value','po_value'=>'cantik_po_value','amount'=>'cantik_po_value','value'=>'cantik_po_value','totalvalue'=>'cantik_po_value',
  'vendorinvoicefrequency'=>'vendor_invoice_frequency','invoicefrequency'=>'vendor_invoice_frequency','frequency'=>'vendor_invoice_frequency',
  'vendorinvnumber'=>'vendor_inv_number','vendorinvoiceno'=>'vendor_inv_number','vendorinvoicenumber'=>'vendor_inv_number','vendorinvoice#'=>'vendor_inv_number','vendorinvno'=>'vendor_inv_number',
  'vendorinvdate'=>'vendor_inv_date','vendorinvoicedate'=>'vendor_inv_date','vendorinv_dt'=>'vendor_inv_date',
  'vendorinvvalue'=>'vendor_inv_value','vendorinvoicevalue'=>'vendor_inv_value','invoicevalue'=>'vendor_inv_value','invvalue'=>'vendor_inv_value','invoice_amount'=>'vendor_inv_value',
  'paymentstatusfromntt'=>'payment_status_from_ntt','paymentstatus'=>'payment_status_from_ntt','status'=>'payment_status_from_ntt',
  'paymentvalue'=>'payment_value','payment_amount'=>'payment_value','paidamount'=>'payment_value',
  'paymentdate'=>'payment_date','payment_dt'=>'payment_date','paiddate'=>'payment_date',
  'remarks'=>'remarks','note'=>'remarks','notes'=>'remarks'
];
$headers=[]; foreach($headersRaw as $h){ $k=canon($h); $headers[]=$alias[$k] ?? $h; }

$required=['project_details','cost_center','customer_po','vendor_name','cantik_po_no'];
$optional=['cantik_po_date','cantik_po_value','vendor_invoice_frequency','vendor_inv_number','vendor_inv_date','vendor_inv_value','payment_status_from_ntt','payment_value','payment_date','remarks'];
foreach($required as $h){ if(!in_array($h,$headers)){ echo json_encode(['success'=>false,'errors'=>[['row'=>0,'message'=>'Missing header: '.$h]]]); exit; } }

$inserted=0;$errors=[];$rowNumber=1; $conn->begin_transaction();
$dryRun = isset($_POST['dry_run']) && $_POST['dry_run']=='1';
$force = isset($_POST['force']) && $_POST['force']=='1';
if (!$dryRun && $force) { $conn->query('SET FOREIGN_KEY_CHECKS=0'); }
for($i=1;$i<count($lines);$i++){
  $rowNumber++; $line=trim($lines[$i]??''); if($line==='') continue; 
  $cols=str_getcsv($line,$delimiter); 
  // Ensure arrays have same length before combining
  if(count($cols)<count($headers)) $cols=array_pad($cols,count($headers),'');
  elseif(count($cols)>count($headers)) $cols=array_slice($cols,0,count($headers));
  $d=array_combine($headers,$cols);
  // Clean and validate values (optional fields allowed)     
  $cleanPoValueRaw = cleanAmount($d['cantik_po_value'] ?? '');
  $cleanInvValueRaw = cleanAmount($d['vendor_inv_value'] ?? '');
  $cleanPayValueRaw = cleanAmount($d['payment_value'] ?? '');

  $cleanPoValue = ($cleanPoValueRaw === '' ? null : (is_numeric($cleanPoValueRaw) ? (float)$cleanPoValueRaw : null));
  $cleanInvValue = ($cleanInvValueRaw === '' ? null : (is_numeric($cleanInvValueRaw) ? (float)$cleanInvValueRaw : null));
  $cleanPayValue = ($cleanPayValueRaw === '' ? null : (is_numeric($cleanPayValueRaw) ? (float)$cleanPayValueRaw : null));

  if (!$force) {
    if ($cleanPoValueRaw !== '' && $cleanPoValue === null) { $errors[]=['row'=>$rowNumber,'message'=>'cantik_po_value must be numeric']; continue; }
    if ($cleanInvValueRaw !== '' && $cleanInvValue === null) { $errors[]=['row'=>$rowNumber,'message'=>'vendor_inv_value must be numeric']; continue; }
    if ($cleanPayValueRaw !== '' && $cleanPayValue === null) { $errors[]=['row'=>$rowNumber,'message'=>'payment_value must be numeric']; continue; }
  } else {
    if ($cleanPoValue === null) { $cleanPoValue = 0.0; }
    if ($cleanInvValue === null) { $cleanInvValue = 0.0; }
    if ($cleanPayValue === null) { $cleanPayValue = 0.0; }
  }

  $poDate = isset($d['cantik_po_date']) ? parseDateToSerial($d['cantik_po_date']) : null;
  $invDate = isset($d['vendor_inv_date']) ? parseDateToSerial($d['vendor_inv_date']) : null;
  $payDate = isset($d['payment_date']) ? parseDateToSerial($d['payment_date']) : null;

  // If provided but unparseable, raise specific errors
  if ((isset($d['cantik_po_date']) && trim((string)$d['cantik_po_date']) !== '') && $poDate===null) { if(!$force){ $errors[]=['row'=>$rowNumber,'message'=>'Invalid cantik_po_date']; continue; } }
  if ((isset($d['vendor_inv_date']) && trim((string)$d['vendor_inv_date']) !== '') && $invDate===null) { if(!$force){ $errors[]=['row'=>$rowNumber,'message'=>'Invalid vendor_inv_date']; continue; } }

  // Enforce schema-required base fields present
  $projectDetailsIn = trim((string)($d['project_details'] ?? ''));
  $costCenterIn = trim((string)($d['cost_center'] ?? ''));
  $customerPoIn = trim((string)($d['customer_po'] ?? ''));
  $vendorNameIn = trim((string)($d['vendor_name'] ?? ''));
  $cantikPoNoIn = trim((string)($d['cantik_po_no'] ?? ''));
  if ($projectDetailsIn === '' || $costCenterIn === '' || $customerPoIn === '' || $vendorNameIn === '' || $cantikPoNoIn === '') {
    if (!$force) {
      $errors[]=['row'=>$rowNumber,'message'=>'Missing required fields (project_details, cost_center, customer_po, vendor_name, cantik_po_no)'];
      continue;
    } else {
      if ($projectDetailsIn === '') $projectDetailsIn = 'UNKNOWN';
      if ($costCenterIn === '') $costCenterIn = 'UNKNOWN';
      if ($customerPoIn === '') $customerPoIn = 'TEMP-PO-'.date('Ymd')."-$rowNumber";
      if ($vendorNameIn === '') $vendorNameIn = 'UNKNOWN';
      if ($cantikPoNoIn === '') $cantikPoNoIn = 'UNKNOWN';
    }
  }

  // FK check: customer_po must exist in po_details (skip if forcing)
  if (!$force) {
    $fk = $conn->prepare("SELECT 1 FROM po_details WHERE po_number = ? LIMIT 1");
    $fk->bind_param('s', $customerPoIn);
    $fk->execute(); $fkres = $fk->get_result();
    if ($fkres->num_rows === 0) { $errors[]=['row'=>$rowNumber,'message'=>'customer_po not found in PO master (po_details)']; $fk->close(); continue; }
    $fk->close();
  }

  if ($dryRun) { $inserted++; continue; }
  $stmt=$conn->prepare("INSERT INTO outsourcing_detail (
    project_details,cost_center,customer_po,vendor_name,cantik_po_no,cantik_po_date,cantik_po_value,remaining_bal_in_po,
    vendor_invoice_frequency,vendor_inv_number,vendor_inv_date,vendor_inv_value,payment_status_from_ntt,payment_value,payment_date,remarks
  ) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)");
  $zero=0.00; 
  // Prepare variables for bind_param (must be variables, not expressions)
  $projectDetails = $projectDetailsIn;
  $costCenter = $costCenterIn;
  $customerPo = $customerPoIn;
  $vendorName = $vendorNameIn;
  $cantikPoNo = $cantikPoNoIn;
  $cantikPoDate = $poDate; // int serial or null
  $cantikPoValue = ($cleanPoValue === null ? 0.0 : $cleanPoValue); // NOT NULL in schema
  $remainingBal = $cantikPoValue; // initialize remaining with PO value
  $vendorInvoiceFreq = trim((string)($d['vendor_invoice_frequency'] ?? ''));
  $vendorInvNumber = trim((string)($d['vendor_inv_number'] ?? ''));
  if ($force) {
    if ($vendorInvoiceFreq === '') $vendorInvoiceFreq = 'NA';
    if ($vendorInvNumber === '') $vendorInvNumber = 'NA';
  }
  $vendorInvDate = $invDate; // int serial or null
  $vendorInvValue = ($cleanInvValue === null ? 0.0 : $cleanInvValue); // NOT NULL in schema
  $paymentStatus = isset($d['payment_status_from_ntt']) && trim($d['payment_status_from_ntt']) !== '' ? $d['payment_status_from_ntt'] : null;
  $paymentValue = ($cleanPayValue === null ? 0.0 : $cleanPayValue);
  $paymentDate = $payDate; // int serial or null
  $remarks = isset($d['remarks']) && trim($d['remarks']) !== '' ? $d['remarks'] : null;
  
  $stmt->bind_param('sssssiddssidsdis',
    $projectDetails, $costCenter, $customerPo, $vendorName, $cantikPoNo, $cantikPoDate, $cantikPoValue, $remainingBal,
    $vendorInvoiceFreq, $vendorInvNumber, $vendorInvDate, $vendorInvValue, $paymentStatus,
    $paymentValue, $paymentDate, $remarks
  );
  if($stmt->execute()){ $inserted++; } else { $errors[]=['row'=>$rowNumber,'message'=>'DB: '.$stmt->error]; }
}
if ($dryRun) { 
  $conn->rollback(); 
} else { 
  if($inserted>0){ $conn->commit(); } else { $conn->rollback(); }
}
if (!$dryRun && $force) { $conn->query('SET FOREIGN_KEY_CHECKS=1'); }
echo json_encode(['success'=>$dryRun ? true : ($inserted>0),'inserted'=>$inserted,'skipped'=>0,'errors'=>$errors,'dry_run'=>$dryRun]);
?>


