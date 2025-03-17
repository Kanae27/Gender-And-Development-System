<?php
require_once '../config.php';
header('Content-Type: application/json');

$response = ['success' => false, 'data' => []];

try {
    // Get unique campuses from target table
    $query = "SELECT DISTINCT campus as id, campus as name FROM target ORDER BY campus";
    $result = $conn->query($query);
    
    if ($result) {
        $campuses = [];
        while ($row = $result->fetch_assoc()) {
            $campuses[] = $row;
        }
        
        $response['success'] = true;
        $response['data'] = $campuses;
    }
} catch(Exception $e) {
    error_log("Error fetching campuses: " . $e->getMessage());
    $response['message'] = 'Database error occurred: ' . $e->getMessage();
}

echo json_encode($response); 