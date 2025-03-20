<?php
// Turn off all error reporting and HTML errors for production
error_reporting(0);
ini_set('display_errors', 0);
ini_set('html_errors', 0);

// Ensure we're sending JSON response
header('Content-Type: application/json');

// Database connection parameters
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "gad_db";

try {
    // Connect to database
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Check if the ps_attribution column already exists in ppas_forms table
    $checkColumnQuery = "SHOW COLUMNS FROM ppas_forms LIKE 'ps_attribution'";
    $stmt = $conn->prepare($checkColumnQuery);
    $stmt->execute();
    $columnExists = $stmt->rowCount() > 0;
    
    if (!$columnExists) {
        // Add ps_attribution column to ppas_forms table
        $alterTableQuery = "ALTER TABLE ppas_forms ADD COLUMN ps_attribution DECIMAL(10,2) DEFAULT 0.00 AFTER source_of_budget";
        $conn->exec($alterTableQuery);
        
        // Return success message
        echo json_encode([
            'success' => true,
            'message' => 'PS Attribution column added successfully to ppas_forms table.'
        ]);
    } else {
        // Column already exists
        echo json_encode([
            'success' => true,
            'message' => 'PS Attribution column already exists in ppas_forms table.'
        ]);
    }
} catch (PDOException $e) {
    // Return error message
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
?> 