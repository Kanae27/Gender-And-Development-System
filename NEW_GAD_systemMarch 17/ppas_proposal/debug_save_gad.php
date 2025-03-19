<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Debug Save GAD Proposal</h1>";

// Check if session is working
session_start();
echo "<h2>Session Test</h2>";
echo "<p>Session ID: " . session_id() . "</p>";
$_SESSION['test'] = 'Test value';
echo "<p>Session test value set</p>";

// Test database connection
echo "<h2>Database Connection Test</h2>";
try {
    require_once '../includes/db_connection.php';
    echo "<p>✅ Database connection successful</p>";
    
    // Check if gad_proposals table exists
    $stmt = $conn->query("SHOW TABLES LIKE 'gad_proposals'");
    if ($stmt->rowCount() > 0) {
        echo "<p>✅ gad_proposals table exists</p>";
    } else {
        echo "<p>❌ gad_proposals table does not exist!</p>";
        echo "<p>Please run the <a href='create_gad_tables.php'>create_gad_tables.php</a> script first.</p>";
    }
} catch (Exception $e) {
    echo "<p>❌ Database connection failed: " . $e->getMessage() . "</p>";
}

// Test form submission
echo "<h2>Form Submission Test</h2>";
echo "<form method='post' action='save_gad_proposal.php' id='testForm'>";
echo "<input type='hidden' name='year' value='2023'>";
echo "<input type='hidden' name='quarter' value='Q1'>";
echo "<input type='hidden' name='activityTitle' value='Test Activity'>";
echo "<input type='hidden' name='startDate' value='2023-01-01'>";
echo "<input type='hidden' name='endDate' value='2023-01-31'>";
echo "<input type='hidden' name='venue' value='Test Venue'>";
echo "<input type='hidden' name='deliveryMode' value='Online'>";
echo "<button type='submit'>Submit Test Data</button>";
echo "</form>";

// Add AJAX submission option
echo "<h2>AJAX Submission Test</h2>";
echo "<button id='ajaxSubmit'>Submit via AJAX</button>";
echo "<div id='ajaxResult' style='margin-top: 10px; padding: 10px; border: 1px solid #ccc;'></div>";

// Add JavaScript for AJAX test
echo "<script>
document.getElementById('ajaxSubmit').addEventListener('click', async function() {
    const resultDiv = document.getElementById('ajaxResult');
    resultDiv.innerHTML = 'Sending request...';
    
    try {
        const formData = new FormData(document.getElementById('testForm'));
        
        const response = await fetch('save_gad_proposal.php', {
            method: 'POST',
            body: formData
        });
        
        resultDiv.innerHTML += '<br>Response status: ' + response.status;
        
        const contentType = response.headers.get('content-type');
        resultDiv.innerHTML += '<br>Content-Type: ' + contentType;
        
        const text = await response.text();
        resultDiv.innerHTML += '<br>Response text: <pre>' + text + '</pre>';
        
        try {
            const json = JSON.parse(text);
            resultDiv.innerHTML += '<br>Parsed JSON: <pre>' + JSON.stringify(json, null, 2) + '</pre>';
        } catch (e) {
            resultDiv.innerHTML += '<br>Failed to parse JSON: ' + e.message;
        }
    } catch (e) {
        resultDiv.innerHTML += '<br>Error: ' + e.message;
    }
});
</script>";

// Check PHP configuration
echo "<h2>PHP Configuration</h2>";
echo "<p>PHP Version: " . phpversion() . "</p>";
echo "<p>display_errors: " . ini_get('display_errors') . "</p>";
echo "<p>error_reporting: " . ini_get('error_reporting') . "</p>";
echo "<p>log_errors: " . ini_get('log_errors') . "</p>";
echo "<p>error_log: " . ini_get('error_log') . "</p>";
echo "<p>max_execution_time: " . ini_get('max_execution_time') . "</p>";
echo "<p>memory_limit: " . ini_get('memory_limit') . "</p>";
echo "<p>post_max_size: " . ini_get('post_max_size') . "</p>";
echo "<p>upload_max_filesize: " . ini_get('upload_max_filesize') . "</p>";

// Check server information
echo "<h2>Server Information</h2>";
echo "<p>SERVER_SOFTWARE: " . $_SERVER['SERVER_SOFTWARE'] . "</p>";
echo "<p>REQUEST_URI: " . $_SERVER['REQUEST_URI'] . "</p>";
echo "<p>SCRIPT_NAME: " . $_SERVER['SCRIPT_NAME'] . "</p>";
echo "<p>DOCUMENT_ROOT: " . $_SERVER['DOCUMENT_ROOT'] . "</p>";
?> 