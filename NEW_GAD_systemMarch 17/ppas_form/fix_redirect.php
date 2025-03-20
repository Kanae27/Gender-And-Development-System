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
    <title>Fix PPAS Form Redirect</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; line-height: 1.6; max-width: 800px; margin: 0 auto; }
        .success { color: green; font-weight: bold; }
        .error { color: red; font-weight: bold; }
        .warning { color: orange; font-weight: bold; }
        pre { background-color: #f5f5f5; padding: 10px; border-radius: 5px; overflow: auto; }
        code { background-color: #f0f0f0; padding: 2px 4px; border-radius: 3px; }
        .action-btn { background-color: #6a1b9a; color: white; padding: 10px 15px; border: none;
                     text-decoration: none; display: inline-block; border-radius: 5px; margin: 5px; }
    </style>
</head>
<body>
    <h1>Fixing PPAS Form Redirect After Submission</h1>
    <p>This script will update your form to properly redirect to the first tab after successful submission.</p>";

// Check if ppas.php exists
if (!file_exists('ppas.php')) {
    echo "<p class='error'>Error: ppas.php file not found!</p>";
    echo "<p>Please make sure you're running this script from the correct directory.</p>";
    exit;
}

// Create backup
echo "<h2>Step 1: Creating backup of ppas.php...</h2>";
if (!copy('ppas.php', 'ppas.php.backup_redirect')) {
    echo "<p class='error'>Failed to create backup file!</p>";
    echo "<p>Please check file permissions and try again.</p>";
    exit;
}
echo "<p class='success'>Successfully created backup at ppas.php.backup_redirect</p>";

// Read the file content
echo "<h2>Step 2: Reading ppas.php file...</h2>";
$ppasContent = file_get_contents('ppas.php');
if ($ppasContent === false) {
    echo "<p class='error'>Failed to read ppas.php file!</p>";
    exit;
}
echo "<p class='success'>Successfully read ppas.php file</p>";

// First locate the AJAX success callback
echo "<h2>Step 3: Updating form submission code...</h2>";

$updated = false;

// Look for the AJAX success callback
if (preg_match('/success\s*:\s*function\s*\(\s*response\s*\)\s*{/i', $ppasContent)) {
    echo "<p>Found AJAX success callback.</p>";
    
    // Update success callback to reset the form and switch to first tab
    $ajaxSuccessPattern = '/(success\s*:\s*function\s*\(\s*response\s*\)\s*{[^}]*?})(}\s*,)/is';
    $replacement = '$1
            // Reset form and switch to first tab
            $("#ppasForm")[0].reset();
            $("#formTabs a[href=\'#basic-info\']").tab("show");
            $2';
    
    $newContent = preg_replace($ajaxSuccessPattern, $replacement, $ppasContent);
    
    if ($newContent !== $ppasContent) {
        echo "<p class='success'>Successfully updated AJAX success callback to switch to first tab!</p>";
        $ppasContent = $newContent;
        $updated = true;
    } else {
        echo "<p class='warning'>AJAX success callback found but could not update it.</p>";
        echo "<p>Looking for alternative patterns...</p>";
    }
}

// If first attempt failed, try another common pattern
if (!$updated) {
    $resetFormPattern = '/(\$\("#ppasForm"\)\[0\]\.reset\(\);)/i';
    if (preg_match($resetFormPattern, $ppasContent)) {
        echo "<p>Found form reset code.</p>";
        
        $replacement = '$1
            // Switch to first tab
            $("#formTabs a[href=\'#basic-info\']").tab("show");';
        
        $newContent = preg_replace($resetFormPattern, $replacement, $ppasContent);
        
        if ($newContent !== $ppasContent) {
            echo "<p class='success'>Successfully added code to switch to first tab after form reset!</p>";
            $ppasContent = $newContent;
            $updated = true;
        } else {
            echo "<p class='warning'>Found form reset but could not update it.</p>";
        }
    }
}

// If still not updated, look for alert success messages
if (!$updated) {
    $alertSuccessPattern = '/(alert\s*\(\s*[\'"].*success.*[\'"]\s*\)\s*;)/i';
    if (preg_match($alertSuccessPattern, $ppasContent)) {
        echo "<p>Found success alert.</p>";
        
        $replacement = '$1
            // Reset form and switch to first tab
            $("#ppasForm")[0].reset();
            $("#formTabs a[href=\'#basic-info\']").tab("show");';
        
        $newContent = preg_replace($alertSuccessPattern, $replacement, $ppasContent);
        
        if ($newContent !== $ppasContent) {
            echo "<p class='success'>Successfully added code to reset form and switch to first tab after success alert!</p>";
            $ppasContent = $newContent;
            $updated = true;
        } else {
            echo "<p class='warning'>Found success alert but could not update it.</p>";
        }
    }
}

// If still not updated, let's add a more generic approach
if (!$updated) {
    echo "<p class='warning'>Could not find specific patterns to update. Applying a general fix...</p>";
    
    // Add a custom function to the end of the script
    $scriptEndPattern = '/(<\/script>\s*<\/body>)/i';
    $customFunction = '
    <script>
    // Custom function to handle form submission success
    function handleFormSubmitSuccess() {
        // Reset form and go to first tab
        $("#ppasForm")[0].reset();
        $("#formTabs a[href=\'#basic-info\']").tab("show");
    }
    
    // Override jQuery AJAX global success for our form
    $(document).ajaxSuccess(function(event, xhr, settings) {
        if (settings.url === "save_ppas.php") {
            handleFormSubmitSuccess();
        }
    });
    </script>
    $1';
    
    $newContent = preg_replace($scriptEndPattern, $customFunction, $ppasContent);
    
    if ($newContent !== $ppasContent) {
        echo "<p class='success'>Added a global AJAX success handler to manage form reset and tab navigation!</p>";
        $ppasContent = $newContent;
        $updated = true;
    } else {
        echo "<p class='error'>Could not add the global handler. Manual update required.</p>";
    }
}

// Write the updated content back to the file
if ($updated) {
    echo "<h2>Step 4: Saving updated ppas.php file...</h2>";
    if (file_put_contents('ppas.php', $ppasContent) !== false) {
        echo "<p class='success'>Successfully updated ppas.php!</p>";
    } else {
        echo "<p class='error'>Failed to write updated file!</p>";
        echo "<p>Please check file permissions.</p>";
    }
} else {
    echo "<h2>Step 4: No changes were made to ppas.php</h2>";
    echo "<p class='warning'>Could not identify the right patterns to update your file.</p>";
    echo "<p>You may need to manually update the ppas.php file to add tab switching after form submission.</p>";
    
    echo "<h3>Manual Fix Instructions:</h3>";
    echo "<pre>
Find your form submission code (usually in a $.ajax call) and add these lines 
in the success callback function:

// Reset form and switch to first tab
$(\"#ppasForm\")[0].reset();
$(\"#formTabs a[href='#basic-info']\").tab(\"show\");
</pre>";
}

// Final instructions
echo "<h2>Next Steps:</h2>";
if ($updated) {
    echo "<p class='success'>The fix has been applied! Now when you submit the form successfully:</p>";
    echo "<ol>";
    echo "<li>The form will reset (clearing all fields)</li>";
    echo "<li>You'll be automatically redirected to the first tab</li>";
    echo "</ol>";
} else {
    echo "<p>Apply the manual fix as described above.</p>";
}

echo "<p>If the fix doesn't work, you can restore the backup file:</p>";
echo "<pre>
1. Rename ppas.php.backup_redirect to ppas.php
2. Or copy its contents manually
</pre>";

echo "<div style='margin-top: 20px;'>";
echo "<a href='ppas.php' class='action-btn'>Go to PPAS Form</a> ";
echo "<a href='fix_ps_attribution_now.php' class='action-btn'>Fix PS Attribution</a> ";
echo "<a href='add_type_field.php' class='action-btn'>Fix Type Field</a>";
echo "</div>";

echo "</body></html>";
?> 