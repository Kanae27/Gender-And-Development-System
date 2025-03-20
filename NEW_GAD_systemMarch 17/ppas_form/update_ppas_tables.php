<?php
// Start output buffering
ob_start();

// Include database connection
require_once '../includes/db_connection.php';

try {
    // Modify the ppas_forms table to add gender_issue and type columns
    $conn->exec("
        ALTER TABLE ppas_forms 
        ADD COLUMN gender_issue VARCHAR(255) AFTER quarter,
        ADD COLUMN type ENUM('Program', 'Project') AFTER gender_issue,
        ADD COLUMN start_date DATE AFTER location,
        ADD COLUMN end_date DATE AFTER start_date
    ");

    // Clear any buffered output
    ob_end_clean();

    // Return success message
    header('Content-Type: application/json');
    echo json_encode(['success' => true, 'message' => 'Database tables updated successfully']);

} catch (PDOException $e) {
    // Clear any buffered output
    ob_end_clean();

    // Log the error
    error_log("Error updating tables: " . $e->getMessage());

    // Return error message
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Error updating database tables. Please check the error log.']);
}
?> 