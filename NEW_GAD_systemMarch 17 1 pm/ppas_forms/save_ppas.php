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
    
    // Connect to database
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Start transaction
    $conn->beginTransaction();
    
    try {
        // Insert main PPAS form first
        $stmt = $conn->prepare("INSERT INTO ppas_forms (
            year, quarter, title, location, date, 
            start_time, end_time, has_lunch_break, has_am_break, has_pm_break,
            total_duration, approved_budget, source_of_budget, created_by
        ) VALUES (
            ?, ?, ?, ?, ?, ?, ?, ?, ?, ?,
            ?, ?, ?, ?
        )");
        
        $stmt->execute([
            $data['year'],
            $data['quarter'],
            $data['title'],
            $data['location'],
            $data['date'],
            $data['startTime'],
            $data['endTime'],
            $data['hasLunchBreak'] ? 1 : 0,
            $data['hasAMBreak'] ? 1 : 0,
            $data['hasPMBreak'] ? 1 : 0,
            $data['totalDuration'],
            $data['approvedBudget'],
            $data['sourceOfBudget'],
            $_SESSION['username'] ?? null
        ]);
        
        $ppasFormId = $conn->lastInsertId();
        error_log('Inserted PPAS form with ID: ' . $ppasFormId);

        // Insert personnel
        if (isset($data['personnel'])) {
            $personnelStmt = $conn->prepare("INSERT INTO ppas_personnel (ppas_id, personnel_id, role, personnel_name) VALUES (?, ?, ?, ?)");
            
            foreach ($data['personnel'] as $role => $personnel) {
                $roleMapping = [
                    'projectLeader' => 'project_leader',
                    'asstProjectLeader' => 'asst_project_leader',
                    'projectStaff' => 'project_staff',
                    'otherParticipants' => 'other_participant'
                ];
                
                if (is_array($personnel)) {
                    foreach ($personnel as $person) {
                        if (isset($person['id']) && isset($person['name'])) {
                            $personnelStmt->execute([
                                $ppasFormId,
                                $person['id'],
                                $roleMapping[$role],
                                $person['name']
                            ]);
                        }
                    }
                }
            }
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