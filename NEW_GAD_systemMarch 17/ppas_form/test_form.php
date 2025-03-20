<?php
session_start();
// Set a test username if not logged in
if (!isset($_SESSION['username'])) {
    $_SESSION['username'] = 'test_user';
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>PPAS Form Debug Test</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; line-height: 1.6; }
        .success { color: green; font-weight: bold; }
        .error { color: red; font-weight: bold; }
        pre { background-color: #f5f5f5; padding: 10px; border-radius: 5px; overflow: auto; max-height: 400px; }
        h1, h2 { color: #333; }
        .hidden { display: none; }
        button { padding: 10px 15px; margin: 5px; cursor: pointer; }
        textarea { width: 100%; height: 300px; font-family: monospace; }
    </style>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>
    <h1>PPAS Form Debug Test</h1>
    <p>This tool will help diagnose issues with the PPAS form submission.</p>
    
    <div>
        <button id="testDebugEndpoint">1. Test Debug Endpoint</button>
        <button id="testSimpleDebugEndpoint">2. Test Simple Debug Endpoint</button>
        <button id="testSaveEndpoint">3. Test Save PPAS Endpoint</button>
        <button id="viewLogButton">4. View Debug Log</button>
        <a href="check_database.php" target="_blank"><button>5. Check Database Structure</button></a>
        <a href="show_error_log.php" target="_blank"><button>6. View PHP Error Logs</button></a>
    </div>
    
    <div style="margin-top: 20px;">
        <h2>Test Data (JSON):</h2>
        <textarea id="testData">{
  "year": "2024",
  "quarter": "1",
  "title": "Debug Test Program",
  "location": "Test Location",
  "startDate": "2024-05-01",
  "endDate": "2024-05-02",
  "startTime": "09:00",
  "endTime": "17:00",
  "hasLunchBreak": true,
  "hasAMBreak": false,
  "hasPMBreak": false,
  "totalDuration": "16 hours",
  "rawTotalDuration": 16,
  "approvedBudget": "50000",
  "sourceOfBudget": "GAA",
  "psAttribution": "25000",
  "personnel": {
    "projectLeader": [],
    "asstProjectLeader": [],
    "projectStaff": [],
    "otherParticipants": []
  },
  "beneficiaries": {
    "internalMaleStudents": 0,
    "internalFemaleStudents": 0,
    "internalMaleFaculty": 0,
    "internalFemaleFaculty": 0
  },
  "sdgs": [1, 2]
}</textarea>
    </div>
    
    <div id="response" style="margin-top: 20px;">
        <h2>Response:</h2>
        <div id="responseContent"></div>
    </div>
    
    <script>
    $(document).ready(function() {
        // Helper function for AJAX calls
        function sendTestData(url, successMsg) {
            const testData = $('#testData').val();
            
            try {
                // Validate the JSON first
                JSON.parse(testData);
                
                // Show what we're doing
                $('#responseContent').html('<p>Sending request to ' + url + '...</p>');
                
                // Send to endpoint
                $.ajax({
                    url: url,
                    method: 'POST',
                    data: testData,
                    contentType: 'application/json',
                    dataType: 'json',
                    success: function(response) {
                        $('#responseContent').html(
                            '<p class="success">' + successMsg + '</p>' +
                            '<pre>' + JSON.stringify(response, null, 2) + '</pre>'
                        );
                    },
                    error: function(xhr, status, error) {
                        $('#responseContent').html(
                            '<p class="error">Error: ' + error + '</p>' +
                            '<p>Status: ' + xhr.status + '</p>' +
                            '<p>Response Text:</p>' +
                            '<pre>' + xhr.responseText + '</pre>'
                        );
                    }
                });
            } catch (e) {
                $('#responseContent').html('<p class="error">Invalid JSON in test data: ' + e.message + '</p>');
            }
        }
        
        // Test Debug Endpoint
        $('#testDebugEndpoint').click(function() {
            sendTestData('debug_response.php', 'Debug endpoint success!');
        });
        
        // Test Simple Debug Endpoint
        $('#testSimpleDebugEndpoint').click(function() {
            sendTestData('save_ppas_debug.php', 'Simple debug endpoint success!');
        });
        
        // Test Save PPAS Endpoint
        $('#testSaveEndpoint').click(function() {
            sendTestData('save_ppas.php', 'Save PPAS success!');
        });

        // View Debug Log
        $('#viewLogButton').click(function() {
            $('#responseContent').html('<p>Loading debug log...</p>');
            
            // Simple AJAX request to get the debug log
            $.ajax({
                url: 'get_debug_log.php',
                method: 'GET',
                success: function(response) {
                    $('#responseContent').html('<pre>' + response + '</pre>');
                },
                error: function() {
                    $('#responseContent').html(
                        '<p class="error">Error: Could not load debug log</p>' +
                        '<p>You may need to create the get_debug_log.php file first. ' +
                        'It should read and return the contents of debug_log.txt</p>'
                    );
                }
            });
        });
    });
    </script>
</body>
</html> 