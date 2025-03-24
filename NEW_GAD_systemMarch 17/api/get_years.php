<?php
session_start();
header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['username'])) {
    echo json_encode(['success' => false, 'message' => 'User not logged in']);
    exit();
}

// Database connection
require_once '../includes/db_connection.php';

try {
    // Get campus parameter
    $campus = isset($_GET['campus_id']) ? $_GET['campus_id'] : null;

    // Prepare query to get distinct years for the campus
    $query = "SELECT DISTINCT year FROM gpb_entries";
    $params = [];
    
    if ($campus) {
        $query .= " WHERE campus = ?";
        $params[] = $campus;
    }
    
    $query .= " ORDER BY year DESC";
    
    $stmt = $conn->prepare($query);
    
    if (!empty($params)) {
        $stmt->bind_param(str_repeat('s', count($params)), ...$params);
    }
    
    $stmt->execute();
    $result = $stmt->get_result();
    
    $years = [];
    while ($row = $result->fetch_assoc()) {
        $years[] = ['year' => $row['year']];
    }
    
    echo json_encode([
        'success' => true,
        'data' => $years
    ]);

} catch (Exception $e) {
    error_log("Error in get_years.php: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'An error occurred while fetching years'
    ]);
}

$conn->close(); 