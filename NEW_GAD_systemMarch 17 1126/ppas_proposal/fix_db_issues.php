<?php
// Database update script to fix GAD proposal structure issues
// Turn on error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "gad_db";

echo "Starting comprehensive database fix script...\n";

try {
    // Connect to database
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "Connected to database successfully.\n";
    
    // Ensure gad_proposal_activities has the right structure
    $conn->exec("ALTER TABLE gad_proposal_activities MODIFY COLUMN created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP");
    echo "Updated created_at column in gad_proposal_activities table.\n";
    
    // Ensure gad_proposal_personnel has the right structure
    $conn->exec("ALTER TABLE gad_proposal_personnel MODIFY COLUMN created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP");
    echo "Updated created_at column in gad_proposal_personnel table.\n";
    
    // Ensure gad_proposals has the right structure for created_at
    $conn->exec("ALTER TABLE gad_proposals MODIFY COLUMN created_at DATETIME DEFAULT CURRENT_TIMESTAMP");
    echo "Updated created_at column in gad_proposals table.\n";
    
    echo "\nDatabase structure update completed successfully.\n";
    echo "\nYou should now be able to save GAD proposals without the 'Unknown column created_at' error.\n";
    
} catch(PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?> 