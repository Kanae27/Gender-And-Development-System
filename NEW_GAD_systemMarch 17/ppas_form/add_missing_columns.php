<?php
// Turn on error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('html_errors', 1);

// Set content type to HTML for better display
header('Content-Type: text/html; charset=utf-8');

echo "<html><head><title>Adding PS Attribution Field</title>
<style>
    body { font-family: Arial, sans-serif; margin: 20px; line-height: 1.6; }
    .success { color: green; font-weight: bold; }
    .error { color: red; font-weight: bold; }
    pre { background-color: #f5f5f5; padding: 10px; border-radius: 5px; }
    h1, h2 { color: #333; }
</style>
</head><body>";

echo "<h1>Adding PS Attribution Field to Database</h1>";

// Database connection parameters
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "gad_db";

try {
    echo "<h2>Connecting to database...</h2>";
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "<p class='success'>Successfully connected to database: $dbname</p>";

    // Check if ps_attribution column exists
    echo "<h2>Checking for PS Attribution column...</h2>";
    $result = $conn->query("SHOW COLUMNS FROM ppas_forms LIKE 'ps_attribution'");
    $ps_attribution_exists = $result->rowCount() > 0;

    if ($ps_attribution_exists) {
        echo "<p class='success'>PS Attribution column already exists in ppas_forms table.</p>";
    } else {
        echo "<p>PS Attribution column doesn't exist. Adding it now...</p>";
        // Add ps_attribution column
        $sql = "ALTER TABLE ppas_forms ADD COLUMN ps_attribution DECIMAL(12,2) DEFAULT 0.00 AFTER source_of_budget";
        $conn->exec($sql);
        echo "<p class='success'>Successfully added PS Attribution column to ppas_forms table.</p>";
    }

    // Check if duration columns exist
    echo "<h2>Checking duration columns...</h2>";
    
    // Check for total_duration column format
    $result = $conn->query("SHOW COLUMNS FROM ppas_forms WHERE Field = 'total_duration'");
    $total_duration_info = $result->fetch(PDO::FETCH_ASSOC);
    
    echo "<p>Current total_duration column type: " . ($total_duration_info ? $total_duration_info['Type'] : 'Not found') . "</p>";
    
    // Make sure total_duration is DECIMAL with enough precision
    if ($total_duration_info && $total_duration_info['Type'] != 'decimal(10,2)') {
        echo "<p>Modifying total_duration column to ensure it can store decimal values...</p>";
        $sql = "ALTER TABLE ppas_forms MODIFY COLUMN total_duration DECIMAL(10,2) NOT NULL DEFAULT 0.00";
        $conn->exec($sql);
        echo "<p class='success'>Successfully modified total_duration column.</p>";
    }
    
    // Check if duration_metadata column exists
    $result = $conn->query("SHOW COLUMNS FROM ppas_forms LIKE 'duration_metadata'");
    $duration_metadata_exists = $result->rowCount() > 0;
    
    if (!$duration_metadata_exists) {
        echo "<p>Duration metadata column doesn't exist. Adding it now...</p>";
        $sql = "ALTER TABLE ppas_forms ADD COLUMN duration_metadata VARCHAR(255) NULL AFTER total_duration";
        $conn->exec($sql);
        echo "<p class='success'>Successfully added duration_metadata column to ppas_forms table.</p>";
    } else {
        echo "<p class='success'>Duration metadata column already exists.</p>";
    }

    echo "<h2>Database Update Summary</h2>";
    echo "<p>The following changes have been applied to your database:</p>";
    echo "<ul>";
    if (!$ps_attribution_exists) {
        echo "<li>Added ps_attribution column to ppas_forms table</li>";
    }
    if ($total_duration_info && $total_duration_info['Type'] != 'decimal(10,2)') {
        echo "<li>Modified total_duration column to ensure it can store decimal values</li>";
    }
    if (!$duration_metadata_exists) {
        echo "<li>Added duration_metadata column to ppas_forms table</li>";
    }
    
    if ($ps_attribution_exists && $duration_metadata_exists && $total_duration_info && $total_duration_info['Type'] == 'decimal(10,2)') {
        echo "<li>No changes were needed - your database already has all required columns</li>";
    }
    echo "</ul>";

    echo "<h2>Current Table Structure</h2>";
    $columns = $conn->query("DESCRIBE ppas_forms")->fetchAll(PDO::FETCH_ASSOC);
    echo "<pre>";
    foreach ($columns as $column) {
        echo "{$column['Field']} - {$column['Type']}" . ($column['Null'] == 'NO' ? ' (NOT NULL)' : '') . "\n";
    }
    echo "</pre>";

} catch(PDOException $e) {
    echo "<p class='error'>Error: " . $e->getMessage() . "</p>";
}

echo "<p><a href='ppas.php'>Go back to PPAS Form</a></p>";
echo "</body></html>";
?> 