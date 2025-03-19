<?php
require_once('../includes/db_connection.php');
header('Content-Type: application/json');

// Get parameters from request
$year = isset($_GET['year']) ? $_GET['year'] : null;
$quarter = isset($_GET['quarter']) ? $_GET['quarter'] : null;

// Prepare response array
$response = [
    'success' => false,
    'data' => null,
    'message' => ''
];

try {
    if (!$year || !$quarter) {
        throw new Exception('Year and quarter are required');
    }

    // Get PPAS form data
    $sql = "SELECT pf.id, pf.year, pf.quarter, pf.title, pf.location, pf.start_date, pf.end_date 
            FROM ppas_forms pf 
            WHERE pf.year = :year AND pf.quarter = :quarter";
    
    $stmt = $conn->prepare($sql);
    $stmt->execute([
        ':year' => $year,
        ':quarter' => $quarter
    ]);

    $ppasData = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($ppasData) {
        // Get personnel data with their roles
        $personnelSql = "SELECT pp.id, pp.personnel_id, pp.personnel_name, pp.role 
                        FROM ppas_personnel pp
                        WHERE pp.ppas_id = :ppas_id";
        
        $stmt = $conn->prepare($personnelSql);
        $stmt->execute([':ppas_id' => $ppasData['id']]);
        $personnelData = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $response['success'] = true;
        $response['data'] = [
            'ppas' => $ppasData,
            'personnel' => $personnelData
        ];
    } else {
        $response['message'] = 'No data found for the specified year and quarter';
    }

} catch (Exception $e) {
    $response['message'] = $e->getMessage();
    $response['error'] = true;
}

echo json_encode($response); 