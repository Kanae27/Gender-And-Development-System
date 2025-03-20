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
    require_once('../includes/db_connection.php');
    
    // Search for gender issues matching the search term
    $stmt = $conn->prepare("
        SELECT DISTINCT gender_issue as value
        FROM gpb_entries 
        WHERE gender_issue LIKE :searchTerm
        AND gender_issue IS NOT NULL
        AND gender_issue != ''
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