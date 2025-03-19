<?php
require_once '../config.php';

$campus_id = $_GET['campus_id'] ?? '';
$year_id = $_GET['year_id'] ?? '';
$response = ['success' => false, 'data' => []];

try {
    if (!$campus_id || !$year_id) {
        throw new Exception('Campus ID and Year ID are required');
    }

    // Get GAA data for the selected campus and year
    $stmt = $pdo->prepare("SELECT total_gaa, total_gad_fund FROM target WHERE campus = ? AND year = ?");
    $stmt->execute([$campus_id, $year_id]);
    $data = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($data) {
        $response['success'] = true;
        $response['total_gaa'] = $data['total_gaa'];
        $response['total_gad_fund'] = $data['total_gad_fund'];
    }
} catch(Exception $e) {
    error_log("Error fetching GAA data: " . $e->getMessage());
    $response['message'] = $e->getMessage();
}

header('Content-Type: application/json');
echo json_encode($response); 