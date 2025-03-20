<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: text/plain');

// Database connection parameters
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "gad_db";

echo "Database Diagnostic Report\n";
echo "=========================\n\n";

try {
    // Connect to database
    echo "Attempting to connect to database: $dbname\n";
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "Connection successful!\n\n";
    
    // List all tables
    echo "Tables in database:\n";
    echo "-----------------\n";
    $tables = $conn->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    if (count($tables) > 0) {
        foreach ($tables as $table) {
            echo "- $table\n";
        }
    } else {
        echo "No tables found in database.\n";
    }
    echo "\n";
    
    // Check for required tables
    $requiredTables = ['ppas_forms', 'ppas_personnel', 'ppas_beneficiaries', 'ppas_sdgs'];
    $missingTables = [];
    
    echo "Required tables check:\n";
    echo "--------------------\n";
    foreach ($requiredTables as $table) {
        $exists = in_array($table, $tables);
        echo "- $table: " . ($exists ? "EXISTS" : "MISSING") . "\n";
        if (!$exists) {
            $missingTables[] = $table;
        }
    }
    echo "\n";
    
    // If ppas_forms exists, check its structure
    if (in_array('ppas_forms', $tables)) {
        echo "Structure of ppas_forms table:\n";
        echo "----------------------------\n";
        $columns = $conn->query("DESCRIBE ppas_forms")->fetchAll(PDO::FETCH_ASSOC);
        
        $requiredColumns = [
            'gender_issue', 'type', 'start_date', 'end_date', 'ps_attribution'
        ];
        $missingColumns = $requiredColumns;
        
        foreach ($columns as $column) {
            echo "- {$column['Field']} ({$column['Type']})\n";
            
            // Check if this is one of our required columns
            if (in_array($column['Field'], $requiredColumns)) {
                $index = array_search($column['Field'], $missingColumns);
                if ($index !== false) {
                    unset($missingColumns[$index]);
                }
            }
        }
        
        echo "\nMissing columns in ppas_forms: ";
        if (count($missingColumns) > 0) {
            echo implode(', ', $missingColumns) . "\n";
        } else {
            echo "NONE - All required columns exist\n";
        }
    }
    
    echo "\nDiagnostic Summary:\n";
    echo "-----------------\n";
    if (count($missingTables) > 0) {
        echo "MISSING TABLES: " . implode(', ', $missingTables) . "\n";
        echo "Please run update_db_structure.php to create missing tables.\n";
    } else if (isset($missingColumns) && count($missingColumns) > 0) {
        echo "MISSING COLUMNS: " . implode(', ', $missingColumns) . "\n";
        echo "Please run update_db_structure.php to add missing columns.\n";
    } else {
        echo "✓ All required tables and columns exist\n";
    }
    
} catch(PDOException $e) {
    echo "ERROR: Could not connect to database\n";
    echo $e->getMessage() . "\n";
}

echo "\nTo update the database structure, visit: update_db_structure.php\n";
?> 