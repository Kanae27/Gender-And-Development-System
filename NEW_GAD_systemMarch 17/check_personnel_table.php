<?php
// Include database connection
require_once 'includes/db_connection.php';

// Function to display table structure
function displayTableStructure($tableName, $conn) {
    echo "Structure of table: $tableName\n";
    echo "-----------------------------\n";
    
    $stmt = $conn->prepare("DESCRIBE $tableName");
    $stmt->execute();
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "Field\tType\tNull\tKey\tDefault\tExtra\n";
    foreach ($columns as $column) {
        echo $column['Field'] . "\t" . 
             $column['Type'] . "\t" . 
             $column['Null'] . "\t" . 
             $column['Key'] . "\t" . 
             $column['Default'] . "\t" . 
             $column['Extra'] . "\n";
    }
    echo "\n";
}

// Function to show sample data
function showSampleData($tableName, $conn, $limit = 3) {
    echo "Sample data from $tableName (limited to $limit records):\n";
    echo "-----------------------------\n";
    
    $stmt = $conn->prepare("SELECT * FROM $tableName LIMIT :limit");
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->execute();
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($rows)) {
        echo "No data found in table.\n\n";
        return;
    }
    
    // Display column headers
    $headers = array_keys($rows[0]);
    foreach ($headers as $header) {
        echo $header . "\t";
    }
    echo "\n";
    
    // Display data rows
    foreach ($rows as $row) {
        foreach ($row as $value) {
            echo $value . "\t";
        }
        echo "\n";
    }
    echo "\n";
}

try {
    // Create PDO connection
    $conn = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Check if personnel table exists
    $stmt = $conn->prepare("SHOW TABLES LIKE 'personnel'");
    $stmt->execute();
    if ($stmt->rowCount() > 0) {
        // Display personnel table structure
        displayTableStructure("personnel", $conn);
        showSampleData("personnel", $conn);
    } else {
        echo "Table 'personnel' does not exist.\n\n";
    }
    
    // Check if personnel_list table exists
    $stmt = $conn->prepare("SHOW TABLES LIKE 'personnel_list'");
    $stmt->execute();
    if ($stmt->rowCount() > 0) {
        // Display personnel_list table structure
        displayTableStructure("personnel_list", $conn);
        showSampleData("personnel_list", $conn);
    } else {
        echo "Table 'personnel_list' does not exist.\n\n";
    }
    
} catch(PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?> 