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
    // Get JSON input and parse
    $jsonInput = file_get_contents('php://input');
    $data = json_decode($jsonInput, true);
    
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception('Invalid JSON data: ' . json_last_error_msg());
    }
    
    // Validate year and quarter
    if (!isset($data['year']) || !isset($data['quarter'])) {
        throw new Exception('Year and quarter parameters are required');
    }
    
    // Connect to database
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Check if a record exists
    $stmt = $conn->prepare("SELECT COUNT(*) FROM ppas_forms WHERE year = ? AND quarter = ?");
    $stmt->execute([$data['year'], $data['quarter']]);
    $count = $stmt->fetchColumn();
    
    // Return result
    echo json_encode([
        'success' => true,
        'exists' => ($count > 0),
        'year' => $data['year'],
        'quarter' => $data['quarter']
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?> 