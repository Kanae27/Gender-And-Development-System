<?php
require_once '../config.php';
header('Content-Type: application/json');

$campus_id = $_GET['campus_id'] ?? '';
$response = ['success' => false, 'data' => []];

try {
    // If campus_id is provided, get years for that campus
    if ($campus_id) {
        $query = "SELECT DISTINCT year as id, year as year FROM target WHERE campus = ? ORDER BY year DESC";
        $stmt = $conn->prepare($query);
        $stmt->bind_param('s', $campus_id);
        $stmt->execute();
        $result = $stmt->get_result();
    } else {
        // If no campus_id is provided, get all years
        $query = "SELECT DISTINCT year as id, year as year FROM target ORDER BY year DESC";
        $result = $conn->query($query);
    }
    
    $years = [];
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $years[] = $row;
        }
    }
    
    if (!empty($years)) {
        $response['success'] = true;
        $response['data'] = $years;
    }
} catch(Exception $e) {
    error_log("Error fetching years: " . $e->getMessage());
    $response['message'] = $e->getMessage();
}

echo json_encode($response); 