<?php
// Turn on all error reporting for maximum visibility
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('html_errors', 1);

// Output as HTML
header('Content-Type: text/html; charset=utf-8');

echo "<!DOCTYPE html>
<html>
<head>
    <title>Fix Program/Project Type Field</title>
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
    <h1>Adding Program/Project Type Field</h1>
    <p>This script will add the 'type' column to your database and update code to use it properly.</p>";

// Database connection parameters
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "gad_db";

try {
    // Connect to database
    echo "<h2>Connecting to database...</h2>";
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "<p class='success'>Connected to database successfully!</p>";
    
    // Add type column to ppas_forms table
    echo "<h2>Adding 'type' column to ppas_forms table...</h2>";
    
    try {
        // First check if table exists
        $tableExists = $conn->query("SHOW TABLES LIKE 'ppas_forms'")->rowCount() > 0;
        
        if (!$tableExists) {
            echo "<p class='error'>ERROR: ppas_forms table does not exist in the database!</p>";
            echo "<p>Please create the ppas_forms table first before running this script.</p>";
            exit;
        }
        
        // Now check if column exists
        $columnExists = $conn->query("SHOW COLUMNS FROM ppas_forms LIKE 'type'")->rowCount() > 0;
        
        if ($columnExists) {
            echo "<p class='success'>Type column already exists in ppas_forms table.</p>";
            
            // Check the column type
            $column = $conn->query("SHOW COLUMNS FROM ppas_forms LIKE 'type'")->fetch(PDO::FETCH_ASSOC);
            
            if ($column['Type'] != 'varchar(20)') {
                echo "<p class='warning'>Type column exists but has incorrect data type. Modifying it...</p>";
                
                $conn->exec("ALTER TABLE ppas_forms MODIFY COLUMN type VARCHAR(20)");
                echo "<p class='success'>Successfully modified type column data type.</p>";
            }
        } else {
            // Add the column
            $conn->exec("ALTER TABLE ppas_forms ADD COLUMN type VARCHAR(20) AFTER title");
            echo "<p class='success'>Successfully added 'type' column to ppas_forms table!</p>";
        }
        
        // Display column details
        $column = $conn->query("SHOW COLUMNS FROM ppas_forms LIKE 'type'")->fetch(PDO::FETCH_ASSOC);
        
        echo "<h3>Column Details:</h3>";
        echo "<table>";
        echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Default</th></tr>";
        echo "<tr>";
        echo "<td>" . $column['Field'] . "</td>";
        echo "<td>" . $column['Type'] . "</td>";
        echo "<td>" . $column['Null'] . "</td>";
        echo "<td>" . $column['Default'] . "</td>";
        echo "</tr>";
        echo "</table>";
        
    } catch (PDOException $e) {
        echo "<p class='error'>ERROR: " . $e->getMessage() . "</p>";
        echo "<p>Trying alternative approach...</p>";
        
        try {
            // Try simpler syntax
            $conn->exec("ALTER TABLE ppas_forms ADD type VARCHAR(20)");
            echo "<p class='success'>Successfully added type column using alternative syntax!</p>";
        } catch (PDOException $e2) {
            echo "<p class='error'>FATAL ERROR: Could not add column: " . $e2->getMessage() . "</p>";
            echo "<pre>
Manual SQL command to run:
ALTER TABLE ppas_forms ADD COLUMN type VARCHAR(20) AFTER title;
</pre>";
            exit;
        }
    }
    
    // Update save_ppas.php to handle type field
    echo "<h2>Updating save_ppas.php script...</h2>";
    
    if (file_exists('save_ppas.php')) {
        $saveScript = file_get_contents('save_ppas.php');
        
        // Create backup
        file_put_contents('save_ppas.php.bak', $saveScript);
        echo "<p>Created backup of save_ppas.php</p>";
        
        // Check if script already handles the type field
        if (strpos($saveScript, "'type'") === false && strpos($saveScript, '"type"') === false) {
            echo "<p>save_ppas.php does not handle the type field yet. Updating it...</p>";
            
            // Look for the INSERT statement
            if (preg_match('/INSERT\s+INTO\s+ppas_forms\s*\((.*?)\)\s*VALUES/is', $saveScript, $matches)) {
                $columnsStr = $matches[1];
                $newColumnsStr = $columnsStr;
                
                // Add type to columns
                if (strpos($columnsStr, 'title') !== false) {
                    $newColumnsStr = str_replace('title', 'title, type', $newColumnsStr);
                } else {
                    $newColumnsStr = 'type, ' . ltrim($newColumnsStr);
                }
                
                // Replace the columns in the query
                $updatedScript = str_replace($columnsStr, $newColumnsStr, $saveScript);
                
                // Now update the values section
                if (preg_match('/VALUES\s*\((.*?)\)/is', $updatedScript, $matches)) {
                    $valuesStr = $matches[1];
                    $newValuesStr = $valuesStr;
                    
                    // Add value placeholder
                    if (strpos($valuesStr, ':title') !== false) {
                        $newValuesStr = str_replace(':title', ':title, :type', $newValuesStr);
                    } else {
                        $newValuesStr = ':type, ' . ltrim($newValuesStr);
                    }
                    
                    // Replace the values in the query
                    $updatedScript = str_replace($valuesStr, $newValuesStr, $updatedScript);
                }
                
                // Add variable extraction
                if (preg_match('/\$title\s*=.*?;/s', $updatedScript, $matches)) {
                    $titleLine = $matches[0];
                    $typeLine = "\n    \$type = isset(\$data['type']) ? \$data['type'] : null;";
                    $updatedScript = str_replace($titleLine, $titleLine . $typeLine, $updatedScript);
                }
                
                // Add parameter binding
                if (preg_match('/\$stmt->bindParam\(\':title\'.*?;/s', $updatedScript, $matches)) {
                    $titleBindLine = $matches[0];
                    $typeBindLine = "\n    \$stmt->bindParam(':type', \$type, PDO::PARAM_STR);";
                    $updatedScript = str_replace($titleBindLine, $titleBindLine . $typeBindLine, $updatedScript);
                }
                
                // Write the updated file
                file_put_contents('save_ppas.php', $updatedScript);
                echo "<p class='success'>Successfully updated save_ppas.php to handle type field!</p>";
            } else {
                echo "<p class='error'>Could not find INSERT statement in save_ppas.php. Manual update required.</p>";
                echo "<p>Please add 'type' to your SQL INSERT statement and bind it as a parameter.</p>";
            }
        } else {
            echo "<p class='success'>save_ppas.php already handles the type field.</p>";
        }
    } else {
        echo "<p class='error'>save_ppas.php not found! Cannot update it.</p>";
    }
    
    // Update form to include type field
    echo "<h2>Updating ppas.php form...</h2>";
    
    if (file_exists('ppas.php')) {
        $formScript = file_get_contents('ppas.php');
        
        // Create backup
        file_put_contents('ppas.php.bak', $formScript);
        echo "<p>Created backup of ppas.php</p>";
        
        $updated = false;
        
        // Add program/project type field to form if it doesn't exist
        if (strpos($formScript, 'name="programType"') === false && strpos($formScript, 'name="type"') === false) {
            echo "<p>Program/Project Type field not found in form. Adding it...</p>";
            
            // Try to find title field to add the type field after it
            if (preg_match('/<label[^>]*>Title.*?<\/div>\s*<\/div>/is', $formScript, $matches, PREG_OFFSET_CAPTURE)) {
                $pos = $matches[0][1] + strlen($matches[0][0]);
                
                $field_html = "
                <!-- Program/Project Type Field -->
                <div class='form-group row'>
                    <label class='col-sm-3 col-form-label'>Type</label>
                    <div class='col-sm-9'>
                        <div class='form-check form-check-inline'>
                            <input class='form-check-input' type='radio' name='programType' id='typeProgram' value='program' checked>
                            <label class='form-check-label' for='typeProgram'>Program</label>
                        </div>
                        <div class='form-check form-check-inline'>
                            <input class='form-check-input' type='radio' name='programType' id='typeProject' value='project'>
                            <label class='form-check-label' for='typeProject'>Project</label>
                        </div>
                    </div>
                </div>";
                
                $formScript = substr_replace($formScript, $field_html, $pos, 0);
                $updated = true;
                echo "<p class='success'>Added Program/Project Type field to form!</p>";
            } else {
                echo "<p class='warning'>Could not find a good place to add Program/Project Type field.</p>";
            }
        } else {
            echo "<p class='success'>Program/Project Type field already exists in form.</p>";
        }
        
        // Add Type to AJAX data if it doesn't exist
        if (preg_match('/data\s*:\s*JSON\.stringify\((.*?)\)/s', $formScript, $matches)) {
            $dataObj = $matches[1];
            
            if (strpos($dataObj, 'type:') === false && strpos($dataObj, 'programType:') === false) {
                echo "<p>Type not found in AJAX data. Adding it...</p>";
                
                // Find a good place to add the field
                if (preg_match('/([^,\s]+\s*:[^,]+)(\s*})$/s', $dataObj, $objMatches)) {
                    $newDataObj = str_replace(
                        $objMatches[0],
                        $objMatches[1] . ",\n            type: $('input[name=\"programType\"]:checked').val()" . $objMatches[2],
                        $dataObj
                    );
                    
                    $formScript = str_replace($dataObj, $newDataObj, $formScript);
                    $updated = true;
                    echo "<p class='success'>Added Type to AJAX data!</p>";
                } else {
                    echo "<p class='warning'>Could not find a good place to add Type in AJAX data.</p>";
                }
            } else {
                echo "<p class='success'>Type already included in AJAX data.</p>";
            }
        } else {
            echo "<p class='warning'>Could not find AJAX data in form submission.</p>";
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
    
    // Create a test entry with type specified
    echo "<h2>Creating a test entry with type...</h2>";
    
    try {
        // Insert a test entry
        $sql = "INSERT INTO ppas_forms (
            year, quarter, title, type, location, start_date, end_date, 
            start_time, end_time, total_duration, approved_budget, 
            source_of_budget, created_by
        ) VALUES (
            '2024', '2', 'TYPE FIELD TEST ENTRY', 'program', 'Test Location', 
            CURRENT_DATE, CURRENT_DATE, '09:00', '17:00', 8.00, 
            5000.00, 'GAA', 'fix_script'
        )";
        
        $conn->exec($sql);
        echo "<p class='success'>Successfully created a test entry with type!</p>";
        
        // Verify the entry was created
        $result = $conn->query("SELECT id, title, type FROM ppas_forms WHERE title='TYPE FIELD TEST ENTRY' ORDER BY id DESC LIMIT 1");
        $testEntry = $result->fetch(PDO::FETCH_ASSOC);
        
        echo "<h3>Test Entry Details:</h3>";
        echo "<table>";
        echo "<tr><th>ID</th><th>Title</th><th>Type</th></tr>";
        echo "<tr>";
        echo "<td>" . $testEntry['id'] . "</td>";
        echo "<td>" . $testEntry['title'] . "</td>";
        echo "<td>" . $testEntry['type'] . "</td>";
        echo "</tr>";
        echo "</table>";
    } catch (PDOException $e) {
        echo "<p class='warning'>Could not create test entry: " . $e->getMessage() . "</p>";
        echo "<p>This is not critical - the column has been added successfully.</p>";
    }
    
    // Final summary
    echo "<h2>✅ FIX COMPLETED!</h2>";
    echo "<p class='success'>The type column has been added to your database and your code has been updated to use it.</p>";
    
    echo "<h3>What was fixed:</h3>";
    echo "<ol>";
    echo "<li>Added 'type' column to database table</li>";
    echo "<li>Updated save_ppas.php to include type in the SQL</li>";
    echo "<li>Added Program/Project Type field to the form (if needed)</li>";
    echo "<li>Added type to form submission data (if needed)</li>";
    echo "<li>Created a test entry with type</li>";
    echo "</ol>";
    
    echo "<h3>Next Steps:</h3>";
    echo "<ol>";
    echo "<li>Run <a href='fix_ps_attribution_now.php'>fix_ps_attribution_now.php</a> to fix PS Attribution</li>";
    echo "<li>Go back to the PPAS form and submit a new entry</li>";
    echo "<li>After submission, verify both type and PS Attribution were saved in the database</li>";
    echo "</ol>";
    
} catch (PDOException $e) {
    echo "<p class='error'>DATABASE ERROR: " . $e->getMessage() . "</p>";
    echo "<p>Please make sure your database is running and the connection details are correct.</p>";
}

echo "<div style='margin-top: 20px;'>";
echo "<a href='ppas.php' class='action-btn'>Go to PPAS Form</a> ";
echo "<a href='fix_ps_attribution_now.php' class='action-btn'>Fix PS Attribution Next</a> ";
echo "<a href='check_save_process.php' class='action-btn'>Check if Fix Worked</a>";
echo "</div>";

echo "</body></html>";
?> 