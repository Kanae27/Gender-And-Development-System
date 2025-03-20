<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('html_errors', 1);

// Set content type to HTML for better display
header('Content-Type: text/html; charset=utf-8');

echo "<!DOCTYPE html>
<html>
<head>
    <title>Add PS Attribution Field</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; line-height: 1.6; }
        .success { color: green; font-weight: bold; }
        .error { color: red; font-weight: bold; }
        .warning { color: orange; font-weight: bold; }
        pre { background-color: #f5f5f5; padding: 10px; border-radius: 5px; overflow: auto; }
        code { background-color: #f0f0f0; padding: 2px 4px; border-radius: 3px; }
        table { border-collapse: collapse; width: 100%; margin: 20px 0; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        .action-btn { background-color: #6a1b9a; color: white; padding: 10px 15px; border: none;
                     text-decoration: none; display: inline-block; border-radius: 5px; margin: 5px; }
    </style>
</head>
<body>
    <h1>Adding PS Attribution Field to Database</h1>
    <p>This script will make sure the PS Attribution field is properly added to your database.</p>";

// Database connection parameters
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "gad_db";

try {
    echo "<h2>Step 1: Connecting to database...</h2>";
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "<p class='success'>Connected to database successfully!</p>";
    
    // Check if ppas_forms table exists
    echo "<h2>Step 2: Checking if ppas_forms table exists...</h2>";
    $tableExists = $conn->query("SHOW TABLES LIKE 'ppas_forms'")->rowCount() > 0;
    
    if (!$tableExists) {
        echo "<p class='error'>Error: The ppas_forms table does not exist!</p>";
        echo "<p>You need to create the ppas_forms table first. Please check your database setup.</p>";
        exit;
    }
    
    echo "<p class='success'>ppas_forms table exists!</p>";
    
    // Check if ps_attribution column exists
    echo "<h2>Step 3: Checking if ps_attribution column exists...</h2>";
    $columnExists = $conn->query("SHOW COLUMNS FROM ppas_forms LIKE 'ps_attribution'")->rowCount() > 0;
    
    if ($columnExists) {
        echo "<p class='warning'>The ps_attribution column already exists in the ppas_forms table.</p>";
    } else {
        echo "<p>The ps_attribution column does not exist. Adding it now...</p>";
        
        // Add the ps_attribution column
        $sql = "ALTER TABLE ppas_forms 
                ADD COLUMN ps_attribution DECIMAL(12,2) DEFAULT 0.00 AFTER source_of_budget";
        
        $conn->exec($sql);
        echo "<p class='success'>Successfully added ps_attribution column to the ppas_forms table!</p>";
    }
    
    // Display current table structure
    echo "<h2>Current Table Structure:</h2>";
    $result = $conn->query("DESCRIBE ppas_forms");
    
    echo "<table>";
    echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
    
    while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
        echo "<tr>";
        foreach ($row as $key => $value) {
            echo "<td>" . htmlspecialchars($value ?? 'NULL') . "</td>";
        }
        echo "</tr>";
    }
    
    echo "</table>";
    
    // Now check if our save_ppas.php script is configured to use the ps_attribution field
    echo "<h2>Step 4: Verifying save_ppas.php script configuration...</h2>";
    
    if (file_exists("save_ppas.php")) {
        $saveScript = file_get_contents("save_ppas.php");
        
        if (strpos($saveScript, 'ps_attribution') !== false) {
            echo "<p class='success'>The save_ppas.php script includes code to handle the ps_attribution field.</p>";
        } else {
            echo "<p class='warning'>The save_ppas.php script might not be properly configured to handle the ps_attribution field.</p>";
            echo "<p>Please make sure your save_ppas.php script includes logic to save the PS Attribution value.</p>";
        }
    } else {
        echo "<p class='error'>Could not find save_ppas.php script!</p>";
    }
    
    // Create a test entry with PS Attribution
    echo "<h2>Step 5: Creating a test entry to verify functionality...</h2>";
    
    try {
        // First check if we have a test entry already
        $checkSql = "SELECT COUNT(*) FROM ppas_forms WHERE title = 'PS Attribution Test Entry'";
        $count = $conn->query($checkSql)->fetchColumn();
        
        if ($count > 0) {
            echo "<p class='warning'>A test entry already exists. Skipping test creation.</p>";
        } else {
            // Insert a test entry
            $insertSql = "INSERT INTO ppas_forms (
                year, quarter, title, location, start_date, end_date, 
                start_time, end_time, total_duration, approved_budget, 
                source_of_budget, ps_attribution, created_by
            ) VALUES (
                '2024', '1', 'PS Attribution Test Entry', 'Test Location', 
                '2024-05-01', '2024-05-02', '09:00', '17:00', 16.00, 
                50000.00, 'GAA', 25000.00, 'test_script'
            )";
            
            $conn->exec($insertSql);
            echo "<p class='success'>Successfully created a test entry with ps_attribution value!</p>";
            
            // Verify the entry was added with PS Attribution
            $verifySql = "SELECT ps_attribution FROM ppas_forms WHERE title = 'PS Attribution Test Entry'";
            $psValue = $conn->query($verifySql)->fetchColumn();
            
            echo "<p>Test entry PS Attribution value: <strong>{$psValue}</strong></p>";
        }
    } catch (Exception $e) {
        echo "<p class='error'>Error creating test entry: " . $e->getMessage() . "</p>";
    }
    
    // Summary and next steps
    echo "<h2>Summary:</h2>";
    echo "<ul>";
    if (!$columnExists) {
        echo "<li class='success'>Added ps_attribution column to the ppas_forms table</li>";
    } else {
        echo "<li>ps_attribution column already existed</li>";
    }
    echo "<li>Verified database structure</li>";
    echo "<li>Checked save_ppas.php script configuration</li>";
    echo "<li>Tested PS Attribution functionality</li>";
    echo "</ul>";
    
    echo "<h2>Next Steps:</h2>";
    echo "<p>The PS Attribution field has been added to your database. Now you should:</p>";
    echo "<ol>";
    echo "<li>Fill out the PPAS form with PS Attribution values</li>";
    echo "<li>Submit the form and check if the PS Attribution value is saved</li>";
    echo "<li>If issues persist, check the php_errors.log file for detailed error messages</li>";
    echo "</ol>";
    
} catch (PDOException $e) {
    echo "<p class='error'>Database error: " . $e->getMessage() . "</p>";
}

echo "<div style='margin-top: 20px;'>";
echo "<a href='ppas.php' class='action-btn'>Go Back to PPAS Form</a> ";
echo "<a href='show_error_log.php' class='action-btn'>View Error Logs</a>";
echo "</div>";

echo "</body></html>";
?> 