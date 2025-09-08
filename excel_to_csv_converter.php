<?php
// Excel to CSV Converter
// This script will help convert your Excel file to CSV format

// First, we need to install PhpSpreadsheet library
// Run this command in your project directory:
// composer require phpoffice/phpspreadsheet

require_once 'vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;

try {
    // Path to your Excel file
    $excelFile = 'PO Mgmt - For New Tool_Pardeep.xlsx';
    
    // Check if file exists
    if (!file_exists($excelFile)) {
        die("Excel file not found: $excelFile");
    }
    
    // Load the Excel file
    $spreadsheet = IOFactory::load($excelFile);
    
    // Get all worksheet names
    $worksheetNames = $spreadsheet->getSheetNames();
    
    echo "<h2>Excel File Conversion</h2>";
    echo "<p>Found " . count($worksheetNames) . " worksheets:</p>";
    echo "<ul>";
    foreach ($worksheetNames as $index => $sheetName) {
        echo "<li>Sheet " . ($index + 1) . ": " . htmlspecialchars($sheetName) . "</li>";
    }
    echo "</ul>";
    
    // Convert each worksheet to CSV
    foreach ($worksheetNames as $index => $sheetName) {
        $worksheet = $spreadsheet->getSheet($index);
        
        // Create CSV filename
        $csvFilename = 'converted_' . preg_replace('/[^a-zA-Z0-9_-]/', '_', $sheetName) . '.csv';
        
        // Get the highest row and column
        $highestRow = $worksheet->getHighestRow();
        $highestColumn = $worksheet->getHighestColumn();
        
        echo "<h3>Converting Sheet: " . htmlspecialchars($sheetName) . "</h3>";
        echo "<p>Rows: $highestRow, Columns: $highestColumn</p>";
        
        // Open CSV file for writing
        $csvFile = fopen($csvFilename, 'w');
        
        if ($csvFile === false) {
            echo "<p style='color: red;'>Error: Could not create CSV file: $csvFilename</p>";
            continue;
        }
        
        // Write data to CSV
        for ($row = 1; $row <= $highestRow; $row++) {
            $rowData = [];
            for ($col = 'A'; $col <= $highestColumn; $col++) {
                $cellValue = $worksheet->getCell($col . $row)->getCalculatedValue();
                $rowData[] = $cellValue;
            }
            fputcsv($csvFile, $rowData);
        }
        
        fclose($csvFile);
        echo "<p style='color: green;'>✓ Created: <a href='$csvFilename' download>$csvFilename</a></p>";
    }
    
    echo "<h3>Conversion Complete!</h3>";
    echo "<p>All worksheets have been converted to CSV files.</p>";
    
} catch (Exception $e) {
    echo "<h2>Error</h2>";
    echo "<p style='color: red;'>Error: " . $e->getMessage() . "</p>";
    echo "<p>Make sure you have installed PhpSpreadsheet library:</p>";
    echo "<pre>composer require phpoffice/phpspreadsheet</pre>";
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Excel to CSV Converter</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        h2, h3 { color: #333; }
        pre { background: #f4f4f4; padding: 10px; border-radius: 5px; }
        a { color: #0066cc; text-decoration: none; }
        a:hover { text-decoration: underline; }
    </style>
</head>
<body>
    <h1>Excel to CSV Converter</h1>
    <p>This tool will convert your Excel file into CSV format.</p>
    
    <h2>Installation Instructions:</h2>
    <ol>
        <li>Install Composer (if not already installed): <a href="https://getcomposer.org/download/" target="_blank">Download Composer</a></li>
        <li>Open Command Prompt in your project directory: <code>C:\xampp\htdocs\PO_3\</code></li>
        <li>Run this command: <code>composer require phpoffice/phpspreadsheet</code></li>
        <li>Refresh this page to convert your Excel file</li>
    </ol>
    
    <h2>Alternative Method (Manual Conversion):</h2>
    <p>If you can't install Composer, you can manually convert the Excel file:</p>
    <ol>
        <li>Open the Excel file in Microsoft Excel</li>
        <li>For each worksheet, go to File → Save As</li>
        <li>Choose "CSV (Comma delimited)" format</li>
        <li>Save with a descriptive name (e.g., "purchase_orders.csv", "invoices.csv")</li>
    </ol>
</body>
</html>
