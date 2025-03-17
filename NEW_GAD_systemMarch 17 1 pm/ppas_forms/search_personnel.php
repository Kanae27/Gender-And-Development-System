<?php
require_once('../config.php');

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Default to empty string if term is not provided
$term = isset($_GET['term']) ? $_GET['term'] : '';

try {
    $query = "SELECT id, name, 
              COALESCE(gender, 'Not specified') as gender,
              COALESCE(academic_rank_id, 'Not specified') as academic_rank,
              COALESCE(monthly_salary, 0.00) as monthly_salary,
              COALESCE(hourly_rate, 0.00) as hourly_rate
              FROM personnel_list 
              WHERE name LIKE ? 
              ORDER BY name ASC
              LIMIT 20"; // Limit results to prevent overwhelming the dropdown

    $stmt = $conn->prepare($query);
    if (!$stmt) {
        throw new Exception("Prepare failed: " . $conn->error);
    }

    $searchTerm = "%$term%";
    $stmt->bind_param('s', $searchTerm);
    
    if (!$stmt->execute()) {
        throw new Exception("Execute failed: " . $stmt->error);
    }
    
    $result = $stmt->get_result();
    
    $personnel = array();
    while($row = $result->fetch_assoc()) {
        $personnel[] = array(
            'id' => (int)$row['id'],
            'name' => $row['name'],
            'gender' => $row['gender'],
            'academic_rank' => $row['academic_rank'],
            'monthly_salary' => $row['monthly_salary'],
            'hourly_rate' => $row['hourly_rate']
        );
    }
    
    // Log the results for debugging
    error_log("Personnel search results for term '$term': " . count($personnel) . " results found");
    
    header('Content-Type: application/json');
    echo json_encode($personnel);
    
} catch (Exception $e) {
    error_log("Error in search_personnel.php: " . $e->getMessage());
    header('Content-Type: application/json');
    echo json_encode([]);
}
?> 