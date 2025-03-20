<?php
require_once 'db_connection.php';

header('Content-Type: application/json');

$quarter = isset($_GET['quarter']) ? (int)$_GET['quarter'] : 0;

// Debug output
error_log("Requested quarter: " . $quarter);

if ($quarter < 1 || $quarter > 4) {
    echo json_encode(['error' => 'Invalid quarter']);
    exit;
}

try {
    // Get the current year
    $currentYear = date('Y');
    
    // Debug output
    error_log("Current year: " . $currentYear);
    error_log("Fetching PPAs for quarter: " . $quarter);
    
    // Convert quarter number to 'Q1', 'Q2' format
    $quarterText = "Q" . $quarter;
    
    $query = "
        SELECT 
            ppas_forms.id,
            ppas_forms.title,
            ppas_forms.start_date as date,
            ppas_forms.total_duration,
            ppas_forms.approved_budget,
            ppas_forms.source_of_budget,
            ppas_forms.ps_attribution,
            ppas_forms.quarter as quarter_num
        FROM ppas_forms 
        WHERE YEAR(start_date) = :year 
        AND quarter = :quarter 
        ORDER BY start_date ASC
    ";
    
    // Debug output
    error_log("SQL Query: " . $query);
    
    $stmt = $pdo->prepare($query);
    $params = [
        'year' => $currentYear,
        'quarter' => $quarterText  // Using Q1, Q2 format
    ];
    error_log("Query parameters: " . print_r($params, true));
    
    $stmt->execute($params);
    
    $ppas = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Debug output with count and results
    error_log("Found " . count($ppas) . " PPAs for quarter " . $quarter);
    error_log("Query results: " . print_r($ppas, true));
    
    if (empty($ppas)) {
        error_log("No PPAs found for quarter " . $quarter . " in year " . $currentYear);
        // Return empty array instead of error
        echo json_encode([]);
        exit;
    }
    
    // Add quarter information to each PPA
    foreach ($ppas as &$ppa) {
        $ppa['quarter'] = $quarter;
        $ppa['quarter_text'] = [
            1 => "Q1",
            2 => "Q2",
            3 => "Q3",
            4 => "Q4"
        ][$quarter];
    }
    
    echo json_encode($ppas);
} catch(PDOException $e) {
    error_log("Database error in get_ppas.php: " . $e->getMessage());
    error_log("Error code: " . $e->getCode());
    error_log("Error trace: " . $e->getTraceAsString());
    echo json_encode([
        'error' => 'Database error: ' . $e->getMessage(),
        'code' => $e->getCode()
    ]);
}
?> 