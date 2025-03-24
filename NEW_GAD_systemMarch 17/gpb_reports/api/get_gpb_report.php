<?php
session_start();
header('Content-Type: application/json');
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Debug log
error_log("get_gpb_report.php accessed");

// Check if user is logged in
if (!isset($_SESSION['username'])) {
    error_log("User not logged in in get_gpb_report.php");
    echo json_encode(['status' => 'error', 'message' => 'User not logged in']);
    exit();
}

try {
    // Database connection
    require_once __DIR__ . '/../../includes/db_connection.php';
    $conn = getConnection();
    
    // Get parameters and clean them
    $campus = isset($_GET['campus']) ? trim($_GET['campus']) : '';
    $year = isset($_GET['year']) ? trim($_GET['year']) : '';

    // Debug log
    error_log("Raw parameters - campus: '" . $campus . "', year: '" . $year . "'");

    // Validate parameters
    if (empty($campus) || empty($year)) {
        error_log("Missing required parameters in get_gpb_report.php");
        echo json_encode(['status' => 'error', 'message' => 'Missing required parameters']);
        exit();
    }

    // Check if data exists for the campus and year
    $checkQuery = "SELECT COUNT(*) FROM gpb_entries WHERE campus = :campus AND year = :year";
    $checkStmt = $conn->prepare($checkQuery);
    $checkStmt->bindParam(':campus', $campus, PDO::PARAM_STR);
    $checkStmt->bindParam(':year', $year, PDO::PARAM_STR);
    $checkStmt->execute();
    $count = $checkStmt->fetchColumn();

    error_log("Found {$count} records for campus '{$campus}' and year '{$year}'");

    if ($count === 0) {
        echo json_encode([
            'status' => 'error',
            'message' => "No data found for campus '{$campus}' and year '{$year}'"
        ]);
        exit();
    }

    // Main query - using the actual column names from the table
    $query = "SELECT 
        category,
        gender_issue,
        cause_of_issue,
        gad_objective,
        relevant_agency,
        generic_activity,
        specific_activities,
        male_participants,
        female_participants,
        total_participants,
        gad_budget,
        source_of_budget,
        responsible_unit,
        created_at,
        campus,
        year,
        total_gaa,
        total_gad_fund
    FROM gpb_entries
    WHERE campus = :campus AND year = :year
    ORDER BY id";

    error_log("Executing query: " . $query);
    error_log("With parameters - campus: {$campus}, year: {$year}");

    $stmt = $conn->prepare($query);
    
    if (!$stmt) {
        $error = $conn->errorInfo();
        error_log("Prepare statement failed: " . print_r($error, true));
        throw new Exception("Failed to prepare statement: " . $error[2]);
    }

    $stmt->bindParam(':campus', $campus, PDO::PARAM_STR);
    $stmt->bindParam(':year', $year, PDO::PARAM_STR);
    
    if (!$stmt->execute()) {
        $error = $stmt->errorInfo();
        error_log("Execute statement failed: " . print_r($error, true));
        throw new Exception("Failed to execute statement: " . $error[2]);
    }

    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    error_log("Successfully fetched " . count($results) . " items");

    echo json_encode([
        'status' => 'success',
        'data' => $results
    ]);

} catch (PDOException $e) {
    error_log("PDO Error in get_gpb_report.php: " . $e->getMessage());
    error_log("PDO Error Code: " . $e->getCode());
    error_log("PDO Error Info: " . print_r($e->errorInfo, true));
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'Database error: ' . $e->getMessage()
    ]);
} catch (Exception $e) {
    error_log("Error in get_gpb_report.php: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'An error occurred: ' . $e->getMessage()
    ]);
}

// PDO connections are automatically closed when the script ends
$stmt = null;
$conn = null; 