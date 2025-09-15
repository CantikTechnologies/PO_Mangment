<?php
// Utility: Clean docs/Outsourcing Details.csv to template headers (CSV + TSV)
header('Content-Type: text/plain; charset=utf-8');

$inputPath  = __DIR__ . '/../../../docs/Outsourcing Details.csv';
$outTsvPath = __DIR__ . '/../../../docs/Outsourcing_Details_Cleaned.tsv';
$outCsvPath = __DIR__ . '/../../../docs/Outsourcing_Details_Cleaned.csv';

if (!file_exists($inputPath)) { echo "Source CSV not found: $inputPath\n"; exit(1); }

$targetHeaders = [
  'project_details','cost_center','customer_po','vendor_name','cantik_po_no','cantik_po_date','cantik_po_value',
  'vendor_invoice_frequency','vendor_inv_number','vendor_inv_date','vendor_inv_value','payment_status_from_ntt',
  'payment_value','payment_date','remarks'
];

function toSerial($v){ if($v===null) return ''; $v=trim($v); if($v==='') return ''; if(is_numeric($v)) return (string)intval($v);
  $v=str_replace(['\\','.'],['/','/'],$v); $ts=strtotime($v); if($ts===false) return ''; return (string)(floor($ts/86400)+25569); }
function money($v){ if($v===null) return ''; $v=trim($v); if($v==='') return ''; $v=str_replace([',',' '],'',$v); $v=str_replace(['"','\''], '', $v); if($v==='-'||$v==='') return ''; return is_numeric($v)? (string)(0+$v):''; }
function text($v){ if($v===null) return ''; return trim(preg_replace('/\s+/',' ',$v)); }

$fh=fopen($inputPath,'r'); if(!$fh){echo"Unable to open input\n";exit(1);} $rawHeaders=fgetcsv($fh); if(!$rawHeaders){echo"No header\n";exit(1);} 
$map=[]; foreach($rawHeaders as $i=>$h){ $k=strtolower(trim(preg_replace('/\s+/',' ',$h))); $map[$k]=$i; }

// Common header aliases from user's sheet
$aliases = [
  'project details'=>'project_details','cost center'=>'cost_center','customer po number'=>'customer_po','customer po'=>'customer_po',
  'vendor name'=>'vendor_name','cantik po no'=>'cantik_po_no','cantik po date'=>'cantik_po_date',' cantik po value '=>'cantik_po_value','cantik po value'=>'cantik_po_value',
  ' remaining bal in po '=>'remaining_bal_in_po', 'remaining bal in po'=>'remaining_bal_in_po',
  'vendor invoice frequency'=>'vendor_invoice_frequency','vendor inv number'=>'vendor_inv_number','vendor inv date'=>'vendor_inv_date',' vendor inv value '=>'vendor_inv_value','vendor inv value'=>'vendor_inv_value',
  'tds ded'=>'tds_ded',' net payble '=>'net_payble','net payble'=>'net_payble','payment status from ntt'=>'payment_status_from_ntt',' payment value '=>'payment_value','payment value'=>'payment_value',' payment date '=>'payment_date','payment date'=>'payment_date',' pending payment '=>'pending_payment','pending payment'=>'pending_payment','remarks'=>'remarks'
];

$outT=fopen($outTsvPath,'w'); $outC=fopen($outCsvPath,'w'); if(!$outT||!$outC){echo"Unable to open output\n";exit(1);} 
fwrite($outT,implode("\t",$targetHeaders)."\n"); fputcsv($outC,$targetHeaders);

while(($row=fgetcsv($fh))!==false){ if(count(array_filter($row, fn($v)=>trim((string)$v) !== ''))===0) continue; 
  $get=function($label) use($map,$aliases,$row){ $k=strtolower($label); if(isset($map[$k])) return $row[$map[$k]]??''; if(isset($aliases[$k])&&isset($map[$aliases[$k]])) return $row[$map[$aliases[$k]]]??''; return ''; };
  $data=[];
  $data['project_details']=text($get('project details'));
  $data['cost_center']=text($get('cost center'));
  $data['customer_po']=text($get('customer po number')) ?: text($get('customer po'));
  $data['vendor_name']=text($get('vendor name'));
  $data['cantik_po_no']=text($get('cantik po no'));
  $data['cantik_po_date']=toSerial($get('cantik po date'));
  $data['cantik_po_value']=money($get(' cantik po value ')) ?: money($get('cantik po value'));
  $data['vendor_invoice_frequency']=text($get('vendor invoice frequency'));
  $data['vendor_inv_number']=text($get('vendor inv number'));
  $data['vendor_inv_date']=toSerial($get('vendor inv date'));
  $data['vendor_inv_value']=money($get(' vendor inv value ')) ?: money($get('vendor inv value'));
  $data['payment_status_from_ntt']=text($get('payment status from ntt'));
  $data['payment_value']=money($get(' payment value ')) ?: money($get('payment value'));
  $data['payment_date']=toSerial($get(' payment date ')) ?: toSerial($get('payment date'));
  $data['remarks']=text($get('remarks'));
  // Derived columns (tds_ded, net_payble, pending_payment) are intentionally omitted

  $rowOut=[]; foreach($targetHeaders as $h){ $rowOut[]=$data[$h]??''; }
  fwrite($outT,implode("\t",$rowOut)."\n"); fputcsv($outC,$rowOut);
}

fclose($fh); fclose($outT); fclose($outC);
echo "Cleaned files written to:\n - $outTsvPath (TSV)\n - $outCsvPath (CSV)\n";
?>


