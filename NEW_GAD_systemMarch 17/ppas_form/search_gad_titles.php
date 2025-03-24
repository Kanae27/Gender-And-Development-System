<?php
require_once('../config.php');

// Add error logging
error_log("Searching GAD titles with term: " . $_GET['term']);

// Get search parameters
$term = isset($_GET['term']) ? $_GET['term'] : '';
$year = isset($_GET['year']) ? $_GET['year'] : '';
$quarter = isset($_GET['quarter']) ? $_GET['quarter'] : '';

try {
    // Build the query
    $query = "SELECT DISTINCT specific_activities as title 
              FROM gpb_entries 
              WHERE specific_activities LIKE ?";
    $params = ["%$term%"];
    $types = "s";

    // Add year filter if provided
    if (!empty($year)) {
        $query .= " AND year = ?";
        $params[] = $year;
        $types .= "s";
    }

    // Prepare and execute the query
    $stmt = $conn->prepare($query);
    if ($stmt) {
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $titles = array();
        while($row = $result->fetch_assoc()) {
            if (!empty($row['title'])) {
                $titles[] = array(
                    'label' => $row['title'],
                    'value' => $row['title']
                );
            }
        }
        
        error_log("Found GAD titles: " . json_encode($titles));
        echo json_encode($titles);
    } else {
        throw new Exception("Failed to prepare statement");
    }
} catch (Exception $e) {
    error_log("Error in search_gad_titles.php: " . $e->getMessage());
    echo json_encode([]);
} 