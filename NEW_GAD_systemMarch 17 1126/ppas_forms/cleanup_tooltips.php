<?php
// Read the ppas.php file
$filename = __DIR__ . '/ppas.php';
$content = file_get_contents($filename);

// Define the pattern to look for (the duplicate tooltip initialization)
$pattern = <<<'EOD'

        // Initialize Bootstrap tooltips
        const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]');
        const tooltipList = [...tooltipTriggerList].map(tooltipTriggerEl => new bootstrap.Tooltip(tooltipTriggerEl));
        
        // Add tooltip for total duration field
        const totalDurationInput = document.getElementById('totalDuration');
        if (totalDurationInput) {
            totalDurationInput.setAttribute('title', 'Total Duration = Number of Days × Daily Hours (minus breaks if selected)');
            totalDurationInput.setAttribute('data-bs-toggle', 'tooltip');
            totalDurationInput.setAttribute('data-bs-placement', 'top');
            new bootstrap.Tooltip(totalDurationInput);
        }
EOD;

// Replace the pattern with an empty string
$replacement = '';
$new_content = str_replace($pattern, $replacement, $content);

// Write the modified content back to the file
if (file_put_contents($filename, $new_content)) {
    echo "Successfully removed duplicate tooltip code from ppas.php";
} else {
    echo "Failed to update ppas.php";
}
?> 