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
  $ph=['-',' - ','(0)','0','N/A','n/a']; if(in_array($t,$ph,true)) return null;
  if (is_numeric($t)) return (int)$t;
  $t=str_replace(['\\','.'],['/','/'],$t); $ts=strtotime($t); if($ts===false) return null; return (int)floor($ts/86400)+25569;
}

if (!isset($_FILES['csvFile']) || $_FILES['csvFile']['error']!==UPLOAD_ERR_OK){
  echo json_encode(['success'=>false,'errors'=>[['row'=>0,'message'=>'No file uploaded']]]); exit;
}
$lines=file($_FILES['csvFile']['tmp_name'], FILE_IGNORE_NEW_LINES); if($lines===false||!count($lines)){ echo json_encode(['success'=>false,'errors'=>[['row'=>0,'message'=>'Empty file']]]); exit; }
$first=str_replace("\xEF\xBB\xBF",'',$lines[0]); $delimiter= substr_count($first, "\t")>substr_count($first, ',')?"\t":',';
$headers=str_getcsv($first,$delimiter); $headers=array_map('trim',$headers);

$required=['project_details','cost_center','customer_po','vendor_name','cantik_po_no','cantik_po_date','cantik_po_value','vendor_invoice_frequency','vendor_inv_number','vendor_inv_date','vendor_inv_value'];
$optional=['payment_status_from_ntt','payment_value','payment_date','remarks'];
foreach($required as $h){ if(!in_array($h,$headers)){ echo json_encode(['success'=>false,'errors'=>[['row'=>0,'message'=>'Missing header: '.$h]]]); exit; } }

$inserted=0;$errors=[];$rowNumber=1; $conn->begin_transaction();
$dryRun = isset($_POST['dry_run']) && $_POST['dry_run']=='1';
for($i=1;$i<count($lines);$i++){
  $rowNumber++; $line=trim($lines[$i]??''); if($line==='') continue; $cols=str_getcsv($line,$delimiter); if(count($cols)<count($headers)) $cols=array_pad($cols,count($headers),'');
  $d=array_combine($headers,$cols);
  // validate numeric
  if(!is_numeric($d['cantik_po_value'])){ $errors[]=['row'=>$rowNumber,'message'=>'cantik_po_value must be numeric']; continue; }
  if(!is_numeric($d['vendor_inv_value'])){ $errors[]=['row'=>$rowNumber,'message'=>'vendor_inv_value must be numeric']; continue; }
  $poDate=parseDateToSerial($d['cantik_po_date']); if($poDate===null){ $errors[]=['row'=>$rowNumber,'message'=>'Invalid cantik_po_date']; continue; }
  $invDate=parseDateToSerial($d['vendor_inv_date']); if($invDate===null){ $errors[]=['row'=>$rowNumber,'message'=>'Invalid vendor_inv_date']; continue; }
  $payDate=isset($d['payment_date'])?parseDateToSerial($d['payment_date']):null;

  if ($dryRun) { $inserted++; continue; }
  $stmt=$conn->prepare("INSERT INTO outsourcing_detail (
    project_details,cost_center,customer_po,vendor_name,cantik_po_no,cantik_po_date,cantik_po_value,remaining_bal_in_po,
    vendor_invoice_frequency,vendor_inv_number,vendor_inv_date,vendor_inv_value,payment_status_from_ntt,payment_value,payment_date,remarks
  ) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)");
  $zero=0.00; $stmt->bind_param('sssssidsssssdids',
    $d['project_details'],$d['cost_center'],$d['customer_po'],$d['vendor_name'],$d['cantik_po_no'],$poDate,$d['cantik_po_value'],$zero,
    $d['vendor_invoice_frequency'],$d['vendor_inv_number'],$invDate,$d['vendor_inv_value'],$d['payment_status_from_ntt']?:null,
    $d['payment_value']?:null,$payDate,$d['remarks']?:null
  );
  if($stmt->execute()){ $inserted++; } else { $errors[]=['row'=>$rowNumber,'message'=>'DB: '.$stmt->error]; }
}
if ($dryRun) { $conn->rollback(); } else { if($inserted>0){ $conn->commit(); } else { $conn->rollback(); } }
echo json_encode(['success'=>$dryRun ? true : ($inserted>0),'inserted'=>$inserted,'skipped'=>0,'errors'=>$errors,'dry_run'=>$dryRun]);
?>


