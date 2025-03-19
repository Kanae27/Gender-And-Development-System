<?php
require_once '../config.php';

// Set appropriate headers
header('Content-Type: application/json');

// Initialize response
$response = [
    'success' => false,
    'data' => [],
    'error' => null
];

try {
    $term = isset($_GET['term']) ? trim($_GET['term']) : '';
    
    // Base query to get distinct gender issues
    $query = "SELECT DISTINCT gender_issue FROM gpb_entries WHERE gender_issue IS NOT NULL AND gender_issue != ''";
    $params = [];
    
    // Add search condition if term is provided
    if ($term !== '') {
        $query .= " AND gender_issue LIKE ?";
        $params[] = "%$term%";
    }
    
    $query .= " ORDER BY gender_issue ASC";
    
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    
    $issues = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        if (!empty($row['gender_issue'])) {
            $issues[] = $row['gender_issue'];
        }
    }
    
    $response['success'] = true;
    $response['data'] = $issues;

} catch (Exception $e) {
    $response['error'] = $e->getMessage();
    error_log("Error in search_gender_issues.php: " . $e->getMessage());
}

// Return JSON response
echo json_encode($response);
exit; 