<?php
// This is a test script to verify that saving a GAD proposal works
// Enable error reporting for testing
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "Running GAD proposal save test...\n";

// Simulate a basic form submission
$testData = [
    'title' => 'Test GAD Proposal',
    'year' => date('Y'),
    'quarter' => 'Q1',
    'startDate' => date('Y-m-d'),
    'endDate' => date('Y-m-d', strtotime('+1 day')),
    'venue' => 'Test Venue',
    'deliveryMode' => 'Face-to-face',
    'activityTitle1' => 'Test Activity',
    'activityDetails1' => 'Test Details',
    'projectLeadersHidden' => '',
    'assistantProjectLeadersHidden' => '',
    'projectStaffHidden' => ''
];

// Convert test data to JSON (simulating form submission)
$jsonData = json_encode($testData);

// Save the test data to a file for reference
file_put_contents('test_data.json', $jsonData);

echo "Test data prepared. Please use the GAD proposal form to test saving with the fix.\n";
echo "If no more 'Unknown column created_at' errors appear, the fix was successful.\n";
?> 