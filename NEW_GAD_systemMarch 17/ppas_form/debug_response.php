<?php
// Turn on error reporting for debugging but capture errors rather than display them
error_reporting(E_ALL);
ini_set('display_errors', 0);

// Log function for detailed debugging
function debug_log($message) {
    file_put_contents('debug_log.txt', date('[Y-m-d H:i:s]') . " DEBUG: " . $message . "\n", FILE_APPEND);
}

// Clear any previous output before we start
if (ob_get_level()) ob_end_clean();

// Start output buffering to capture any errors or unexpected output
ob_start();

// Set the content type to JSON
header('Content-Type: application/json');

// Get the raw input data
$rawInput = file_get_contents('php://input');

// Log the raw input for debugging
debug_log("Raw input received: " . $rawInput);

// Try to parse the JSON
$inputData = json_decode($rawInput, true);

// Check if parsing was successful
if ($inputData === null && json_last_error() !== JSON_ERROR_NONE) {
    debug_log("JSON parsing error: " . json_last_error_msg());
    $response = [
        'success' => false,
        'message' => 'Invalid JSON: ' . json_last_error_msg(),
        'raw_input' => substr($rawInput, 0, 200) . (strlen($rawInput) > 200 ? '...' : '')
    ];
} else {
    debug_log("JSON parsed successfully: " . json_encode($inputData));
    
    // Here we're just echoing back the data for testing
    $response = [
        'success' => true,
        'message' => 'Data received successfully',
        'data_received' => $inputData
    ];
}

// Get any output that might have been generated
$output = ob_get_clean();

// If there was unexpected output, include it in the response
if (!empty($output)) {
    debug_log("Unexpected output captured: " . $output);
    $response['unexpected_output'] = $output;
}

// Return the response as JSON
echo json_encode($response);
debug_log("Response sent: " . json_encode($response));
?> 