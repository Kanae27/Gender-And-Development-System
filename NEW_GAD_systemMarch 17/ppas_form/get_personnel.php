<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: application/json');
session_start();

// Check if user is logged in
if (!isset($_SESSION['username'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

// Database connection
try {
    // Log connection attempt
    error_log("Attempting database connection...");
    
    $conn = new PDO("mysql:host=localhost;dbname=gad_db", "root", "");
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Log successful connection
    error_log("Database connection successful");
    
    // Query to get personnel data with academic rank information
    $stmt = $conn->prepare("
        SELECT 
            p.id,
            p.name,
            p.gender,
            a.rank_name as academic_rank,
            p.monthly_salary,
            p.hourly_rate
        FROM personnel_list p
        LEFT JOIN academic_rank a ON p.academic_rank_id = a.id
        ORDER BY p.name ASC
    ");
    
    // Log query execution attempt
    error_log("Executing personnel query...");
    
    $stmt->execute();
    $personnel = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Log successful query
    error_log("Query executed successfully. Found " . count($personnel) . " records");
    
    echo json_encode($personnel);
    
} catch(PDOException $e) {
    // Log the detailed error
    error_log("Database Error: " . $e->getMessage());
    
    http_response_code(500);
    echo json_encode([
        'error' => 'Database error occurred',
        'message' => $e->getMessage(),
        'code' => $e->getCode()
    ]);
} catch(Exception $e) {
    // Log any other errors
    error_log("General Error: " . $e->getMessage());
    
    http_response_code(500);
    echo json_encode([
        'error' => 'An error occurred',
        'message' => $e->getMessage()
    ]);
}
?> 