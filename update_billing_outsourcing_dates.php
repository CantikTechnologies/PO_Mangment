<?php
// One-time script to allow NULL dates for specified columns
// Usage: visit /po-mgmt/update_billing_outsourcing_dates.php once, then delete the file

require_once __DIR__ . '/config/db.php';

header('Content-Type: text/plain');

$queries = [
    // billing_details
    "ALTER TABLE billing_details MODIFY COLUMN cantik_invoice_date INT(11) NULL DEFAULT NULL",
    // outsourcing_detail
    "ALTER TABLE outsourcing_detail MODIFY COLUMN cantik_po_date INT(11) NULL DEFAULT NULL",
    "ALTER TABLE outsourcing_detail MODIFY COLUMN vendor_inv_date INT(11) NULL DEFAULT NULL",
];

$ok = true;
foreach ($queries as $sql) {
    if (!$conn->query($sql)) {
        $ok = false;
        echo "Failed: $sql\n";
        echo "Error: " . $conn->error . "\n\n";
    }
}

if ($ok) {
    echo "Success: Updated columns to allow NULL values. You can now leave these dates empty.\n";
} else {
    echo "Completed with errors. Review messages above.\n";
}

$conn->close();
?>


