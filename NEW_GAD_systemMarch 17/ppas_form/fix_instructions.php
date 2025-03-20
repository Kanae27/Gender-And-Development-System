<?php
header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html>
<head>
    <title>Fix for PPAS Form JSON Error</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; line-height: 1.6; max-width: 800px; margin: 0 auto; }
        .success { color: green; font-weight: bold; }
        .error { color: red; font-weight: bold; }
        .warning { color: orange; font-weight: bold; }
        pre { background-color: #f5f5f5; padding: 10px; border-radius: 5px; overflow: auto; }
        h1, h2, h3 { color: #333; }
        .step { margin-bottom: 20px; padding: 15px; border-left: 3px solid #6a1b9a; background-color: #f9f9f9; }
        code { background-color: #f0f0f0; padding: 2px 4px; border-radius: 3px; }
        .action-btn { background-color: #6a1b9a; color: white; padding: 10px 15px; border: none;
                      text-decoration: none; display: inline-block; border-radius: 5px; margin: 5px; }
    </style>
</head>
<body>
    <h1>How to Fix the PPAS Form JSON Error</h1>
    
    <div class="error">
        <p><strong>Current Error:</strong> Unexpected token '&lt;', "&lt;br /&gt;
        &lt;fo"... is not valid JSON</p>
    </div>
    
    <p>This error occurs when PHP is returning HTML error messages instead of valid JSON. We've created several diagnostic tools and fixes to help resolve this issue.</p>
    
    <h2>Steps to Fix the Issue:</h2>
    
    <div class="step">
        <h3>Step 1: Update Database Structure</h3>
        <p>First, make sure your database has all the required columns:</p>
        <p><a href="update_columns.php" class="action-btn" target="_blank">Update Database Columns</a></p>
        <p>This will add missing columns like <code>ps_attribution</code>, <code>duration_metadata</code>, <code>gender_issue</code>, and <code>type</code> to your database.</p>
    </div>
    
    <div class="step">
        <h3>Step 2: Test Form Submission</h3>
        <p>We've created a simple test form to help diagnose any remaining issues:</p>
        <p><a href="test_form.php" class="action-btn" target="_blank">Open Test Form</a></p>
        <p>This form allows you to test the AJAX submission process with a clean interface and provides better error feedback.</p>
    </div>
    
    <div class="step">
        <h3>Step 3: Check PHP Error Logs</h3>
        <p>If you're still having issues, check the PHP error logs for more details:</p>
        <p><a href="show_error_log.php" class="action-btn" target="_blank">View PHP Error Logs</a></p>
        <p>These logs may contain important information about what's causing the error.</p>
    </div>
    
    <div class="step">
        <h3>Step 4: Try the Modified PPAS Form</h3>
        <p>After completing steps 1-3, return to the PPAS form and try submitting it again:</p>
        <p><a href="ppas.php" class="action-btn" target="_blank">Open PPAS Form</a></p>
        <p>The form should now work correctly with the fixed save_ppas.php script.</p>
    </div>
    
    <h2>Technical Details of the Fix:</h2>
    
    <p>The main issues we identified and fixed were:</p>
    
    <ol>
        <li><strong>Output Buffering:</strong> The script was capturing JSON responses in the output buffer instead of sending them directly to the client.</li>
        <li><strong>Database Schema:</strong> Missing columns required for storing PS Attribution and Duration metadata.</li>
        <li><strong>Error Handling:</strong> Improved error handling and reporting to provide more meaningful error messages.</li>
    </ol>
    
    <p class="warning"><strong>Note:</strong> If you make changes to the PHP files, make sure your webserver can write to them and that they have the correct permissions.</p>
    
    <h2>Testing the Fix:</h2>
    
    <p>Use the diagnostic tools we've created to test the fix:</p>
    
    <ul>
        <li><a href="update_columns.php">update_columns.php</a> - Updates database structure</li>
        <li><a href="test_form.php">test_form.php</a> - Tests form submission</li>
        <li><a href="show_error_log.php">show_error_log.php</a> - Views PHP error logs</li>
        <li><a href="debug_response.php">debug_response.php</a> - Diagnostic endpoint for testing JSON</li>
    </ul>
    
    <p>If you're still experiencing issues after following these steps, consider checking:</p>
    
    <ul>
        <li>PHP version and configuration</li>
        <li>Webserver error logs</li>
        <li>Database connection settings</li>
    </ul>
</body>
</html> 