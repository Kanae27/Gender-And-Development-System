<?php
require_once('../config.php');

// Get search parameters
$term = isset($_GET['term']) ? $_GET['term'] : '';
$year = isset($_GET['year']) ? $_GET['year'] : '';
$quarter = isset($_GET['quarter']) ? $_GET['quarter'] : '';
$type = isset($_GET['type']) ? $_GET['type'] : '';

try {
    // Base query
    $sql = "SELECT DISTINCT ";
    if ($type === 'program') {
        $sql .= "program";
    } else {
        $sql .= "project";
    }
    $sql .= " FROM gpb_entries WHERE 1=1";

    // Add search conditions
    if (!empty($term)) {
        if ($type === 'program') {
            $sql .= " AND program LIKE ?";
        } else {
            $sql .= " AND project LIKE ?";
        }
    }
    
    // Add year filter if provided
    if (!empty($year)) {
        $sql .= " AND year = ?";
    }
    
    // Add quarter filter if provided
    if (!empty($quarter)) {
        $sql .= " AND quarter = ?";
    }

    $stmt = $conn->prepare($sql);
    
    // Bind parameters
    $paramIndex = 1;
    if (!empty($term)) {
        $stmt->bindValue($paramIndex++, "%$term%");
    }
    if (!empty($year)) {
        $stmt->bindValue($paramIndex++, $year);
    }
    if (!empty($quarter)) {
        $stmt->bindValue($paramIndex++, $quarter);
    }

    $stmt->execute();
    
    $results = array();
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $value = $type === 'program' ? $row['program'] : $row['project'];
        if (!empty($value)) {
            $results[] = array(
                'label' => $value,
                'value' => $value
            );
        }
    }
    
    header('Content-Type: application/json');
    echo json_encode($results);

} catch (Exception $e) {
    error_log("Error in search_gad_activities.php: " . $e->getMessage());
    header('Content-Type: application/json');
    echo json_encode(array());
}
?> 