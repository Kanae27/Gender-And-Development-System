<?php
header('Content-Type: application/json');
session_start();

// Check if user is logged in
if (!isset($_SESSION['username'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "gad_db";

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
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
    
    $stmt->execute();
    $personnel = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode($personnel);
    
} catch(PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}
?> 