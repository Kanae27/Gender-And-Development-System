<?php
// Disable error reporting in production
error_reporting(0);
ini_set('display_errors', 0);

header('Content-Type: application/json');
require_once '../config.php';

try {
    $campus = isset($_GET['campus']) ? $_GET['campus'] : null;
    $year = isset($_GET['year']) ? (int)$_GET['year'] : null;
    $quarter = isset($_GET['quarter']) ? $_GET['quarter'] : null;

    // Validate required parameters
    $errors = [];
    if (!$year) {
        $errors[] = 'Year is required';
    }
    if (!$quarter) {
        $errors[] = 'Quarter is required';
    }
    
    if (!empty($errors)) {
        throw new Exception(implode(', ', $errors));
    }

    // Use the existing PDO connection from config.php
    $db = $pdo;

    // Base query
    $query = "
        SELECT 
            p.title,
            p.location,
            p.start_date,
            p.end_date,
            p.total_duration,
            p.duration_metadata,
            p.approved_budget,
            p.ps_attribution,
            p.source_of_budget,
            p.gender_issue,
            GROUP_CONCAT(DISTINCT pp.personnel_name) as personnel,
            GROUP_CONCAT(DISTINCT ps.sdg_number) as sdgs,
            SUM(CASE WHEN pb.type = 'internal_student' THEN pb.male_count ELSE 0 END) as male_students,
            SUM(CASE WHEN pb.type = 'internal_student' THEN pb.female_count ELSE 0 END) as female_students,
            SUM(CASE WHEN pb.type = 'internal_faculty' THEN pb.male_count ELSE 0 END) as male_faculty,
            SUM(CASE WHEN pb.type = 'internal_faculty' THEN pb.female_count ELSE 0 END) as female_faculty,
            SUM(CASE WHEN pb.type = 'external' THEN pb.male_count ELSE 0 END) as male_external,
            SUM(CASE WHEN pb.type = 'external' THEN pb.female_count ELSE 0 END) as female_external,
            MAX(CASE WHEN pb.type = 'external' THEN pb.external_type END) as external_type
        FROM ppas_forms p
        LEFT JOIN ppas_personnel pp ON p.id = pp.ppas_id
        LEFT JOIN ppas_sdgs ps ON p.id = ps.ppas_id
        LEFT JOIN ppas_beneficiaries pb ON p.id = pb.ppas_id
        WHERE p.year = :year AND p.quarter = :quarter
    ";

    if ($campus && $campus !== 'Central') {
        $query .= " AND p.created_by = :campus";
    }

    $query .= " GROUP BY p.id";

    $stmt = $db->prepare($query);
    $stmt->bindParam(':year', $year, PDO::PARAM_INT);
    $stmt->bindParam(':quarter', $quarter, PDO::PARAM_STR);
    
    if ($campus && $campus !== 'Central') {
        $stmt->bindParam(':campus', $campus, PDO::PARAM_STR);
    }

    $stmt->execute();
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Process the results
    $processedResults = array_map(function($row) {
        // Calculate total participants
        $totalParticipants = (
            (int)$row['male_students'] + (int)$row['female_students'] +
            (int)$row['male_faculty'] + (int)$row['female_faculty'] +
            (int)$row['male_external'] + (int)$row['female_external']
        );

        // Format dates
        $startDate = new DateTime($row['start_date']);
        $endDate = new DateTime($row['end_date']);
        
        // Format duration
        $duration = $row['duration_metadata'] ?? ($row['total_duration'] . ' hours');

        return [
            'title' => $row['title'],
            'location' => $row['location'],
            'date_conducted' => $startDate->format('M d, Y') . ($startDate != $endDate ? ' - ' . $endDate->format('M d, Y') : ''),
            'duration' => $duration,
            'budget' => number_format($row['approved_budget'], 2),
            'actual_cost' => number_format($row['approved_budget'], 2),
            'ps_attribution' => $row['ps_attribution'],
            'source_of_budget' => $row['source_of_budget'],
            'gender_issue' => $row['gender_issue'] ?? '',
            'personnel' => $row['personnel'] ? explode(',', $row['personnel']) : [],
            'sdgs' => $row['sdgs'] ? explode(',', $row['sdgs']) : [],
            'participants' => [
                'students' => [
                    'male' => (int)$row['male_students'],
                    'female' => (int)$row['female_students']
                ],
                'faculty' => [
                    'male' => (int)$row['male_faculty'],
                    'female' => (int)$row['female_faculty']
                ],
                'external' => [
                    'male' => (int)$row['male_external'],
                    'female' => (int)$row['female_external'],
                    'type' => $row['external_type']
                ],
                'total' => $totalParticipants
            ]
        ];
    }, $results);

    echo json_encode(['success' => true, 'data' => $processedResults]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
} 