<?php
require_once '../config.php';

header('Content-Type: application/json');

$response = ['success' => false, 'message' => ''];

try {
    // Get POST data
    $data = json_decode(file_get_contents('php://input'), true);
    
    // Validate required fields
    $requiredFields = [
        'category', 'gender_issue', 'cause_of_issue', 'gad_objective',
        'relevant_agency', 'generic_activity', 'specific_activities',
        'male_participants', 'female_participants', 'gad_budget',
        'source_of_budget', 'responsible_unit', 'campus', 'year',
        'total_gaa', 'total_gad_fund'
    ];

    foreach ($requiredFields as $field) {
        if (!isset($data[$field])) {
            throw new Exception("Missing required field: {$field}");
        }
    }

    // Calculate total participants
    $total_participants = $data['male_participants'] + $data['female_participants'];

    // Ensure generic_activity is an array
    if (!is_array($data['generic_activity'])) {
        $data['generic_activity'] = [$data['generic_activity']];
    }

    // Ensure specific_activities is an array
    if (!is_array($data['specific_activities'])) {
        $data['specific_activities'] = [$data['specific_activities']];
    }

    // Convert arrays to JSON strings
    $generic_activity_json = json_encode($data['generic_activity']);
    $specific_activities_json = json_encode($data['specific_activities']);

    // Prepare SQL statement
    $sql = "INSERT INTO gpb_entries (
        category, gender_issue, cause_of_issue, gad_objective,
        relevant_agency, generic_activity, specific_activities,
        male_participants, female_participants, total_participants,
        gad_budget, source_of_budget, responsible_unit,
        campus, year, total_gaa, total_gad_fund
    ) VALUES (
        :category, :gender_issue, :cause_of_issue, :gad_objective,
        :relevant_agency, :generic_activity, :specific_activities,
        :male_participants, :female_participants, :total_participants,
        :gad_budget, :source_of_budget, :responsible_unit,
        :campus, :year, :total_gaa, :total_gad_fund
    )";

    $stmt = $pdo->prepare($sql);
    
    // Bind parameters
    $params = [
        ':category' => $data['category'],
        ':gender_issue' => $data['gender_issue'],
        ':cause_of_issue' => $data['cause_of_issue'],
        ':gad_objective' => $data['gad_objective'],
        ':relevant_agency' => $data['relevant_agency'],
        ':generic_activity' => $generic_activity_json,
        ':specific_activities' => $specific_activities_json,
        ':male_participants' => $data['male_participants'],
        ':female_participants' => $data['female_participants'],
        ':total_participants' => $total_participants,
        ':gad_budget' => $data['gad_budget'],
        ':source_of_budget' => $data['source_of_budget'],
        ':responsible_unit' => $data['responsible_unit'],
        ':campus' => $data['campus'],
        ':year' => $data['year'],
        ':total_gaa' => $data['total_gaa'],
        ':total_gad_fund' => $data['total_gad_fund']
    ];

    $stmt->execute($params);
    
    $response['success'] = true;
    $response['message'] = 'Gender issue saved successfully';
    $response['debug'] = [
        'generic_activity' => $data['generic_activity'],
        'specific_activities' => $data['specific_activities']
    ];
    
} catch (Exception $e) {
    $response['message'] = $e->getMessage();
    error_log("Error saving gender issue: " . $e->getMessage());
}

echo json_encode($response); 