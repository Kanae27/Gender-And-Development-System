<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Start the session
session_start();

echo "<h1>Test Form Submission</h1>";

// Check if form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    echo "<h2>Form Submitted</h2>";
    echo "<pre>";
    print_r($_POST);
    echo "</pre>";
    
    // Forward the request to save_gad_proposal.php
    echo "<h2>Forwarding to save_gad_proposal.php</h2>";
    
    // Save the original POST data
    $originalPost = $_POST;
    
    // Create a new cURL resource
    $ch = curl_init();
    
    // Set URL and other appropriate options
    $url = 'http://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['REQUEST_URI']) . '/save_gad_proposal.php';
    echo "Sending request to: " . $url . "<br>";
    
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($originalPost));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    
    // Execute and get the response
    $response = curl_exec($ch);
    $info = curl_getinfo($ch);
    $error = curl_error($ch);
    
    // Close cURL resource
    curl_close($ch);
    
    // Display the results
    echo "<h3>cURL Info</h3>";
    echo "<pre>";
    print_r($info);
    echo "</pre>";
    
    if ($error) {
        echo "<h3>cURL Error</h3>";
        echo "<p>" . $error . "</p>";
    }
    
    echo "<h3>Response</h3>";
    echo "<pre>";
    echo htmlspecialchars($response);
    echo "</pre>";
    
    // Try to decode JSON response
    $jsonResponse = json_decode($response, true);
    if ($jsonResponse !== null) {
        echo "<h3>Decoded JSON Response</h3>";
        echo "<pre>";
        print_r($jsonResponse);
        echo "</pre>";
    } else {
        echo "<h3>Failed to decode JSON response</h3>";
        echo "<p>JSON error: " . json_last_error_msg() . "</p>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test Form Submission</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        form { margin-bottom: 20px; }
        label { display: block; margin-top: 10px; }
        input, textarea { width: 100%; padding: 8px; margin-top: 5px; }
        button { padding: 10px 15px; background-color: #4CAF50; color: white; border: none; cursor: pointer; margin-top: 15px; }
        pre { background-color: #f5f5f5; padding: 10px; overflow: auto; }
    </style>
</head>
<body>
    <h2>Test Form</h2>
    <form method="post" action="">
        <label for="year">Year:</label>
        <input type="text" id="year" name="year" value="2023" required>
        
        <label for="quarter">Quarter:</label>
        <input type="text" id="quarter" name="quarter" value="Q1" required>
        
        <label for="activityTitle">Activity Title:</label>
        <input type="text" id="activityTitle" name="activityTitle" value="Test Activity" required>
        
        <label for="startDate">Start Date:</label>
        <input type="date" id="startDate" name="startDate" value="2023-01-01" required>
        
        <label for="endDate">End Date:</label>
        <input type="date" id="endDate" name="endDate" value="2023-01-31" required>
        
        <label for="venue">Venue:</label>
        <input type="text" id="venue" name="venue" value="Test Venue" required>
        
        <label for="deliveryMode">Delivery Mode:</label>
        <input type="text" id="deliveryMode" name="deliveryMode" value="Online" required>
        
        <label for="projectLeaders">Project Leaders:</label>
        <input type="text" id="projectLeaders" name="projectLeaders" value="John Doe">
        
        <label for="maleBeneficiaries">Male Beneficiaries:</label>
        <input type="number" id="maleBeneficiaries" name="maleBeneficiaries" value="10">
        
        <label for="femaleBeneficiaries">Female Beneficiaries:</label>
        <input type="number" id="femaleBeneficiaries" name="femaleBeneficiaries" value="15">
        
        <label for="totalBeneficiaries">Total Beneficiaries:</label>
        <input type="number" id="totalBeneficiaries" name="totalBeneficiaries" value="25">
        
        <label for="rationale">Rationale:</label>
        <textarea id="rationale" name="rationale">Test rationale</textarea>
        
        <label for="totalBudget">Total Budget:</label>
        <input type="number" id="totalBudget" name="totalBudget" value="10000" step="0.01">
        
        <button type="submit">Submit Test Form</button>
    </form>
</body>
</html> 