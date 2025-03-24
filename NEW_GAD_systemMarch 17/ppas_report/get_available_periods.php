<?php
// Disable error reporting in production
error_reporting(0);
ini_set('display_errors', 0);

header('Content-Type: application/json');
require_once '../config.php';

try {
    $db = $pdo;
    $campus = isset($_GET['campus']) ? $_GET['campus'] : null;

    $query = "
        SELECT DISTINCT year, quarter 
        FROM ppas_forms 
        WHERE 1=1
    ";

    if ($campus && $campus !== 'Central') {
        $query .= " AND created_by = :campus";
    }

    $query .= " ORDER BY year DESC, quarter ASC";

    $stmt = $db->prepare($query);
    
    if ($campus && $campus !== 'Central') {
        $stmt->bindParam(':campus', $campus, PDO::PARAM_STR);
    }

    $stmt->execute();
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Process results into a structured format
    $periods = [];
    foreach ($results as $row) {
        if (!isset($periods[$row['year']])) {
            $periods[$row['year']] = [];
        }
        $periods[$row['year']][] = $row['quarter'];
    }

    echo json_encode(['success' => true, 'data' => $periods]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
} 