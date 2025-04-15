<?php
session_start();
error_reporting(0); // Disable error reporting to prevent HTML errors from being output
ini_set('display_errors', 0); // Disable error display
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/debug.log');

// Check if format=word parameter is provided (handle multiple formats)
$format = $_GET['format'] ?? '';
$wordFormat = false;

// Check for different variations of the word format parameter
if (!empty($format)) {
    if ($format === 'word' || strpos($format, 'word') === 0) {
        $wordFormat = true;
        error_log("Word format detected: $format");
    }
}

// Set appropriate content type for Word export
if ($wordFormat) {
    // Set content type for Word documents
    header('Content-Type: application/msword');
    header('Content-Disposition: inline; filename="GAD_Proposal.doc"');
} else {
    header('Content-Type: application/json');
}

// Function to safely get array value with null default
function safe_get($array, $key, $default = null) {
    return isset($array[$key]) ? $array[$key] : $default;
}

// Check if user is logged in
if (!isset($_SESSION['username'])) {
    error_log("Session not found. Current session data: " . print_r($_SESSION, true));
    if ($wordFormat) {
        echo "<p>Error: User not logged in</p>";
    } else {
        echo json_encode(['status' => 'error', 'message' => 'User not logged in']);
    }
    exit;
}

// Get parameters
$campus = $_GET['campus'] ?? null;
$year = $_GET['year'] ?? null;
$proposal_id = $_GET['proposal_id'] ?? null;

error_log("Request parameters: campus=$campus, year=$year, proposal_id=$proposal_id, format=$format");

if (!$campus || !$year || !$proposal_id) {
    error_log("Missing required parameters: campus=$campus, year=$year, proposal_id=$proposal_id");
    if ($wordFormat) {
        echo "<p>Error: Missing required parameters</p>";
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Missing required parameters']);
    }
    exit;
}

try {
    // Use config file for database connection
    require_once '../../includes/config.php';
    
    // Enable detailed error logging 
    error_log("Using database: host=$servername, dbname=$dbname, user=$username");
    error_log("Parameters: proposal_id=$proposal_id, campus=$campus, year=$year, format=$format");
    
    // Create database connection using config variables
    $db = new PDO(
        "mysql:host=$servername;dbname=$dbname;charset=utf8mb4",
        $username,
        $password,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ]
    );
    
    error_log("Database connection successful");
    
    // Get proposal details
    $query = "
        SELECT 
            gp.*,
            pf.year,
            pf.campus,
            pf.location as venue,
            pf.activity,
            pf.students_male,
            pf.students_female,
            pf.faculty_male,
            pf.faculty_female,
            pf.total_internal_male,
            pf.total_internal_female,
            pf.external_type,
            pf.external_male,
            pf.external_female,
            pf.total_male,
            pf.total_female,
            pf.total_beneficiaries,
            CONCAT(
                DATE_FORMAT(pf.start_date, '%M %d, %Y'),
                ' to ',
                DATE_FORMAT(pf.end_date, '%M %d, %Y')
            ) as duration
        FROM gad_proposals gp
        JOIN ppas_forms pf ON gp.ppas_form_id = pf.id
        WHERE gp.proposal_id = :proposal_id
        AND pf.campus = :campus
        AND pf.year = :year
    ";
    
    error_log("Executing query: " . $query);
    error_log("Parameters: proposal_id=$proposal_id, campus=$campus, year=$year");
    
    try {
        $stmt = $db->prepare($query);
        $stmt->execute([
            'proposal_id' => $proposal_id,
            'campus' => $campus,
            'year' => $year
        ]);
    } catch (PDOException $e) {
        error_log("Query execution error: " . $e->getMessage());
        // Try a simpler query to debug
        error_log("Attempting simpler query to debug");
        $simple_query = "SELECT * FROM gad_proposals WHERE proposal_id = :proposal_id";
        $stmt = $db->prepare($simple_query);
        $stmt->execute(['proposal_id' => $proposal_id]);
    }
    
    $proposal = $stmt->fetch();
    
    if (!$proposal) {
        error_log("No proposal found for ID: $proposal_id, Campus: $campus, Year: $year");
        if ($wordFormat) {
            echo "<p>Error: Proposal not found</p>";
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Proposal not found']);
        }
        exit;
    }
    
    error_log("Found proposal: " . json_encode($proposal));
    
    // Get personnel from ppas_personnel table
    $personnel_query = "
        SELECT 
            pp.personnel_id,
            pp.role,
            p.name,
            p.gender,
            p.academic_rank
        FROM ppas_personnel pp 
        JOIN personnel p ON pp.personnel_id = p.id
        WHERE pp.ppas_form_id = :ppas_form_id
        ORDER BY pp.role, p.name
    ";
    
    try {
        $stmt = $db->prepare($personnel_query);
        $stmt->execute(['ppas_form_id' => $proposal['ppas_form_id']]);
        $personnel = $stmt->fetchAll();
        error_log("Found personnel: " . json_encode($personnel));
    } catch (PDOException $e) {
        error_log("Personnel query error: " . $e->getMessage());
        $personnel = [];
    }
    
    // Group personnel by role
    $personnel_by_role = [
        'project_leaders' => [],
        'assistant_project_leaders' => [],
        'project_staff' => []
    ];
    
    foreach ($personnel as $person) {
        if ($person['role'] == 'Project Leader') {
            $personnel_by_role['project_leaders'][] = $person;
        } elseif ($person['role'] == 'Assistant Project Leader') {
            $personnel_by_role['assistant_project_leaders'][] = $person;
        } elseif ($person['role'] == 'Staff') {
            $personnel_by_role['project_staff'][] = $person;
        }
    }
    
    error_log("Personnel data: " . json_encode($personnel_by_role));
    
    // Format the response with null checks for all fields
    $response = array(
        'status' => 'success',
        'data' => array(
            'campus' => $campus,
            'year' => $year,
            'quarter' => safe_get($proposal, 'quarter'),
            'sections' => array(
                'title' => safe_get($proposal, 'activity'),
                'date_venue' => array(
                    'venue' => safe_get($proposal, 'venue'),
                    'date' => safe_get($proposal, 'duration')
                ),
                'delivery_mode' => safe_get($proposal, 'mode_of_delivery'),
                'project_team' => array(
                    'project_leaders' => array(
                        'names' => implode(', ', array_map(function($person) { 
                            return $person['name']; 
                        }, $personnel_by_role['project_leaders'])),
                        'responsibilities' => json_decode(safe_get($proposal, 'project_leader_responsibilities'), true)
                    ),
                    'assistant_project_leaders' => array(
                        'names' => implode(', ', array_map(function($person) { 
                            return $person['name']; 
                        }, $personnel_by_role['assistant_project_leaders'])),
                        'responsibilities' => json_decode(safe_get($proposal, 'assistant_leader_responsibilities'), true)
                    ),
                    'project_staff' => array(
                        'names' => implode(', ', array_map(function($person) { 
                            return $person['name']; 
                        }, $personnel_by_role['project_staff'])),
                        'responsibilities' => json_decode(safe_get($proposal, 'staff_responsibilities'), true)
                    )
                ),
                'partner_offices' => safe_get($proposal, 'partner_office'),
                'participants' => array(
                    'students_male' => intval(safe_get($proposal, 'students_male', 0)),
                    'students_female' => intval(safe_get($proposal, 'students_female', 0)),
                    'faculty_male' => intval(safe_get($proposal, 'faculty_male', 0)),
                    'faculty_female' => intval(safe_get($proposal, 'faculty_female', 0)),
                    'total_internal_male' => intval(safe_get($proposal, 'total_internal_male', 0)),
                    'total_internal_female' => intval(safe_get($proposal, 'total_internal_female', 0)),
                    'external_type' => safe_get($proposal, 'external_type', ''),
                    'external_male' => intval(safe_get($proposal, 'external_male', 0)),
                    'external_female' => intval(safe_get($proposal, 'external_female', 0)),
                    'male' => intval(safe_get($proposal, 'total_male', 0)),
                    'female' => intval(safe_get($proposal, 'total_female', 0)),
                    'total' => intval(safe_get($proposal, 'total_beneficiaries', 0))
                ),
                'rationale' => safe_get($proposal, 'rationale'),
                'description' => safe_get($proposal, 'description'),
                'objectives' => array(
                    'general' => safe_get($proposal, 'general_objectives'),
                    'specific' => json_decode(safe_get($proposal, 'specific_objectives'), true) ?? array()
                ),
                'methodology' => safe_get($proposal, 'methodology'),
                'agenda' => json_decode(safe_get($proposal, 'agenda'), true),
                'expected_outputs' => safe_get($proposal, 'expected_outputs'),
                'success_indicators' => safe_get($proposal, 'success_indicators'),
                'sustainability_measures' => safe_get($proposal, 'sustainability_measures'),
                'budget' => json_decode(safe_get($proposal, 'budget'), true)
            )
        )
    );
    
    if ($wordFormat) {
        // Format the proposal as HTML for Word export
        $html = formatProposalAsHtml($response['data']);
        echo $html;
    } else {
        // Return JSON response
        echo json_encode($response);
    }
    
} catch (Exception $e) {
    error_log("Error: " . $e->getMessage());
    
    if ($wordFormat) {
        echo "<p>Error: " . $e->getMessage() . "</p>";
    } else {
        echo json_encode([
            'status' => 'error',
            'message' => 'Error generating proposal: ' . $e->getMessage()
        ]);
    }
}

// Function to format proposal as HTML for Word export
function formatProposalAsHtml($data) {
    // Start building the HTML document for Word
    $html = '<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>GAD Proposal</title>
    <style>
        /* Word styling goes here */
        body {
            font-family: "Times New Roman", Times, serif;
            font-size: 12pt;
            line-height: 1.3;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
        }
        th, td {
            border: 1px solid black;
            padding: 8px;
            text-align: left;
            vertical-align: top;
        }
        .center {
            text-align: center;
        }
        .title {
            font-weight: bold;
            font-size: 14pt;
            text-align: center;
            margin: 15px 0;
        }
    </style>
</head>
<body>
    <div class="title">GAD PROPOSAL</div>
    
    <!-- Content would be generated based on data -->
    <p>This is a placeholder for a complete Word document</p>
    <p>The actual implementation would format all sections properly</p>
</body>
</html>';

    return $html;
} 