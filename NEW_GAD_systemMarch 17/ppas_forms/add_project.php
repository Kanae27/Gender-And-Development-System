<?php
require_once('../config.php');

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: application/json');
$response = array('success' => false, 'message' => '', 'debug' => array());

try {
    // Log POST data
    $response['debug']['post'] = $_POST;
    
    if(!isset($_POST['project_name'])) {
        throw new Exception('Project name not provided in POST data');
    }

    $project_name = trim($_POST['project_name']);
    $response['debug']['project_name'] = $project_name;
    
    if(empty($project_name)) {
        throw new Exception('Project name cannot be empty');
    }

    // Test database connection
    if ($conn->connect_error) {
        throw new Exception("Database connection failed: " . $conn->connect_error);
    }
    
    // Insert new project
    $query = "INSERT INTO projects (project_name) VALUES (?)";
    $stmt = $conn->prepare($query);
    
    if (!$stmt) {
        throw new Exception("Prepare failed: " . $conn->error);
    }
    
    $stmt->bind_param('s', $project_name);
    
    if (!$stmt->execute()) {
        throw new Exception("Execute failed: " . $stmt->error);
    }
    
    if ($stmt->affected_rows > 0) {
        $response['success'] = true;
        $response['message'] = "Project '$project_name' added successfully";
    } else {
        throw new Exception("No rows were inserted");
    }

} catch (Exception $e) {
    $response['success'] = false;
    $response['message'] = $e->getMessage();
    $response['debug']['error'] = $e->getMessage();
    error_log("Error in add_project.php: " . $e->getMessage());
}

echo json_encode($response); 