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
  'projectdetails'=>'project_details','project'=>'project_details','projectname'=>'project_details',
  'costcentre'=>'cost_center','costcenter'=>'cost_center',
  'customerpo'=>'customer_po','customerpono'=>'customer_po',
  'vendorname'=>'vendor_name','vendor'=>'vendor_name',
  'cantikpono'=>'cantik_po_no','pono'=>'cantik_po_no',
  'cantikpodate'=>'cantik_po_date','podate'=>'cantik_po_date',
  'cantikpovalue'=>'cantik_po_value','povalue'=>'cantik_po_value',
  'vendorinvoicefrequency'=>'vendor_invoice_frequency','invoicefrequency'=>'vendor_invoice_frequency',
  'vendorinvnumber'=>'vendor_inv_number','vendorinvoiceno'=>'vendor_inv_number','vendorinvoicenumber'=>'vendor_inv_number',
  'vendorinvdate'=>'vendor_inv_date','vendorinvoicedate'=>'vendor_inv_date',
  'vendorinvvalue'=>'vendor_inv_value','vendorinvoicevalue'=>'vendor_inv_value','invoicevalue'=>'vendor_inv_value',
  'paymentstatusfromntt'=>'payment_status_from_ntt','paymentstatus'=>'payment_status_from_ntt',
  'paymentvalue'=>'payment_value',
  'paymentdate'=>'payment_date',
  'remarks'=>'remarks'
];
$headers=[]; foreach($headersRaw as $h){ $k=canon($h); $headers[]=$alias[$k] ?? $h; }

$required=['project_details','cost_center','customer_po','vendor_name','cantik_po_no','cantik_po_date','cantik_po_value','vendor_invoice_frequency','vendor_inv_number','vendor_inv_date','vendor_inv_value'];
$optional=['payment_status_from_ntt','payment_value','payment_date','remarks'];
foreach($required as $h){ if(!in_array($h,$headers)){ echo json_encode(['success'=>false,'errors'=>[['row'=>0,'message'=>'Missing header: '.$h]]]); exit; } }

$inserted=0;$errors=[];$rowNumber=1; $conn->begin_transaction();
$dryRun = isset($_POST['dry_run']) && $_POST['dry_run']=='1';
for($i=1;$i<count($lines);$i++){
  $rowNumber++; $line=trim($lines[$i]??''); if($line==='') continue; 
  $cols=str_getcsv($line,$delimiter); 
  // Ensure arrays have same length before combining
  if(count($cols)<count($headers)) $cols=array_pad($cols,count($headers),'');
  elseif(count($cols)>count($headers)) $cols=array_slice($cols,0,count($headers));
  $d=array_combine($headers,$cols);
  // Clean and validate numeric values
  $cleanPoValue = cleanAmount($d['cantik_po_value'] ?? '');
  $cleanInvValue = cleanAmount($d['vendor_inv_value'] ?? '');
  if($cleanPoValue === '' || !is_numeric($cleanPoValue)){ $errors[]=['row'=>$rowNumber,'message'=>'cantik_po_value must be numeric']; continue; }
  if($cleanInvValue === '' || !is_numeric($cleanInvValue)){ $errors[]=['row'=>$rowNumber,'message'=>'vendor_inv_value must be numeric']; continue; }
  $poDate=parseDateToSerial($d['cantik_po_date']); if($poDate===null){ $errors[]=['row'=>$rowNumber,'message'=>'Invalid cantik_po_date']; continue; }
  $invDate=parseDateToSerial($d['vendor_inv_date']); if($invDate===null){ $errors[]=['row'=>$rowNumber,'message'=>'Invalid vendor_inv_date']; continue; }
  $payDate=isset($d['payment_date'])?parseDateToSerial($d['payment_date']):null;

  if ($dryRun) { $inserted++; continue; }
  $stmt=$conn->prepare("INSERT INTO outsourcing_detail (
    project_details,cost_center,customer_po,vendor_name,cantik_po_no,cantik_po_date,cantik_po_value,remaining_bal_in_po,
    vendor_invoice_frequency,vendor_inv_number,vendor_inv_date,vendor_inv_value,payment_status_from_ntt,payment_value,payment_date,remarks
  ) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)");
  $zero=0.00; 
  // Prepare variables for bind_param (must be variables, not expressions)
  $projectDetails = $d['project_details'];
  $costCenter = $d['cost_center'];
  $customerPo = $d['customer_po'];
  $vendorName = $d['vendor_name'];
  $cantikPoNo = $d['cantik_po_no'];
  $cantikPoDate = $poDate; // int serial
  $cantikPoValue = (float)$cleanPoValue;
  $remainingBal = $zero;
  $vendorInvoiceFreq = $d['vendor_invoice_frequency'];
  $vendorInvNumber = $d['vendor_inv_number'];
  $vendorInvDate = $invDate; // int serial
  $vendorInvValue = (float)$cleanInvValue;
  $paymentStatus = isset($d['payment_status_from_ntt']) && trim($d['payment_status_from_ntt']) !== '' ? $d['payment_status_from_ntt'] : null;
  $paymentValue = isset($d['payment_value']) && trim($d['payment_value']) !== '' ? $d['payment_value'] : null;
  $paymentDate = $payDate; // int serial or null
  $remarks = isset($d['remarks']) && trim($d['remarks']) !== '' ? $d['remarks'] : null;
  
  $stmt->bind_param('sssssidsssssdids',
    $projectDetails, $costCenter, $customerPo, $vendorName, $cantikPoNo, $cantikPoDate, $cantikPoValue, $remainingBal,
    $vendorInvoiceFreq, $vendorInvNumber, $vendorInvDate, $vendorInvValue, $paymentStatus,
    $paymentValue, $paymentDate, $remarks
  );
  if($stmt->execute()){ $inserted++; } else { $errors[]=['row'=>$rowNumber,'message'=>'DB: '.$stmt->error]; }
}
if ($dryRun) { $conn->rollback(); } else { if($inserted>0){ $conn->commit(); } else { $conn->rollback(); } }
echo json_encode(['success'=>$dryRun ? true : ($inserted>0),'inserted'=>$inserted,'skipped'=>0,'errors'=>$errors,'dry_run'=>$dryRun]);
?>


