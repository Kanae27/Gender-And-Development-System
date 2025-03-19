<?php
require_once '../config.php';

$response = ['success' => false, 'data' => []];

try {
    // Get unique campuses from target table
    $stmt = $pdo->prepare("SELECT DISTINCT campus as id, campus as name FROM target ORDER BY campus");
    $stmt->execute();
    $campuses = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if ($campuses) {
        $response['success'] = true;
        $response['data'] = $campuses;
    }
} catch(PDOException $e) {
    error_log("Error fetching campuses: " . $e->getMessage());
    $response['message'] = 'Database error occurred';
}

header('Content-Type: application/json');
echo json_encode($response); 