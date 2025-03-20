<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('html_errors', 1);

// Set content type to HTML for better display
header('Content-Type: text/html; charset=utf-8');

// Database connection parameters
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "gad_db";

echo "<h1>Database Connection Test</h1>";

try {
    // Test database connection
    echo "<h2>Connecting to database...</h2>";
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "<p style='color:green'>✓ Successfully connected to database: $dbname</p>";
    
    // Check for ppas_forms table
    echo "<h2>Checking 'ppas_forms' table...</h2>";
    $result = $conn->query("SHOW TABLES LIKE 'ppas_forms'");
    
    if ($result->rowCount() > 0) {
        echo "<p style='color:green'>✓ Table 'ppas_forms' exists</p>";
        
        // Check table structure
        echo "<h3>Checking table structure:</h3>";
        $result = $conn->query("DESCRIBE ppas_forms");
        echo "<table border='1' cellpadding='5'>";
        echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
        
        $requiredColumns = [
            'gender_issue', 'type', 'start_date', 'end_date', 'ps_attribution'
        ];
        $missingColumns = $requiredColumns;
        
        while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
            echo "<tr>";
            foreach ($row as $key => $value) {
                echo "<td>" . htmlspecialchars($value ?? 'NULL') . "</td>";
            }
            echo "</tr>";
            
            // Check if this is one of our required columns
            if (in_array($row['Field'], $requiredColumns)) {
                $index = array_search($row['Field'], $missingColumns);
                if ($index !== false) {
                    unset($missingColumns[$index]);
                }
            }
        }
        echo "</table>";
        
        // Report on missing columns
        if (count($missingColumns) > 0) {
            echo "<p style='color:red'>⚠ Missing columns: " . implode(', ', $missingColumns) . "</p>";
            echo "<p>Please run the <a href='update_db_structure.php' target='_blank'>update_db_structure.php</a> script to add these columns.</p>";
        } else {
            echo "<p style='color:green'>✓ All required columns exist</p>";
        }
    } else {
        echo "<p style='color:red'>⚠ Table 'ppas_forms' does not exist</p>";
        echo "<p>Please run the <a href='update_db_structure.php' target='_blank'>update_db_structure.php</a> script to create the table.</p>";
    }
    
} catch(PDOException $e) {
    echo "<p style='color:red'>❌ Connection failed: " . $e->getMessage() . "</p>";
}

echo "<h2>Actions</h2>";
echo "<ul>";
echo "<li><a href='update_db_structure.php' target='_blank'>Run Database Update Script</a></li>";
echo "<li><a href='test_db_connection.php' target='_blank'>Refresh this Test</a></li>";
echo "<li><a href='../ppas_form/ppas.php' target='_blank'>Go to PPAS Form</a></li>";
echo "</ul>";
?> 