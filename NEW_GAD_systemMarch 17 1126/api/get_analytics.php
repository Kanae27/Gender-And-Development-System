<?php
require_once '../config.php';
header('Content-Type: application/json');

$response = [
    'success' => false,
    'data' => [],
    'message' => ''
];

try {
    // Get parameters (optional)
    $campus = isset($_GET['campus']) ? $_GET['campus'] : null;
    $year = isset($_GET['year']) ? $_GET['year'] : null;

    // Build WHERE clause (optional filtering)
    $whereConditions = [];
    $params = [];
    
    if ($campus) {
        $whereConditions[] = "campus = ?";
        $params[] = $campus;
    }
    if ($year) {
        $whereConditions[] = "year = ?";
        $params[] = $year;
    }
    
    $whereClause = !empty($whereConditions) ? "WHERE " . implode(" AND ", $whereConditions) : "";

    // Main analytics query
    $query = "SELECT 
        COUNT(DISTINCT id) as total_entries,
        COUNT(DISTINCT generic_activity) as total_generic,
        COUNT(DISTINCT specific_activities) as total_specific,
        SUM(male_participants) as total_male,
        SUM(female_participants) as total_female,
        SUM(gad_budget) as total_budget,
        COUNT(CASE WHEN category = 'Client-Focused' THEN 1 END) as client_focused,
        COUNT(CASE WHEN category = 'Organization-Focused' THEN 1 END) as org_focused
        FROM gpb_entries
        $whereClause";

    // Prepare and execute using mysqli instead of PDO
    $stmt = $conn->prepare($query);
    
    // Bind parameters if any
    if (!empty($params)) {
        $types = str_repeat('s', count($params)); // Assuming all parameters are strings
        $stmt->bind_param($types, ...$params);
    }
    
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    
    // Get total GAA for budget percentage
    $gaaQuery = "SELECT SUM(total_gaa) as total_gaa FROM target";
    if (!empty($whereConditions)) {
        $gaaWhereConditions = [];
        if ($campus) $gaaWhereConditions[] = "campus = ?";
        if ($year) $gaaWhereConditions[] = "year = ?";
        
        if (!empty($gaaWhereConditions)) {
            $gaaQuery .= " WHERE " . implode(" AND ", $gaaWhereConditions);
        }
    }
    
    $gaaStmt = $conn->prepare($gaaQuery);
    
    // Bind parameters if any
    if (!empty($params)) {
        $types = str_repeat('s', count($params)); // Assuming all parameters are strings
        $gaaStmt->bind_param($types, ...$params);
    }
    
    $gaaStmt->execute();
    $gaaResult = $gaaStmt->get_result()->fetch_assoc();
    
    // Calculate budget percentage
    $totalGAA = floatval($gaaResult['total_gaa'] ?? 0);
    $totalBudget = floatval($result['total_budget'] ?? 0);
    $budgetPercentage = $totalGAA > 0 ? ($totalBudget / $totalGAA) * 100 : 0;

    // Format response data
    $response['data'] = [
        'activities' => [
            'total' => intval($result['total_entries'] ?? 0),
            'generic' => intval($result['total_generic'] ?? 0),
            'specific' => intval($result['total_specific'] ?? 0)
        ],
        'participants' => [
            'total' => intval(($result['total_male'] ?? 0) + ($result['total_female'] ?? 0)),
            'male' => intval($result['total_male'] ?? 0),
            'female' => intval($result['total_female'] ?? 0)
        ],
        'budget' => [
            'total' => $totalBudget,
            'percentage' => round($budgetPercentage, 2)
        ],
        'categories' => [
            'total' => intval(($result['client_focused'] ?? 0) + ($result['org_focused'] ?? 0)),
            'client_focused' => intval($result['client_focused'] ?? 0),
            'org_focused' => intval($result['org_focused'] ?? 0)
        ]
    ];

    $response['success'] = true;

} catch (Exception $e) {
    $response['message'] = $e->getMessage();
    error_log("Analytics Error: " . $e->getMessage());
}

echo json_encode($response); 