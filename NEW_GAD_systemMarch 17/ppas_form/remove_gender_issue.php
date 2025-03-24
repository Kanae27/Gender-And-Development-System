<?php
require_once('../config.php');

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

try {
    // Check if gender_issue column exists
    $result = $conn->query("SHOW COLUMNS FROM ppas_forms LIKE 'gender_issue'");
    
    if ($result->num_rows > 0) {
        // Remove the gender_issue column
        $sql = "ALTER TABLE ppas_forms DROP COLUMN gender_issue";
        
        if ($conn->query($sql)) {
            echo "Successfully removed gender_issue column from ppas_forms table.<br>";
        } else {
            throw new Exception("Failed to remove gender_issue column: " . $conn->error);
        }
    } else {
        echo "gender_issue column does not exist in ppas_forms table.<br>";
    }
    
    echo "Migration completed successfully.";
    
} catch (Exception $e) {
    echo "Error during migration: " . $e->getMessage();
    error_log("Migration error: " . $e->getMessage());
} 