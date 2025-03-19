<?php
header('Content-Type: application/json');
session_start();

// Check if user is logged in
if (!isset($_SESSION['username'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

// Get search term
$searchTerm = $_GET['term'] ?? '';

try {
    $conn = new PDO("mysql:host=localhost;dbname=gad_db", "root", "");
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Search for titles matching the search term
    $stmt = $conn->prepare("
        SELECT DISTINCT gender_issue as title
        FROM gpb_entries 
        WHERE gender_issue LIKE :searchTerm
        ORDER BY gender_issue ASC
        LIMIT 10
    ");
    
    $stmt->execute(['searchTerm' => "%$searchTerm%"]);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode($results);
    
} catch(PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}
?> 