<?php
require_once '../includes/db_connection.php';

try {
    // Get the campus from the session if it exists
    session_start();
    $campus = isset($_SESSION['username']) ? $_SESSION['username'] : null;

    // Base query for total personnel
    $sql = "SELECT 
                COUNT(*) as total,
                SUM(CASE WHEN category = 'Teaching' THEN 1 ELSE 0 END) as teaching_total,
                SUM(CASE WHEN category = 'Teaching' AND status = 'Permanent' THEN 1 ELSE 0 END) as teaching_permanent,
                SUM(CASE WHEN category = 'Teaching' AND status = 'Temporary' THEN 1 ELSE 0 END) as teaching_temporary,
                SUM(CASE WHEN category = 'Teaching' AND status = 'Guest Lecturer' THEN 1 ELSE 0 END) as teaching_guest,
                SUM(CASE WHEN category = 'Non-teaching' THEN 1 ELSE 0 END) as nonteaching_total,
                SUM(CASE WHEN category = 'Non-teaching' AND status = 'Permanent' THEN 1 ELSE 0 END) as nonteaching_permanent,
                SUM(CASE WHEN category = 'Non-teaching' AND status = 'Job Order' THEN 1 ELSE 0 END) as nonteaching_jo,
                SUM(CASE WHEN category = 'Non-teaching' AND status = 'Part-timer' THEN 1 ELSE 0 END) as nonteaching_parttime,
                SUM(CASE WHEN category = 'Non-teaching' AND status = 'Casual' THEN 1 ELSE 0 END) as nonteaching_casual,
                SUM(CASE WHEN gender = 'male' THEN 1 ELSE 0 END) as male_count,
                SUM(CASE WHEN gender = 'female' THEN 1 ELSE 0 END) as female_count,
                SUM(CASE WHEN gender = 'other' THEN 1 ELSE 0 END) as other_count
            FROM personnel";

    // Add campus filter if not Central
    if ($campus && $campus !== 'Central') {
        $sql .= " WHERE campus = ?";
    }

    $stmt = $conn->prepare($sql);

    // Bind campus parameter if needed
    if ($campus && $campus !== 'Central') {
        $stmt->bind_param("s", $campus);
    }

    $stmt->execute();
    $result = $stmt->get_result();
    $data = $result->fetch_assoc();

    // Return JSON response
    header('Content-Type: application/json');
    echo json_encode([
        'success' => true,
        'data' => [
            'total' => (int)$data['total'],
            'teaching' => [
                'total' => (int)$data['teaching_total'],
                'permanent' => (int)$data['teaching_permanent'],
                'temporary' => (int)$data['teaching_temporary'],
                'guest' => (int)$data['teaching_guest']
            ],
            'nonteaching' => [
                'total' => (int)$data['nonteaching_total'],
                'permanent' => (int)$data['nonteaching_permanent'],
                'job_order' => (int)$data['nonteaching_jo'],
                'part_time' => (int)$data['nonteaching_parttime'],
                'casual' => (int)$data['nonteaching_casual']
            ],
            'gender' => [
                'male' => (int)$data['male_count'],
                'female' => (int)$data['female_count'],
                'other' => (int)$data['other_count']
            ]
        ]
    ]);

} catch (Exception $e) {
    // Log error and return error response
    error_log("Error fetching analytics data: " . $e->getMessage());
    header('Content-Type: application/json');
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Error fetching analytics data']);
} finally {
    if (isset($stmt)) {
        $stmt->close();
    }
    if (isset($conn)) {
        $conn->close();
    }
} 