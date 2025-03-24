<?php
session_start();
header('Content-Type: application/json');
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Debug log
error_log("get_campuses.php accessed");

// Check if user is logged in
if (!isset($_SESSION['username'])) {
    error_log("User not logged in in get_campuses.php");
    echo json_encode(['status' => 'error', 'message' => 'User not logged in']);
    exit();
}

// Database connection
require_once '../../includes/db_connection.php';

try {
    // Use PDO connection from db_connection.php
    $query = "SELECT DISTINCT campus as name FROM gpb_entries ORDER BY campus";
    
    // Debug log
    error_log("Query: " . $query);
    
    $stmt = $conn->prepare($query);
    $stmt->execute();
    
    $campuses = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $campuses[] = ['name' => $row['name']];
    }
    
    // Debug log
    error_log("Found campuses: " . json_encode($campuses));
    
    echo json_encode([
        'status' => 'success',
        'data' => $campuses
    ]);

} catch (Exception $e) {
    error_log("Error in get_campuses.php: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'An error occurred while fetching campuses: ' . $e->getMessage()
    ]);
} 