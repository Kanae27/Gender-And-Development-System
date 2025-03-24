<?php
require_once('../config.php');

// Set header to return JSON
header('Content-Type: application/json');

// Get project name from POST data
$project_name = isset($_POST['project_name']) ? trim($_POST['project_name']) : '';

// Validate input
if (empty($project_name)) {
    echo json_encode(['success' => false, 'message' => 'Project name is required']);
    exit;
}

try {
    // Check if project already exists
    $check_stmt = $conn->prepare("SELECT id FROM projects WHERE project_name = ?");
    $check_stmt->bind_param('s', $project_name);
    $check_stmt->execute();
    $result = $check_stmt->get_result();
    
    if ($result->num_rows > 0) {
        echo json_encode(['success' => false, 'message' => 'Project already exists']);
        exit;
    }
    
    // Insert new project
    $insert_stmt = $conn->prepare("INSERT INTO projects (project_name) VALUES (?)");
    $insert_stmt->bind_param('s', $project_name);
    
    if ($insert_stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Project added successfully']);
    } else {
        throw new Exception("Failed to add project");
    }
    
} catch (Exception $e) {
    error_log("Error in add_project.php: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Failed to add project: ' . $e->getMessage()]);
} 