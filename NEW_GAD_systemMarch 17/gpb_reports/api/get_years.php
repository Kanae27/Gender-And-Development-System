<?php
session_start();
header('Content-Type: application/json');
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Debug log
error_log("get_years.php accessed");

// Check if user is logged in
if (!isset($_SESSION['username'])) {
    error_log("User not logged in in get_years.php");
    echo json_encode(['status' => 'error', 'message' => 'User not logged in']);
    exit();
}

// Database connection
require_once '../../includes/db_connection.php';

try {
    // Get campus parameter
    $campus = isset($_GET['campus_id']) ? $_GET['campus_id'] : null;

    // Debug log
    error_log("Fetching years for campus: " . $campus);

    // Use PDO connection from db_connection.php
    $query = "SELECT DISTINCT year FROM gpb_entries";
    $params = [];
    
    if ($campus) {
        $query .= " WHERE campus = ?";
        $params[] = $campus;
    }
    
    $query .= " ORDER BY year DESC";
    
    // Debug log
    error_log("Query: " . $query);
    error_log("Parameters: " . ($campus ? $campus : 'none'));
    
    $stmt = $conn->prepare($query);
    
    if ($campus) {
        $stmt->execute([$campus]);
    } else {
        $stmt->execute();
    }
    
    $years = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $years[] = ['year' => $row['year']];
    }
    
    // Debug log
    error_log("Found years: " . json_encode($years));
    
    echo json_encode([
        'status' => 'success',
        'data' => $years
    ]);

} catch (Exception $e) {
    error_log("Error in get_years.php: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'An error occurred while fetching years: ' . $e->getMessage()
    ]);
}

// PDO connections are automatically closed when the script ends
$stmt = null;
$conn = null; 