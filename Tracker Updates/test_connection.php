<?php
// Test database connection
require_once 'config.php';

echo "<h2>Database Connection Test</h2>";

// Test 1: Check if database configuration is loaded
echo "<h3>1. Configuration Check</h3>";
echo "DB_HOST: " . DB_HOST . "<br>";
echo "DB_NAME: " . DB_NAME . "<br>";
echo "DB_USER: " . DB_USER . "<br>";
echo "DB_PASS: " . (DB_PASS ? '[SET]' : '[EMPTY]') . "<br><br>";

// Test 2: Test database connection
echo "<h3>2. Database Connection Test</h3>";
$connectionTest = testDatabaseConnection();
if ($connectionTest['success']) {
    echo "<span style='color: green;'>✓ " . $connectionTest['message'] . "</span><br><br>";
} else {
    echo "<span style='color: red;'>✗ " . $connectionTest['message'] . "</span><br><br>";
}

// Test 3: Check if table exists
echo "<h3>3. Table Structure Check</h3>";
try {
    $pdo = getDatabaseConnection();
    
    // Check if finance_tasks table exists
    $stmt = $pdo->query("SHOW TABLES LIKE 'finance_tasks'");
    if ($stmt->rowCount() > 0) {
        echo "<span style='color: green;'>✓ finance_tasks table exists</span><br>";
        
        // Show table structure
        $stmt = $pdo->query("DESCRIBE finance_tasks");
        $columns = $stmt->fetchAll();
        echo "<h4>Table Structure:</h4>";
        echo "<table border='1' style='border-collapse: collapse;'>";
        echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th></tr>";
        foreach ($columns as $column) {
            echo "<tr>";
            echo "<td>" . $column['Field'] . "</td>";
            echo "<td>" . $column['Type'] . "</td>";
            echo "<td>" . $column['Null'] . "</td>";
            echo "<td>" . $column['Key'] . "</td>";
            echo "<td>" . $column['Default'] . "</td>";
            echo "</tr>";
        }
        echo "</table><br>";
        
        // Count records
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM finance_tasks");
        $count = $stmt->fetch()['count'];
        echo "Total records: " . $count . "<br><br>";
        
    } else {
        echo "<span style='color: red;'>✗ finance_tasks table does not exist</span><br>";
        echo "Please run the database_setup.sql file to create the table.<br><br>";
    }
    
} catch (Exception $e) {
    echo "<span style='color: red;'>✗ Error: " . $e->getMessage() . "</span><br><br>";
}

// Test 4: PHP Requirements
echo "<h3>4. PHP Requirements Check</h3>";
echo "PHP Version: " . phpversion() . "<br>";
echo "PDO Extension: " . (extension_loaded('pdo') ? '✓ Enabled' : '✗ Disabled') . "<br>";
echo "PDO MySQL Extension: " . (extension_loaded('pdo_mysql') ? '✓ Enabled' : '✗ Disabled') . "<br>";
echo "MySQL Extension: " . (extension_loaded('mysqli') ? '✓ Enabled' : '✗ Disabled') . "<br><br>";

// Test 5: File Permissions
echo "<h3>5. File Permissions Check</h3>";
$files = ['config.php', 'process.php', 'index.html', 'script.js', 'styles.css'];
foreach ($files as $file) {
    if (file_exists($file)) {
        echo "✓ " . $file . " exists<br>";
    } else {
        echo "✗ " . $file . " missing<br>";
    }
}

echo "<br><h3>Next Steps:</h3>";
echo "1. If all tests pass, your application should work correctly.<br>";
echo "2. If database connection fails, check your XAMPP MySQL service is running.<br>";
echo "3. If table doesn't exist, import database_setup.sql into phpMyAdmin.<br>";
echo "4. Access your application at: <a href='index.html'>index.html</a><br>";
?> 