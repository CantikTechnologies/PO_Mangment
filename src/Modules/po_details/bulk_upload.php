<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

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
if ($fileExtension !== 'csv') {
    echo json_encode(['success' => false, 'errors' => [['row' => 0, 'message' => 'Only CSV files are allowed']]]);
    exit();
}

// Define required and optional fields
$requiredFields = [
    'project_description',
    'cost_center', 
    'sow_number',
    'po_number',
    'po_date',
    'po_value'
];

$optionalFields = [
    'vendor_name',
    'remarks',
    'pending_amount',
    'po_status'
];

$allFields = array_merge($requiredFields, $optionalFields);

// Convert a provided value to Excel serial date (days since 1899-12-30) or return null if invalid
function parseDateToSerial($value) {
    if ($value === null) return null;
    // Treat common placeholders as empty
    $placeholders = ['-', ' - ', '(0)', '0', 'N/A', 'n/a'];
    if (in_array(trim((string)$value), $placeholders, true)) {
        return null;
    }
    if (is_numeric($value)) {
        return (int)$value;
    }
    $trimmed = trim((string)$value);
    if ($trimmed === '') return null;

    // Normalize separators
    $normalized = str_replace(['\\', '.'], ['/', '/'], $trimmed);
    // Try with DateTime in UTC
    try {
        $dt = new DateTimeImmutable($normalized, new DateTimeZone('UTC'));
        $ts = $dt->getTimestamp();
        // Excel serial: days since 1899-12-30
        return (int)floor($ts / 86400) + 25569;
    } catch (Throwable $e) {
        // Fallback: strtotime
        $ts = strtotime($normalized);
        if ($ts === false) return null;
        return (int)floor($ts / 86400) + 25569;
    }
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
    
    // Check required fields
    foreach ($requiredFields as $field) {
        if (!isset($row[$field]) || trim($row[$field]) === '') {
            $errors[] = "Required field '$field' is missing or empty";
        }
    }
    
    // Validate specific fields
    if (isset($row['po_number']) && !empty($row['po_number'])) {
        if (strlen($row['po_number']) > 50) {
            $errors[] = "PO number exceeds maximum length of 50 characters";
        }
    }
    
    if (isset($row['po_value']) && !empty($row['po_value'])) {
        if (!is_numeric($row['po_value']) || $row['po_value'] < 0) {
            $errors[] = "PO value must be a positive number";
        }
    }
    
    if (isset($row['target_gm']) && !empty($row['target_gm'])) {
        if (!is_numeric($row['target_gm']) || $row['target_gm'] < 0 || $row['target_gm'] > 1) {
            $errors[] = "Target GM must be a decimal between 0 and 1 (e.g., 0.05 for 5%)";
        }
    }
    
    if (isset($row['start_date']) && trim((string)$row['start_date']) !== '') {
        if (parseDateToSerial($row['start_date']) === null) {
            $errors[] = "Start date must be Excel serial or a valid date (e.g., 16/Jan/2025, 31-03-2025)";
        }
    }
    
    if (isset($row['end_date']) && trim((string)$row['end_date']) !== '') {
        if (parseDateToSerial($row['end_date']) === null) {
            $errors[] = "End date must be Excel serial or a valid date (e.g., 16/Jan/2025, 31-03-2025)";
        }
    }
    
    if (isset($row['po_date']) && trim((string)$row['po_date']) !== '') {
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
    if (isset($row['pending_amount']) && trim($row['pending_amount']) !== '') {
        if (!is_numeric($row['pending_amount']) || (float)$row['pending_amount'] < 0) {
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
    
    return $errors;
}

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
    $headers = str_getcsv($firstLine, $delimiter);
    if (!$headers || count($headers) === 0) {
        throw new Exception('Could not read headers from uploaded file');
    }

    // Clean headers (trim whitespace)
    $headers = array_map(function($header) {
        return trim($header);
    }, $headers);
    
    // Validate headers
    $missingHeaders = [];
    foreach ($requiredFields as $requiredField) {
        if (!in_array($requiredField, $headers)) {
            $missingHeaders[] = $requiredField;
        }
    }
    
    if (!empty($missingHeaders)) {
        throw new Exception('Missing required headers: ' . implode(', ', $missingHeaders));
    }
    
    // Check for extra headers
    $extraHeaders = array_diff($headers, $allFields);
    if (!empty($extraHeaders)) {
        // Log warning but don't fail
        error_log('Extra headers found in CSV: ' . implode(', ', $extraHeaders));
    }
    
    $inserted = 0;
    $skipped = 0;
    $errors = [];
    $rowNumber = 1; // Start from 1 since we already read the header
    
    // Begin transaction
    $conn->begin_transaction();
    
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
        $rowErrors = validateRow($rowData, $rowNumber);
        if (!empty($rowErrors)) {
            foreach ($rowErrors as $error) {
                $errors[] = ['row' => $rowNumber, 'message' => $error];
            }
            continue;
        }
        
        // Check for duplicate PO number
        $poNumber = trim($rowData['po_number']);
        $checkStmt = $conn->prepare("SELECT id FROM po_details WHERE po_number = ?");
        $checkStmt->bind_param("s", $poNumber);
        $checkStmt->execute();
        $result = $checkStmt->get_result();
        
        if ($result->num_rows > 0) {
            $skipped++;
            $errors[] = ['row' => $rowNumber, 'message' => "PO number '$poNumber' already exists"];
            continue;
        }
        
        // Prepare data for insertion
        $projectDescription = trim($rowData['project_description']);
        $costCenter = trim($rowData['cost_center']);
        $sowNumber = trim($rowData['sow_number']);
        $startDate = parseDateToSerial($rowData['start_date']);
        $endDate = parseDateToSerial($rowData['end_date']);
        $poDate = parseDateToSerial($rowData['po_date']);
        // Fallbacks: if start missing, use po_date; if end missing, use start
        if ($startDate === null && $poDate !== null) {
            $startDate = $poDate;
        }
        if ($endDate === null && $startDate !== null) {
            $endDate = $startDate;
        }
        // Final validation
        if ($poDate === null) {
            $errors[] = ['row' => $rowNumber, 'message' => 'PO date is missing or invalid'];
            continue;
        }
        if ($startDate === null) {
            $errors[] = ['row' => $rowNumber, 'message' => 'Start date is missing or invalid'];
            continue;
        }
        if ($endDate === null) {
            $errors[] = ['row' => $rowNumber, 'message' => 'End date is missing or invalid'];
            continue;
        }
        $poValue = (float)$rowData['po_value'];
        $billingFrequency = trim($rowData['billing_frequency']);
        $targetGm = (float)$rowData['target_gm'];
        $vendorName = isset($rowData['vendor_name']) ? trim($rowData['vendor_name']) : null;
        $remarks = isset($rowData['remarks']) ? trim($rowData['remarks']) : null;
        $pendingAmount = isset($rowData['pending_amount']) && trim($rowData['pending_amount']) !== ''
            ? (float)$rowData['pending_amount'] : 0.00;
        $poStatus = isset($rowData['po_status']) && trim($rowData['po_status']) !== ''
            ? ucfirst(strtolower(trim($rowData['po_status']))) : 'Active';
        
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
    }
    
    // No file handle to close; using file() above
    
    // Commit transaction if no critical errors
    if (empty($errors) || $inserted > 0) {
        $conn->commit();
        echo json_encode([
            'success' => true,
            'inserted' => $inserted,
            'skipped' => $skipped,
            'errors' => $errors
        ]);
    } else {
        $conn->rollback();
        echo json_encode([
            'success' => false,
            'inserted' => 0,
            'skipped' => $skipped,
            'errors' => $errors
        ]);
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
