<?php
require_once '../config.php';

// Set appropriate headers
header('Content-Type: application/json');

$campus_id = $_GET['campus_id'] ?? '';
$year_id = $_GET['year_id'] ?? '';
$response = [
    'success' => false, 
    'data' => [],
    'message' => '',
    'debug' => []
];

try {
    // Check if PDO connection exists
    if (!isset($pdo) || !$pdo) {
        throw new Exception('Database connection not established');
    }
    
    // Validate input parameters
    if (!$campus_id || !$year_id) {
        throw new Exception('Campus ID and Year ID are required');
    }
    
    // Log the request parameters
    error_log("Fetching GAA data for Campus: $campus_id, Year: $year_id");
    $response['debug']['params'] = ['campus' => $campus_id, 'year' => $year_id];
    
    // Check if target table exists
    $table_check = $pdo->query("SHOW TABLES LIKE 'target'");
    $table_exists = $table_check->rowCount() > 0;
    $response['debug']['table_exists'] = $table_exists;
    
    if (!$table_exists) {
        throw new Exception('Target table does not exist');
    }
    
    // Get GAA data for the selected campus and year
    $stmt = $pdo->prepare("SELECT total_gaa, total_gad_fund FROM target WHERE campus = ? AND year = ?");
    $stmt->execute([$campus_id, $year_id]);
    $data = $stmt->fetch(PDO::FETCH_ASSOC);
    
    $response['debug']['query_result'] = $data;
    
    if ($data) {
        $response['success'] = true;
        $response['total_gaa'] = $data['total_gaa'];
        $response['total_gad_fund'] = $data['total_gad_fund'];
        $response['message'] = 'GAA data retrieved successfully';
    } else {
        // No data found, but not an error - just return empty values
        $response['success'] = true;
        $response['total_gaa'] = '0';
        $response['total_gad_fund'] = '0';
        $response['message'] = 'No GAA data found for the specified campus and year';
    }
} catch(Exception $e) {
    $error_message = "Error fetching GAA data: " . $e->getMessage();
    error_log($error_message);
    $response['success'] = false;
    $response['message'] = $error_message;
}

echo json_encode($response);
exit; 