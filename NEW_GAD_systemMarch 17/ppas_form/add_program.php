<?php
require_once('../config.php');

// Set header to return JSON
header('Content-Type: application/json');

// Get program name from POST data
$program_name = isset($_POST['program_name']) ? trim($_POST['program_name']) : '';

// Validate input
if (empty($program_name)) {
    echo json_encode(['success' => false, 'message' => 'Program name is required']);
    exit;
}

try {
    // Check if program already exists
    $check_stmt = $conn->prepare("SELECT id FROM programs WHERE program_name = ?");
    $check_stmt->bind_param('s', $program_name);
    $check_stmt->execute();
    $result = $check_stmt->get_result();
    
    if ($result->num_rows > 0) {
        echo json_encode(['success' => false, 'message' => 'Program already exists']);
        exit;
    }
    
    // Insert new program
    $insert_stmt = $conn->prepare("INSERT INTO programs (program_name) VALUES (?)");
    $insert_stmt->bind_param('s', $program_name);
    
    if ($insert_stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Program added successfully']);
    } else {
        throw new Exception("Failed to add program");
    }
    
} catch (Exception $e) {
    error_log("Error in add_program.php: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Failed to add program: ' . $e->getMessage()]);
} 