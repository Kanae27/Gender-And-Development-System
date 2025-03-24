<?php
require_once 'db_connection.php';

header('Content-Type: application/json');

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Debug output
error_log("Requested PPA ID: " . $id);

if ($id <= 0) {
    echo json_encode(['error' => 'Invalid PPA ID']);
    exit;
}

try {
    $query = "
        SELECT 
            start_date as date,
            end_date,
            total_duration,
            title,
            approved_budget,
            source_of_budget,
            ps_attribution,
            location
        FROM ppas_forms
        WHERE id = :id
    ";
    
    // Debug output
    error_log("SQL Query: " . $query);
    
    $stmt = $pdo->prepare($query);
    $stmt->execute(['id' => $id]);
    $ppa = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Debug output
    error_log("Query results: " . print_r($ppa, true));
    
    if ($ppa) {
        $ppa['date'] = date('Y-m-d', strtotime($ppa['date']));
        if ($ppa['end_date']) {
            $ppa['end_date'] = date('Y-m-d', strtotime($ppa['end_date']));
        }
        // Convert duration to hours if needed
        $ppa['total_duration'] = floatval($ppa['total_duration']);
        $ppa['ps_attribution'] = floatval($ppa['ps_attribution']);
        $ppa['approved_budget'] = floatval($ppa['approved_budget']);
        echo json_encode($ppa);
    } else {
        error_log("No PPA found with ID: " . $id);
        echo json_encode(['error' => 'PPA not found']);
    }
} catch(PDOException $e) {
    error_log("Database error: " . $e->getMessage());
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}
?> 