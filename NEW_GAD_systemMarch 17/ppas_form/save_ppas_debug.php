<?php
// Turn off output buffering for immediate response
ob_end_clean();

// Ensure we're sending JSON
header('Content-Type: application/json');

// Start session
session_start();

// For debugging
error_log("save_ppas_debug.php script started");

try {
    // Get JSON input
    $jsonInput = file_get_contents('php://input');
    error_log("Received raw input: " . $jsonInput);
    
    // Parse JSON
    $data = json_decode($jsonInput, true);
    
    // Simple validation - just check if we got valid JSON
    if ($data === null && json_last_error() !== JSON_ERROR_NONE) {
        error_log("Invalid JSON received: " . json_last_error_msg());
        throw new Exception("Invalid JSON format: " . json_last_error_msg());
    }
    
    // Log what data we received
    error_log("Parsed JSON data successfully");
    
    // Return success response - no database operations
    echo json_encode([
        'success' => true,
        'message' => 'Debug success - data was received but not saved to database',
        'data_received' => [
            'year' => $data['year'] ?? 'not provided',
            'quarter' => $data['quarter'] ?? 'not provided',
            'title' => $data['title'] ?? 'not provided',
            // Don't echo back the entire payload for security
        ]
    ]);
    
    error_log("Debug response sent successfully");
    
} catch (Exception $e) {
    error_log("Error in save_ppas_debug.php: " . $e->getMessage());
    
    // Return error response
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
}
?> 