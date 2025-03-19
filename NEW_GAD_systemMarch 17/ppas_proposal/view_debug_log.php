<?php
// Set content type to text/plain for better readability
header('Content-Type: text/plain');

// Define the debug log path
$debug_file = __DIR__ . '/debug_log.txt';

// Check if clear parameter is set
if (isset($_GET['clear']) && $_GET['clear'] === 'true') {
    // Clear the log file
    file_put_contents($debug_file, "Log cleared at " . date('Y-m-d H:i:s') . "\n");
    echo "Debug log cleared at " . date('Y-m-d H:i:s') . "\n";
    echo "Refresh page without clear parameter to view the log.\n";
    exit;
}

// Check if the file exists
if (file_exists($debug_file)) {
    // Get file size
    $size = filesize($debug_file);
    echo "Debug log file size: " . number_format($size / 1024, 2) . " KB\n\n";
    
    // Display creation/modification time
    echo "Last modified: " . date("Y-m-d H:i:s", filemtime($debug_file)) . "\n\n";
    
    // Display the file content
    echo "===================== DEBUG LOG CONTENT =====================\n\n";
    echo file_get_contents($debug_file);
    
    // Add a link to clear the log (shown as text since we're in text/plain mode)
    echo "\n\n==========================================================\n";
    echo "To clear the log, navigate to: view_debug_log.php?clear=true\n";
} else {
    echo "Debug log file does not exist.\n";
}
?> 