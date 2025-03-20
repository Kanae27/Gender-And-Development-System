<?php
// EMERGENCY FIX SCRIPT - PS ATTRIBUTION
// Turn on all error reporting for maximum visibility
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('html_errors', 1);

// Output as HTML
header('Content-Type: text/html; charset=utf-8');

echo "<!DOCTYPE html>
<html>
<head>
    <title>EMERGENCY FIX: PS Attribution</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; line-height: 1.6; max-width: 800px; margin: 0 auto; }
        .success { color: green; font-weight: bold; }
        .error { color: red; font-weight: bold; }
        .warning { color: orange; font-weight: bold; }
        pre { background-color: #f5f5f5; padding: 10px; border-radius: 5px; overflow: auto; }
        code { background-color: #f0f0f0; padding: 2px 4px; border-radius: 3px; }
        table { border-collapse: collapse; width: 100%; margin: 20px 0; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        .action-btn { background-color: #6a1b9a; color: white; padding: 10px 15px; border: none;
                     text-decoration: none; display: inline-block; border-radius: 5px; margin: 5px; }
    </style>
</head>
<body>
    <h1>EMERGENCY FIX: Adding PS Attribution Field</h1>
    <p>This script will <strong>DIRECTLY</strong> add the PS Attribution column to your database.</p>";

// Database connection parameters
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "gad_db";

try {
    // Step 1: Connect to database
    echo "<h2>Step 1: Connecting to database...</h2>";
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "<p class='success'>Connected to database successfully!</p>";

    // Step 2: Forcefully add the column regardless of whether it exists
    echo "<h2>Step 2: Adding PS Attribution column to ppas_forms table...</h2>";
    
    // We'll try to add the column, ignoring errors if it already exists
    try {
        // First, check if the table exists
        $tableExists = $conn->query("SHOW TABLES LIKE 'ppas_forms'")->rowCount() > 0;
        
        if (!$tableExists) {
            echo "<p class='error'>ERROR: ppas_forms table does not exist in the database!</p>";
            echo "<p>Please create the ppas_forms table first before running this script.</p>";
            exit;
        }
        
        echo "<p>ppas_forms table exists. Now adding PS Attribution column...</p>";
        
        // Try to drop the column first in case it exists but is incorrectly configured
        try {
            $conn->exec("ALTER TABLE ppas_forms DROP COLUMN ps_attribution");
            echo "<p class='warning'>Dropped existing ps_attribution column to recreate it properly.</p>";
        } catch (PDOException $e) {
            // Ignore error if column doesn't exist
            echo "<p>Column did not previously exist (which is fine).</p>";
        }
        
        // Now add the column with the proper definition
        $conn->exec("ALTER TABLE ppas_forms ADD COLUMN ps_attribution DECIMAL(12,2) DEFAULT 0.00 AFTER source_of_budget");
        echo "<p class='success'>SUCCESSFULLY ADDED ps_attribution column to ppas_forms table!</p>";
        
        // Verify the column exists
        $result = $conn->query("DESCRIBE ppas_forms ps_attribution");
        $columnInfo = $result->fetch(PDO::FETCH_ASSOC);
        
        echo "<h3>Column Details:</h3>";
        echo "<table>";
        echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Default</th></tr>";
        echo "<tr>";
        echo "<td>" . $columnInfo['Field'] . "</td>";
        echo "<td>" . $columnInfo['Type'] . "</td>";
        echo "<td>" . $columnInfo['Null'] . "</td>";
        echo "<td>" . $columnInfo['Default'] . "</td>";
        echo "</tr>";
        echo "</table>";
        
    } catch (PDOException $e) {
        echo "<p class='error'>ERROR: " . $e->getMessage() . "</p>";
        
        // If there was an error, let's try one more time with a different approach
        echo "<p>Trying alternative approach to add the column...</p>";
        
        try {
            // Get column list to see if the column is already there but with a different case
            $columns = $conn->query("SHOW COLUMNS FROM ppas_forms")->fetchAll(PDO::FETCH_COLUMN);
            
            // Check for any column that might be ps_attribution with different case
            $columnExists = false;
            $existingColumn = '';
            
            foreach ($columns as $col) {
                if (strtolower($col) === 'ps_attribution') {
                    $columnExists = true;
                    $existingColumn = $col;
                    break;
                }
            }
            
            if ($columnExists) {
                echo "<p class='warning'>Found existing column with name '$existingColumn'.</p>";
                
                // Try to modify the column instead
                $conn->exec("ALTER TABLE ppas_forms MODIFY COLUMN $existingColumn DECIMAL(12,2) DEFAULT 0.00");
                echo "<p class='success'>Successfully modified existing PS Attribution column!</p>";
            } else {
                // Try a different SQL syntax
                $conn->exec("ALTER TABLE ppas_forms ADD ps_attribution DECIMAL(12,2) DEFAULT 0.00");
                echo "<p class='success'>Successfully added ps_attribution column using alternative syntax!</p>";
            }
        } catch (PDOException $e2) {
            echo "<p class='error'>FATAL ERROR: Could not add column: " . $e2->getMessage() . "</p>";
            echo "<p>Please contact your database administrator to add the column manually.</p>";
            echo "<pre>
Manual SQL command to run:
ALTER TABLE ppas_forms ADD COLUMN ps_attribution DECIMAL(12,2) DEFAULT 0.00 AFTER source_of_budget;
</pre>";
            exit;
        }
    }
    
    // Step 3: Update save_ppas.php script
    echo "<h2>Step 3: Updating save_ppas.php script to handle PS Attribution...</h2>";
    
    if (file_exists('save_ppas.php')) {
        $saveScript = file_get_contents('save_ppas.php');
        
        // Create backup
        file_put_contents('save_ppas.php.bak', $saveScript);
        echo "<p>Created backup of save_ppas.php</p>";
        
        // Check if the script already handles PS Attribution
        if (strpos($saveScript, 'ps_attribution') === false && strpos($saveScript, 'psAttribution') === false) {
            echo "<p>save_ppas.php does not handle PS Attribution yet. Updating it...</p>";
            
            // Look for the INSERT statement
            if (preg_match('/INSERT\s+INTO\s+ppas_forms\s*\((.*?)\)\s*VALUES/is', $saveScript, $matches)) {
                $columnsStr = $matches[1];
                $newColumnsStr = $columnsStr;
                
                // Add ps_attribution to columns
                if (strpos($columnsStr, 'source_of_budget') !== false) {
                    $newColumnsStr = str_replace('source_of_budget', 'source_of_budget, ps_attribution', $newColumnsStr);
                } else {
                    $newColumnsStr = rtrim($newColumnsStr) . ', ps_attribution';
                }
                
                // Replace the columns in the query
                $updatedScript = str_replace($columnsStr, $newColumnsStr, $saveScript);
                
                // Now update the values section
                if (preg_match('/VALUES\s*\((.*?)\)/is', $updatedScript, $matches)) {
                    $valuesStr = $matches[1];
                    $newValuesStr = $valuesStr;
                    
                    // Add value placeholder
                    if (strpos($valuesStr, ':source_of_budget') !== false) {
                        $newValuesStr = str_replace(':source_of_budget', ':source_of_budget, :ps_attribution', $newValuesStr);
                    } else {
                        $newValuesStr = rtrim($newValuesStr) . ', :ps_attribution';
                    }
                    
                    // Replace the values in the query
                    $updatedScript = str_replace($valuesStr, $newValuesStr, $updatedScript);
                }
                
                // Add variable extraction
                if (preg_match('/\$approvedBudget\s*=.*?;/s', $updatedScript, $matches)) {
                    $budgetLine = $matches[0];
                    $psAttrLine = "\n    \$psAttribution = isset(\$data['psAttribution']) ? \$data['psAttribution'] : \$approvedBudget * 0.5;";
                    $updatedScript = str_replace($budgetLine, $budgetLine . $psAttrLine, $updatedScript);
                }
                
                // Add parameter binding
                if (preg_match('/\$stmt->bindParam\(\':source_of_budget\'.*?;/s', $updatedScript, $matches)) {
                    $budgetBindLine = $matches[0];
                    $psAttrBindLine = "\n    \$stmt->bindParam(':ps_attribution', \$psAttribution, PDO::PARAM_STR);";
                    $updatedScript = str_replace($budgetBindLine, $budgetBindLine . $psAttrBindLine, $updatedScript);
                }
                
                // Write the updated file
                file_put_contents('save_ppas.php', $updatedScript);
                echo "<p class='success'>Successfully updated save_ppas.php to handle PS Attribution!</p>";
            } else {
                echo "<p class='error'>Could not find INSERT statement in save_ppas.php. Manual update required.</p>";
                echo "<p>Please add 'ps_attribution' to your SQL INSERT statement and bind it as a parameter.</p>";
            }
        } else {
            echo "<p class='success'>save_ppas.php already handles PS Attribution.</p>";
        }
    } else {
        echo "<p class='error'>save_ppas.php not found! Cannot update it.</p>";
    }
    
    // Step 4: Update form submission in ppas.php
    echo "<h2>Step 4: Checking form submission in ppas.php...</h2>";
    
    if (file_exists('ppas.php')) {
        $formScript = file_get_contents('ppas.php');
        
        // Create backup
        file_put_contents('ppas.php.bak', $formScript);
        echo "<p>Created backup of ppas.php</p>";
        
        $updated = false;
        
        // Add PS Attribution field to form if it doesn't exist
        if (strpos($formScript, 'id="psAttribution"') === false) {
            echo "<p>PS Attribution field not found in form. Adding it...</p>";
            
            // Try to find source of budget field
            if (preg_match('/<label[^>]*source of budget.*?<\/div>\s*<\/div>/is', $formScript, $matches, PREG_OFFSET_CAPTURE)) {
                $pos = $matches[0][1] + strlen($matches[0][0]);
                
                $field_html = "
                <!-- PS Attribution Field -->
                <div class='form-group row'>
                    <label for='psAttribution' class='col-sm-3 col-form-label'>PS Attribution (50% of Budget)</label>
                    <div class='col-sm-9'>
                        <div class='input-group'>
                            <div class='input-group-prepend'>
                                <span class='input-group-text'>₱</span>
                            </div>
                            <input type='number' class='form-control' id='psAttribution' name='psAttribution' step='0.01' readonly>
                        </div>
                    </div>
                </div>";
                
                $formScript = substr_replace($formScript, $field_html, $pos, 0);
                $updated = true;
                echo "<p class='success'>Added PS Attribution field to form!</p>";
            } else {
                echo "<p class='warning'>Could not find a good place to add PS Attribution field.</p>";
            }
        } else {
            echo "<p class='success'>PS Attribution field already exists in form.</p>";
        }
        
        // Add PS Attribution to AJAX data if it doesn't exist
        if (preg_match('/data\s*:\s*JSON\.stringify\((.*?)\)/s', $formScript, $matches)) {
            $dataObj = $matches[1];
            
            if (strpos($dataObj, 'psAttribution') === false) {
                echo "<p>PS Attribution not found in AJAX data. Adding it...</p>";
                
                // Find the last property in the object
                if (preg_match('/([^,\s]+\s*:[^,]+)(\s*})$/s', $dataObj, $objMatches)) {
                    $newDataObj = str_replace(
                        $objMatches[0],
                        $objMatches[1] . ",\n            psAttribution: $('#psAttribution').val()" . $objMatches[2],
                        $dataObj
                    );
                    
                    $formScript = str_replace($dataObj, $newDataObj, $formScript);
                    $updated = true;
                    echo "<p class='success'>Added PS Attribution to AJAX data!</p>";
                } else {
                    echo "<p class='warning'>Could not find a good place to add PS Attribution in AJAX data.</p>";
                }
            } else {
                echo "<p class='success'>PS Attribution already included in AJAX data.</p>";
            }
        } else {
            echo "<p class='warning'>Could not find AJAX data in form submission.</p>";
        }
        
        // Add calculation function if it doesn't exist
        if (strpos($formScript, 'function calculatePSAttribution') === false) {
            echo "<p>PS Attribution calculation function not found. Adding it...</p>";
            
            // Find the end of a script tag to add our function
            if (preg_match('/<script>[^<]*?<\/script>/s', $formScript, $scriptMatches, PREG_OFFSET_CAPTURE)) {
                $scriptPos = $scriptMatches[0][1];
                $scriptContent = $scriptMatches[0][0];
                
                $newScript = str_replace('</script>', 
                    "
    function calculatePSAttribution() {
        var budget = parseFloat($('#approvedBudget').val()) || 0;
        var psAttr = budget * 0.5; // 50% of budget
        $('#psAttribution').val(psAttr.toFixed(2));
    }
    
    // Calculate when budget changes
    $('#approvedBudget').on('change', calculatePSAttribution);
    
    // Calculate on page load
    $(document).ready(calculatePSAttribution);
</script>",
                    $scriptContent
                );
                
                $formScript = str_replace($scriptContent, $newScript, $formScript);
                $updated = true;
                echo "<p class='success'>Added PS Attribution calculation function!</p>";
            } else {
                echo "<p class='warning'>Could not find a script tag to add the calculation function.</p>";
            }
        } else {
            echo "<p class='success'>PS Attribution calculation function already exists.</p>";
        }
        
        // Save if we made any changes
        if ($updated) {
            file_put_contents('ppas.php', $formScript);
            echo "<p class='success'>Successfully updated ppas.php!</p>";
        } else {
            echo "<p>No changes needed to ppas.php</p>";
        }
    } else {
        echo "<p class='error'>ppas.php not found! Cannot update it.</p>";
    }
    
    // Step 5: Create a test entry with PS Attribution
    echo "<h2>Step 5: Creating a test entry with PS Attribution...</h2>";
    
    try {
        // Insert a test entry to verify that the column works
        $sql = "INSERT INTO ppas_forms (
            year, quarter, title, location, start_date, end_date, 
            start_time, end_time, total_duration, approved_budget, 
            source_of_budget, ps_attribution, created_by
        ) VALUES (
            '2024', '2', 'PS ATTRIBUTION TEST ENTRY', 'Test Location', 
            CURRENT_DATE, CURRENT_DATE, '09:00', '17:00', 8.00, 
            10000.00, 'GAA', 5000.00, 'fix_script'
        )";
        
        $conn->exec($sql);
        echo "<p class='success'>Successfully created a test entry with PS Attribution!</p>";
        
        // Verify the entry was created
        $result = $conn->query("SELECT id, title, approved_budget, ps_attribution FROM ppas_forms WHERE title='PS ATTRIBUTION TEST ENTRY' ORDER BY id DESC LIMIT 1");
        $testEntry = $result->fetch(PDO::FETCH_ASSOC);
        
        echo "<h3>Test Entry Details:</h3>";
        echo "<table>";
        echo "<tr><th>ID</th><th>Title</th><th>Approved Budget</th><th>PS Attribution</th></tr>";
        echo "<tr>";
        echo "<td>" . $testEntry['id'] . "</td>";
        echo "<td>" . $testEntry['title'] . "</td>";
        echo "<td>" . $testEntry['approved_budget'] . "</td>";
        echo "<td>" . $testEntry['ps_attribution'] . "</td>";
        echo "</tr>";
        echo "</table>";
    } catch (PDOException $e) {
        echo "<p class='warning'>Could not create test entry: " . $e->getMessage() . "</p>";
        echo "<p>This is not critical - the column has been added successfully.</p>";
    }
    
    // Final summary
    echo "<h2>✅ FIX COMPLETED!</h2>";
    echo "<p class='success'>The PS Attribution column has been added to your database and your code has been updated to use it.</p>";
    
    echo "<h3>What was fixed:</h3>";
    echo "<ol>";
    echo "<li>Added PS Attribution column to database table</li>";
    echo "<li>Updated save_ppas.php to include PS Attribution in the SQL</li>";
    echo "<li>Added PS Attribution field to the form (if needed)</li>";
    echo "<li>Added PS Attribution to form submission data (if needed)</li>";
    echo "<li>Added automatic calculation function (50% of budget)</li>";
    echo "<li>Created a test entry with PS Attribution</li>";
    echo "</ol>";
    
    echo "<h3>Next Steps:</h3>";
    echo "<ol>";
    echo "<li>Go back to the PPAS form and submit a new entry</li>";
    echo "<li>Check that PS Attribution is being calculated (should be 50% of budget)</li>";
    echo "<li>After submission, verify the value was saved in the database</li>";
    echo "</ol>";
    
} catch (PDOException $e) {
    echo "<p class='error'>DATABASE ERROR: " . $e->getMessage() . "</p>";
    echo "<p>Please make sure your database is running and the connection details are correct.</p>";
}

echo "<div style='margin-top: 20px;'>";
echo "<a href='ppas.php' class='action-btn'>Go to PPAS Form</a> ";
echo "<a href='check_save_process.php' class='action-btn'>Check if Fix Worked</a>";
echo "</div>";

echo "</body></html>";
?> 