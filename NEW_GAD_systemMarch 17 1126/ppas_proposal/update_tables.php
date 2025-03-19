<?php
// Database update script for GAD proposal tables
// Turn on error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "gad_db";

echo "Starting database update script for GAD proposal tables...\n";

try {
    // Connect to database
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "Connected to database successfully.\n";
    
    // Check if the table exists first
    $stmt = $conn->query("SHOW TABLES LIKE 'gad_proposal_activities'");
    if ($stmt->rowCount() == 0) {
        echo "Table 'gad_proposal_activities' does not exist. Creating table...\n";
        
        // Create the table if it doesn't exist
        $conn->exec("CREATE TABLE gad_proposal_activities (
            id INT PRIMARY KEY AUTO_INCREMENT,
            proposal_id INT NOT NULL,
            title VARCHAR(255) NOT NULL,
            details TEXT,
            sequence INT NOT NULL DEFAULT 0,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )");
        
        echo "Table 'gad_proposal_activities' created successfully.\n";
    } else {
        echo "Table 'gad_proposal_activities' exists.\n";
        
        // Check if gad_proposal_activities needs updating
        $stmt = $conn->query("SHOW COLUMNS FROM gad_proposal_activities LIKE 'created_at'");
        $columnExists = $stmt->rowCount() > 0;
        
        echo "Column check for 'created_at': " . ($columnExists ? "Found" : "Not found") . "\n";
        
        if (!$columnExists) {
            echo "Adding 'created_at' column to gad_proposal_activities table...\n";
            $conn->exec("ALTER TABLE gad_proposal_activities ADD COLUMN created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP");
            echo "Column 'created_at' added successfully.\n";
        } else {
            echo "Column 'created_at' already exists in gad_proposal_activities table.\n";
        }
        
        // Check sequence column (in case it's missing)
        $stmt = $conn->query("SHOW COLUMNS FROM gad_proposal_activities LIKE 'sequence'");
        $sequenceExists = $stmt->rowCount() > 0;
        
        echo "Column check for 'sequence': " . ($sequenceExists ? "Found" : "Not found") . "\n";
        
        if (!$sequenceExists) {
            echo "Adding 'sequence' column to gad_proposal_activities table...\n";
            $conn->exec("ALTER TABLE gad_proposal_activities ADD COLUMN sequence INT NOT NULL DEFAULT 0");
            echo "Column 'sequence' added successfully.\n";
        } else {
            echo "Column 'sequence' already exists in gad_proposal_activities table.\n";
        }
    }
    
    echo "Current columns in gad_proposal_activities table:\n";
    $stmt = $conn->query("DESCRIBE gad_proposal_activities");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "- " . $row['Field'] . " (" . $row['Type'] . ")\n";
    }
    
    echo "\nDatabase update completed successfully.\n";
    
} catch(PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?> 