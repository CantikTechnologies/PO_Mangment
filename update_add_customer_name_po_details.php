<?php
// One-time script to add customer_name column to po_details
// Usage: visit /po-mgmt/update_add_customer_name_po_details.php once, then delete the file

require_once __DIR__ . '/config/db.php';

header('Content-Type: text/plain');

// Check if column already exists
$checkSql = "SHOW COLUMNS FROM po_details LIKE 'customer_name'";
$result = $conn->query($checkSql);
if ($result && $result->num_rows > 0) {
    echo "Column customer_name already exists on po_details. Nothing to do.\n";
    $conn->close();
    exit;
}

$sql = "ALTER TABLE po_details ADD COLUMN customer_name VARCHAR(255) NULL DEFAULT NULL AFTER vendor_name";

if ($conn->query($sql) === TRUE) {
    echo "Success: Added customer_name (VARCHAR(255) NULL) to po_details.\n";
} else {
    echo "Error adding column: " . $conn->error . "\n";
}

$conn->close();
?>


