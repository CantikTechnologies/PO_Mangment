<?php
// Enable error reporting but do not display to keep JSON output clean
error_reporting(E_ALL);
ini_set('display_errors', 0);

// Set content type to JSON first
header('Content-Type: application/json');

session_start();
if (!isset($_SESSION['username'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'errors' => [['row' => 0, 'message' => 'Unauthorized access']]]);
    exit();
}

try {
    include '../../../config/db.php';
    include '../../../config/auth.php';
    
    // Check permission without redirecting
    if (!hasPermission('add_po_details')) {
        echo json_encode(['success' => false, 'errors' => [['row' => 0, 'message' => 'Insufficient permissions']]]);
        exit();
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'errors' => [['row' => 0, 'message' => 'Configuration error: ' . $e->getMessage()]]]);
    exit();
}

// Check if file was uploaded
if (!isset($_FILES['csvFile']) || $_FILES['csvFile']['error'] !== UPLOAD_ERR_OK) {
    echo json_encode(['success' => false, 'errors' => [['row' => 0, 'message' => 'No file uploaded or upload error']]]);
    exit();
}

$file = $_FILES['csvFile'];

// Validate file type
$fileExtension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
if (!in_array($fileExtension, ['csv','tsv','txt'], true)) {
    echo json_encode(['success' => false, 'errors' => [['row' => 0, 'message' => 'Only CSV/TSV files are allowed']]]);
    exit();
}

// Define required and optional fields (canonical names)
$requiredFields = [
    // No hard-required fields; rows with missing PO number will get a temporary ID
];

$optionalFields = [
    'vendor_name',
    'remarks',
    'pending_amount',
    'po_status'
];

$allFields = array_merge($requiredFields, $optionalFields);

// Helper to canonicalize header keys: lowercase, remove spaces/underscores/non-alnum
function canon($s) {
    $s = strtolower(trim((string)$s));
    // common visual variants
    $s = str_replace(['–','—','‐','‑'], '-', $s);
    // remove non-alphanumeric
    $s = preg_replace('/[^a-z0-9]+/', '', $s);
    return $s;
}

// Build alias map (canonical key => canonical field name)
$aliasMap = [
    'projectname' => 'project_description', 'projectdescription' => 'project_description',
    'costcentre' => 'cost_center', 'costcenter' => 'cost_center',
    'sownumber' => 'sow_number', 'sow' => 'sow_number',
    'ponumber' => 'po_number', 'pono' => 'po_number', 'pon' => 'po_number', 'ponum' => 'po_number',
    'customerpono' => 'po_number', 'customerponumber' => 'po_number', 'custponumber' => 'po_number',
    'podate' => 'po_date', 'purchaseorderdate' => 'po_date', 'purchasedate' => 'po_date',
    'povalue' => 'po_value', 'purchaseordervalue' => 'po_value', 'orderamount' => 'po_value',
    'amount' => 'po_value', 'value' => 'po_value', 'totalvalue' => 'po_value',
    'billingfrequency' => 'billing_frequency', 'billingfreq' => 'billing_frequency',
    'targetgm' => 'target_gm', 'targetmargin' => 'target_gm',
    'vendorname' => 'vendor_name', 'vendor' => 'vendor_name',
    'remark' => 'remarks',
    'pendingamount' => 'pending_amount', 'pending' => 'pending_amount', 'pendingamountinpo' => 'pending_amount',
    'postatus' => 'po_status', 'status' => 'po_status',
];

function isEmptyLike($value) {
    if ($value === null) return true;
    $t = trim((string)$value);
    if ($t === '') return true;
    $placeholders = ['-', ' - ', '--', '—', '–', '(0)', '0', 'N/A', 'n/a', 'NA', 'na', 'N.A.', 'n.a.', 'Nil', 'nil', 'NIL'];
    return in_array($t, $placeholders, true);
}

// Clean numeric amount like "₹ 1,23,456.78" or "1,234" to float string
function cleanAmount($value) {
    if ($value === null) return '';
    $str = trim((string)$value);
    if ($str === '' || $str === '-' || strtoupper($str) === 'N/A') return '';
    // Remove currency symbols, commas, spaces, parentheses
    $str = preg_replace('/[₹$,\s]/u', '', $str);
    // Handle Indian numbering commas already removed; handle parentheses for negatives
    $isNegative = false;
    if (preg_match('/^\((.*)\)$/', $str, $m)) { $isNegative = true; $str = $m[1]; }
    // Keep only digits and dot
    $str = preg_replace('/[^0-9.\-]/', '', $str);
    if ($str === '' || !is_numeric($str)) return '';
    $num = (float)$str;
    if ($isNegative) $num = -$num;
    return (string)$num;
}

// Clean GM which can be decimal (0.05), percent string (5%), or whole number 5..100
function cleanGmToDecimal($value) {
    if ($value === null) return '';
    $str = trim((string)$value);
    if ($str === '' || $str === '-') return '';
    if (substr($str, -1) === '%') {
        $num = rtrim($str, '%');
        if (is_numeric($num)) return (string)max(0.0, min(((float)$num)/100, 1.0));
        return '';
    }
    if (!is_numeric($str)) return '';
    $num = (float)$str;
    // If user provided 5..100 we treat as percent; if 0..1 keep as decimal
    if ($num > 1) $num = $num / 100;
    if ($num < 0) $num = 0; if ($num > 1) $num = 1;
    return (string)$num;
}

// Convert a provided value to Excel serial date (days since 1899-12-30) or return null if invalid
function parseDateToSerial($value) {
    if (isEmptyLike($value)) return null;
    if (is_numeric($value)) {
        return (int)$value;
    }
    $s = trim((string)$value);
    // dd/mm/yyyy or dd-mm-yyyy (or with single-digit day/month)
    $s2 = str_replace(['.', ' '], ['', ''], $s);
    if (preg_match('/^(\d{1,2})[\/-](\d{1,2})[\/-](\d{2,4})$/', $s2, $m)) {
        $d = (int)$m[1]; $mo = (int)$m[2]; $y = (int)$m[3];
        if ($y < 100) { $y += ($y >= 70 ? 1900 : 2000); }
        if ($mo>=1 && $mo<=12 && $d>=1 && $d<=31) {
            $ts = gmmktime(0,0,0,$mo,$d,$y);
            return (int)floor($ts/86400)+25569;
        }
    }
    // d-MMM-yyyy with -, /, or space separators
    if (preg_match('/^(\d{1,2})[\-\/\s]([A-Za-z]{3,})[\-\/\s](\d{4})$/', $s, $m)) {
        $d = (int)$m[1]; $mon = strtolower(substr($m[2],0,3)); $y = (int)$m[3];
        $map = ['jan'=>1,'feb'=>2,'mar'=>3,'apr'=>4,'may'=>5,'jun'=>6,'jul'=>7,'aug'=>8,'sep'=>9,'oct'=>10,'nov'=>11,'dec'=>12];
        if (isset($map[$mon]) && $d>=1 && $d<=31) {
            $ts = gmmktime(0,0,0,$map[$mon],$d,$y);
            return (int)floor($ts/86400)+25569;
        }
    }
    // Fallback: parse using UTC to avoid timezone-induced off-by-one
    $normalized = str_replace(['\\', '.'], ['/', '/'], $s);
    try {
        $dt = new DateTime($normalized, new DateTimeZone('UTC'));
        // Normalize to midnight UTC
        $dt->setTime(0, 0, 0);
        $ts = $dt->getTimestamp();
    } catch (Exception $e) {
        return null;
    }
    if ($ts === false) return null;
    return (int)floor($ts / 86400) + 25569;
}

// Function to convert Excel serial date to MySQL date
function excelToDate($excelDate) {
    if (empty($excelDate) || !is_numeric($excelDate)) {
        return null;
    }
    // Excel serial date starts from 1900-01-01, but Excel incorrectly treats 1900 as a leap year
    // So we need to adjust for this
    $unixTimestamp = ($excelDate - 25569) * 86400;
    return date('Y-m-d', $unixTimestamp);
}

// Function to validate data
function validateRow($row, $rowNumber) {
    global $requiredFields;
    $errors = [];
    $warnings = [];
    
    // Check required fields
    foreach ($requiredFields as $field) {
        if (!isset($row[$field]) || trim($row[$field]) === '') {
            $errors[] = "Required field '$field' is missing or empty";
        }
    }
    
    // Check for blank/empty optional fields and add as warnings
    $optionalFieldsToCheck = ['project_description', 'cost_center', 'sow_number', 'start_date', 'end_date', 'po_date', 'billing_frequency', 'target_gm', 'vendor_name', 'remarks'];
    foreach ($optionalFieldsToCheck as $field) {
        if (!isset($row[$field]) || trim($row[$field]) === '' || trim($row[$field]) === '-' || isEmptyLike($row[$field])) {
            $warnings[] = "Field '$field' is blank";
        }
    }
    
    // Validate specific fields
    if (isset($row['po_number']) && !empty($row['po_number'])) {
        if (strlen($row['po_number']) > 50) {
            $errors[] = "PO number exceeds maximum length of 50 characters";
        }
    }
    
    if (isset($row['po_value']) && !isEmptyLike($row['po_value'])) {
        $clean = cleanAmount($row['po_value']);
        if ($clean === '' || !is_numeric($clean) || (float)$clean < 0) {
            $errors[] = "PO value must be a positive number";
        }
    }
    
    if (isset($row['target_gm']) && !isEmptyLike($row['target_gm'])) {
        $gm = cleanGmToDecimal($row['target_gm']);
        if ($gm === '' || !is_numeric($gm) || (float)$gm < 0 || (float)$gm > 1) {
            $errors[] = "Target GM must be a decimal between 0 and 1 (e.g., 0.05 for 5%)";
        }
    }
    
    if (isset($row['start_date']) && !isEmptyLike($row['start_date'])) {
        if (parseDateToSerial($row['start_date']) === null) {
            $errors[] = "Start date must be Excel serial or a valid date (e.g., 16/Jan/2025, 31-03-2025)";
        }
    }
    
    if (isset($row['end_date']) && !isEmptyLike($row['end_date'])) {
        if (parseDateToSerial($row['end_date']) === null) {
            $errors[] = "End date must be Excel serial or a valid date (e.g., 16/Jan/2025, 31-03-2025)";
        }
    }
    
    if (isset($row['po_date']) && !isEmptyLike($row['po_date'])) {
        if (parseDateToSerial($row['po_date']) === null) {
            $errors[] = "PO date must be Excel serial or a valid date (e.g., 16/Jan/2025, 31-03-2025)";
        }
    }
    
    if (isset($row['project_description']) && strlen($row['project_description']) > 500) {
        $errors[] = "Project description exceeds maximum length of 500 characters";
    }
    
    if (isset($row['cost_center']) && strlen($row['cost_center']) > 100) {
        $errors[] = "Cost center exceeds maximum length of 100 characters";
    }
    
    if (isset($row['sow_number']) && strlen($row['sow_number']) > 100) {
        $errors[] = "SOW number exceeds maximum length of 100 characters";
    }
    
    if (isset($row['billing_frequency']) && strlen($row['billing_frequency']) > 50) {
        $errors[] = "Billing frequency exceeds maximum length of 50 characters";
    }
    
    if (isset($row['vendor_name']) && strlen($row['vendor_name']) > 200) {
        $errors[] = "Vendor name exceeds maximum length of 200 characters";
    }
    
    // Optional: pending_amount numeric >= 0
    if (isset($row['pending_amount']) && !isEmptyLike($row['pending_amount'])) {
        $cleanPending = cleanAmount($row['pending_amount']);
        if ($cleanPending === '' || !is_numeric($cleanPending) || (float)$cleanPending < 0) {
            $errors[] = "Pending amount must be a non-negative number";
        }
    }
    
    // Optional: po_status allow-listed values
    if (isset($row['po_status']) && trim($row['po_status']) !== '') {
        $status = strtolower(trim($row['po_status']));
        $allowed = ['active','closed','open','inactive'];
        if (!in_array($status, $allowed)) {
            $errors[] = "PO status must be one of: Active, Closed, Open, Inactive";
        }
    }
    
    // Validate date relationships
    if (isset($row['start_date']) && isset($row['end_date']) && 
        !empty($row['start_date']) && !empty($row['end_date']) && 
        is_numeric($row['start_date']) && is_numeric($row['end_date'])) {
        if ($row['start_date'] > $row['end_date']) {
            $errors[] = "Start date cannot be after end date";
        }
    }
    
    return ['errors' => $errors, 'warnings' => $warnings];
}

// Check if this is a dry run
$dryRun = isset($_POST['dry_run']) && $_POST['dry_run'] === '1';

try {
    // Validate file size (max 10MB)
    if ($file['size'] > 10 * 1024 * 1024) {
        throw new Exception('File size exceeds 10MB limit');
    }
    
    // Read entire file to support auto-detect of delimiter (comma or tab)
    $lines = file($file['tmp_name'], FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    if ($lines === false || count($lines) === 0) {
        throw new Exception('Uploaded file is empty or unreadable');
    }

    // Auto-detect delimiter using first non-empty line
    $firstLine = $lines[0];
    // Remove UTF-8 BOM if present
    $firstLine = str_replace("\xEF\xBB\xBF", '', $firstLine);
    $commaCount = substr_count($firstLine, ',');
    $tabCount = substr_count($firstLine, "\t");
    $delimiter = $tabCount > $commaCount ? "\t" : ',';

    // Parse headers
    $headersRaw = str_getcsv($firstLine, $delimiter);
    if (!$headersRaw || count($headersRaw) === 0) {
        throw new Exception('Could not read headers from uploaded file');
    }

    // Map headers to canonical field names using alias map
    $headers = [];
    foreach ($headersRaw as $h) {
        $trim = trim($h);
        $key = canon($trim);
        if (in_array($trim, $allFields, true)) { $headers[] = $trim; continue; }
        if (isset($aliasMap[$key])) { $headers[] = $aliasMap[$key]; continue; }
        $headers[] = $trim; // keep as-is; may be extra header
    }
    
    // No mandatory headers enforced server-side
    
    // Check for extra headers
    $extraHeaders = array_diff($headers, $allFields);
    if (!empty($extraHeaders)) {
        // Log warning but don't fail
        error_log('Extra headers found in CSV: ' . implode(', ', $extraHeaders));
    }
    
    $inserted = 0;
    $skipped = 0;
    $errors = [];
    $warnings = [];
    $rowNumber = 1; // Start from 1 since we already read the header
    
    // Begin transaction (skip for dry run)
    if (!$dryRun) {
        $conn->begin_transaction();
    }
    
    // Process each row
    for ($i = 1; $i < count($lines); $i++) {
        $rowNumber++;

        $line = $lines[$i];
        if ($line === null) { continue; }
        $line = trim($line);
        if ($line === '') { continue; }

        $row = str_getcsv($line, $delimiter);
        // Skip if row is empty
        if (!$row || count(array_filter($row, fn($v) => trim((string)$v) !== '')) === 0) {
            continue;
        }

        // Pad or trim row to match header count
        if (count($row) < count($headers)) {
            $row = array_pad($row, count($headers), '');
        } elseif (count($row) > count($headers)) {
            $row = array_slice($row, 0, count($headers));
        }

        // Create associative array from headers and row data
        $rowData = array_combine($headers, $row);
        
        // Validate row data
        $validation = validateRow($rowData, $rowNumber);
        $rowErrors = $validation['errors'];
        $rowWarnings = $validation['warnings'];
        
        // Add warnings to the warnings array
        if (!empty($rowWarnings)) {
            foreach ($rowWarnings as $warning) {
                $warnings[] = ['row' => $rowNumber, 'message' => $warning];
            }
        }
        
        // Only skip row if there are actual errors (not warnings)
        if (!empty($rowErrors)) {
            foreach ($rowErrors as $error) {
                $errors[] = ['row' => $rowNumber, 'message' => $error];
            }
            continue;
        }
        
        // Determine PO number; generate temporary if missing
        $originalPo = isset($rowData['po_number']) ? trim($rowData['po_number']) : '';
        $generatedTemp = false;
        
        // Check if PO number is empty or contains only empty-like values
        if ($originalPo === '' || isEmptyLike($originalPo) || $originalPo === '0') {
            // Generate a unique temporary PO number
            $poNumber = 'TEMP-PO-' . date('Ymd') . '-' . $rowNumber . '-' . substr(uniqid('', true), -6);
            $generatedTemp = true;
        } else {
            $poNumber = trim($originalPo);
            
            // Check for duplicate only when provided
            $checkStmt = $conn->prepare("SELECT id FROM po_details WHERE po_number = ?");
            $checkStmt->bind_param("s", $poNumber);
            $checkStmt->execute();
            $result = $checkStmt->get_result();
            if ($result->num_rows > 0) {
                $skipped++;
                $warnings[] = ['row' => $rowNumber, 'message' => "PO number '$poNumber' already exists - skipped"];
                continue;
            }
            $checkStmt->close();
        }
        
        // Prepare data for insertion
        $projectDescription = isset($rowData['project_description']) ? trim($rowData['project_description']) : '';
        $costCenter = isset($rowData['cost_center']) ? trim($rowData['cost_center']) : '';
        $sowNumber = isset($rowData['sow_number']) ? trim($rowData['sow_number']) : '';
        $startDate = parseDateToSerial($rowData['start_date'] ?? null);
        $endDate = parseDateToSerial($rowData['end_date'] ?? null);
        $poDate = parseDateToSerial($rowData['po_date'] ?? null);
        // Fallbacks: if start missing, use po_date; if end missing, use start
        if ($startDate === null && $poDate !== null) {
            $startDate = $poDate;
        }
        if ($endDate === null && $startDate !== null) {
            $endDate = $startDate;
        }
        // Final validation
        // Dates are optional; only error if explicitly provided but invalid (handled above)
        $poValueStr = cleanAmount($rowData['po_value'] ?? '');
        $poValue = ($poValueStr === '') ? 0.0 : (float)$poValueStr;
        $billingFrequency = isset($rowData['billing_frequency']) ? trim($rowData['billing_frequency']) : '';
        $gmStr = cleanGmToDecimal($rowData['target_gm'] ?? '');
        $targetGm = ($gmStr === '') ? 0.0 : (float)$gmStr;
        $vendorName = isset($rowData['vendor_name']) ? trim($rowData['vendor_name']) : null;
        $remarks = isset($rowData['remarks']) ? trim($rowData['remarks']) : null;
        if ($generatedTemp) {
            $note = 'Auto-generated PO number due to missing value in upload';
            $remarks = $remarks ? ($remarks . ' | ' . $note) : $note;
        }
        $pendingAmountStr = cleanAmount($rowData['pending_amount'] ?? '');
        $pendingAmount = ($pendingAmountStr === '') ? 0.00 : (float)$pendingAmountStr;
        $poStatus = isset($rowData['po_status']) && trim($rowData['po_status']) !== ''
            ? ucfirst(strtolower(trim($rowData['po_status']))) : 'Active';
        
<<<<<<< Updated upstream
        // Insert record (skip for dry run)
        if ($dryRun) {
            $inserted++; // Count as successful for dry run
        } else {
            $insertStmt = $conn->prepare("
                INSERT INTO po_details (
                    project_description, cost_center, sow_number, start_date, end_date,
                    po_number, po_date, po_value, billing_frequency, target_gm,
                    vendor_name, remarks, po_status, pending_amount
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            
            $insertStmt->bind_param(
                "sssiiisdsdsssd",
                $projectDescription, $costCenter, $sowNumber, $startDate, $endDate,
                $poNumber, $poDate, $poValue, $billingFrequency, $targetGm,
                $vendorName, $remarks, $poStatus, $pendingAmount
            );
            
            if ($insertStmt->execute()) {
                $inserted++;
                
                // Log the creation in audit log
                $userId = $_SESSION['user_id'] ?? 1;
                $auditStmt = $conn->prepare("
                    INSERT INTO audit_log (user_id, action, table_name, record_id, created_at) 
                    VALUES (?, 'create_po', 'po_details', ?, NOW())
                ");
                $auditStmt->bind_param("ii", $userId, $conn->insert_id);
                $auditStmt->execute();
            } else {
                $errors[] = ['row' => $rowNumber, 'message' => 'Database error: ' . $insertStmt->error];
            }
=======
        // Final safety check - ensure PO number is never empty
        if (empty($poNumber) || $poNumber === '0' || trim($poNumber) === '') {
            $poNumber = 'TEMP-PO-' . date('Ymd') . '-' . $rowNumber . '-' . substr(uniqid('', true), -8);
            $generatedTemp = true;
        }
        
        // Insert record
        $insertStmt = $conn->prepare("
            INSERT INTO po_details (
                project_description, cost_center, sow_number, start_date, end_date,
                po_number, po_date, po_value, billing_frequency, target_gm,
                vendor_name, remarks, po_status, pending_amount
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        
        $insertStmt->bind_param(
            "sssiiisdsdsssd",
            $projectDescription, $costCenter, $sowNumber, $startDate, $endDate,
            $poNumber, $poDate, $poValue, $billingFrequency, $targetGm,
            $vendorName, $remarks, $poStatus, $pendingAmount
        );
        
        if ($insertStmt->execute()) {
            $inserted++;
            
            // Log the creation in audit log (if audit_log table exists)
            $userId = $_SESSION['user_id'] ?? 1;
            $recordId = $conn->insert_id;
            
            // Check if audit_log table exists before trying to insert
            $tableCheck = $conn->query("SHOW TABLES LIKE 'audit_log'");
            if ($tableCheck && $tableCheck->num_rows > 0) {
                $auditStmt = $conn->prepare("
                    INSERT INTO audit_log (user_id, action, table_name, record_id, created_at) 
                    VALUES (?, 'create_po', 'po_details', ?, NOW())
                ");
                if ($auditStmt) {
                    $auditStmt->bind_param("ii", $userId, $recordId);
                    $auditStmt->execute();
                    $auditStmt->close();
                }
            }
        } else {
            // Check if it's a duplicate entry error
            if (strpos($insertStmt->error, 'Duplicate entry') !== false) {
                $skipped++;
                $warnings[] = ['row' => $rowNumber, 'message' => "Duplicate PO number '$poNumber' - skipped"];
            } else {
                $errors[] = ['row' => $rowNumber, 'message' => 'Database error: ' . $insertStmt->error];
            }
        }
        
        if (isset($insertStmt)) {
            $insertStmt->close();
>>>>>>> Stashed changes
        }
    }
    
    // No file handle to close; using file() above
    
    // Commit transaction if no critical errors (skip for dry run)
    if ($dryRun) {
        echo json_encode([
            'success' => empty($errors),
            'inserted' => $inserted,
            'skipped' => $skipped,
            'errors' => $errors,
<<<<<<< Updated upstream
            'dry_run' => true,
            'message' => empty($errors) ? 'Dry run successful - CSV is ready for upload' : 'Dry run found validation errors'
        ]);
    } else {
        if (empty($errors) || $inserted > 0) {
            $conn->commit();
            echo json_encode([
                'success' => true,
                'inserted' => $inserted,
                'skipped' => $skipped,
                'errors' => $errors,
                'dry_run' => false
            ]);
        } else {
            $conn->rollback();
            echo json_encode([
                'success' => false,
                'inserted' => 0,
                'skipped' => $skipped,
                'errors' => $errors,
                'dry_run' => false
            ]);
        }
=======
            'warnings' => $warnings
        ]);
    } else {
        $conn->rollback();
        echo json_encode([
            'success' => false,
            'inserted' => 0,
            'skipped' => $skipped,
            'errors' => $errors,
            'warnings' => $warnings
        ]);
>>>>>>> Stashed changes
    }
    
} catch (Exception $e) {
    if (isset($conn)) {
        $conn->rollback();
    }
    echo json_encode([
        'success' => false,
        'errors' => [['row' => 0, 'message' => $e->getMessage()]],
        'debug' => [
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => $e->getTraceAsString()
        ]
    ]);
} catch (Error $e) {
    if (isset($conn)) {
        $conn->rollback();
    }
    echo json_encode([
        'success' => false,
        'errors' => [['row' => 0, 'message' => 'PHP Error: ' . $e->getMessage()]],
        'debug' => [
            'file' => $e->getFile(),
            'line' => $e->getLine()
        ]
    ]);
}
?>
