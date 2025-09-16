<?php
session_start();
if (!isset($_SESSION['username'])) {
    header('Location: login.php');
    exit();
}

include 'config/db.php';

try {
    // Update date columns to allow NULL values
    $conn->query('ALTER TABLE po_details MODIFY COLUMN start_date int(11) NULL');
    $conn->query('ALTER TABLE po_details MODIFY COLUMN end_date int(11) NULL');
    $conn->query('ALTER TABLE po_details MODIFY COLUMN po_date int(11) NULL');
    
    echo "<h2>Database schema updated successfully!</h2>";
    echo "<p>Date fields (start_date, end_date, po_date) now allow NULL values.</p>";
    
    // Verify the changes
    echo "<h3>Updated schema:</h3>";
    $result = $conn->query('DESCRIBE po_details');
    echo "<table border='1'>";
    echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Default</th></tr>";
    while($row = $result->fetch_assoc()) {
        if (in_array($row['Field'], ['start_date', 'end_date', 'po_date'])) {
            echo "<tr>";
            echo "<td>" . $row['Field'] . "</td>";
            echo "<td>" . $row['Type'] . "</td>";
            echo "<td>" . $row['Null'] . "</td>";
            echo "<td>" . $row['Default'] . "</td>";
            echo "</tr>";
        }
    }
    echo "</table>";
    
    echo "<p><a href='src/Modules/po_details/list.php'>Go back to PO List</a></p>";
    
} catch (Exception $e) {
    echo "<h2>Error:</h2>";
    echo "<p>" . $e->getMessage() . "</p>";
}
?>
