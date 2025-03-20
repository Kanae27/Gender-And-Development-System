<?php
// Set content type to plain text
header('Content-Type: text/plain');

// Path to the debug log file
$logFile = __DIR__ . '/debug_log.txt';

// Check if the log file exists
if (file_exists($logFile)) {
    // Read the file and output its contents
    $logContents = file_get_contents($logFile);
    
    // If log is empty
    if (empty($logContents)) {
        echo "Debug log is empty.";
    } else {
        // Get the last 100 lines or so
        $lines = explode("\n", $logContents);
        $lastLines = array_slice($lines, -100);
        echo implode("\n", $lastLines);
    }
} else {
    echo "Debug log file does not exist yet.";
}
?> 