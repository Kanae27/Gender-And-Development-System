<?php
session_start();
header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['username'])) {
    echo json_encode(['success' => false, 'message' => 'User not logged in']);
    exit();
}

// Database connection
require_once '../includes/db_connection.php';

// Get parameters
$campus = isset($_GET['campus']) ? $_GET['campus'] : null;
$year = isset($_GET['year']) ? $_GET['year'] : null;

// Validate parameters
if (!$campus || !$year) {
    echo json_encode(['success' => false, 'message' => 'Missing required parameters']);
    exit();
}

try {
    // Prepare the query to fetch GPB data from gpb_entries table
    $query = "SELECT 
        gender_issue,
        cause_of_gender_issue,
        gad_statement,
        relevant_organization,
        gad_activity,
        performance_indicator,
        target_male,
        target_female,
        gad_budget,
        source_of_budget,
        responsible_unit
    FROM gpb_entries
    WHERE campus = ? AND year = ?
    ORDER BY id";

    $stmt = $conn->prepare($query);
    $stmt->bind_param("ss", $campus, $year);
    $stmt->execute();
    $result = $stmt->get_result();

    $items = [];
    $total_budget = 0;

    while ($row = $result->fetch_assoc()) {
        $items[] = [
            'gender_issue' => $row['gender_issue'],
            'cause_of_issue' => $row['cause_of_gender_issue'],
            'gad_objective' => $row['gad_statement'],
            'relevant_agency' => $row['relevant_organization'],
            'gad_activity' => $row['gad_activity'],
            'performance_indicators' => $row['performance_indicator'],
            'male_target' => (int)$row['target_male'],
            'female_target' => (int)$row['target_female'],
            'gad_budget' => (float)$row['gad_budget'],
            'source_of_budget' => $row['source_of_budget'],
            'responsible_unit' => $row['responsible_unit']
        ];
        $total_budget += (float)$row['gad_budget'];
    }

    echo json_encode([
        'success' => true,
        'data' => [
            'year' => $year,
            'campus' => $campus,
            'total_budget' => $total_budget,
            'items' => $items
        ]
    ]);

} catch (Exception $e) {
    error_log("Error in get_gpb_report.php: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'An error occurred while fetching the report data'
    ]);
}

$conn->close(); 