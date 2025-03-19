<?php
require_once('../config.php');

header('Content-Type: application/json');
$response = array('success' => false, 'debug' => array());

try {
    // Check if tables exist
    $tables = array('programs', 'projects');
    foreach ($tables as $table) {
        $result = $conn->query("SHOW TABLES LIKE '$table'");
        $response['debug'][$table . '_exists'] = $result->num_rows > 0;
        
        if ($result->num_rows > 0) {
            // Get table structure
            $structure = $conn->query("DESCRIBE $table");
            $response['debug'][$table . '_structure'] = array();
            while ($row = $structure->fetch_assoc()) {
                $response['debug'][$table . '_structure'][] = $row;
            }
            
            // Get row count
            $count = $conn->query("SELECT COUNT(*) as count FROM $table");
            $response['debug'][$table . '_count'] = $count->fetch_assoc()['count'];
        }
    }
    
    $response['success'] = true;
} catch (Exception $e) {
    $response['success'] = false;
    $response['error'] = $e->getMessage();
}

echo json_encode($response, JSON_PRETTY_PRINT); 