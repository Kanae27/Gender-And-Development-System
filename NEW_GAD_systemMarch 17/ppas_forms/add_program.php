<?php
require_once('../config.php');

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Add CORS headers
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');

// Start output buffering
ob_start();

$response = array('success' => false, 'message' => '', 'debug' => array());

try {
    // Log request method and data
    $response['debug']['request_method'] = $_SERVER['REQUEST_METHOD'];
    $response['debug']['post_data'] = $_POST;
    $response['debug']['raw_input'] = file_get_contents('php://input');
    
    if(!isset($_POST['program_name'])) {
        throw new Exception('Program name not provided in POST data');
    }

    $program_name = trim($_POST['program_name']);
    $response['debug']['program_name'] = $program_name;
    
    if(empty($program_name)) {
        throw new Exception('Program name cannot be empty');
    }

    // Test database connection
    if (!$conn || $conn->connect_error) {
        throw new Exception("Database connection failed: " . ($conn ? $conn->connect_error : 'Connection is null'));
    }
    
    $response['debug']['database_connected'] = true;
    
    // Insert new program
    $query = "INSERT INTO programs (program_name) VALUES (?)";
    $stmt = $conn->prepare($query);
    
    if (!$stmt) {
        throw new Exception("Prepare failed: " . $conn->error);
    }
    
    $response['debug']['prepare_successful'] = true;
    
    $stmt->bind_param('s', $program_name);
    $response['debug']['bind_successful'] = true;
    
    if (!$stmt->execute()) {
        throw new Exception("Execute failed: " . $stmt->error);
    }
    
    $response['debug']['execute_successful'] = true;
    $response['debug']['affected_rows'] = $stmt->affected_rows;
    
    if ($stmt->affected_rows > 0) {
        $response['success'] = true;
        $response['message'] = "Program '$program_name' added successfully";
    } else {
        throw new Exception("No rows were inserted");
    }

} catch (Exception $e) {
    $response['success'] = false;
    $response['message'] = $e->getMessage();
    $response['debug']['error'] = $e->getMessage();
    $response['debug']['error_trace'] = $e->getTraceAsString();
    error_log("Error in add_program.php: " . $e->getMessage());
}

// Get any output from the buffer
$response['debug']['output_buffer'] = ob_get_clean();

echo json_encode($response); 