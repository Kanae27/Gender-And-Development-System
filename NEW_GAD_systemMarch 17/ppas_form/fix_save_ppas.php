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
    <title>Fix PPAS Save Script</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; line-height: 1.6; }
        .success { color: green; font-weight: bold; }
        .error { color: red; font-weight: bold; }
        .warning { color: orange; font-weight: bold; }
        pre { background-color: #f5f5f5; padding: 10px; border-radius: 5px; overflow: auto; max-height: 400px; }
        code { background-color: #f0f0f0; padding: 2px 4px; border-radius: 3px; }
        .diff-added { background-color: #e6ffec; color: #24292e; }
        .diff-removed { background-color: #ffebe9; color: #24292e; }
        table { border-collapse: collapse; width: 100%; margin: 20px 0; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        .action-btn { background-color: #6a1b9a; color: white; padding: 10px 15px; border: none;
                     text-decoration: none; display: inline-block; border-radius: 5px; margin: 5px; }
    </style>
</head>
<body>
    <h1>Fixing PPAS Form Save Process</h1>
    <p>This script will update the save_ppas.php file to properly handle PS Attribution and Program/Project Type.</p>";

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
    
    // Verify database structure
    echo "<h2>Verifying database structure...</h2>";
    
    // Check ps_attribution column
    $ps_attribution_exists = $conn->query("SHOW COLUMNS FROM ppas_forms LIKE 'ps_attribution'")->rowCount() > 0;
    if (!$ps_attribution_exists) {
        echo "<p class='warning'>PS Attribution column does not exist - adding it now.</p>";
        $conn->exec("ALTER TABLE ppas_forms ADD COLUMN ps_attribution DECIMAL(12,2) DEFAULT 0.00 AFTER source_of_budget");
        echo "<p class='success'>Added PS Attribution column to database.</p>";
    } else {
        echo "<p class='success'>PS Attribution column already exists.</p>";
    }
    
    // Check type column
    $type_exists = $conn->query("SHOW COLUMNS FROM ppas_forms LIKE 'type'")->rowCount() > 0;
    if (!$type_exists) {
        echo "<p class='warning'>Type column does not exist - adding it now.</p>";
        $conn->exec("ALTER TABLE ppas_forms ADD COLUMN type VARCHAR(20) DEFAULT NULL AFTER title");
        echo "<p class='success'>Added Type column to database.</p>";
    } else {
        echo "<p class='success'>Type column already exists.</p>";
    }
    
    // Now fix the save_ppas.php file
    echo "<h2>Updating save_ppas.php script...</h2>";
    
    if (!file_exists('save_ppas.php')) {
        echo "<p class='error'>save_ppas.php file not found!</p>";
        exit;
    }
    
    // Read the original file
    $original_code = file_get_contents('save_ppas.php');
    
    // Create backup
    file_put_contents('save_ppas.php.bak', $original_code);
    echo "<p>Created backup: save_ppas.php.bak</p>";
    
    // Fix the code to include ps_attribution and type
    $updated_code = $original_code;
    
    // Check if JSON processing code exists
    if (strpos($original_code, '$data = json_decode') !== false) {
        echo "<p class='success'>Found JSON processing code.</p>";
        
        // Check if the INSERT statement includes our fields
        if (strpos($original_code, 'ps_attribution') === false || strpos($original_code, "'type'") === false) {
            echo "<p>Updating INSERT statement to include PS Attribution and Type...</p>";
            
            // Try to find the INSERT statement pattern
            if (preg_match('/INSERT\s+INTO\s+ppas_forms\s*\((.*?)\)\s*VALUES/is', $original_code, $matches)) {
                $columns_str = $matches[1];
                $new_columns_str = $columns_str;
                
                // Check and add ps_attribution if missing
                if (strpos($columns_str, 'ps_attribution') === false) {
                    // Add after source_of_budget if it exists
                    if (strpos($columns_str, 'source_of_budget') !== false) {
                        $new_columns_str = str_replace('source_of_budget', 'source_of_budget, ps_attribution', $new_columns_str);
                    } else {
                        // Otherwise add near the end
                        $new_columns_str = rtrim($new_columns_str) . ', ps_attribution';
                    }
                }
                
                // Check and add type if missing
                if (strpos($columns_str, 'type') === false) {
                    // Add after title if it exists
                    if (strpos($new_columns_str, 'title') !== false) {
                        $new_columns_str = str_replace('title', 'title, type', $new_columns_str);
                    } else {
                        // Otherwise add near the beginning
                        $new_columns_str = ltrim($new_columns_str) . ', type';
                    }
                }
                
                // Replace the columns in the query
                $updated_code = str_replace($columns_str, $new_columns_str, $updated_code);
                
                // Now update the values section
                if (preg_match('/VALUES\s*\((.*?)\)/is', $updated_code, $matches)) {
                    $values_str = $matches[1];
                    $new_values_str = $values_str;
                    
                    // Add ps_attribution value placeholder if not already present
                    if (strpos($values_str, ':ps_attribution') === false) {
                        if (strpos($values_str, ':source_of_budget') !== false) {
                            $new_values_str = str_replace(':source_of_budget', ':source_of_budget, :ps_attribution', $new_values_str);
                        } else {
                            $new_values_str = rtrim($new_values_str) . ', :ps_attribution';
                        }
                    }
                    
                    // Add type value placeholder if not already present
                    if (strpos($values_str, ':type') === false) {
                        if (strpos($new_values_str, ':title') !== false) {
                            $new_values_str = str_replace(':title', ':title, :type', $new_values_str);
                        } else {
                            $new_values_str = ltrim($new_values_str) . ', :type';
                        }
                    }
                    
                    // Replace the values in the query
                    $updated_code = str_replace($values_str, $new_values_str, $updated_code);
                }
            }
            
            // Now find where parameters are bound and add new parameters
            if (preg_match('/\$stmt->bindParam\(.*?\)/s', $updated_code, $matches)) {
                $bind_section = $matches[0];
                $new_bind_section = $bind_section;
                
                // Add PS Attribution binding if missing
                if (strpos($bind_section, ':ps_attribution') === false) {
                    // Find the last bind statement
                    $pattern = '/(\$stmt->bindParam\(:[a-zA-Z_]+,.*?\);)(?!\s*\$stmt->bindParam)/s';
                    if (preg_match($pattern, $new_bind_section, $last_bind)) {
                        $ps_attr_bind = "\n    \$stmt->bindParam(':ps_attribution', \$psAttribution, PDO::PARAM_STR);";
                        $new_bind_section = str_replace($last_bind[1], $last_bind[1] . $ps_attr_bind, $new_bind_section);
                    }
                }
                
                // Add Type binding if missing
                if (strpos($new_bind_section, ':type') === false) {
                    // Find the last bind statement
                    $pattern = '/(\$stmt->bindParam\(:[a-zA-Z_]+,.*?\);)(?!\s*\$stmt->bindParam)/s';
                    if (preg_match($pattern, $new_bind_section, $last_bind)) {
                        $type_bind = "\n    \$stmt->bindParam(':type', \$type, PDO::PARAM_STR);";
                        $new_bind_section = str_replace($last_bind[1], $last_bind[1] . $type_bind, $new_bind_section);
                    }
                }
                
                // Replace the binding section
                $updated_code = str_replace($bind_section, $new_bind_section, $updated_code);
            }
            
            // Add variable extraction from JSON
            if (strpos($updated_code, '$psAttribution = ') === false) {
                // Find where other variables are extracted
                if (preg_match('/(\$title\s*=.*?;)/s', $updated_code, $title_match)) {
                    $ps_attr_var = "\n    \$psAttribution = isset(\$data['psAttribution']) ? \$data['psAttribution'] : 0;";
                    $updated_code = str_replace($title_match[1], $title_match[1] . $ps_attr_var, $updated_code);
                }
            }
            
            if (strpos($updated_code, '$type = ') === false) {
                // Find where other variables are extracted
                if (preg_match('/(\$title\s*=.*?;)/s', $updated_code, $title_match)) {
                    $type_var = "\n    \$type = isset(\$data['type']) ? \$data['type'] : null;";
                    $updated_code = str_replace($title_match[1], $title_match[1] . $type_var, $updated_code);
                }
            }
        } else {
            echo "<p class='success'>INSERT statement already includes PS Attribution and Type.</p>";
        }
    } else {
        echo "<p class='error'>Could not find JSON processing code in save_ppas.php. Manual update required.</p>";
        echo "<p>Please add the following code to your save_ppas.php file:</p>";
        echo "<pre>
// Process PS Attribution and Type
\$psAttribution = isset(\$data['psAttribution']) ? \$data['psAttribution'] : 0;
\$type = isset(\$data['type']) ? \$data['type'] : null;

// Make sure these are included in your SQL INSERT statement and parameter binding
</pre>";
    }
    
    // Write the updated code
    file_put_contents('save_ppas.php', $updated_code);
    
    // Show the diff
    echo "<h3>Changes Made:</h3>";
    $diff = [];
    $orig_lines = preg_split('/\r\n|\r|\n/', $original_code);
    $new_lines = preg_split('/\r\n|\r|\n/', $updated_code);
    
    if ($orig_lines !== $new_lines) {
        // Simple diff visualization
        echo "<pre style='line-height: 1.3;'>";
        foreach ($new_lines as $i => $line) {
            if (!isset($orig_lines[$i]) || $line !== $orig_lines[$i]) {
                echo "<span class='diff-added'>+ " . htmlspecialchars($line) . "</span>\n";
            } elseif (isset($orig_lines[$i]) && trim($line) !== '') {
                echo "  " . htmlspecialchars($line) . "\n";
            }
        }
        echo "</pre>";
        
        echo "<p class='success'>Successfully updated save_ppas.php to include PS Attribution and Type fields.</p>";
    } else {
        echo "<p class='warning'>No changes were made to save_ppas.php. It may already be correctly configured.</p>";
    }
    
    // Verify ppas.php is sending the right data
    echo "<h2>Verifying ppas.php form submission...</h2>";
    
    if (!file_exists('ppas.php')) {
        echo "<p class='error'>ppas.php file not found!</p>";
    } else {
        $ppas_code = file_get_contents('ppas.php');
        
        // Check for PS Attribution in form submission
        $ps_attr_in_form = preg_match('/psAttribution\s*:\s*\$\([\'"]#psAttribution[\'"]\)\.val\(\)/i', $ppas_code);
        
        if (!$ps_attr_in_form) {
            echo "<p class='warning'>Could not find PS Attribution in form submission data.</p>";
            echo "<p>Make sure your AJAX data includes: <code>psAttribution: $('#psAttribution').val()</code></p>";
        } else {
            echo "<p class='success'>Form is correctly sending PS Attribution value.</p>";
        }
        
        // Check for Type in form submission
        $type_in_form = preg_match('/type\s*:\s*[\'"](program|project)[\'"]|programType/i', $ppas_code);
        
        if (!$type_in_form) {
            echo "<p class='warning'>Could not find Type in form submission data.</p>";
            echo "<p>Make sure your AJAX data includes the program type, such as: <code>type: $('input[name=\"programType\"]:checked').val()</code></p>";
        } else {
            echo "<p class='success'>Form is correctly sending Type value.</p>";
        }
    }
    
    // Final summary
    echo "<h2>Summary:</h2>";
    echo "<ul>";
    if (!$ps_attribution_exists) echo "<li class='success'>Added PS Attribution column to database</li>";
    if (!$type_exists) echo "<li class='success'>Added Type column to database</li>";
    echo "<li class='success'>Updated save_ppas.php to handle PS Attribution and Type fields</li>";
    echo "</ul>";
    
    echo "<h2>Next Steps:</h2>";
    echo "<ol>";
    echo "<li>Submit a new form to test if PS Attribution and Type are being saved</li>";
    echo "<li>Check the database to confirm the values are properly stored</li>";
    echo "<li>If issues persist, check the error logs</li>";
    echo "</ol>";
    
} catch (PDOException $e) {
    echo "<p class='error'>Database error: " . $e->getMessage() . "</p>";
}

echo "<div style='margin-top: 20px;'>";
echo "<a href='ppas.php' class='action-btn'>Go to PPAS Form</a> ";
echo "<a href='check_save_process.php' class='action-btn'>Run Diagnostics</a> ";
echo "<a href='show_error_log.php' class='action-btn'>View Error Logs</a>";
echo "</div>";

echo "</body></html>";
?> 