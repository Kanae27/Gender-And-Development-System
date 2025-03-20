<?php
// Turn off all error reporting and HTML errors for production
error_reporting(0);
ini_set('display_errors', 0);
ini_set('html_errors', 0);

// Ensure we're sending JSON response
header('Content-Type: application/json');

// Get JSON input and parse
$jsonInput = file_get_contents('php://input');
$data = json_decode($jsonInput, true);

// Always return exists=false to allow multiple entries
echo json_encode([
    'success' => true,
    'exists' => false,
    'year' => isset($data['year']) ? $data['year'] : '',
    'quarter' => isset($data['quarter']) ? $data['quarter'] : ''
]);
?> 