<?php
require_once('../config.php');

// Add error logging
error_log("Searching projects with term: " . $_GET['term']);

$term = $_GET['term'];
$query = "SELECT project_name FROM projects WHERE project_name LIKE ?";
$stmt = $conn->prepare($query);
$searchTerm = "%$term%";
$stmt->bind_param('s', $searchTerm);

try {
    $stmt->execute();
    $result = $stmt->get_result();
    
    $projects = array();
    while($row = $result->fetch_assoc()) {
        $projects[] = $row['project_name'];
    }
    
    error_log("Found projects: " . json_encode($projects));
    echo json_encode($projects);
} catch (Exception $e) {
    error_log("Error in search_projects.php: " . $e->getMessage());
    echo json_encode([]);
} 