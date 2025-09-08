<?php
// Auto-create database and table
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>Auto Database Setup</h2>";

try {
    // First, connect without specifying database
    $pdo = new PDO(
        "mysql:host=localhost;charset=utf8mb4",
        "root",
        "",
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]
    );
    echo "<span style='color: green;'>✓ Connected to MySQL server</span><br>";
    
    // Create database if it doesn't exist
    $pdo->exec("CREATE DATABASE IF NOT EXISTS  po_management");
    echo "<span style='color: green;'>✓ Database ' po_management' created/verified</span><br>";
    
    // Connect to the specific database
    $pdo = new PDO(
        "mysql:host=localhost;dbname= po_management;charset=utf8mb4",
        "root",
        "",
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]
    );
    
    // Create table if it doesn't exist
    $sql = "CREATE TABLE IF NOT EXISTS finance_tasks (
        id INT AUTO_INCREMENT PRIMARY KEY,
        task_date DATE NOT NULL,
        emp_dept VARCHAR(100) NOT NULL,
        emp_id VARCHAR(50) NOT NULL,
        action_req_by VARCHAR(100) NOT NULL,
        action_req TEXT NOT NULL,
        action_owner VARCHAR(100) NOT NULL,
        status ENUM('Incomplete', 'Pending', 'Complete') NOT NULL,
        completion_date DATE NULL,
        remark TEXT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX idx_task_date (task_date),
        INDEX idx_status (status),
        INDEX idx_emp_dept (emp_dept),
        INDEX idx_created_at (created_at)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    
    $pdo->exec($sql);
    echo "<span style='color: green;'>✓ Table 'finance_tasks' created/verified</span><br>";
    
    // Check if table has data
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM finance_tasks");
    $count = $stmt->fetch()['count'];
    
    if ($count == 0) {
        // Insert sample data
        $sampleData = [
            ['2024-01-15', 'Finance', 'EMP001', 'John Smith', 'Review monthly budget reports and prepare variance analysis', 'Sarah Johnson', 'Complete', '2024-01-20', 'All reports reviewed and submitted to management'],
            ['2024-01-16', 'Accounting', 'EMP002', 'Mike Davis', 'Process vendor payments for Q4 invoices', 'Lisa Chen', 'Pending', NULL, 'Waiting for approval from finance manager'],
            ['2024-01-17', 'Payroll', 'EMP003', 'HR Department', 'Calculate and process year-end bonuses', 'David Wilson', 'Incomplete', NULL, 'Need to verify employee performance data'],
            ['2024-01-18', 'Budget', 'EMP004', 'CFO', 'Prepare annual budget presentation for board meeting', 'Maria Garcia', 'Complete', '2024-01-25', 'Presentation completed and approved'],
            ['2024-01-19', 'Audit', 'EMP005', 'Internal Audit Team', 'Conduct quarterly internal audit of financial processes', 'Robert Brown', 'Pending', NULL, 'Audit in progress - expected completion next week']
        ];
        
        $insertSql = "INSERT INTO finance_tasks (task_date, emp_dept, emp_id, action_req_by, action_req, action_owner, status, completion_date, remark) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $pdo->prepare($insertSql);
        
        foreach ($sampleData as $data) {
            $stmt->execute($data);
        }
        
        echo "<span style='color: green;'>✓ Sample data inserted</span><br>";
    }
    
    echo "<span style='color: green;'>✓ Total records: " . $count . "</span><br>";
    echo "<br><span style='color: green; font-weight: bold;'>✓ Database setup completed successfully!</span><br>";
    echo "<br><a href='index.html'>Go to Finance Tracker Application</a>";
    
} catch (PDOException $e) {
    echo "<span style='color: red;'>✗ Error: " . $e->getMessage() . "</span><br>";
    echo "<br>Please make sure:";
    echo "<br>1. XAMPP is running (Apache and MySQL)";
    echo "<br>2. MySQL service is started";
    echo "<br>3. No other MySQL service is running on port 3306";
}
?> 