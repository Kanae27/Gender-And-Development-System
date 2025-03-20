<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('html_errors', 1);

// Set content type to HTML for better display
header('Content-Type: text/html; charset=utf-8');

echo "<!DOCTYPE html>
<html>
<head>
    <title>PPAS Form Saving Diagnostic</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; line-height: 1.6; }
        .success { color: green; font-weight: bold; }
        .error { color: red; font-weight: bold; }
        .warning { color: orange; font-weight: bold; }
        pre { background-color: #f5f5f5; padding: 10px; border-radius: 5px; overflow: auto; max-height: 400px; }
        code { background-color: #f0f0f0; padding: 2px 4px; border-radius: 3px; }
        table { border-collapse: collapse; width: 100%; margin: 20px 0; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        tr:nth-child(even) { background-color: #f9f9f9; }
        .highlight { background-color: #fff3cd; }
        .action-btn { background-color: #6a1b9a; color: white; padding: 10px 15px; border: none;
                     text-decoration: none; display: inline-block; border-radius: 5px; margin: 5px; }
    </style>
</head>
<body>
    <h1>PPAS Form Saving Diagnostic Tool</h1>
    <p>This script will check why PS Attribution and Program/Project Title are not being saved correctly.</p>";

// Database connection parameters
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "gad_db";

try {
    echo "<h2>Connecting to database...</h2>";
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "<p class='success'>Successfully connected to database: $dbname</p>";
    
    // Check required columns
    echo "<h2>Checking required columns in ppas_forms table...</h2>";
    
    // Check ps_attribution column
    $ps_attribution_exists = $conn->query("SHOW COLUMNS FROM ppas_forms LIKE 'ps_attribution'")->rowCount() > 0;
    echo "<p>" . ($ps_attribution_exists ? 
        "<span class='success'>✓ PS Attribution column exists</span>" : 
        "<span class='error'>✗ PS Attribution column does not exist</span>") . "</p>";
    
    // Check type column
    $type_exists = $conn->query("SHOW COLUMNS FROM ppas_forms LIKE 'type'")->rowCount() > 0;
    echo "<p>" . ($type_exists ? 
        "<span class='success'>✓ Type column exists</span>" : 
        "<span class='error'>✗ Type column does not exist</span>") . "</p>";
    
    // Check database records
    echo "<h2>Checking existing records...</h2>";
    
    // Count records with ps_attribution values
    $ps_attr_count = 0;
    if ($ps_attribution_exists) {
        $ps_attr_count = $conn->query("SELECT COUNT(*) FROM ppas_forms WHERE ps_attribution > 0")->fetchColumn();
        echo "<p>" . ($ps_attr_count > 0 ? 
            "<span class='success'>Found $ps_attr_count records with PS Attribution values</span>" : 
            "<span class='warning'>No records found with PS Attribution values</span>") . "</p>";
    }
    
    // Count records with type values
    $type_count = 0;
    if ($type_exists) {
        $type_count = $conn->query("SELECT COUNT(*) FROM ppas_forms WHERE type IS NOT NULL AND type != ''")->fetchColumn();
        echo "<p>" . ($type_count > 0 ? 
            "<span class='success'>Found $type_count records with Type values</span>" : 
            "<span class='warning'>No records found with Type values</span>") . "</p>";
    }
    
    // Get most recent entries
    echo "<h2>Most recent entries from ppas_forms table:</h2>";
    $stmt = $conn->query("SELECT * FROM ppas_forms ORDER BY created_at DESC LIMIT 10");
    $records = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (count($records) > 0) {
        echo "<table>";
        
        // Get all column names
        $columns = array_keys($records[0]);
        
        echo "<tr>";
        foreach ($columns as $column) {
            echo "<th>" . htmlspecialchars($column) . "</th>";
        }
        echo "</tr>";
        
        foreach ($records as $record) {
            echo "<tr>";
            foreach ($record as $key => $value) {
                $class = '';
                
                // Highlight fields we're diagnosing
                if (($key == 'ps_attribution' && ($value === null || $value == 0)) || 
                    ($key == 'type' && ($value === null || $value == ''))) {
                    $class = 'class="highlight"';
                }
                
                echo "<td $class>" . htmlspecialchars($value ?? 'NULL') . "</td>";
            }
            echo "</tr>";
        }
        
        echo "</table>";
    } else {
        echo "<p class='warning'>No records found in the ppas_forms table.</p>";
    }
    
    // Examine save_ppas.php script
    echo "<h2>Examining save_ppas.php script...</h2>";
    
    if (file_exists('save_ppas.php')) {
        $save_script = file_get_contents('save_ppas.php');
        
        // Check if script handles PS Attribution
        $ps_attr_in_script = strpos($save_script, 'ps_attribution') !== false || 
                             strpos($save_script, 'psAttribution') !== false;
        
        echo "<p>" . ($ps_attr_in_script ? 
            "<span class='success'>✓ save_ppas.php includes code for handling PS Attribution</span>" : 
            "<span class='error'>✗ save_ppas.php does not appear to handle PS Attribution</span>") . "</p>";
        
        // Check if script handles Type
        $type_in_script = strpos($save_script, '"type"') !== false || 
                          strpos($save_script, "'type'") !== false;
        
        echo "<p>" . ($type_in_script ? 
            "<span class='success'>✓ save_ppas.php includes code for handling Type</span>" : 
            "<span class='error'>✗ save_ppas.php does not appear to handle Type</span>") . "</p>";
        
        // Find the JSON parse section and field assignment
        echo "<h3>JSON data processing in save_ppas.php:</h3>";
        if (preg_match('/\$jsonInput\s*=\s*file_get_contents\(.*\);.*?\$data\s*=\s*json_decode\(/s', $save_script, $matches)) {
            echo "<p class='success'>✓ Found JSON data processing code</p>";
        } else {
            echo "<p class='error'>✗ Could not find JSON data processing code</p>";
        }
        
        // Find SQL INSERT statement
        echo "<h3>SQL INSERT statement in save_ppas.php:</h3>";
        if (preg_match('/INSERT\s+INTO\s+ppas_forms\s*\((.*?)\)\s*VALUES/is', $save_script, $matches)) {
            $columns_str = $matches[1];
            echo "<p class='success'>✓ Found SQL INSERT statement</p>";
            
            // Check if ps_attribution is in the column list
            $ps_attr_in_sql = strpos($columns_str, 'ps_attribution') !== false;
            echo "<p>" . ($ps_attr_in_sql ? 
                "<span class='success'>✓ PS Attribution is included in the SQL INSERT statement</span>" : 
                "<span class='error'>✗ PS Attribution is NOT included in the SQL INSERT statement</span>") . "</p>";
            
            // Check if type is in the column list
            $type_in_sql = strpos($columns_str, 'type') !== false;
            echo "<p>" . ($type_in_sql ? 
                "<span class='success'>✓ Type is included in the SQL INSERT statement</span>" : 
                "<span class='error'>✗ Type is NOT included in the SQL INSERT statement</span>") . "</p>";
        } else {
            echo "<p class='error'>✗ Could not find SQL INSERT statement</p>";
        }
    } else {
        echo "<p class='error'>Could not find save_ppas.php script</p>";
    }
    
    // Examine ppas.php script for form fields
    echo "<h2>Examining ppas.php for form fields...</h2>";
    
    if (file_exists('ppas.php')) {
        $form_script = file_get_contents('ppas.php');
        
        // Check for PS Attribution field
        $ps_attr_field = strpos($form_script, 'psAttribution') !== false;
        echo "<p>" . ($ps_attr_field ? 
            "<span class='success'>✓ Found PS Attribution field in form</span>" : 
            "<span class='error'>✗ Could not find PS Attribution field in form</span>") . "</p>";
        
        // Check for Type field
        $type_field = strpos($form_script, 'programType') !== false || 
                      strpos($form_script, 'type') !== false;
        
        echo "<p>" . ($type_field ? 
            "<span class='success'>✓ Found Type field in form</span>" : 
            "<span class='error'>✗ Could not find Type field in form</span>") . "</p>";
        
        // Check AJAX submission in form
        echo "<h3>AJAX form submission in ppas.php:</h3>";
        if (preg_match('/\$.ajax\(\s*{\s*url\s*:\s*[\'"]save_ppas\.php[\'"]/s', $form_script, $matches)) {
            echo "<p class='success'>✓ Found AJAX submission to save_ppas.php</p>";
            
            // Check data preparation
            if (preg_match('/data\s*:\s*JSON\.stringify\((.*?)\)/s', $form_script, $data_matches)) {
                echo "<p class='success'>✓ Found data preparation for AJAX submission</p>";
                
                // Check if PS Attribution is included
                $ps_attr_in_data = strpos($data_matches[1], 'psAttribution') !== false;
                echo "<p>" . ($ps_attr_in_data ? 
                    "<span class='success'>✓ PS Attribution is included in the AJAX data</span>" : 
                    "<span class='error'>✗ PS Attribution is NOT included in the AJAX data</span>") . "</p>";
                
                // Check if Type is included
                $type_in_data = strpos($data_matches[1], 'type') !== false || 
                               strpos($data_matches[1], 'programType') !== false;
                               
                echo "<p>" . ($type_in_data ? 
                    "<span class='success'>✓ Type is included in the AJAX data</span>" : 
                    "<span class='error'>✗ Type is NOT included in the AJAX data</span>") . "</p>";
            } else {
                echo "<p class='error'>✗ Could not find data preparation for AJAX submission</p>";
            }
        } else {
            echo "<p class='error'>✗ Could not find AJAX submission to save_ppas.php</p>";
        }
    } else {
        echo "<p class='error'>Could not find ppas.php script</p>";
    }
    
    // Provide a summary and recommendations
    echo "<h2>Summary and Recommendations:</h2>";
    echo "<ul>";
    
    if (!$ps_attribution_exists) {
        echo "<li class='error'>PS Attribution column does not exist in database - Run <a href='add_ps_attribution.php'>add_ps_attribution.php</a> to add it.</li>";
    } else if ($ps_attr_count == 0) {
        echo "<li class='warning'>PS Attribution column exists but no values have been saved.</li>";
    }
    
    if (!$type_exists) {
        echo "<li class='error'>Type column does not exist in database - Run <a href='update_columns.php'>update_columns.php</a> to add it.</li>";
    } else if ($type_count == 0) {
        echo "<li class='warning'>Type column exists but no values have been saved.</li>";
    }
    
    if (isset($ps_attr_in_script) && !$ps_attr_in_script) {
        echo "<li class='error'>save_ppas.php does not handle PS Attribution - The script needs to be updated.</li>";
    }
    
    if (isset($type_in_script) && !$type_in_script) {
        echo "<li class='error'>save_ppas.php does not handle Type - The script needs to be updated.</li>";
    }
    
    if (isset($ps_attr_field) && !$ps_attr_field) {
        echo "<li class='error'>PS Attribution field not found in form - Check ppas.php for field definition.</li>";
    }
    
    if (isset($type_field) && !$type_field) {
        echo "<li class='error'>Type field not found in form - Check ppas.php for field definition.</li>";
    }
    
    echo "</ul>";
    
    // Fix option
    echo "<h2>Fix Options:</h2>";
    echo "<a href='fix_save_ppas.php' class='action-btn'>Fix save_ppas.php Script</a> ";
    echo "<a href='update_columns.php' class='action-btn'>Update Database Columns</a> ";
    echo "<a href='ppas.php' class='action-btn'>Go to PPAS Form</a>";

} catch (PDOException $e) {
    echo "<p class='error'>Database error: " . $e->getMessage() . "</p>";
}

echo "</body></html>";
?> 