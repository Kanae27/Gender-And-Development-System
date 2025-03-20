<?php
// For debugging, enable detailed error reporting
error_reporting(E_ALL);
// Do not display errors directly in the output
ini_set('display_errors', 0);
ini_set('html_errors', 0);
// Ensure all errors are logged instead
ini_set('log_errors', 1);
ini_set('error_log', dirname(__FILE__) . '/php_errors.log');

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

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "gad_db";

// Log function for detailed debugging
function debug_log($message) {
    error_log(date('[Y-m-d H:i:s]') . " DEBUG: " . $message);
}

try {
    debug_log("Starting save_ppas.php script");
    
    // Check if user is logged in
    if (!isset($_SESSION['username'])) {
        debug_log("User not logged in - unauthorized access");
        http_response_code(401);
        echo json_encode(['success' => false, 'error' => 'Unauthorized']);
        exit();
    }
    
    debug_log("User authenticated: " . $_SESSION['username']);

    // Get JSON input
    $jsonInput = file_get_contents('php://input');
    debug_log("Received raw input: " . $jsonInput);
    $data = json_decode($jsonInput, true);
    
    // Log received data for debugging
    debug_log("Parsed JSON data: " . ($data ? 'valid JSON' : 'INVALID JSON'));

    // Check if data is valid
    if (!$data) {
        debug_log("Invalid JSON data format");
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid data format']);
        ob_end_flush();
        exit();
    }

    // Debug the type field
    debug_log("Type field value: " . (isset($data['type']) ? $data['type'] : 'NOT SET'));
    
    // Validate required fields
    $requiredFields = [
        'year', 'quarter', 'title', 'location', 
        'startDate', 'endDate', 'startTime', 'endTime', 'totalDuration', 
        'approvedBudget', 'sourceOfBudget'
    ];

    $missingFields = [];
    foreach ($requiredFields as $field) {
        if (!isset($data[$field]) || $data[$field] === '') {
            $missingFields[] = $field;
        }
    }
    
    if (!empty($missingFields)) {
        $missingFieldsList = implode(', ', $missingFields);
        debug_log("Missing required fields: " . $missingFieldsList);
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => "Missing required fields: $missingFieldsList"]);
        ob_end_flush();
        exit();
    }
    
    debug_log("All required fields are present");

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
    
    // Begin transaction
    debug_log("Beginning database transaction");
    $conn->beginTransaction();
    
    // Get table columns
    $columns = [];
    try {
        $columnsQuery = $conn->query("SHOW COLUMNS FROM ppas_forms");
        while ($column = $columnsQuery->fetch(PDO::FETCH_ASSOC)) {
            $columns[] = $column['Field'];
        }
        debug_log("Existing columns in ppas_forms: " . implode(', ', $columns));
    } catch (PDOException $e) {
        debug_log("Error getting table columns: " . $e->getMessage());
        throw $e;
    }
    
    // Prepare the INSERT statement - include ps_attribution and duration_metadata if they exist
    $insertColumns = 'year, quarter, title, location, start_date, end_date, start_time, end_time, has_lunch_break, has_am_break, has_pm_break, total_duration';
    $insertValues = ':year, :quarter, :title, :location, :start_date, :end_date, :start_time, :end_time, :has_lunch_break, :has_am_break, :has_pm_break, :total_duration';
    
    // Check if gender_issue column exists
    $hasGenderIssue = in_array('gender_issue', $columns);
    if ($hasGenderIssue) {
        $insertColumns .= ', gender_issue';
        $insertValues .= ', :gender_issue';
        debug_log("Gender issue column found and will be included in the INSERT");
    } else {
        debug_log("Gender issue column NOT found in the database table");
    }
    
    // Check if type column exists
    $hasTypeColumn = in_array('type', $columns);
    if ($hasTypeColumn) {
        $insertColumns .= ', type';
        $insertValues .= ', :type';
        debug_log("Type column found and will be included in the INSERT");
    } else {
        debug_log("Type column NOT found in the database table");
    }
    
    // Check if ps_attribution column exists
    $hasPsAttribution = in_array('ps_attribution', $columns);
    if ($hasPsAttribution) {
        $insertColumns .= ', ps_attribution';
        $insertValues .= ', :ps_attribution';
        debug_log("PS Attribution column found and will be included in the INSERT");
    } else {
        debug_log("PS Attribution column NOT found in the database table");
    }
    
    // Check if duration_metadata column exists
    $hasDurationMetadata = in_array('duration_metadata', $columns);
    if ($hasDurationMetadata) {
        $insertColumns .= ', duration_metadata';
        $insertValues .= ', :duration_metadata';
        debug_log("Duration metadata column found and will be included in the INSERT");
    } else {
        debug_log("Duration metadata column NOT found in the database table");
    }
    
    // Add required fields for the end of the statement
    $insertColumns .= ', approved_budget, source_of_budget, created_by';
    $insertValues .= ', :approved_budget, :source_of_budget, :created_by';
    
    // Build the SQL statement dynamically
    $sql = "INSERT INTO ppas_forms ($insertColumns) VALUES ($insertValues)";
    debug_log("Dynamically built SQL: " . $sql);
    
    $stmt = $conn->prepare($sql);
    
    // Process form data
    $hasLunchBreak = isset($data['hasLunchBreak']) && $data['hasLunchBreak'] ? 1 : 0;
    $hasAMBreak = isset($data['hasAMBreak']) && $data['hasAMBreak'] ? 1 : 0;
    $hasPMBreak = isset($data['hasPMBreak']) && $data['hasPMBreak'] ? 1 : 0;
    
    // Get raw duration value
    $totalDuration = isset($data['rawTotalDuration']) ? $data['rawTotalDuration'] : 0;
    
    // Get display duration for metadata
    $durationMetadata = isset($data['totalDuration']) ? $data['totalDuration'] : '';
    
    // Handle empty gender_issue - but we don't need it for the database
    $genderIssue = !empty($data['gender_issue']) ? $data['gender_issue'] : null;
    
    // Get PS Attribution value
    $psAttribution = isset($data['psAttribution']) ? $data['psAttribution'] : 0;
    
    // Get Type value (Program or Project)
    $type = isset($data['type']) ? $data['type'] : 'Program';
    debug_log("Type value from form: " . $type);
    
    // Log parameter values
    debug_log("Parameters for SQL statement:");
    debug_log("year: " . $data['year']);
    debug_log("quarter: " . $data['quarter']);
    debug_log("gender_issue: " . $genderIssue);
    debug_log("title: " . $data['title']);
    debug_log("location: " . $data['location']);
    debug_log("start_date: " . $data['startDate']);
    debug_log("end_date: " . $data['endDate']);
    debug_log("start_time: " . $data['startTime']);
    debug_log("end_time: " . $data['endTime']);
    debug_log("has_lunch_break: " . $hasLunchBreak);
    debug_log("has_am_break: " . $hasAMBreak);
    debug_log("has_pm_break: " . $hasPMBreak);
    debug_log("total_duration: " . $totalDuration);
    debug_log("duration_metadata: " . $durationMetadata);
    debug_log("approved_budget: " . $data['approvedBudget']);
    debug_log("source_of_budget: " . $data['sourceOfBudget']);
    debug_log("ps_attribution: " . $psAttribution);
    debug_log("type: " . $type);
    debug_log("created_by: " . $_SESSION['username']);
    
    // Prepare parameters for SQL execution
    $params = [
        ':year' => $data['year'],
        ':quarter' => $data['quarter'],
        ':title' => $data['title'],
        ':location' => $data['location'],
        ':start_date' => $data['startDate'],
        ':end_date' => $data['endDate'],
        ':start_time' => $data['startTime'],
        ':end_time' => $data['endTime'],
        ':has_lunch_break' => $hasLunchBreak,
        ':has_am_break' => $hasAMBreak,
        ':has_pm_break' => $hasPMBreak,
        ':total_duration' => $totalDuration,
        ':approved_budget' => $data['approvedBudget'],
        ':source_of_budget' => $data['sourceOfBudget'],
        ':created_by' => $_SESSION['username']
    ];
    
    // Add conditional parameters
    if ($hasGenderIssue) {
        $params[':gender_issue'] = $genderIssue;
    }
    
    if ($hasTypeColumn) {
        $params[':type'] = $type;
    }
    
    if ($hasPsAttribution) {
        $params[':ps_attribution'] = $psAttribution;
    }
    
    if ($hasDurationMetadata) {
        $params[':duration_metadata'] = $durationMetadata;
    }
    
    // Execute the insert statement for ppas_forms
    debug_log("Executing SQL INSERT statement");
    try {
        $stmt->execute($params);
        debug_log("SQL INSERT successful");
    } catch (PDOException $e) {
        debug_log("Error executing SQL INSERT: " . $e->getMessage());
        throw $e;
    }
    
    // Get the last inserted ID
    $ppasId = $conn->lastInsertId();
    debug_log("Inserted ppas_form with ID: " . $ppasId);
    
    // Insert personnel data
    if (isset($data['personnel']) && is_array($data['personnel'])) {
        debug_log("Processing personnel data");
        
        try {
            $personnelStmt = $conn->prepare("
                INSERT INTO ppas_personnel (
                    ppas_id, personnel_id, personnel_name, role
                ) VALUES (
                    :ppas_id, :personnel_id, :personnel_name, :role
                )
            ");
            
            // Process each personnel category
            foreach ($data['personnel'] as $role => $personnelList) {
                debug_log("Processing role: " . $role . " with " . count($personnelList) . " personnel");
                
                // Map frontend role to database role
                $dbRole = '';
                switch ($role) {
                    case 'projectLeader':
                        $dbRole = 'project_leader';
                        break;
                    case 'asstProjectLeader':
                        $dbRole = 'asst_project_leader';
                        break;
                    case 'projectStaff':
                        $dbRole = 'project_staff';
                        break;
                    case 'otherParticipants':
                        $dbRole = 'other_participant';
                        break;
                    default:
                        debug_log("Unknown role: " . $role . " - skipping");
                        continue 2; // Skip unknown roles
                }
                
                // Process each person in this role
                foreach ($personnelList as $person) {
                    debug_log("Processing person: ID=" . $person['id'] . ", Name=" . $person['name'] . ", Role=" . $dbRole);
                    
                    $personnelStmt->execute([
                        ':ppas_id' => $ppasId,
                        ':personnel_id' => $person['id'],
                        ':personnel_name' => $person['name'],
                        ':role' => $dbRole
                    ]);
                    
                    debug_log("Successfully inserted personnel with ID " . $person['id']);
                }
            }
        } catch (PDOException $e) {
            debug_log("Error inserting personnel data: " . $e->getMessage());
            throw $e;
        }
    }
    
    // Insert beneficiaries data
    if (isset($data['beneficiaries'])) {
        debug_log("Processing beneficiaries data");
        
        try {
            $beneficiariesStmt = $conn->prepare("
                INSERT INTO ppas_beneficiaries (
                    ppas_id, type, male_count, female_count
                ) VALUES (
                    :ppas_id, :type, :male_count, :female_count
                )
            ");
            
            // Internal students
            $beneficiariesStmt->execute([
                ':ppas_id' => $ppasId,
                ':type' => 'internal_students',
                ':male_count' => isset($data['beneficiaries']['internalMaleStudents']) ? $data['beneficiaries']['internalMaleStudents'] : 0,
                ':female_count' => isset($data['beneficiaries']['internalFemaleStudents']) ? $data['beneficiaries']['internalFemaleStudents'] : 0
            ]);
            
            // Internal faculty
            $beneficiariesStmt->execute([
                ':ppas_id' => $ppasId,
                ':type' => 'internal_faculty',
                ':male_count' => isset($data['beneficiaries']['internalMaleFaculty']) ? $data['beneficiaries']['internalMaleFaculty'] : 0,
                ':female_count' => isset($data['beneficiaries']['internalFemaleFaculty']) ? $data['beneficiaries']['internalFemaleFaculty'] : 0
            ]);
            
            // External beneficiaries
            if (isset($data['beneficiaries']['externalType']) && $data['beneficiaries']['externalType'] !== '') {
                $beneficiariesStmt->execute([
                    ':ppas_id' => $ppasId,
                    ':type' => 'external_' . strtolower(preg_replace('/[^a-zA-Z0-9]/', '_', $data['beneficiaries']['externalType'])),
                    ':male_count' => isset($data['beneficiaries']['externalMale']) ? $data['beneficiaries']['externalMale'] : 0,
                    ':female_count' => isset($data['beneficiaries']['externalFemale']) ? $data['beneficiaries']['externalFemale'] : 0
                ]);
            }
            
            debug_log("Successfully inserted beneficiaries data");
        } catch (PDOException $e) {
            debug_log("Error inserting beneficiaries data: " . $e->getMessage());
            throw $e;
        }
    }
    
    // Insert SDGs data
    if (isset($data['sdgs']) && is_array($data['sdgs'])) {
        debug_log("Processing SDGs data: " . implode(', ', $data['sdgs']));
        
        try {
            $sdgsStmt = $conn->prepare("
                INSERT INTO ppas_sdgs (
                    ppas_id, sdg_number
                ) VALUES (
                    :ppas_id, :sdg_number
                )
            ");
            
            foreach ($data['sdgs'] as $sdg) {
                $sdgsStmt->execute([
                    ':ppas_id' => $ppasId,
                    ':sdg_number' => $sdg
                ]);
            }
            
            debug_log("Successfully inserted SDGs data");
        } catch (PDOException $e) {
            debug_log("Error inserting SDGs data: " . $e->getMessage());
            throw $e;
        }
    }
    
    // Commit the transaction
    debug_log("Committing transaction");
    $conn->commit();
    debug_log("Transaction committed successfully");
    
    // Clean output buffer - get any unexpected output
    $unexpectedOutput = ob_get_clean();
    
    // If there was unexpected output, log it
    if (!empty($unexpectedOutput)) {
        debug_log("Unexpected output captured: " . $unexpectedOutput);
    }
    
    // Start fresh output buffer for our JSON response
    ob_start();
    
    // Return success response
    debug_log("Returning success response");
    echo json_encode([
        'success' => true,
        'message' => 'PPAS form saved successfully',
        'id' => $ppasId
    ]);
    
    // Send the buffer to the client
    ob_end_flush();
    
} catch (PDOException $e) {
    // Rollback the transaction on error
    if (isset($conn) && $conn->inTransaction()) {
        debug_log("Rolling back transaction due to error");
        $conn->rollBack();
    }
    
    // Log the error with more details
    error_log("Error in database operations: " . $e->getMessage());
    error_log("SQL State: " . $e->getCode());
    debug_log("PDOException: " . $e->getMessage());
    debug_log("SQL State: " . $e->getCode());
    
    // Log SQL error info if available
    if (isset($stmt) && $stmt) {
        $errorInfo = $stmt->errorInfo();
        debug_log("SQL Error info: " . print_r($errorInfo, true));
    }
    
    // Clean output buffer - get any unexpected output
    $unexpectedOutput = ob_get_clean();
    
    // If there was unexpected output, log it
    if (!empty($unexpectedOutput)) {
        debug_log("Unexpected output captured before error response: " . $unexpectedOutput);
    }
    
    // Start fresh output buffer for our JSON response
    ob_start();
    
    // Return error response
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
    
    // Send the buffer to the client
    ob_end_flush();
    
} catch (Exception $e) {
    // Rollback the transaction on error
    if (isset($conn) && $conn->inTransaction()) {
        debug_log("Rolling back transaction due to generic exception");
        $conn->rollBack();
    }
    
    // Log the error with more details
    error_log("Error: " . $e->getMessage());
    error_log("Error type: " . get_class($e));
    debug_log("Exception type: " . get_class($e));
    debug_log("Exception message: " . $e->getMessage());
    
    // Clean output buffer - get any unexpected output
    $unexpectedOutput = ob_get_clean();
    
    // If there was unexpected output, log it
    if (!empty($unexpectedOutput)) {
        debug_log("Unexpected output captured before error response: " . $unexpectedOutput);
    }
    
    // Start fresh output buffer for our JSON response
    ob_start();
    
    // Return error response
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
    
    // Send the buffer to the client
    ob_end_flush();
}

debug_log("Script execution completed");
exit;
?> 