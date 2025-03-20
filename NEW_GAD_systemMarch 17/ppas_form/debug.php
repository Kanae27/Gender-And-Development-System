<?php
// Set content type to HTML for better display
header('Content-Type: text/html; charset=utf-8');
session_start();

// Set a username for testing if not already set
if (!isset($_SESSION['username'])) {
    $_SESSION['username'] = 'debug_test_user';
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>PPAS Form Debug Tool</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; line-height: 1.6; }
        .success { color: green; font-weight: bold; }
        .error { color: red; font-weight: bold; }
        pre { background-color: #f5f5f5; padding: 10px; border-radius: 5px; overflow: auto; max-height: 400px; }
        h1, h2 { color: #333; }
        button { padding: 10px 15px; background-color: #6a1b9a; color: white; border: none; cursor: pointer; border-radius: 4px; }
        #response { margin-top: 20px; }
    </style>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>
    <h1>PPAS Form Debug Tool</h1>
    <p>This tool submits a test form to save_ppas.php to help diagnose JSON issues.</p>
    <p>Current session username: <strong><?php echo $_SESSION['username']; ?></strong></p>

    <button id="testButton">Test Simple Submission</button>
    <button id="viewLogButton">View Error Log</button>
    <a href="check_database.php" target="_blank">Check Database Structure</a>
    <a href="add_missing_columns.php" target="_blank">Add Missing Columns</a>

    <div id="response"></div>

    <script>
    $(document).ready(function() {
        $('#testButton').click(function() {
            // Create a minimal test payload with all required fields
            const testData = {
                year: '2024',
                quarter: '1',
                title: 'Debug Test Program',
                location: 'Test Location',
                startDate: '2024-05-01',
                endDate: '2024-05-02',
                startTime: '09:00',
                endTime: '17:00',
                hasLunchBreak: true,
                totalDuration: '16 hours',
                rawTotalDuration: 16,
                approvedBudget: '50000',
                sourceOfBudget: 'GAA',
                psAttribution: '25000',
                personnel: {},
                beneficiaries: {},
                sdgs: []
            };
            
            // Log the data being sent
            console.log('Sending test data:', testData);
            
            // Show the data in the response area
            $('#response').html('<h2>Sending Request...</h2><pre>' + JSON.stringify(testData, null, 2) + '</pre>');
            
            // Submit the test data
            $.ajax({
                url: 'save_ppas.php',
                method: 'POST',
                data: JSON.stringify(testData),
                contentType: 'application/json',
                dataType: 'json',
                success: function(response) {
                    console.log('Success response:', response);
                    $('#response').append(
                        '<h2 class="success">Success Response:</h2>' +
                        '<pre>' + JSON.stringify(response, null, 2) + '</pre>'
                    );
                },
                error: function(xhr, status, error) {
                    console.error('Error:', error);
                    console.error('Response Text:', xhr.responseText);
                    
                    $('#response').append(
                        '<h2 class="error">Error (' + xhr.status + '): ' + error + '</h2>' +
                        '<h3>Response Content:</h3>' +
                        '<pre>' + xhr.responseText.substring(0, 500) + 
                        (xhr.responseText.length > 500 ? '...' : '') + '</pre>'
                    );
                }
            });
        });

        $('#viewLogButton').click(function() {
            // Make a simple request to view the error log
            $('#response').html('<h2>Loading error log...</h2>');
            
            $.ajax({
                url: 'view_log.php',
                method: 'GET',
                success: function(response) {
                    $('#response').html('<h2>Error Log Contents:</h2><pre>' + response + '</pre>');
                },
                error: function() {
                    $('#response').html('<h2 class="error">Error: Could not load log file</h2>' +
                        '<p>The view_log.php script might not exist yet.</p>');
                }
            });
        });
    });
    </script>
</body>
</html> 