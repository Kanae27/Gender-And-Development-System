<?php
require_once 'db_connection.php';

try {
    // Check ppas_forms table
    $stmt = $pdo->query("SHOW TABLES LIKE 'ppas_forms'");
    $ppasTableExists = $stmt->rowCount() > 0;
    
    echo "ppas_forms table exists: " . ($ppasTableExists ? "Yes" : "No") . "\n";
    
    if ($ppasTableExists) {
        $stmt = $pdo->query("DESCRIBE ppas_forms");
        echo "\nppas_forms table structure:\n";
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            echo $row['Field'] . " - " . $row['Type'] . "\n";
        }
    }
    
    // Check academic_ranks table
    $stmt = $pdo->query("SHOW TABLES LIKE 'academic_ranks'");
    $ranksTableExists = $stmt->rowCount() > 0;
    
    echo "\nacademic_ranks table exists: " . ($ranksTableExists ? "Yes" : "No") . "\n";
    
    if ($ranksTableExists) {
        $stmt = $pdo->query("DESCRIBE academic_ranks");
        echo "\nacademic_ranks table structure:\n";
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            echo $row['Field'] . " - " . $row['Type'] . "\n";
        }
        
        // Check if there's any data
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM academic_ranks");
        $count = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
        echo "\nNumber of records in academic_ranks: " . $count . "\n";
    }
    
    // Check personnel table
    $stmt = $pdo->query("SHOW TABLES LIKE 'personnel'");
    $personnelTableExists = $stmt->rowCount() > 0;
    
    echo "\npersonnel table exists: " . ($personnelTableExists ? "Yes" : "No") . "\n";
    
    if ($personnelTableExists) {
        $stmt = $pdo->query("DESCRIBE personnel");
        echo "\npersonnel table structure:\n";
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            echo $row['Field'] . " - " . $row['Type'] . "\n";
        }
        
        // Check if there's any data
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM personnel");
        $count = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
        echo "\nNumber of records in personnel: " . $count . "\n";
    }
    
} catch(PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?> 