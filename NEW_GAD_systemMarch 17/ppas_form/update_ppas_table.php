<?php
// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "gad_db";

try {
    // Connect to database
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Check if the date column exists
    $stmt = $conn->prepare("SHOW COLUMNS FROM ppas_forms LIKE 'date'");
    $stmt->execute();
    $dateColumnExists = $stmt->fetch() !== false;
    
    if ($dateColumnExists) {
        // Begin transaction
        $conn->beginTransaction();
        
        try {
            // First, add new columns
            $conn->exec("ALTER TABLE ppas_forms ADD COLUMN start_date DATE AFTER location");
            $conn->exec("ALTER TABLE ppas_forms ADD COLUMN end_date DATE AFTER start_date");
            
            // Add a column for duration metadata (details about the calculation)
            $conn->exec("ALTER TABLE ppas_forms ADD COLUMN duration_metadata TEXT AFTER total_duration");
            
            // Copy data from date to start_date (and end_date)
            $conn->exec("UPDATE ppas_forms SET start_date = date, end_date = date");
            
            // Drop the old date column
            $conn->exec("ALTER TABLE ppas_forms DROP COLUMN date");
            
            // Commit changes
            $conn->commit();
            
            echo "Successfully updated table structure: Changed 'date' to 'start_date' and 'end_date', added 'duration_metadata'";
        } catch (Exception $e) {
            $conn->rollBack();
            throw $e;
        }
    } else {
        // Check if start_date and end_date columns already exist
        $stmt = $conn->prepare("SHOW COLUMNS FROM ppas_forms LIKE 'start_date'");
        $stmt->execute();
        $startDateExists = $stmt->fetch() !== false;
        
        $stmt = $conn->prepare("SHOW COLUMNS FROM ppas_forms LIKE 'end_date'");
        $stmt->execute();
        $endDateExists = $stmt->fetch() !== false;
        
        if ($startDateExists && $endDateExists) {
            echo "Column structure is already updated. The table has start_date and end_date columns.";
        } else {
            echo "Cannot update table: 'date' column was not found in the ppas_forms table.";
        }
    }
} catch (PDOException $e) {
    echo "Database error: " . $e->getMessage();
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?> 