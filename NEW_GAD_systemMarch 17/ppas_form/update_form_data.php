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
    <title>Update PPAS Form Submission</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; line-height: 1.6; }
        .success { color: green; font-weight: bold; }
        .error { color: red; font-weight: bold; }
        .warning { color: orange; font-weight: bold; }
        pre { background-color: #f5f5f5; padding: 10px; border-radius: 5px; overflow: auto; max-height: 400px; }
        code { background-color: #f0f0f0; padding: 2px 4px; border-radius: 3px; }
        .diff-added { background-color: #e6ffec; color: #24292e; }
        .diff-removed { background-color: #ffebe9; color: #24292e; }
        .action-btn { background-color: #6a1b9a; color: white; padding: 10px 15px; border: none;
                     text-decoration: none; display: inline-block; border-radius: 5px; margin: 5px; }
    </style>
</head>
<body>
    <h1>Update PPAS Form Submission</h1>
    <p>This script will update the form submission in ppas.php to include Program/Project Type and PS Attribution values.</p>";

try {
    // Check if ppas.php exists
    echo "<h2>Checking ppas.php file...</h2>";
    
    if (!file_exists('ppas.php')) {
        echo "<p class='error'>ppas.php file not found!</p>";
        exit;
    }
    
    echo "<p class='success'>Found ppas.php file.</p>";
    
    // Read the original file
    $original_code = file_get_contents('ppas.php');
    
    // Create backup
    file_put_contents('ppas.php.bak', $original_code);
    echo "<p>Created backup: ppas.php.bak</p>";
    
    // Find AJAX form submission code
    echo "<h2>Analyzing form submission code...</h2>";
    
    // Look for AJAX submission
    if (preg_match_all('/\$.ajax\(\s*{\s*url\s*:\s*[\'"]save_ppas\.php[\'"]/s', $original_code, $matches, PREG_OFFSET_CAPTURE)) {
        echo "<p class='success'>Found " . count($matches[0]) . " AJAX form submission(s) to save_ppas.php</p>";
        
        $updated_code = $original_code;
        $changes_made = false;
        
        foreach ($matches[0] as $key => $match) {
            $pos = $match[1];
            
            // Find the data part of this AJAX call
            if (preg_match('/data\s*:\s*JSON\.stringify\((.*?)\)(?=\s*,|\s*})/s', $updated_code, $data_matches, PREG_OFFSET_CAPTURE, $pos)) {
                $data_content = $data_matches[1][0];
                $data_pos = $data_matches[1][1];
                
                echo "<h3>Examining form data:</h3>";
                echo "<pre>" . htmlspecialchars($data_content) . "</pre>";
                
                // Check if PS Attribution is included
                $ps_attr_included = preg_match('/psAttribution\s*:/i', $data_content);
                
                if (!$ps_attr_included) {
                    echo "<p class='warning'>PS Attribution field not found in form data - adding it.</p>";
                    
                    // Find a good place to add the field (before the last property)
                    if (preg_match('/([^\s,]+\s*:\s*[^,]+)(\s*})$/s', $data_content, $end_matches)) {
                        $new_data_content = str_replace(
                            $end_matches[0],
                            $end_matches[1] . ',
            psAttribution: $("#psAttribution").val()' . $end_matches[2],
                            $data_content
                        );
                        
                        // Replace the data content
                        $updated_code = substr_replace(
                            $updated_code,
                            $new_data_content,
                            $data_pos,
                            strlen($data_content)
                        );
                        
                        $changes_made = true;
                    }
                } else {
                    echo "<p class='success'>PS Attribution field is already included in form data.</p>";
                }
                
                // Re-fetch the data content if it was updated
                if (preg_match('/data\s*:\s*JSON\.stringify\((.*?)\)(?=\s*,|\s*})/s', $updated_code, $data_matches, PREG_OFFSET_CAPTURE, $pos)) {
                    $data_content = $data_matches[1][0];
                    $data_pos = $data_matches[1][1];
                }
                
                // Check if Type is included
                $type_included = preg_match('/type\s*:/i', $data_content) || 
                                preg_match('/programType\s*:/i', $data_content);
                
                if (!$type_included) {
                    echo "<p class='warning'>Type field not found in form data - adding it.</p>";
                    
                    // Find a good place to add the field (before the last property)
                    if (preg_match('/([^\s,]+\s*:\s*[^,]+)(\s*})$/s', $data_content, $end_matches)) {
                        $new_data_content = str_replace(
                            $end_matches[0],
                            $end_matches[1] . ',
            type: $("input[name=\'programType\']:checked").val()' . $end_matches[2],
                            $data_content
                        );
                        
                        // Replace the data content
                        $updated_code = substr_replace(
                            $updated_code,
                            $new_data_content,
                            $data_pos,
                            strlen($data_content)
                        );
                        
                        $changes_made = true;
                    }
                } else {
                    echo "<p class='success'>Type field is already included in form data.</p>";
                }
            } else {
                echo "<p class='error'>Could not find data section in AJAX call.</p>";
            }
        }
        
        // Check for PS Attribution calculation function
        if (strpos($updated_code, 'function calculatePSAttribution') === false) {
            echo "<h2>Adding PS Attribution calculation function...</h2>";
            
            // Find a suitable location to add the function (at the end of the script section)
            if (preg_match('/<\/script>\s*$/m', $updated_code, $script_end_matches, PREG_OFFSET_CAPTURE)) {
                $pos = $script_end_matches[0][1];
                
                $function_code = "
    // Function to calculate PS Attribution based on approved budget 
    function calculatePSAttribution() {
        var approvedBudget = parseFloat($('#approvedBudget').val()) || 0;
        var psAttribution = approvedBudget * 0.50; // 50% of budget as default
        $('#psAttribution').val(psAttribution.toFixed(2));
    }
    
    // Calculate PS Attribution when approved budget changes
    $('#approvedBudget').on('change', function() {
        calculatePSAttribution();
    });
    
    // Calculate initial PS Attribution
    $(document).ready(function() {
        calculatePSAttribution();
    });
</script>";
                
                $updated_code = substr_replace($updated_code, $function_code, $pos, strlen('</script>'));
                $changes_made = true;
            }
        } else {
            echo "<p class='success'>PS Attribution calculation function already exists.</p>";
        }
        
        // Check for PS Attribution field in the form
        if (strpos($updated_code, 'id="psAttribution"') === false) {
            echo "<h2>Looking for a place to add PS Attribution field to form...</h2>";
            
            // Try to find source of budget field to add PS Attribution field after it
            if (preg_match('/source of budget.*?<\/div>\s*<\/div>/is', $updated_code, $source_matches, PREG_OFFSET_CAPTURE)) {
                $pos = $source_matches[0][1] + strlen($source_matches[0][0]);
                
                $field_html = "
                <!-- PS Attribution Field -->
                <div class='form-group row'>
                    <label for='psAttribution' class='col-sm-3 col-form-label'>PS Attribution</label>
                    <div class='col-sm-9'>
                        <div class='input-group'>
                            <div class='input-group-prepend'>
                                <span class='input-group-text'>₱</span>
                            </div>
                            <input type='number' class='form-control' id='psAttribution' name='psAttribution' step='0.01' readonly>
                            <div class='input-group-append'>
                                <span class='input-group-text'>.00</span>
                            </div>
                        </div>
                        <small class='form-text text-muted'>PS Attribution is calculated as 50% of the approved budget</small>
                    </div>
                </div>";
                
                $updated_code = substr_replace($updated_code, $field_html, $pos, 0);
                $changes_made = true;
            } else {
                echo "<p class='warning'>Could not find suitable location to add PS Attribution field. You may need to add it manually.</p>";
            }
        } else {
            echo "<p class='success'>PS Attribution field already exists in the form.</p>";
        }
        
        // Check for programType field in the form
        $program_type_exists = strpos($updated_code, 'name="programType"') !== false || 
                               strpos($updated_code, 'id="programType"') !== false ||
                               strpos($updated_code, 'name="type"') !== false;
        
        if (!$program_type_exists) {
            echo "<h2>Looking for a place to add Program/Project Type field to form...</h2>";
            
            // Try to find title field to add Type field after it
            if (preg_match('/<label[^>]*>Title.*?<\/div>\s*<\/div>/is', $updated_code, $title_matches, PREG_OFFSET_CAPTURE)) {
                $pos = $title_matches[0][1] + strlen($title_matches[0][0]);
                
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
                
                $updated_code = substr_replace($updated_code, $field_html, $pos, 0);
                $changes_made = true;
            } else {
                echo "<p class='warning'>Could not find suitable location to add Program/Project Type field. You may need to add it manually.</p>";
            }
        } else {
            echo "<p class='success'>Program/Project Type field already exists in the form.</p>";
        }
        
        // Write the updated code if changes were made
        if ($changes_made) {
            file_put_contents('ppas.php', $updated_code);
            echo "<p class='success'>Updated ppas.php with the necessary changes.</p>";
            
            // Show a summary of changes
            echo "<h2>Summary of Changes:</h2>";
            echo "<ul>";
            if (!$ps_attr_included) {
                echo "<li>Added PS Attribution field to form submission data</li>";
            }
            if (!$type_included) {
                echo "<li>Added Type field to form submission data</li>";
            }
            if (strpos($original_code, 'function calculatePSAttribution') === false && 
                strpos($updated_code, 'function calculatePSAttribution') !== false) {
                echo "<li>Added PS Attribution calculation function</li>";
            }
            if (strpos($original_code, 'id="psAttribution"') === false && 
                strpos($updated_code, 'id="psAttribution"') !== false) {
                echo "<li>Added PS Attribution field to form</li>";
            }
            if (!$program_type_exists && strpos($updated_code, 'name="programType"') !== false) {
                echo "<li>Added Program/Project Type field to form</li>";
            }
            echo "</ul>";
        } else {
            echo "<p class='success'>No changes needed - ppas.php already contains the required fields.</p>";
        }
    } else {
        echo "<p class='error'>Could not find AJAX submission to save_ppas.php. Manual update required.</p>";
        echo "<pre>
When you save your form, make sure to include the following fields in your AJAX data:

data: JSON.stringify({
    // ... other fields ...
    psAttribution: $('#psAttribution').val(),
    type: $('input[name=\"programType\"]:checked').val()
})
</pre>";
    }
    
    // Final message
    echo "<h2>Next Steps:</h2>";
    echo "<ol>";
    echo "<li>Run <a href='fix_save_ppas.php'>fix_save_ppas.php</a> to ensure save_ppas.php can handle PS Attribution and Type fields</li>";
    echo "<li>Submit a new form to test if PS Attribution and Type are being saved correctly</li>";
    echo "<li>Check the database to confirm values are stored properly</li>";
    echo "</ol>";
    
} catch (Exception $e) {
    echo "<p class='error'>Error: " . $e->getMessage() . "</p>";
}

echo "<div style='margin-top: 20px;'>";
echo "<a href='ppas.php' class='action-btn'>Go to PPAS Form</a> ";
echo "<a href='fix_save_ppas.php' class='action-btn'>Fix Save Script</a> ";
echo "<a href='check_save_process.php' class='action-btn'>Run Diagnostics</a>";
echo "</div>";

echo "</body></html>";
?> 