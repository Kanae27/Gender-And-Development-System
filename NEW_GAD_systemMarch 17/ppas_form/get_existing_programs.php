<?php
// For debugging, enable error reporting but capture instead of display
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('html_errors', 0);

// Start the session before headers
session_start();

// Clear any existing output buffers
while (ob_get_level()) {
    ob_end_clean();
}

// Start fresh output buffer
ob_start();

// Ensure we're sending JSON response
header('Content-Type: application/json');

// Log function for detailed debugging
function debug_log($message) {
    error_log(date('[Y-m-d H:i:s]') . " DEBUG (get_existing): " . $message);
}

try {
    debug_log("Starting get_existing_programs.php script");
    
    // Check if user is logged in
    if (!isset($_SESSION['username'])) {
        debug_log("User not logged in - unauthorized access");
        http_response_code(401);
        echo json_encode(['error' => 'Unauthorized']);
        ob_end_flush();
        exit();
    }
    
    debug_log("User authenticated: " . $_SESSION['username']);

    // Get the gender issue from POST data
    $genderIssue = isset($_POST['gender_issue']) ? $_POST['gender_issue'] : '';
    debug_log("Gender issue parameter: " . $genderIssue);
    
    // If gender issue is empty, return empty array
    if (empty($genderIssue)) {
        debug_log("Gender issue is empty, returning empty array");
        echo json_encode([]);
        ob_end_flush();
        exit();
    }

    // Database connection parameters
    $servername = "localhost";
    $username = "root";
    $password = "";
    $dbname = "gad_db";

    // Connect to database
    debug_log("Connecting to database");
    try {
        $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        debug_log("Database connection successful");
    } catch (PDOException $e) {
        debug_log("Database connection failed: " . $e->getMessage());
        throw $e;
    }
    
    // Check if gender_issue column exists
    debug_log("Checking if gender_issue column exists in ppas_forms table");
    $hasGenderIssue = false;
    
    try {
        $columnsQuery = $conn->query("SHOW COLUMNS FROM ppas_forms");
        $columns = [];
        while ($column = $columnsQuery->fetch(PDO::FETCH_ASSOC)) {
            $columns[] = $column['Field'];
        }
        $hasGenderIssue = in_array('gender_issue', $columns);
        
        debug_log("gender_issue column exists: " . ($hasGenderIssue ? "Yes" : "No"));
    } catch (PDOException $e) {
        debug_log("Error checking columns: " . $e->getMessage());
        // Continue - we'll handle it below
    }
    
    // Get existing programs/projects
    $results = [];
    
    if ($hasGenderIssue) {
        debug_log("Searching for programs with gender_issue: " . $genderIssue);
        try {
            $stmt = $conn->prepare("
                SELECT id, title, type 
                FROM ppas_forms 
                WHERE gender_issue = :gender_issue
                ORDER BY year DESC, quarter DESC
            ");
            
            $stmt->execute(['gender_issue' => $genderIssue]);
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            debug_log("Found " . count($results) . " matching programs/projects");
        } catch (PDOException $e) {
            debug_log("Error searching for programs: " . $e->getMessage());
            throw $e;
        }
    } else {
        // If gender_issue column doesn't exist, return empty results
        debug_log("gender_issue column doesn't exist, returning empty array");
        $results = [];
    }
    
    // Clean output buffer - get any unexpected output
    $unexpectedOutput = ob_get_clean();
    
    // If there was unexpected output, log it
    if (!empty($unexpectedOutput)) {
        debug_log("Unexpected output captured: " . $unexpectedOutput);
    }
    
    // Start fresh output buffer for our JSON response
    ob_start();
    
    // Return results
    debug_log("Returning response with " . count($results) . " items");
    echo json_encode($results);
    
    // Send the buffer to the client
    ob_end_flush();
    
} catch (PDOException $e) {
    // Clean output buffer - get any unexpected output
    $unexpectedOutput = ob_get_clean();
    
    // If there was unexpected output, log it
    if (!empty($unexpectedOutput)) {
        debug_log("Unexpected output captured before error response: " . $unexpectedOutput);
    }
    
    // Log the error with more details
    debug_log("PDOException: " . $e->getMessage());
    
    // Start fresh output buffer for our JSON response
    ob_start();
    
    // Return error response
    http_response_code(500);
    echo json_encode([
        'error' => 'Database error',
        'message' => $e->getMessage()
    ]);
    
    // Send the buffer to the client
    ob_end_flush();
    
} catch (Exception $e) {
    // Clean output buffer - get any unexpected output
    $unexpectedOutput = ob_get_clean();
    
    // If there was unexpected output, log it
    if (!empty($unexpectedOutput)) {
        debug_log("Unexpected output captured before error response: " . $unexpectedOutput);
    }
    
    // Log the error with more details
    debug_log("Exception: " . $e->getMessage());
    
    // Start fresh output buffer for our JSON response
    ob_start();
    
    // Return error response
    http_response_code(500);
    echo json_encode([
        'error' => 'Error',
        'message' => $e->getMessage()
    ]);
    
    // Send the buffer to the client
    ob_end_flush();
}

debug_log("Script execution completed");
exit;
?> 