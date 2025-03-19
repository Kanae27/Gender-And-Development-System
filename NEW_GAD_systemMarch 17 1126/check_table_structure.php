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

try {
    // Create PDO connection
    $conn = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Display ppas_personnel table structure
    displayTableStructure("ppas_personnel", $conn);
    
    // Also display ppas_forms structure for reference
    displayTableStructure("ppas_forms", $conn);
    
} catch(PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?> 