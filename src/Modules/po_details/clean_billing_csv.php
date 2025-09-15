<?php
// Utility: Clean docs/Billing and Payment Details.csv to invoices template (CSV + TSV)
header('Content-Type: text/plain; charset=utf-8');

$inputPath  = __DIR__ . '/../../../docs/Billing and Payment Details.csv';
$outTsvPath = __DIR__ . '/../../../docs/Billing_and_Payment_Details_Cleaned.tsv';
$outCsvPath = __DIR__ . '/../../../docs/Billing_and_Payment_Details_Cleaned.csv';

if (!file_exists($inputPath)) { echo "Source CSV not found: $inputPath\n"; exit(1); }

$targetHeaders = [
  'project_details','cost_center','customer_po','cantik_invoice_no','cantik_invoice_date','cantik_inv_value_taxable',
  'against_vendor_inv_number','payment_receipt_date','payment_advise_no','vendor_name'
];

function toSerial($v){ if($v===null) return ''; $v=trim($v); if($v==='') return ''; if(is_numeric($v)) return (string)intval($v);
  $v=str_replace(['\\','.'],['/','/'],$v); $ts=strtotime($v); if($ts===false) return ''; return (string)(floor($ts/86400)+25569); }
function money($v){ if($v===null) return ''; $v=trim($v); if($v==='') return ''; $v=str_replace([',',' '],'',$v); $v=str_replace(['"','\''], '', $v); if($v==='-'||$v==='') return ''; return is_numeric($v)? (string)(0+$v):''; }
function text($v){ if($v===null) return ''; return trim(preg_replace('/\s+/',' ',$v)); }

$fh=fopen($inputPath,'r'); if(!$fh){echo"Unable to open input\n";exit(1);} $rawHeaders=fgetcsv($fh); if(!$rawHeaders){echo"No header\n";exit(1);} 
$map=[]; foreach($rawHeaders as $i=>$h){ $k=strtolower(trim(preg_replace('/\s+/',' ',$h))); $map[$k]=$i; }

$aliases=[
  'project details'=>'project_details','cost center'=>'cost_center','customer po number'=>'customer_po','customer po'=>'customer_po',
  'cantik invoice no'=>'cantik_invoice_no','cantik invoice date'=>'cantik_invoice_date',' cantik inv value taxable '=>'cantik_inv_value_taxable','cantik inv value taxable'=>'cantik_inv_value_taxable',
  'against vendor inv number'=>'against_vendor_inv_number','payment receipt date'=>'payment_receipt_date','payment advise no'=>'payment_advise_no','vendor name'=>'vendor_name'
];

$outT=fopen($outTsvPath,'w'); $outC=fopen($outCsvPath,'w'); if(!$outT||!$outC){echo"Unable to open output\n";exit(1);} 
fwrite($outT,implode("\t",$targetHeaders)."\n"); fputcsv($outC,$targetHeaders);

while(($row=fgetcsv($fh))!==false){ if(count(array_filter($row, fn($v)=>trim((string)$v) !== ''))===0) continue; 
  $get=function($label) use($map,$aliases,$row){ $k=strtolower($label); if(isset($map[$k])) return $row[$map[$k]]??''; if(isset($aliases[$k])&&isset($map[$aliases[$k]])) return $row[$map[$aliases[$k]]]??''; return ''; };
  $data=[];
  $data['project_details']=text($get('project details'));
  $data['cost_center']=text($get('cost center'));
  $data['customer_po']=text($get('customer po number')) ?: text($get('customer po'));
  $data['cantik_invoice_no']=text($get('cantik invoice no'));
  $data['cantik_invoice_date']=toSerial($get('cantik invoice date'));
  $data['cantik_inv_value_taxable']=money($get(' cantik inv value taxable ')) ?: money($get('cantik inv value taxable'));
  $data['against_vendor_inv_number']=text($get('against vendor inv number'));
  $data['payment_receipt_date']=toSerial($get('payment receipt date'));
  $data['payment_advise_no']=text($get('payment advise no'));
  $data['vendor_name']=text($get('vendor name'));

  $rowOut=[]; foreach($targetHeaders as $h){ $rowOut[]=$data[$h]??''; }
  fwrite($outT,implode("\t",$rowOut)."\n"); fputcsv($outC,$rowOut);
}

fclose($fh); fclose($outT); fclose($outC);
echo "Cleaned files written to:\n - $outTsvPath (TSV)\n - $outCsvPath (CSV)\n";
?>


