<?php
// Utility: Clean docs/PO Details.csv into a tab-delimited file matching the bulk-upload template
// Output: docs/PO_Details_Cleaned.tsv and docs/PO_Details_Cleaned.csv

header('Content-Type: text/plain; charset=utf-8');

$inputPath  = __DIR__ . '/../../../docs/PO Details.csv';
$outputPathTsv = __DIR__ . '/../../../docs/PO_Details_Cleaned.tsv';
$outputPathCsv = __DIR__ . '/../../../docs/PO_Details_Cleaned.csv';

if (!file_exists($inputPath)) {
  echo "Source CSV not found: $inputPath\n";
  exit(1);
}

// Target headers (exact, tab-delimited)
$targetHeaders = [
  'project_description','cost_center','sow_number','start_date','end_date',
  'po_number','po_date','po_value','billing_frequency','target_gm',
  'vendor_name','remarks','pending_amount','po_status'
];

// Load entire file (handles embedded commas by fgetcsv)
$fh = fopen($inputPath, 'r');
if (!$fh) { echo "Unable to open input file\n"; exit(1); }

// Read header row and map indices
$rawHeaders = fgetcsv($fh);
if (!$rawHeaders) { echo "Unable to read header row\n"; exit(1); }

// Normalize header names for matching
function norm($s) {
  $s = trim($s);
  $s = preg_replace('/\s+/', ' ', $s);
  $s = strtolower($s);
  $s = str_replace(['  ', '  '], ' ', $s);
  $s = str_replace([' in po', ' in  po'], '', $s);
  return $s;
}

$map = [];
foreach ($rawHeaders as $idx => $h) {
  $map[norm($h)] = $idx;
}

// Possible input header variants -> target
$headerAliases = [
  'project description' => 'project_description',
  'cost center' => 'cost_center',
  'sow number' => 'sow_number',
  'start date' => 'start_date',
  'end date' => 'end_date',
  'po number' => 'po_number',
  'po date' => 'po_date',
  'po value' => 'po_value',
  ' billing frequency' => 'billing_frequency',
  'billing frequency' => 'billing_frequency',
  'target gm' => 'target_gm',
  ' pending amount' => 'pending_amount',
  ' pending amount in po' => 'pending_amount',
  'po status' => 'po_status',
  ' remarks' => 'remarks',
  'remarks' => 'remarks',
  ' vendor name' => 'vendor_name',
  'vendor name' => 'vendor_name',
];

// Helpers
function toExcelSerial($dateStr) {
  if ($dateStr === null) return '';
  $dateStr = trim($dateStr);
  if ($dateStr === '') return '';
  // If already numeric, assume excel serial
  if (is_numeric($dateStr)) return (string)intval($dateStr);
  // Normalize separators
  $dateStr = str_replace(['\\', '.'], ['/', '/'], $dateStr);
  // Some inputs like 16/Jan/2025, 3/31/2025, 1-04-24 etc.
  $ts = strtotime($dateStr);
  if ($ts === false) return '';
  // Convert to Excel serial (UTC days since 1899-12-30)
  $serial = floor($ts / 86400) + 25569;
  return (string)$serial;
}

function cleanMoney($v) {
  if ($v === null) return '';
  $v = trim($v);
  if ($v === '') return '';
  // Remove spaces and commas
  $v = str_replace([',', ' '], '', $v);
  // Remove quotes
  $v = str_replace(['"', '\''], '', $v);
  if ($v === '' || $v === '-') return '';
  if (!is_numeric($v)) return '';
  return (string)(0 + $v);
}

function cleanPercentToDecimal($v) {
  if ($v === null) return '';
  $v = trim($v);
  if ($v === '') return '';
  $v = str_replace(' ', '', $v);
  if (substr($v, -1) === '%') {
    $num = rtrim($v, '%');
    if (is_numeric($num)) {
      return (string)round(((float)$num)/100, 4);
    }
    return '';
  }
  if (is_numeric($v)) return $v; // already decimal like 0.05
  return '';
}

function cleanText($v) {
  if ($v === null) return '';
  $v = trim(preg_replace("/\s+/", ' ', $v));
  return $v;
}

function cleanStatus($v) {
  $v = cleanText($v);
  if ($v === '') return '';
  $low = strtolower($v);
  $allowed = ['active','closed','open','inactive'];
  if (in_array($low, $allowed)) return ucfirst($low);
  return '';
}

// Open output files
$outTsv = fopen($outputPathTsv, 'w');
if (!$outTsv) { echo "Unable to open TSV output file for writing\n"; exit(1); }
$outCsv = fopen($outputPathCsv, 'w');
if (!$outCsv) { echo "Unable to open CSV output file for writing\n"; exit(1); }

// Write headers
fwrite($outTsv, implode("\t", $targetHeaders) . "\n");
fputcsv($outCsv, $targetHeaders);

$rowNum = 1; // including header
while (($row = fgetcsv($fh)) !== false) {
  $rowNum++;
  // Skip empty
  if (count(array_filter($row, fn($v) => trim((string)$v) !== '')) === 0) continue;

  // Extract by aliases
  $get = function($alias) use ($map, $row) {
    return isset($map[$alias]) ? ($row[$map[$alias]] ?? '') : '';
  };

  $data = [];
  // Map source to target with cleaning; keep empty if unknown
  $data['project_description'] = cleanText($get('project description'));
  $data['cost_center'] = cleanText($get('cost center'));
  $data['sow_number'] = cleanText($get('sow number'));
  $data['start_date'] = toExcelSerial($get('start date'));
  $data['end_date'] = toExcelSerial($get('end date'));
  $data['po_number'] = cleanText($get('po number'));
  if ($data['po_number'] === '') {
    // Generate a temporary PO number if missing
    $data['po_number'] = 'TEMP-PO-' . date('Ymd') . '-' . $rowNum;
    // Append note in remarks
    $existingRemarks = cleanText($get('remarks'));
    $note = 'Auto-generated PO number due to missing value in source';
    $data['remarks'] = $existingRemarks ? ($existingRemarks . ' | ' . $note) : $note;
  }
  $data['po_date'] = toExcelSerial($get('po date'));
  $data['po_value'] = cleanMoney($get('po value'));
  $data['billing_frequency'] = cleanText($get('billing frequency'));
  $data['target_gm'] = cleanPercentToDecimal($get('target gm'));
  $data['vendor_name'] = cleanText($get('vendor name'));
  $data['remarks'] = cleanText($get('remarks'));
  $data['pending_amount'] = cleanMoney($get(' pending amount in po'));
  if ($data['pending_amount'] === '') {
    // Try variant
    $data['pending_amount'] = cleanMoney($get(' pending amount'));
  }
  $data['po_status'] = cleanStatus($get(' po status'));

  // Emit row in target order
  $outRow = [];
  foreach ($targetHeaders as $h) {
    $outRow[] = $data[$h] ?? '';
  }
  fwrite($outTsv, implode("\t", $outRow) . "\n");
  fputcsv($outCsv, $outRow);
}

fclose($fh);
fclose($outTsv);
fclose($outCsv);

echo "Cleaned files written to:\n - $outputPathTsv (TSV)\n - $outputPathCsv (CSV)\n";
?>


