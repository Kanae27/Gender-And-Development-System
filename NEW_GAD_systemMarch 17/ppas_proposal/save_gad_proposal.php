<?php
// Start the session
session_start();

// Start output buffering
ob_start();

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1); // Display errors for debugging

// Log the start of the script
error_log("save_gad_proposal.php started");

// Create a debug log file
$debug_file = __DIR__ . '/debug_log.txt';
file_put_contents($debug_file, "Script started at " . date('Y-m-d H:i:s') . "\n", FILE_APPEND);

// Set JSON header
header('Content-Type: application/json');

// Initialize response array
$response = [
    'success' => false,
    'message' => '',
    'proposalId' => null
];

try {
    // Include database connection
    require_once '../includes/db_connection.php';
    file_put_contents($debug_file, "Database connection included\n", FILE_APPEND);
    
    // Log the start of the script
    error_log("save_gad_proposal.php processing");
    file_put_contents($debug_file, "Processing request\n", FILE_APPEND);
    
    // Check if it's a POST request
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        file_put_contents($debug_file, "Invalid request method: " . $_SERVER['REQUEST_METHOD'] . "\n", FILE_APPEND);
        throw new Exception('Invalid request method');
    }

    // Get form data
    $data = $_POST;
    
    // Log the received data
    error_log("Received form data: " . print_r($data, true));
    file_put_contents($debug_file, "Received form data: " . print_r($data, true) . "\n", FILE_APPEND);

    // Validate required fields
    $requiredFields = ['year', 'quarter', 'activityTitle', 'startDate', 'endDate', 'venue', 'deliveryMode'];
    foreach ($requiredFields as $field) {
        if (!isset($data[$field]) || empty(trim($data[$field]))) {
            file_put_contents($debug_file, "Required field missing: {$field}\n", FILE_APPEND);
            throw new Exception("Required field missing: {$field}");
        }
    }

    // Check if table exists first
    try {
        $tableCheckSql = "SHOW TABLES LIKE 'gad_proposals'";
        $tableCheckStmt = $conn->prepare($tableCheckSql);
        $tableCheckStmt->execute();
        
        if ($tableCheckStmt->rowCount() === 0) {
            // Table doesn't exist - create it
            file_put_contents($debug_file, "Table gad_proposals doesn't exist. Creating table...\n", FILE_APPEND);
            
            $createTableSql = "CREATE TABLE IF NOT EXISTS gad_proposals (
                id INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
                year VARCHAR(4) NOT NULL,
                quarter VARCHAR(10) NOT NULL,
                activity_title VARCHAR(255) NOT NULL,
                start_date DATE NOT NULL,
                end_date DATE NOT NULL,
                venue VARCHAR(255) NOT NULL,
                delivery_mode VARCHAR(50) NOT NULL,
                ppas_id INT(11) NULL,
                project_leaders VARCHAR(255) NULL,
                leader_responsibilities TEXT NULL,
                assistant_project_leaders VARCHAR(255) NULL,
                assistant_responsibilities TEXT NULL,
                project_staff VARCHAR(255) NULL,
                staff_responsibilities TEXT NULL,
                partner_offices VARCHAR(255) NULL,
                male_beneficiaries INT(11) NULL DEFAULT 0,
                female_beneficiaries INT(11) NULL DEFAULT 0,
                total_beneficiaries INT(11) NULL DEFAULT 0,
                rationale TEXT NULL,
                specific_objectives TEXT NULL,
                strategies TEXT NULL,
                budget_source VARCHAR(50) NULL,
                total_budget DECIMAL(10,2) NULL DEFAULT 0.00,
                budget_breakdown TEXT NULL,
                sustainability_plan TEXT NULL,
                created_by VARCHAR(100) NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
            
            $createTableStmt = $conn->prepare($createTableSql);
            $createTableStmt->execute();
            file_put_contents($debug_file, "Table gad_proposals created successfully\n", FILE_APPEND);
            
            // Also create the activities table
            $createActivitiesTableSql = "CREATE TABLE IF NOT EXISTS gad_proposal_activities (
                id INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
                proposal_id INT(11) NOT NULL,
                title VARCHAR(255) NOT NULL,
                details TEXT NULL,
                sequence INT(11) NOT NULL DEFAULT 1,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (proposal_id) REFERENCES gad_proposals(id) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
            
            $createActivitiesTableStmt = $conn->prepare($createActivitiesTableSql);
            $createActivitiesTableStmt->execute();
            file_put_contents($debug_file, "Table gad_proposal_activities created successfully\n", FILE_APPEND);
            
            // Create the personnel table
            $createPersonnelTableSql = "CREATE TABLE IF NOT EXISTS gad_proposal_personnel (
                id INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
                proposal_id INT(11) NOT NULL,
                personnel_id INT(11) NOT NULL,
                role VARCHAR(50) NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (proposal_id) REFERENCES gad_proposals(id) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
            
            $createPersonnelTableStmt = $conn->prepare($createPersonnelTableSql);
            $createPersonnelTableStmt->execute();
            file_put_contents($debug_file, "Table gad_proposal_personnel created successfully\n", FILE_APPEND);
        } else {
            // Table exists - check if ppas_id column exists
            $columnCheckSql = "SHOW COLUMNS FROM gad_proposals LIKE 'ppas_id'";
            $columnCheckStmt = $conn->prepare($columnCheckSql);
            $columnCheckStmt->execute();
            
            if ($columnCheckStmt->rowCount() === 0) {
                // ppas_id column doesn't exist - add it
                file_put_contents($debug_file, "Column ppas_id doesn't exist. Adding column...\n", FILE_APPEND);
                
                $addColumnSql = "ALTER TABLE gad_proposals ADD COLUMN ppas_id INT(11) NULL AFTER delivery_mode";
                $addColumnStmt = $conn->prepare($addColumnSql);
                $addColumnStmt->execute();
                file_put_contents($debug_file, "Column ppas_id added successfully\n", FILE_APPEND);
            }
            
            // Also check if gad_proposal_personnel table exists
            $personnelTableCheckSql = "SHOW TABLES LIKE 'gad_proposal_personnel'";
            $personnelTableCheckStmt = $conn->prepare($personnelTableCheckSql);
            $personnelTableCheckStmt->execute();
            
            if ($personnelTableCheckStmt->rowCount() === 0) {
                // Create the personnel table
                $createPersonnelTableSql = "CREATE TABLE IF NOT EXISTS gad_proposal_personnel (
                    id INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
                    proposal_id INT(11) NOT NULL,
                    personnel_id INT(11) NOT NULL,
                    role VARCHAR(50) NOT NULL,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    FOREIGN KEY (proposal_id) REFERENCES gad_proposals(id) ON DELETE CASCADE
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
                
                $createPersonnelTableStmt = $conn->prepare($createPersonnelTableSql);
                $createPersonnelTableStmt->execute();
                file_put_contents($debug_file, "Table gad_proposal_personnel created successfully\n", FILE_APPEND);
            }
        }
    } catch (PDOException $e) {
        file_put_contents($debug_file, "Error checking/creating table: " . $e->getMessage() . "\n", FILE_APPEND);
        throw new Exception("Database schema error: " . $e->getMessage());
    }

    // Start transaction
    $conn->beginTransaction();
    file_put_contents($debug_file, "Database transaction started\n", FILE_APPEND);

    // Check if we're updating an existing proposal
    $isUpdate = isset($data['currentProposalId']) && !empty($data['currentProposalId']);
    $proposalId = $isUpdate ? $data['currentProposalId'] : null;
    
    // Add or update the proposal
    if ($isUpdate) {
        $sql = "UPDATE gad_proposals SET 
                year = :year,
                quarter = :quarter,
                activity_title = :activityTitle,
                start_date = :startDate,
                end_date = :endDate,
                venue = :venue,
                delivery_mode = :deliveryMode,
                ppas_id = :ppasId,
                project_leaders = :projectLeaders,
                leader_responsibilities = :leaderResponsibilities,
                assistant_project_leaders = :assistantProjectLeaders,
                assistant_responsibilities = :assistantResponsibilities,
                project_staff = :projectStaff,
                staff_responsibilities = :staffResponsibilities,
                partner_offices = :partnerOffices,
                male_beneficiaries = :maleBeneficiaries,
                female_beneficiaries = :femaleBeneficiaries,
                total_beneficiaries = :totalBeneficiaries,
                rationale = :rationale,
                specific_objectives = :specificObjectives,
                strategies = :strategies,
                budget_source = :budgetSource,
                total_budget = :totalBudget,
                budget_breakdown = :budgetBreakdown,
                sustainability_plan = :sustainabilityPlan,
                updated_at = CURRENT_TIMESTAMP
                WHERE id = :proposalId";
    } else {
        $sql = "INSERT INTO gad_proposals (
                year, quarter, activity_title, start_date, end_date, venue, delivery_mode, 
                ppas_id, project_leaders, leader_responsibilities, assistant_project_leaders, 
                assistant_responsibilities, project_staff, staff_responsibilities, 
                partner_offices, male_beneficiaries, female_beneficiaries, total_beneficiaries, 
                rationale, specific_objectives, strategies, budget_source, total_budget, 
                budget_breakdown, sustainability_plan, created_at
            ) VALUES (
                :year, :quarter, :activityTitle, :startDate, :endDate, :venue, :deliveryMode, 
                :ppasId, :projectLeaders, :leaderResponsibilities, :assistantProjectLeaders, 
                :assistantResponsibilities, :projectStaff, :staffResponsibilities, 
                :partnerOffices, :maleBeneficiaries, :femaleBeneficiaries, :totalBeneficiaries, 
                :rationale, :specificObjectives, :strategies, :budgetSource, :totalBudget, 
                :budgetBreakdown, :sustainabilityPlan, CURRENT_TIMESTAMP
            )";
    }

    file_put_contents($debug_file, "SQL Query: " . $sql . "\n", FILE_APPEND);
    
    $stmt = $conn->prepare($sql);
    
    // Common parameters
    $params = [
        ':year' => $data['year'],
        ':quarter' => $data['quarter'],
        ':activityTitle' => $data['activityTitle'],
        ':startDate' => $data['startDate'],
        ':endDate' => $data['endDate'],
        ':venue' => $data['venue'],
        ':deliveryMode' => $data['deliveryMode'],
        ':ppasId' => isset($data['ppasId']) ? $data['ppasId'] : null,
        ':projectLeaders' => isset($data['projectLeaders']) ? $data['projectLeaders'] : '',
        ':leaderResponsibilities' => isset($data['leaderResponsibilities']) ? $data['leaderResponsibilities'] : '',
        ':assistantProjectLeaders' => isset($data['assistantProjectLeaders']) ? $data['assistantProjectLeaders'] : '',
        ':assistantResponsibilities' => isset($data['assistantResponsibilities']) ? $data['assistantResponsibilities'] : '',
        ':projectStaff' => isset($data['projectStaff']) ? $data['projectStaff'] : '',
        ':staffResponsibilities' => isset($data['staffResponsibilities']) ? $data['staffResponsibilities'] : '',
        ':partnerOffices' => isset($data['partnerOffices']) ? $data['partnerOffices'] : '',
        ':maleBeneficiaries' => isset($data['maleBeneficiaries']) ? $data['maleBeneficiaries'] : 0,
        ':femaleBeneficiaries' => isset($data['femaleBeneficiaries']) ? $data['femaleBeneficiaries'] : 0,
        ':totalBeneficiaries' => isset($data['totalBeneficiaries']) ? $data['totalBeneficiaries'] : 0,
        ':rationale' => isset($data['rationale']) ? $data['rationale'] : '',
        ':specificObjectives' => isset($data['specificObjectives']) ? $data['specificObjectives'] : '',
        ':strategies' => isset($data['strategies']) ? $data['strategies'] : '',
        ':budgetSource' => isset($data['budgetSource']) ? $data['budgetSource'] : '',
        ':totalBudget' => isset($data['totalBudget']) ? $data['totalBudget'] : 0,
        ':budgetBreakdown' => isset($data['budgetBreakdown']) ? $data['budgetBreakdown'] : '',
        ':sustainabilityPlan' => isset($data['sustainabilityPlan']) ? $data['sustainabilityPlan'] : ''
    ];
    
    // Add proposal ID parameter for updates
    if ($isUpdate) {
        $params[':proposalId'] = $proposalId;
    }
    
    file_put_contents($debug_file, "About to execute main insert query\n", FILE_APPEND);
    
    // Execute the query
    $stmt->execute($params);
    
    file_put_contents($debug_file, "Main insert query executed successfully\n", FILE_APPEND);
    
    // Get the proposal ID
    if (!$isUpdate) {
        $proposalId = $conn->lastInsertId();
    }
    
    // Save the activities
    // First, delete existing activities if updating
    if ($isUpdate) {
        $conn->exec("DELETE FROM gad_proposal_activities WHERE proposal_id = $proposalId");
    }
    
    // Find all activities in the POST data
    $activities = [];
    foreach ($data as $key => $value) {
        if (strpos($key, 'activity_title_') === 0) {
            $index = substr($key, strlen('activity_title_'));
            if (!isset($activities[$index])) {
                $activities[$index] = ['title' => '', 'details' => ''];
            }
            $activities[$index]['title'] = $value;
        } else if (strpos($key, 'activity_details_') === 0) {
            $index = substr($key, strlen('activity_details_'));
            if (!isset($activities[$index])) {
                $activities[$index] = ['title' => '', 'details' => ''];
            }
            $activities[$index]['details'] = $value;
        }
    }
    
    // Insert activities
    if (!empty($activities)) {
        $activitySql = "INSERT INTO gad_proposal_activities (proposal_id, title, details, created_at) 
                         VALUES (:proposalId, :title, :details, CURRENT_TIMESTAMP)";
        $activityStmt = $conn->prepare($activitySql);
        
        foreach ($activities as $activity) {
            if (!empty($activity['title'])) {
                $activityStmt->execute([
                    ':proposalId' => $proposalId,
                    ':title' => $activity['title'],
                    ':details' => $activity['details'] ?? ''
                ]);
            }
        }
    }
    
    // Save personnel data
    // First, delete existing personnel if updating
    if ($isUpdate) {
        $conn->exec("DELETE FROM gad_proposal_personnel WHERE proposal_id = $proposalId");
    }
    
    // Process personnel data
    $personnelRoles = ['projectLeadersHidden', 'assistantProjectLeadersHidden', 'projectStaffHidden'];
    foreach ($personnelRoles as $roleField) {
        if (isset($data[$roleField]) && !empty($data[$roleField])) {
            $personnel = explode(',', $data[$roleField]);
            
            // Map the role field to the database role value
            $role = '';
            $ppasRole = ''; // For ppas_personnel table
            if ($roleField === 'projectLeadersHidden') {
                $role = 'project_leader';
                $ppasRole = 'project_leader';
            } else if ($roleField === 'assistantProjectLeadersHidden') {
                $role = 'assistant_project_leader';
                $ppasRole = 'asst_project_leader';
            } else if ($roleField === 'projectStaffHidden') {
                $role = 'project_staff';
                $ppasRole = 'project_staff';
            }
            
            if (!empty($role)) {
                // Insert into gad_proposal_personnel
                $personnelSql = "INSERT INTO gad_proposal_personnel (proposal_id, personnel_id, role, created_at) 
                                 VALUES (:proposalId, :personnelId, :role, CURRENT_TIMESTAMP)";
                $personnelStmt = $conn->prepare($personnelSql);
                
                // If we have a PPAS ID, prepare for ppas_personnel table too
                $ppasPersonnelStmt = null;
                $ppasId = isset($data['ppasId']) ? $data['ppasId'] : null;
                
                if ($ppasId) {
                    // First, check if the personnel already exists in ppas_personnel
                    $checkPpasSql = "SELECT COUNT(*) FROM ppas_personnel WHERE ppas_id = :ppasId AND personnel_id = :personnelId";
                    $checkPpasStmt = $conn->prepare($checkPpasSql);
                    
                    // Get personnel name
                    $getPersonnelNameSql = "SELECT name as personnel_name FROM personnel WHERE id = :personnelId";
                    $getPersonnelNameStmt = $conn->prepare($getPersonnelNameSql);
                    
                    // Fallback to personnel_list if not found in personnel table
                    $getPersonnelListNameSql = "SELECT name as personnel_name FROM personnel_list WHERE id = :personnelId";
                    $getPersonnelListNameStmt = $conn->prepare($getPersonnelListNameSql);
                    
                    // Prepare statement for inserting new personnel
                    $ppasPersonnelSql = "INSERT INTO ppas_personnel (ppas_id, personnel_id, role, personnel_name) 
                                        VALUES (:ppasId, :personnelId, :role, :personnelName)";
                    $ppasPersonnelStmt = $conn->prepare($ppasPersonnelSql);
                }
                
                foreach ($personnel as $personnelId) {
                    if (!empty($personnelId)) {
                        // Insert into gad_proposal_personnel
                        $personnelStmt->execute([
                            ':proposalId' => $proposalId,
                            ':personnelId' => $personnelId,
                            ':role' => $role
                        ]);
                        
                        // If we have a PPAS ID, also handle ppas_personnel table
                        if ($ppasId && $ppasPersonnelStmt) {
                            // Check if this personnel already exists in ppas_personnel
                            $checkPpasStmt->execute([
                                ':ppasId' => $ppasId,
                                ':personnelId' => $personnelId
                            ]);
                            
                            $exists = $checkPpasStmt->fetchColumn();
                            
                            // Only insert if not already exists
                            if (!$exists) {
                                // Try to get personnel name from personnel table
                                $getPersonnelNameStmt->execute([':personnelId' => $personnelId]);
                                $personnelName = $getPersonnelNameStmt->fetchColumn();
                                
                                // If not found in personnel table, try personnel_list
                                if (!$personnelName) {
                                    $getPersonnelListNameStmt->execute([':personnelId' => $personnelId]);
                                    $personnelName = $getPersonnelListNameStmt->fetchColumn();
                                    file_put_contents($debug_file, "Got name from personnel_list: $personnelName\n", FILE_APPEND);
                                } else {
                                    file_put_contents($debug_file, "Got name from personnel table: $personnelName\n", FILE_APPEND);
                                }
                                
                                // If we still couldn't get the name, use a default
                                if (!$personnelName) {
                                    $personnelName = "Personnel ID: " . $personnelId;
                                    file_put_contents($debug_file, "Using default name: $personnelName\n", FILE_APPEND);
                                }
                                
                                // Add more detailed debug info
                                file_put_contents($debug_file, "Preparing to insert: PPAS ID: $ppasId, Personnel ID: $personnelId, Role: $ppasRole, Name: $personnelName\n", FILE_APPEND);
                                
                                try {
                                    // Insert into ppas_personnel table
                                    $ppasPersonnelStmt->execute([
                                        ':ppasId' => $ppasId,
                                        ':personnelId' => $personnelId,
                                        ':role' => $ppasRole,
                                        ':personnelName' => $personnelName
                                    ]);
                                    
                                    file_put_contents($debug_file, "Successfully inserted personnel ID $personnelId with role $ppasRole to ppas_personnel\n", FILE_APPEND);
                                } catch (Exception $e) {
                                    file_put_contents($debug_file, "ERROR inserting personnel: " . $e->getMessage() . "\n", FILE_APPEND);
                                }
                            } else {
                                file_put_contents($debug_file, "Personnel ID $personnelId with role $ppasRole already exists in ppas_personnel\n", FILE_APPEND);
                            }
                        }
                    }
                }
            }
        }
    }
    
    // Commit transaction
    $conn->commit();
    file_put_contents($debug_file, "Transaction committed successfully\n", FILE_APPEND);
    
    // Set success response
    $response['success'] = true;
    $response['message'] = $isUpdate ? 'GAD Proposal updated successfully' : 'GAD Proposal created successfully';
    $response['proposalId'] = $proposalId;
    
} catch (PDOException $e) {
    // Rollback transaction on error
    if (isset($conn) && $conn->inTransaction()) {
        $conn->rollBack();
        file_put_contents($debug_file, "Transaction rolled back due to error\n", FILE_APPEND);
    }
    
    // Log the error
    $error_message = "Database error in save_gad_proposal.php: " . $e->getMessage();
    error_log($error_message);
    file_put_contents($debug_file, $error_message . "\n", FILE_APPEND);
    file_put_contents($debug_file, "SQL State: " . $e->getCode() . "\n", FILE_APPEND);
    file_put_contents($debug_file, "Error trace: " . $e->getTraceAsString() . "\n", FILE_APPEND);
    
    // Set error response
    $response['success'] = false;
    $response['message'] = 'Database error: ' . $e->getMessage();
} catch (Exception $e) {
    // Log the error
    $error_message = "Error in save_gad_proposal.php: " . $e->getMessage();
    error_log($error_message);
    file_put_contents($debug_file, $error_message . "\n", FILE_APPEND);
    file_put_contents($debug_file, "Error trace: " . $e->getTraceAsString() . "\n", FILE_APPEND);
    
    // Set error response
    $response['success'] = false;
    $response['message'] = 'Error: ' . $e->getMessage();
}

// End output buffering and clear the buffer
ob_end_clean();

// Send JSON response
echo json_encode($response);
exit();
?> 