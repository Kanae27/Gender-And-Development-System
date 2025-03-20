<?php
// Minimal test script
session_start();
$_SESSION['username'] = 'test_user'; // Set a test username

echo '<!DOCTYPE html>
<html>
<head>
    <title>Simple PPAS Test</title>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>
    <h1>Simple PPAS Form Test</h1>
    <button id="testBtn">Test API</button>
    <div id="result" style="margin-top: 20px; white-space: pre-wrap;"></div>

    <script>
    $(document).ready(function() {
        $("#testBtn").click(function() {
            const testData = {
                year: "2024",
                quarter: "1",
                title: "Test Program",
                location: "Test Location",
                startDate: "2024-05-01",
                endDate: "2024-05-02",
                startTime: "09:00",
                endTime: "17:00",
                hasLunchBreak: true,
                hasAMBreak: false,
                hasPMBreak: false,
                totalDuration: "16 hours",
                rawTotalDuration: 16,
                approvedBudget: "50000",
                sourceOfBudget: "GAA",
                psAttribution: "25000",
                personnel: {},
                beneficiaries: {},
                sdgs: []
            };
            
            $("#result").text("Sending request...");
            
            $.ajax({
                url: "save_ppas.php",
                method: "POST",
                data: JSON.stringify(testData),
                contentType: "application/json",
                dataType: "json",
                success: function(response) {
                    $("#result").text("Success: " + JSON.stringify(response, null, 2));
                },
                error: function(xhr, status, error) {
                    $("#result").text("Error: " + error + "\n\nResponse: " + xhr.responseText);
                }
            });
        });
    });
    </script>
</body>
</html>';
?> 