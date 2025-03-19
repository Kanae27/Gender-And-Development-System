<?php
// Turn off all error reporting and HTML errors
error_reporting(0);
ini_set('display_errors', 0);
ini_set('html_errors', 0);
ini_set('xdebug.default_enable', 0);

// Ensure we're sending JSON response
header('Content-Type: application/json');

// Buffer all output
ob_start();

session_start();

// Enable error logging
ini_set('log_errors', 1);
ini_set('error_log', 'php_errors.log');

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "gad_db";

try {
    // Get JSON input and parse
    $jsonInput = file_get_contents('php://input');
    error_log('Received JSON input: ' . $jsonInput);
    
    $data = json_decode($jsonInput, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception('Invalid JSON data: ' . json_last_error_msg());
    }
    
    // Debug personnel data
    if (isset($data['personnel'])) {
        error_log('Personnel data received: ' . print_r($data['personnel'], true));
    } else {
        error_log('No personnel data received in the request');
    }
    
    // Basic validation
    if (empty($data['startDate']) || empty($data['endDate'])) {
        throw new Exception('Start date and end date are required');
    }
    
    // Validate date range
    $startDate = new DateTime($data['startDate']);
    $endDate = new DateTime($data['endDate']);
    
    if ($endDate < $startDate) {
        throw new Exception('End date cannot be earlier than start date');
    }
    
    // Connect to database
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Start transaction
    $conn->beginTransaction();
    
    try {
        // Check if a record with the same year and quarter already exists
        $checkStmt = $conn->prepare("SELECT COUNT(*) FROM ppas_forms WHERE year = ? AND quarter = ?");
        $checkStmt->execute([$data['year'], $data['quarter']]);
        $count = $checkStmt->fetchColumn();
        
        if ($count > 0) {
            // A record with the same year and quarter already exists
            throw new Exception("A PPAS form for {$data['quarter']} {$data['year']} already exists. Only one quarter per year can have saved data.");
        }
        
        // Insert main PPAS form first
        $stmt = $conn->prepare("INSERT INTO ppas_forms (
            year, quarter, title, location, start_date, end_date, 
            start_time, end_time, has_lunch_break, has_am_break, has_pm_break,
            total_duration, duration_metadata, approved_budget, source_of_budget, created_by
        ) VALUES (
            ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?,
            ?, ?, ?, ?, ?
        )");
        
        // Prepare total_duration value
        $totalDurationValue = $data['rawTotalDuration'] ?? null;
        
        // If rawTotalDuration is not provided, extract numeric value from totalDuration
        if ($totalDurationValue === null || $totalDurationValue === '') {
            // Extract the first numeric value from the formatted string
            if (preg_match('/^([\d.]+)/', $data['totalDuration'], $matches)) {
                $totalDurationValue = $matches[1];
            } else {
                $totalDurationValue = 0; // Default if no numeric value found
            }
        }
        
        $stmt->execute([
            $data['year'],
            $data['quarter'],
            $data['title'],
            $data['location'],
            $data['startDate'],
            $data['endDate'],
            $data['startTime'],
            $data['endTime'],
            $data['hasLunchBreak'] ? 1 : 0,
            $data['hasAMBreak'] ? 1 : 0,
            $data['hasPMBreak'] ? 1 : 0,
            $totalDurationValue, // Use the prepared value
            $data['totalDuration'],
            $data['approvedBudget'],
            $data['sourceOfBudget'],
            $_SESSION['username'] ?? null
        ]);
        
        $ppasFormId = $conn->lastInsertId();
        error_log('Inserted PPAS form with ID: ' . $ppasFormId);

        // Insert personnel
        if (isset($data['personnel'])) {
            error_log('Preparing to insert personnel data');
            $personnelStmt = $conn->prepare("INSERT INTO ppas_personnel (ppas_id, personnel_id, role, personnel_name) VALUES (?, ?, ?, ?)");
            
            foreach ($data['personnel'] as $role => $personnel) {
                error_log("Processing role: $role with " . (is_array($personnel) ? count($personnel) : 0) . " personnel");
                
                $roleMapping = [
                    'projectLeader' => 'project_leader',
                    'asstProjectLeader' => 'asst_project_leader',
                    'projectStaff' => 'project_staff',
                    'otherParticipants' => 'other_participant'
                ];
                
                if (is_array($personnel)) {
                    foreach ($personnel as $person) {
                        if (isset($person['id']) && isset($person['name'])) {
                            error_log("Inserting person: ID={$person['id']}, Name={$person['name']}, Role={$roleMapping[$role]}");
                            try {
                                $personnelStmt->execute([
                                    $ppasFormId,
                                    $person['id'],
                                    $roleMapping[$role],
                                    $person['name']
                                ]);
                                error_log("Successfully inserted personnel with ID {$person['id']}");
                            } catch (Exception $e) {
                                error_log("Error inserting personnel: " . $e->getMessage());
                            }
                        } else {
                            error_log("Skipping personnel due to missing id or name: " . print_r($person, true));
                        }
                    }
                } else {
                    error_log("Personnel for role $role is not an array: " . print_r($personnel, true));
                }
            }
        } else {
            error_log('No personnel data to insert');
        }

        // Insert beneficiaries
        if (isset($data['beneficiaries'])) {
            // Insert internal students
            if (isset($data['beneficiaries']['internalMaleStudents']) || isset($data['beneficiaries']['internalFemaleStudents'])) {
                $beneficiaryStmt = $conn->prepare("INSERT INTO ppas_beneficiaries (
                    ppas_id, type, male_count, female_count
                ) VALUES (?, 'internal_student', ?, ?)");
                
                $beneficiaryStmt->execute([
                    $ppasFormId,
                    intval($data['beneficiaries']['internalMaleStudents'] ?? 0),
                    intval($data['beneficiaries']['internalFemaleStudents'] ?? 0)
                ]);
            }
            
            // Insert internal faculty
            if (isset($data['beneficiaries']['internalMaleFaculty']) || isset($data['beneficiaries']['internalFemaleFaculty'])) {
                $beneficiaryStmt = $conn->prepare("INSERT INTO ppas_beneficiaries (
                    ppas_id, type, male_count, female_count
                ) VALUES (?, 'internal_faculty', ?, ?)");
                
                $beneficiaryStmt->execute([
                    $ppasFormId,
                    intval($data['beneficiaries']['internalMaleFaculty'] ?? 0),
                    intval($data['beneficiaries']['internalFemaleFaculty'] ?? 0)
                ]);
            }
            
            // Insert external beneficiaries
            if (isset($data['beneficiaries']['externalMale']) || isset($data['beneficiaries']['externalFemale'])) {
                $beneficiaryStmt = $conn->prepare("INSERT INTO ppas_beneficiaries (
                    ppas_id, type, male_count, female_count, external_type
                ) VALUES (?, 'external', ?, ?, ?)");
                
                $beneficiaryStmt->execute([
                    $ppasFormId,
                    intval($data['beneficiaries']['externalMale'] ?? 0),
                    intval($data['beneficiaries']['externalFemale'] ?? 0),
                    $data['beneficiaries']['externalType'] ?? null
                ]);
            }
        }

        // Insert SDGs
        if (isset($data['sdgs']) && is_array($data['sdgs'])) {
            $sdgStmt = $conn->prepare("INSERT INTO ppas_sdgs (ppas_id, sdg_number) VALUES (?, ?)");
            foreach ($data['sdgs'] as $sdgNumber) {
                $sdgStmt->execute([$ppasFormId, $sdgNumber]);
            }
        }
        
        // Commit transaction
        $conn->commit();
        error_log('Transaction committed successfully');
        
        // Send success response
        echo json_encode(['success' => true, 'message' => 'Data saved successfully']);
        
    } catch (Exception $e) {
        $conn->rollBack();
        error_log('Error in database operations: ' . $e->getMessage());
        throw $e;
    }
    
} catch (Exception $e) {
    error_log('Error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

// End output buffering and flush
ob_end_flush();
exit;
?> 