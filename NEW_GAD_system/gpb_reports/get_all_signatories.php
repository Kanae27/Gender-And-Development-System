<?php
header('Content-Type: application/json');
session_start();

// Include database connection
require_once '../config.php';

// Get campus from session
$campus = isset($_SESSION['username']) ? $_SESSION['username'] : '';

try {
    $query = "SELECT * FROM signatories";
    $params = [];
    
    // If not Central user, filter by campus
    if ($campus !== 'Central') {
        $query .= " WHERE campus = ?";
        $params[] = $campus;
    }
    
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    
    $signatories = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if ($signatories && count($signatories) > 0) {
        echo json_encode([
            'status' => 'success',
            'data' => $signatories
        ]);
    } else {
        echo json_encode([
            'status' => 'error',
            'message' => 'No signatories found for ' . ($campus === 'Central' ? 'any campus' : "campus '$campus'")
        ]);
    }
} catch (PDOException $e) {
    error_log("Error fetching all signatories: " . $e->getMessage());
    echo json_encode([
        'status' => 'error',
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
?> 