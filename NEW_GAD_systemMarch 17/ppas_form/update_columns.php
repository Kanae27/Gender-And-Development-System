<?php
// For debugging, enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('html_errors', 1);

// Set content type to HTML for better display
header('Content-Type: text/html; charset=utf-8');

echo "<html><head><title>PPAS Database Update</title>
<style>
    body { font-family: Arial, sans-serif; margin: 20px; line-height: 1.6; }
    .success { color: green; font-weight: bold; }
    .error { color: red; font-weight: bold; }
    pre { background-color: #f5f5f5; padding: 10px; border-radius: 5px; }
    h1, h2 { color: #333; }
    .action-btn { background-color: #6a1b9a; color: white; padding: 10px 15px; border: none; 
                 text-decoration: none; display: inline-block; border-radius: 5px; margin-top: 20px; }
</style>
</head><body>";

echo "<h1>PPAS Database Update Tool</h1>";

// Database connection parameters
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "gad_db";

try {
    echo "<h2>Connecting to database...</h2>";
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "<p class='success'>Connected to database successfully!</p>";
    
    // Check if ppas_forms table exists
    $tableExists = $conn->query("SHOW TABLES LIKE 'ppas_forms'")->rowCount() > 0;
    
    if (!$tableExists) {
        echo "<p class='error'>The ppas_forms table does not exist. Please create it first.</p>";
        throw new Exception("Table ppas_forms does not exist");
    }
    
    echo "<h2>Checking and updating columns in ppas_forms table...</h2>";
    
    // Check for required columns and add them if missing
    $requiredColumns = [
        'ps_attribution' => "ADD COLUMN ps_attribution DECIMAL(12,2) DEFAULT 0.00 AFTER source_of_budget",
        'duration_metadata' => "ADD COLUMN duration_metadata VARCHAR(255) NULL AFTER total_duration",
        'gender_issue' => "ADD COLUMN gender_issue TEXT NULL AFTER quarter",
        'type' => "ADD COLUMN type VARCHAR(20) NULL AFTER gender_issue"
    ];
    
    $addedColumns = [];
    
    // Get existing columns
    $columnQuery = $conn->query("SHOW COLUMNS FROM ppas_forms");
    $existingColumns = [];
    while ($column = $columnQuery->fetch(PDO::FETCH_ASSOC)) {
        $existingColumns[] = $column['Field'];
    }
    
    echo "<p>Existing columns: " . implode(", ", $existingColumns) . "</p>";
    
    // Check and add missing columns
    foreach ($requiredColumns as $column => $addSql) {
        if (!in_array($column, $existingColumns)) {
            echo "<p>Adding missing column: $column</p>";
            try {
                $sql = "ALTER TABLE ppas_forms $addSql";
                $conn->exec($sql);
                $addedColumns[] = $column;
                echo "<p class='success'>Successfully added $column column!</p>";
            } catch (PDOException $e) {
                echo "<p class='error'>Error adding $column column: " . $e->getMessage() . "</p>";
            }
        } else {
            echo "<p>Column $column already exists.</p>";
        }
    }
    
    // Ensure total_duration is DECIMAL with enough precision
    echo "<h2>Checking total_duration column type...</h2>";
    $result = $conn->query("SHOW COLUMNS FROM ppas_forms WHERE Field = 'total_duration'");
    $total_duration_info = $result->fetch(PDO::FETCH_ASSOC);
    
    echo "<p>Current total_duration column type: " . ($total_duration_info ? $total_duration_info['Type'] : 'Not found') . "</p>";
    
    // Make sure total_duration is DECIMAL with enough precision
    if ($total_duration_info && $total_duration_info['Type'] != 'decimal(10,2)') {
        echo "<p>Modifying total_duration column to ensure it can store decimal values...</p>";
        try {
            $sql = "ALTER TABLE ppas_forms MODIFY COLUMN total_duration DECIMAL(10,2) NOT NULL DEFAULT 0.00";
            $conn->exec($sql);
            echo "<p class='success'>Successfully modified total_duration column to DECIMAL(10,2)!</p>";
        } catch (PDOException $e) {
            echo "<p class='error'>Error modifying total_duration column: " . $e->getMessage() . "</p>";
        }
    } else {
        echo "<p>Column total_duration already has the correct type.</p>";
    }
    
    // Check current table structure
    echo "<h2>Current Table Structure:</h2>";
    $result = $conn->query("DESCRIBE ppas_forms");
    echo "<pre>";
    while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
        echo $row['Field'] . " - " . $row['Type'] . 
             ($row['Null'] == 'NO' ? ' NOT NULL' : ' NULL') . 
             ($row['Default'] ? " DEFAULT '" . $row['Default'] . "'" : "") . 
             ($row['Extra'] ? " " . $row['Extra'] : "") . "\n";
    }
    echo "</pre>";
    
    // Summary
    echo "<h2>Update Summary</h2>";
    if (count($addedColumns) > 0) {
        echo "<p class='success'>Added the following columns: " . implode(", ", $addedColumns) . "</p>";
    } else {
        echo "<p class='success'>All required columns already exist.</p>";
    }
    
} catch (Exception $e) {
    echo "<p class='error'>Error: " . $e->getMessage() . "</p>";
}

echo "<div style='margin-top: 20px;'>";
echo "<a href='test_form.php' class='action-btn'>Go to Test Form</a> ";
echo "<a href='ppas.php' class='action-btn'>Go to PPAS Form</a>";
echo "</div>";

echo "</body></html>";
?> 