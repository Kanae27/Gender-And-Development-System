<?php
// Set content type to HTML for better display
header('Content-Type: text/html; charset=utf-8');

echo "<html><head><title>Database Structure Check</title>
<style>
    body { font-family: Arial, sans-serif; margin: 20px; line-height: 1.6; }
    .success { color: green; font-weight: bold; }
    .error { color: red; font-weight: bold; }
    .warning { color: orange; font-weight: bold; }
    pre { background-color: #f5f5f5; padding: 10px; border-radius: 5px; }
    h1, h2 { color: #333; }
    table { border-collapse: collapse; width: 100%; margin-top: 20px; }
    th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
    th { background-color: #f2f2f2; }
    tr:nth-child(even) { background-color: #f9f9f9; }
</style>
</head><body>";

echo "<h1>PPAS Form Database Structure Check</h1>";

// Database connection parameters
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "gad_db";

try {
    echo "<h2>Testing database connection...</h2>";
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "<p class='success'>Successfully connected to database: $dbname</p>";

    // Check if ppas_forms table exists
    echo "<h2>Checking for ppas_forms table...</h2>";
    $tables = $conn->query("SHOW TABLES LIKE 'ppas_forms'")->fetchAll();
    if (count($tables) === 0) {
        echo "<p class='error'>Error: ppas_forms table does not exist!</p>";
    } else {
        echo "<p class='success'>ppas_forms table exists.</p>";
        
        // Get table structure
        echo "<h2>ppas_forms Table Structure:</h2>";
        $columns = $conn->query("DESCRIBE ppas_forms")->fetchAll(PDO::FETCH_ASSOC);
        
        echo "<table>";
        echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
        
        foreach ($columns as $column) {
            echo "<tr>";
            foreach ($column as $key => $value) {
                echo "<td>" . htmlspecialchars($value ?? 'NULL') . "</td>";
            }
            echo "</tr>";
        }
        echo "</table>";
        
        // Check for required columns
        $requiredColumns = [
            'year', 'quarter', 'title', 'location', 
            'start_date', 'end_date', 'start_time', 'end_time', 
            'has_lunch_break', 'has_am_break', 'has_pm_break', 
            'total_duration', 'approved_budget', 'source_of_budget',
            'ps_attribution', 'duration_metadata'
        ];
        
        $missingColumns = [];
        foreach ($requiredColumns as $requiredColumn) {
            $found = false;
            foreach ($columns as $column) {
                if ($column['Field'] === $requiredColumn) {
                    $found = true;
                    break;
                }
            }
            if (!$found) {
                $missingColumns[] = $requiredColumn;
            }
        }
        
        if (count($missingColumns) > 0) {
            echo "<h2>Missing Columns:</h2>";
            echo "<p class='warning'>The following required columns are missing in the ppas_forms table:</p>";
            echo "<ul>";
            foreach ($missingColumns as $missingColumn) {
                echo "<li>" . htmlspecialchars($missingColumn) . "</li>";
            }
            echo "</ul>";
            echo "<p>You should run the <a href='add_missing_columns.php'>add_missing_columns.php</a> script to add these columns.</p>";
        } else {
            echo "<p class='success'>All required columns are present in the ppas_forms table.</p>";
        }
    }
    
    // Check if ppas_personnel table exists
    echo "<h2>Checking for ppas_personnel table...</h2>";
    $tables = $conn->query("SHOW TABLES LIKE 'ppas_personnel'")->fetchAll();
    if (count($tables) === 0) {
        echo "<p class='error'>Error: ppas_personnel table does not exist!</p>";
    } else {
        echo "<p class='success'>ppas_personnel table exists.</p>";
        
        // Get table structure
        echo "<h2>ppas_personnel Table Structure:</h2>";
        $columns = $conn->query("DESCRIBE ppas_personnel")->fetchAll(PDO::FETCH_ASSOC);
        
        echo "<table>";
        echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
        
        foreach ($columns as $column) {
            echo "<tr>";
            foreach ($column as $key => $value) {
                echo "<td>" . htmlspecialchars($value ?? 'NULL') . "</td>";
            }
            echo "</tr>";
        }
        echo "</table>";
    }

    // Check log file for recent errors
    echo "<h2>Recent PHP Errors (if available):</h2>";
    $logFile = dirname(__FILE__) . '/php_errors.log';
    if (file_exists($logFile)) {
        $logs = file_exists($logFile) ? file($logFile) : [];
        if (count($logs) > 0) {
            echo "<pre>";
            // Display the last 20 lines or all if less than 20
            $lastLogs = array_slice($logs, -20);
            foreach ($lastLogs as $log) {
                echo htmlspecialchars($log);
            }
            echo "</pre>";
        } else {
            echo "<p>No errors in the log file.</p>";
        }
    } else {
        echo "<p>Log file does not exist yet.</p>";
    }
    
    echo "<h2>How to fix issues:</h2>";
    echo "<ol>";
    echo "<li>Run <a href='add_missing_columns.php'>add_missing_columns.php</a> to add any missing database columns</li>";
    echo "<li>Check if your JSON data is being properly formatted in the AJAX request</li>";
    echo "<li>Make sure all required fields in the form are filled out</li>";
    echo "</ol>";

} catch(PDOException $e) {
    echo "<p class='error'>Database error: " . htmlspecialchars($e->getMessage()) . "</p>";
}

echo "<p><a href='ppas.php'>Go back to PPAS Form</a></p>";
echo "</body></html>";
?> 