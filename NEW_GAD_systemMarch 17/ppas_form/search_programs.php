<?php
require_once('../config.php');

// Add error logging
error_log("Searching programs with term: " . $_GET['term']);

$term = $_GET['term'];
$query = "SELECT program_name FROM programs WHERE program_name LIKE ?";
$stmt = $conn->prepare($query);
$searchTerm = "%$term%";
$stmt->bind_param('s', $searchTerm);

try {
    $stmt->execute();
    $result = $stmt->get_result();
    
    $programs = array();
    while($row = $result->fetch_assoc()) {
        $programs[] = $row['program_name'];
    }
    
    error_log("Found programs: " . json_encode($programs));
    echo json_encode($programs);
} catch (Exception $e) {
    error_log("Error in search_programs.php: " . $e->getMessage());
    echo json_encode([]);
} 