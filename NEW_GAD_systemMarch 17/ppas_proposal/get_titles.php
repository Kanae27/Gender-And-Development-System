<?php
require_once('../includes/db_connection.php');
header('Content-Type: application/json');

// Get parameters from request
$year = isset($_GET['year']) ? $_GET['year'] : null;
$quarter = isset($_GET['quarter']) ? $_GET['quarter'] : null;

// Prepare response array
$response = [
    'success' => false,
    'titles' => [],
    'message' => ''
];

try {
    if (!$year || !$quarter) {
        throw new Exception('Year and quarter are required');
    }

    // Get all titles for the specified year and quarter
    $sql = "SELECT id, title FROM ppas_forms 
            WHERE year = :year AND quarter = :quarter
            ORDER BY title ASC";
    
    $stmt = $conn->prepare($sql);
    $stmt->execute([
        ':year' => $year,
        ':quarter' => $quarter
    ]);

    $titles = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if ($titles && count($titles) > 0) {
        $response['success'] = true;
        $response['titles'] = $titles;
        $response['message'] = count($titles) . ' activities found';
    } else {
        $response['message'] = 'No activities found for the specified year and quarter';
    }

} catch (Exception $e) {
    $response['message'] = 'Error: ' . $e->getMessage();
}

echo json_encode($response); 