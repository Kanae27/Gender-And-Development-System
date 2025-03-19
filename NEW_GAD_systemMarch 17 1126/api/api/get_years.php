<?php
require_once '../config.php';

$campus_id = $_GET['campus_id'] ?? '';
$response = ['success' => false, 'data' => []];

try {
    // If campus_id is provided, get years for that campus
    if ($campus_id) {
        $stmt = $pdo->prepare("SELECT DISTINCT year as id, year as year FROM target WHERE campus = ? ORDER BY year DESC");
        $stmt->execute([$campus_id]);
    } else {
        // If no campus_id is provided, get all years
        $stmt = $pdo->prepare("SELECT DISTINCT year as id, year as year FROM target ORDER BY year DESC");
        $stmt->execute();
    }
    
    $years = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if ($years) {
        $response['success'] = true;
        $response['data'] = $years;
    }
} catch(Exception $e) {
    error_log("Error fetching years: " . $e->getMessage());
    $response['message'] = $e->getMessage();
}

header('Content-Type: application/json');
echo json_encode($response); 